<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AmcContract;
use App\Models\InstallationJob;
use App\Models\InstalledEquipment;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Unified Global Omnisearch across all CRM data models.
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));
        if (strlen($query) < 2) {
            return response()->json([
                'query'   => $query,
                'results' => [],
                'total'   => 0,
            ]);
        }

        $results = [];

        // 1. Leads & Customer Sites
        $leads = Lead::where(function ($q) use ($query) {
            $q->where('customer_name', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('site_address', 'like', "%{$query}%");
        })->take(4)->get();

        foreach ($leads as $lead) {
            $results[] = [
                'type'     => 'Leads & Customers',
                'title'    => $lead->customer_name,
                'subtitle' => "📞 {$lead->phone} " . ($lead->site_address ? "• {$lead->site_address}" : ''),
                'url'      => route('leads.show', $lead),
                'badge'    => ucfirst($lead->status),
                'icon'     => 'user',
            ];
        }

        // 2. Quotations & Deals
        $quotations = Quotation::with('lead')
            ->where(function($q) use ($query) {
                $q->where('quotation_no', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        foreach ($quotations as $quotation) {
            $results[] = [
                'type'     => 'Quotations & Deals',
                'title'    => $quotation->quotation_no,
                'subtitle' => ($quotation->lead?->customer_name ?? 'N/A') . ' • ₹' . number_format($quotation->total, 2),
                'url'      => route('quotations.show', $quotation),
                'badge'    => ucfirst($quotation->status),
                'icon'     => 'document',
            ];
        }

        // 3. Field Jobs & Installations
        $jobs = InstallationJob::with('quotation.lead')
            ->where(function($q) use ($query) {
                $q->where('job_no', 'like', "%{$query}%")
                  ->orWhereHas('quotation.lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        foreach ($jobs as $job) {
            $results[] = [
                'type'     => 'Tasks & Field Jobs',
                'title'    => $job->job_no,
                'subtitle' => ($job->quotation?->lead?->customer_name ?? 'N/A') . ' • ' . ucfirst(str_replace('_', ' ', $job->status)),
                'url'      => route('jobs.show', $job),
                'badge'    => 'Field Job',
                'icon'     => 'wrench',
            ];
        }

        // 4. Support Tickets (SLA)
        $tickets = ServiceTicket::with('lead')
            ->where(function($q) use ($query) {
                $q->where('ticket_no', 'like', "%{$query}%")
                  ->orWhere('title', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        foreach ($tickets as $ticket) {
            $results[] = [
                'type'     => 'Support Tickets',
                'title'    => $ticket->ticket_no . ' — ' . $ticket->title,
                'subtitle' => ($ticket->lead?->customer_name ?? 'N/A') . ' • ' . ucfirst($ticket->priority) . ' priority',
                'url'      => route('service-tickets.show', $ticket),
                'badge'    => ucfirst($ticket->status),
                'icon'     => 'ticket',
            ];
        }

        // 5. Cameras & Installed Equipment
        $equipments = InstalledEquipment::with('lead')
            ->where(function($q) use ($query) {
                $q->where('equipment_name', 'like', "%{$query}%")
                  ->orWhere('serial_number', 'like', "%{$query}%")
                  ->orWhere('mac_address', 'like', "%{$query}%")
                  ->orWhere('location_tag', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        foreach ($equipments as $equipment) {
            $results[] = [
                'type'     => 'Cameras & Equipment',
                'title'    => $equipment->equipment_name,
                'subtitle' => "S/N: " . ($equipment->serial_number ?: 'N/A') . " • " . ($equipment->lead?->customer_name ?? 'N/A'),
                'url'      => route('equipment.show', $equipment),
                'badge'    => 'Asset',
                'icon'     => 'camera',
            ];
        }

        // 6. Invoices & Billing
        $invoices = Invoice::with('quotation.lead')
            ->where(function($q) use ($query) {
                $q->where('invoice_no', 'like', "%{$query}%")
                  ->orWhereHas('quotation.lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        foreach ($invoices as $inv) {
            $results[] = [
                'type'     => 'Invoices & Billing',
                'title'    => $inv->invoice_no,
                'subtitle' => ($inv->quotation?->lead?->customer_name ?? 'N/A') . ' • Total: ₹' . number_format($inv->total, 2),
                'url'      => route('invoices.show', $inv),
                'badge'    => ucfirst($inv->status),
                'icon'     => 'receipt',
            ];
        }

        // 7. Site Surveys
        $surveys = SiteSurvey::with('lead')
            ->where(function($q) use ($query) {
                $q->where('site_address', 'like', "%{$query}%")
                  ->orWhere('contact_person', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(3)->get();

        foreach ($surveys as $survey) {
            $results[] = [
                'type'     => 'Site Surveys',
                'title'    => "Survey for " . ($survey->lead?->customer_name ?? 'Client'),
                'subtitle' => ($survey->site_address ?: 'No address') . " • " . ucfirst($survey->status),
                'url'      => route('site-surveys.show', $survey),
                'badge'    => 'Survey',
                'icon'     => 'clipboard',
            ];
        }

        // 8. AMC Maintenance Contracts
        $amcs = AmcContract::with('lead')
            ->where(function($q) use ($query) {
                $q->where('contract_no', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(3)->get();

        foreach ($amcs as $amc) {
            $results[] = [
                'type'     => 'AMC Contracts',
                'title'    => $amc->contract_no,
                'subtitle' => ($amc->lead?->customer_name ?? 'N/A') . ' • ' . ucfirst($amc->status),
                'url'      => route('amcs.show', $amc),
                'badge'    => 'AMC',
                'icon'     => 'shield',
            ];
        }

        // 9. Warehouse Products
        $products = Product::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('model_no', 'like', "%{$query}%")
              ->orWhere('sku', 'like', "%{$query}%");
        })->take(3)->get();

        foreach ($products as $prod) {
            $results[] = [
                'type'     => 'Inventory Items',
                'title'    => $prod->name,
                'subtitle' => "Model: " . ($prod->model_no ?: 'N/A') . " • Stock: {$prod->stock_quantity}",
                'url'      => route('inventory.index', ['search' => $prod->name]),
                'badge'    => 'Inventory',
                'icon'     => 'cube',
            ];
        }

        return response()->json([
            'query'   => $query,
            'results' => $results,
            'total'   => count($results),
        ]);
    }
}
