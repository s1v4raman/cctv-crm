<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\Company;
use App\Models\ProjectAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;

class ProjectDocumentController extends Controller
{
    // Types a browser can show safely in its own tab; anything else is downloaded.
    private const INLINE_TYPES = ['application/pdf', 'image/png', 'image/jpeg', 'image/webp', 'image/gif'];

    /**
     * Upload document or Delivery Challan to a project.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:delivery_challan,purchase_order,site_drawing,completion_signoff,invoice,other',
            'title' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,webp,gif,doc,docx,xls,xlsx|max:20480', // max 20MB, a copy is kept in the database
            'dc_number' => 'required_if:document_type,delivery_challan|nullable|string|max:100',
            'dc_date' => 'nullable|date',
            'items_summary' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Duplicate DC check if type is delivery_challan
        if ($validated['document_type'] === 'delivery_challan' && !empty($validated['dc_number'])) {
            $companyId = $project->company_id;
            $existingDc = ProjectDocument::where('dc_number', $validated['dc_number'])
                ->whereHas('project', function ($q) use ($companyId) {
                    if ($companyId) {
                        $q->where('company_id', $companyId);
                    }
                })
                ->exists();

            if ($existingDc) {
                return back()->with('error', "Duplicate DC Alert: Delivery Challan #{$validated['dc_number']} already exists for this executing company!")->withInput();
            }
        }

        $uploadedFile = $request->file('file');
        $fileName = str_replace(['/', '\\'], '-', $uploadedFile->getClientOriginalName());
        $fileSize = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType();
        $extension = strtolower($uploadedFile->getClientOriginalExtension() ?: (string) $uploadedFile->guessExtension());
        $contents = $uploadedFile->get();

        // Determine storage disk (Cloudflare R2 / S3 if configured, otherwise public disk)
        $disk = config('filesystems.disks.r2.key') ? 'r2' : 'public';
        $storagePath = "projects/{$project->id}/documents";
        $filePath = $uploadedFile->store($storagePath, $disk);

        $title = !empty($validated['title']) ? trim($validated['title']) : null;
        if (!$title) {
            if ($validated['document_type'] === 'delivery_challan' && !empty($validated['dc_number'])) {
                $title = "Delivery Challan #{$validated['dc_number']}";
            } else {
                $title = pathinfo($fileName, PATHINFO_FILENAME);
            }
        }

        // The server disk is wiped on every restart, so the database copy is what keeps the file.
        $document = DB::transaction(function () use ($project, $validated, $title, $fileName, $filePath, $fileSize, $extension, $mimeType, $disk, $contents) {
            $document = ProjectDocument::create([
                'project_id' => $project->id,
                'title' => $title,
                'document_type' => $validated['document_type'],
                'dc_number' => $validated['dc_number'] ?? null,
                'dc_date' => $validated['dc_date'] ?? null,
                'items_summary' => $validated['items_summary'] ?? null,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_type' => $extension ?: 'file',
                'mime_type' => $mimeType,
                'storage_disk' => $disk,
                'uploaded_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
                'is_invoiced' => false,
            ]);
            $document->storedFile()->create(['data' => base64_encode($contents)]);

            return $document;
        });

        ProjectAuditLog::logChange(
            $project,
            'document_uploaded',
            null,
            null,
            "Document '{$document->title}' ({$document->document_type_label}" . ($document->dc_number ? " #{$document->dc_number}" : '') . ") uploaded"
        );

        return redirect()->route('projects.show', ['project' => $project, 'tab' => 'documents'])
            ->with('status', "Document '{$document->title}' uploaded successfully!");
    }

    /**
     * Ready to Invoice console: list all uninvoiced Delivery Challans.
     */
    public function toInvoice(Request $request)
    {
        $companyFilter = $request->input('company', 'all');
        $search = trim($request->input('search', ''));

        $query = ProjectDocument::where('document_type', 'delivery_challan')
            ->where('is_invoiced', false)
            ->with(['project.site', 'project.company', 'uploader']);

        if ($companyFilter !== 'all') {
            $query->whereHas('project', function ($q) use ($companyFilter) {
                $q->where('company_id', $companyFilter);
            });
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('dc_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('items_summary', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($pq) use ($search) {
                      $pq->where('title', 'like', "%{$search}%")
                         ->orWhere('project_code', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $uninvoicedDcs = $query->latest('dc_date')->get();

        // Group by executing company
        $groupedByCompany = $uninvoicedDcs->groupBy(function ($doc) {
            return $doc->project->company?->name ?? 'Unassigned Company';
        });

        $companies = Company::where('is_active', true)->get();
        $totalUninvoicedCount = $uninvoicedDcs->count();

        return view('documents.to_invoice', compact(
            'groupedByCompany',
            'uninvoicedDcs',
            'companies',
            'companyFilter',
            'search',
            'totalUninvoicedCount'
        ));
    }

    /**
     * Mark Delivery Challans as invoiced.
     */
    public function markInvoiced(Request $request)
    {
        $validated = $request->validate([
            'document_ids' => 'required|array|min:1',
            'document_ids.*' => 'exists:project_documents,id',
            'invoice_number' => 'required|string|max:100',
            'invoiced_at' => 'required|date',
        ]);

        $count = 0;
        foreach ($validated['document_ids'] as $docId) {
            $doc = ProjectDocument::find($docId);
            if ($doc) {
                $doc->update([
                    'is_invoiced' => true,
                    'invoice_number' => $validated['invoice_number'],
                    'invoiced_at' => $validated['invoiced_at'],
                ]);
                $count++;

                ProjectAuditLog::logChange(
                    $doc->project,
                    'dc_invoiced',
                    null,
                    null,
                    "DC #{$doc->dc_number} marked invoiced with Invoice #{$validated['invoice_number']}"
                );
            }
        }

        return back()->with('status', "{$count} Delivery Challans successfully marked as invoiced with Invoice #{$validated['invoice_number']}!");
    }

    /**
     * Download project document.
     */
    public function download(ProjectDocument $document): Response
    {
        return $this->fileResponse($document, false);
    }

    /**
     * Preview project document in browser.
     */
    public function view(ProjectDocument $document): Response
    {
        return $this->fileResponse($document, true);
    }

    /**
     * Delete document.
     */
    public function destroy(ProjectDocument $document)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can delete project documents.');
        }

        $project = $document->project;
        $title = $document->title;
        $disk = $document->storage_disk ?? 'public';

        if (config("filesystems.disks.{$disk}") && Storage::disk($disk)->exists($document->file_path)) {
            Storage::disk($disk)->delete($document->file_path);
        }

        $document->storedFile()->delete();
        $document->delete();

        ProjectAuditLog::logChange(
            $project,
            'document_deleted',
            null,
            null,
            "Document '{$title}' removed"
        );

        return back()->with('status', "Document '{$title}' removed.");
    }

    private function fileResponse(ProjectDocument $document, bool $inline): Response
    {
        $contents = $document->fileContents();
        if ($contents === null) {
            return response()->view('documents.file_missing', ['document' => $document], 404);
        }

        $type = $document->contentType();
        $disposition = $inline && in_array($type, self::INLINE_TYPES, true) ? 'inline' : 'attachment';
        $name = str_replace(['/', '\\'], '-', $document->file_name) ?: 'document';
        $asciiName = str_replace('%', '', Str::ascii($name)) ?: 'document';

        return response($contents, 200, [
            'Content-Type' => $type,
            'Content-Length' => strlen($contents),
            'Content-Disposition' => HeaderUtils::makeDisposition($disposition, $name, $asciiName),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
