<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\SiteSurvey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CctvEstimatorController extends Controller
{
    /**
     * Display the CCTV Storage, Cable & Network Estimator workspace.
     */
    public function index(Request $request): View
    {
        $leads = Lead::orderBy('customer_name')->get(['id', 'customer_name', 'phone', 'site_address', 'status']);
        $siteSurveys = SiteSurvey::with('lead:id,customer_name,phone')->latest()->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        // Selected Lead or Site Survey if provided via URL params
        $selectedLeadId = $request->query('lead_id');
        $selectedSurveyId = $request->query('survey_id');
        $initialData = [
            'lead_id' => $selectedLeadId ? (int)$selectedLeadId : null,
            'survey_id' => $selectedSurveyId ? (int)$selectedSurveyId : null,
            'camera_count' => null,
            'cable_length' => null,
        ];

        if ($selectedSurveyId) {
            $survey = SiteSurvey::find($selectedSurveyId);
            if ($survey) {
                $initialData['lead_id'] = $survey->lead_id;
                $initialData['camera_count'] = $survey->camera_count_recommended;
                $initialData['cable_length'] = (float) $survey->cable_length_estimate;
            }
        }

        return view('estimator.index', compact('leads', 'siteSurveys', 'products', 'initialData'));
    }

    /**
     * Convert the calculated BOM directly into a CRM Quotation.
     */
    public function convertToQuotation(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'lead_id' => ['required', 'exists:leads,id'],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit' => ['required', 'string', 'max:30'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $lead = Lead::findOrFail($request->input('lead_id'));

        $quotation = DB::transaction(function () use ($request, $lead) {
            $items = $request->input('items');
            $subtotal = 0;

            foreach ($items as $item) {
                $subtotal += (float) $item['quantity'] * (float) $item['unit_price'];
            }

            $requestedDiscount = (float) ($request->input('discount') ?? 0);
            $discount = min($requestedDiscount, $subtotal);

            $taxPercent = (float) ($request->input('tax_percent') ?? 18);
            $taxableAmount = $subtotal - $discount;
            $taxAmount = $taxableAmount * ($taxPercent / 100);
            $grandTotal = $taxableAmount + $taxAmount;

            $quotation = Quotation::create([
                'lead_id' => $lead->id,
                'quotation_no' => 'QT-' . now()->format('Ymd') . '-' . str_pad(
                    (string) (Quotation::count() + 1),
                    4,
                    '0',
                    STR_PAD_LEFT
                ),
                'quotation_date' => $request->input('quotation_date'),
                'valid_until' => $request->input('valid_until'),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $grandTotal,
                'status' => 'draft',
                'notes' => $request->input('notes') ?? 'Auto-generated from CCTV Storage & Auto-BOM Estimator.',
            ]);

            foreach ($items as $item) {
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];

                $quotation->items()->create([
                    'product_id' => !empty($item['product_id']) ? $item['product_id'] : null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => (float) $item['quantity'],
                    'unit' => $item['unit'] ?? 'Nos',
                    'unit_price' => (float) $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }

            $lead->update([
                'status' => 'quoted',
            ]);

            $quotation->statusHistories()->create([
                'changed_by' => Auth::id(),
                'from_status' => 'draft',
                'to_status' => 'draft',
                'notes' => 'Created via CCTV Auto-BOM Estimator Engine',
            ]);

            return $quotation;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Quotation generated successfully from BOM!',
                'quotation_id' => $quotation->id,
                'redirect_url' => route('quotations.show', $quotation),
            ]);
        }

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation generated successfully from CCTV Estimator & Auto-BOM.');
    }
}
