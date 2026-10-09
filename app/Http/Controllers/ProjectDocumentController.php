<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\Company;
use App\Models\ProjectAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentController extends Controller
{
    /**
     * Upload document or Delivery Challan to a project.
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'document_type' => 'required|in:delivery_challan,purchase_order,site_drawing,completion_signoff,invoice,other',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:51200', // max 50MB
            'dc_number' => 'nullable|string|max:100',
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
        $fileName = $uploadedFile->getClientOriginalName();
        $fileSize = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType();

        // Determine storage disk (Cloudflare R2 / S3 if configured, otherwise public disk)
        $disk = config('filesystems.disks.r2.key') ? 'r2' : 'public';
        $storagePath = "projects/{$project->id}/documents";
        $filePath = $uploadedFile->store($storagePath, $disk);

        $document = ProjectDocument::create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'document_type' => $validated['document_type'],
            'dc_number' => $validated['dc_number'] ?? null,
            'dc_date' => $validated['dc_date'] ?? null,
            'items_summary' => $validated['items_summary'] ?? null,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'storage_disk' => $disk,
            'uploaded_by' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
            'is_invoiced' => false,
        ]);

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
    public function download(ProjectDocument $document)
    {
        $disk = $document->storage_disk ?? 'public';

        if (!Storage::disk($disk)->exists($document->file_path)) {
            // Check fallback
            if (Storage::disk('public')->exists($document->file_path)) {
                $disk = 'public';
            } else {
                return back()->with('error', 'The file was not found on cloud or local storage.');
            }
        }

        return Storage::disk($disk)->download($document->file_path, $document->file_name);
    }

    /**
     * Preview project document in browser.
     */
    public function view(ProjectDocument $document)
    {
        $disk = $document->storage_disk ?? 'public';

        if (!Storage::disk($disk)->exists($document->file_path)) {
            if (Storage::disk('public')->exists($document->file_path)) {
                $disk = 'public';
            } else {
                return back()->with('error', 'The file was not found on storage.');
            }
        }

        return Storage::disk($disk)->response($document->file_path, $document->file_name);
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

        if (Storage::disk($disk)->exists($document->file_path)) {
            Storage::disk($disk)->delete($document->file_path);
        }

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
}
