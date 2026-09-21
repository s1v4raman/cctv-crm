<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAmcRequest;
use App\Models\AmcContract;
use App\Models\AmcVisit;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AmcContractController extends Controller
{
    public function index(Request $request)
    {
        $query = AmcContract::with('lead');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('contract_no', 'like', "%{$search}%")
                  ->orWhere('plan_type', 'like', "%{$search}%")
                  ->orWhereHas('lead', function($l) use ($search) {
                      $l->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('site_address', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $amcs = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('amcs.index', compact('amcs'));
    }

    public function create()
    {
        $leads = Lead::orderBy('customer_name')->get();
        return view('amcs.create', compact('leads'));
    }

    public function store(StoreAmcRequest $request)
    {
        $validated = $request->validated();

        $amc = DB::transaction(function () use ($validated) {
            $amc = AmcContract::create([
                ...$validated,
                'contract_no' => 'AMC-' . now()->format('Ymd') . '-' . str_pad((string) (AmcContract::count() + 1), 4, '0', STR_PAD_LEFT),
                'status' => 'active',
            ]);

            $amc->generateVisits();

            return $amc;
        });

        return redirect()->route('amcs.show', $amc)->with('status', 'AMC Contract registered and servicing visits generated.');
    }

    public function show(AmcContract $amc)
    {
        $amc->load(['lead', 'visits.assignedTechnician', 'serviceTickets.assignedTechnician']);
        $technicians = User::where('role', 'technician')->orderBy('name')->get();
        return view('amcs.show', compact('amc', 'technicians'));
    }

    public function updateStatus(Request $request, AmcContract $amc)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,active,expired,cancelled'],
        ]);

        $amc->update(['status' => $validated['status']]);

        return back()->with('status', 'AMC Contract status updated.');
    }

    public function assignVisitTechnician(Request $request, AmcVisit $visit)
    {
        $validated = $request->validate([
            'assigned_technician_id' => ['required', 'exists:users,id'],
        ]);

        $visit->update([
            'assigned_technician_id' => $validated['assigned_technician_id'],
        ]);

        return back()->with('status', 'Technician assigned to visit.');
    }

    public function completeVisit(Request $request, AmcVisit $visit)
    {
        $validated = $request->validate([
            'completion_notes' => ['required', 'string', 'max:1000'],
        ]);

        $visit->update([
            'status' => 'completed',
            'completion_notes' => $validated['completion_notes'],
            'completed_at' => now(),
        ]);

        return back()->with('status', 'Servicing visit logged as completed.');
    }

    public function destroy(AmcContract $amc)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $amc->delete();

        return redirect()->route('amcs.index')->with('status', 'AMC Contract deleted.');
    }
}
