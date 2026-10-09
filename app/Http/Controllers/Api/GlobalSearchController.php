<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AmcContract;
use App\Models\InstalledEquipment;
use App\Models\InstallationJob;
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

        $results = array_merge(
            $this->searchLeads($query),
            $this->searchQuotations($query),
            $this->searchJobs($query),
            $this->searchTickets($query),
            $this->searchEquipment($query),
            $this->searchInvoices($query),
            $this->searchSurveys($query),
            $this->searchAmcs($query),
            $this->searchProducts($query)
        );

        return response()->json([
            'query'   => $query,
            'results' => $results,
            'total'   => count($results),
        ]);
    }

    private function searchLeads(string $query): array
    {
        $leads = Lead::where(function ($q) use ($query) {
            $q->where('customer_name', 'like', "%{$query}%")
              ->orWhere('phone', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('site_address', 'like', "%{$query}%");
        })->take(4)->get();

        $items = [];
        foreach ($leads as $lead) {
            $items[] = [
                'type'     => 'Leads & Customers',
                'title'    => $lead->customer_name,
                'subtitle' => "📞 {$lead->phone} " . ($lead->site_address ? "• {$lead->site_address}" : ''),
                'url'      => route('leads.show', $lead),
                'badge'    => ucfirst($lead->status),
                'icon'     => 'user',
            ];
        }
        return $items;
    }

    private function searchQuotations(string $query): array
    {
        $quotations = Quotation::with('lead')
            ->where(function ($q) use ($query) {
                $q->where('quotation_no', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        $items = [];
        foreach ($quotations as $quotation) {
            $items[] = [
                'type'     => 'Quotations & Deals',
                'title'    => $quotation->quotation_no,
                'subtitle' => ($quotation->lead?->customer_name ?? 'N/A') . ' • ₹' . number_format((float) $quotation->total, 2),
                'url'      => route('quotations.show', $quotation),
                'badge'    => ucfirst($quotation->status),
                'icon'     => 'document',
            ];
        }
        return $items;
    }

    private function searchJobs(string $query): array
    {
        $jobs = InstallationJob::with('quotation.lead')
            ->where(function ($q) use ($query) {
                $q->where('job_no', 'like', "%{$query}%")
                  ->orWhereHas('quotation.lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        $items = [];
        foreach ($jobs as $job) {
            $items[] = [
                'type'     => 'Tasks & Field Jobs',
                'title'    => $job->job_no,
                'subtitle' => ($job->quotation?->lead?->customer_name ?? 'N/A') . ' • ' . ucfirst(str_replace('_', ' ', $job->status)),
                'url'      => route('jobs.show', $job),
                'badge'    => 'Field Job',
                'icon'     => 'wrench',
            ];
        }
        return $items;
    }

    private function searchTickets(string $query): array
    {
        $tickets = ServiceTicket::with('lead')
            ->where(function ($q) use ($query) {
                $q->where('ticket_no', 'like', "%{$query}%")
                  ->orWhere('title', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        $items = [];
        foreach ($tickets as $ticket) {
            $items[] = [
                'type'     => 'Support Tickets',
                'title'    => $ticket->ticket_no . ' — ' . $ticket->title,
                'subtitle' => ($ticket->lead?->customer_name ?? 'N/A') . ' • ' . ucfirst($ticket->priority) . ' priority',
                'url'      => route('service-tickets.show', $ticket),
                'badge'    => ucfirst($ticket->status),
                'icon'     => 'ticket',
            ];
        }
        return $items;
    }

    private function searchEquipment(string $query): array
    {
        $equipments = InstalledEquipment::with('lead')
            ->where(function ($q) use ($query) {
                $q->where('equipment_name', 'like', "%{$query}%")
                  ->orWhere('serial_number', 'like', "%{$query}%")
                  ->orWhere('mac_address', 'like', "%{$query}%")
                  ->orWhere('location_tag', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        $items = [];
        foreach ($equipments as $equipment) {
            $items[] = [
                'type'     => 'Cameras & Equipment',
                'title'    => $equipment->equipment_name,
                'subtitle' => "S/N: " . ($equipment->serial_number ?: 'N/A') . " • " . ($equipment->lead?->customer_name ?? 'N/A'),
                'url'      => route('equipment.show', $equipment),
                'badge'    => 'Asset',
                'icon'     => 'camera',
            ];
        }
        return $items;
    }

    private function searchInvoices(string $query): array
    {
        $invoices = Invoice::with('quotation.lead')
            ->where(function ($q) use ($query) {
                $q->where('invoice_no', 'like', "%{$query}%")
                  ->orWhereHas('quotation.lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(4)->get();

        $items = [];
        foreach ($invoices as $inv) {
            $items[] = [
                'type'     => 'Invoices & Billing',
                'title'    => $inv->invoice_no,
                'subtitle' => ($inv->quotation?->lead?->customer_name ?? 'N/A') . ' • Total: ₹' . number_format((float) $inv->total, 2),
                'url'      => route('invoices.show', $inv),
                'badge'    => ucfirst($inv->status),
                'icon'     => 'receipt',
            ];
        }
        return $items;
    }

    private function searchSurveys(string $query): array
    {
        $surveys = SiteSurvey::with('lead')
            ->where(function ($q) use ($query) {
                $q->where('site_address', 'like', "%{$query}%")
                  ->orWhere('contact_person', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(3)->get();

        $items = [];
        foreach ($surveys as $survey) {
            $items[] = [
                'type'     => 'Site Surveys',
                'title'    => "Survey for " . ($survey->lead?->customer_name ?? 'Client'),
                'subtitle' => ($survey->site_address ?: 'No address') . " • " . ucfirst($survey->status),
                'url'      => route('site-surveys.show', $survey),
                'badge'    => 'Survey',
                'icon'     => 'clipboard',
            ];
        }
        return $items;
    }

    private function searchAmcs(string $query): array
    {
        $amcs = AmcContract::with('lead')
            ->where(function ($q) use ($query) {
                $q->where('contract_no', 'like', "%{$query}%")
                  ->orWhereHas('lead', fn($l) => $l->where('customer_name', 'like', "%{$query}%"));
            })
            ->take(3)->get();

        $items = [];
        foreach ($amcs as $amc) {
            $items[] = [
                'type'     => 'AMC Contracts',
                'title'    => $amc->contract_no,
                'subtitle' => ($amc->lead?->customer_name ?? 'N/A') . ' • ' . ucfirst($amc->status),
                'url'      => route('amcs.show', $amc),
                'badge'    => 'AMC',
                'icon'     => 'shield',
            ];
        }
        return $items;
    }

    private function searchProducts(string $query): array
    {
        $products = Product::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('model_no', 'like', "%{$query}%")
              ->orWhere('sku', 'like', "%{$query}%");
        })->take(3)->get();

        $items = [];
        foreach ($products as $prod) {
            $items[] = [
                'type'     => 'Inventory Items',
                'title'    => $prod->name,
                'subtitle' => "Model: " . ($prod->model_no ?: 'N/A') . " • Stock: {$prod->stock_quantity}",
                'url'      => route('inventory.index', ['search' => $prod->name]),
                'badge'    => 'Inventory',
                'icon'     => 'cube',
            ];
        }
        return $items;
    }
}
