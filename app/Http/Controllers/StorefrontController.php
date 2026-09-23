<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Product;
use App\Models\ServiceTicket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    /**
     * Display the public Amazon-style CCTV Storefront homepage.
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Port 8001 is designated strictly for Admin, Technician & Employee ERP Suite.
        // If accessed on port 8001, redirect directly to Login (or internal dashboard if already authenticated).
        if ((int) $request->getPort() === 8001) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->role === 'technician') {
                    return redirect()->route('technician.dashboard');
                }
                if ($user->isCustomer()) {
                    return redirect()->route('portal.dashboard');
                }
                return redirect()->route('dashboard');
            }

            return redirect()->route('staff.login');
        }

        $search = trim((string) $request->input('q', ''));
        $category = (string) $request->input('category', 'all');
        $sort = (string) $request->input('sort', 'featured');

        $query = Product::where('is_active', true);

        // Keyword Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Category Filtering
        if ($category !== 'all' && $category !== '') {
            if ($category === 'smart_wifi') {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%PTZ%')
                        ->orWhere('name', 'like', '%Dome%')
                        ->orWhere('name', 'like', '%Wi-Fi%')
                        ->orWhere('name', 'like', '%Wireless%')
                        ->orWhere('sku', 'like', '%PTZ%')
                        ->orWhere('sku', 'like', '%DOME%');
                });
            } elseif ($category === 'outdoor_bullet') {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%Bullet%')
                        ->orWhere('name', 'like', '%Outdoor%')
                        ->orWhere('sku', 'like', '%BUL%')
                        ->orWhere('sku', 'like', 'HK-%')
                        ->orWhere('sku', 'like', 'DAH-%');
                });
            } elseif ($category === 'color_night') {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%ColorVu%')
                        ->orWhere('name', 'like', '%Full-Color%')
                        ->orWhere('name', 'like', '%Color%')
                        ->orWhere('name', 'like', '%Night%');
                });
            } elseif ($category === 'nvr_kits') {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%NVR%')
                        ->orWhere('name', 'like', '%DVR%')
                        ->orWhere('name', 'like', '%Kit%')
                        ->orWhere('sku', 'like', 'NVR%')
                        ->orWhere('sku', 'like', 'DVR%');
                });
            } elseif ($category === 'storage') {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%Hard Disk%')
                        ->orWhere('name', 'like', '%HDD%')
                        ->orWhere('name', 'like', '%Storage%')
                        ->orWhere('sku', 'like', 'HDD%');
                });
            } elseif ($category === 'accessories') {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%PoE%')
                        ->orWhere('name', 'like', '%Switch%')
                        ->orWhere('name', 'like', '%Power%')
                        ->orWhere('name', 'like', '%Rack%')
                        ->orWhere('name', 'like', '%Cable%')
                        ->orWhere('name', 'like', '%Connector%')
                        ->orWhere('sku', 'like', 'POE%')
                        ->orWhere('sku', 'like', 'UPS%')
                        ->orWhere('sku', 'like', 'CABLE%')
                        ->orWhere('sku', 'like', 'RACK%');
                });
            }
        }

        // Sorting
        if ($sort === 'name') {
            $query->orderBy('name', 'asc');
        } elseif ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sort === 'warranty') {
            $query->orderBy('default_warranty_months', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('unit_price', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('unit_price', 'desc');
        } else {
            $query->orderByRaw('CASE WHEN stock_quantity > 0 THEN 0 ELSE 1 END')
                ->orderBy('id', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();

        // Calculate Amazon-style dynamic display specs (MRP strike price, rating, reviews count, deal badge)
        $products->getCollection()->transform(function ($product) {
            $price = (float) $product->unit_price;
            // Generate a realistic MRP (25-35% higher) for the strike-through Amazon effect
            $multiplier = 1.30 + ((crc32($product->name ?? (string) $product->id) % 10) / 100);
            $mrp = round($price * $multiplier, -1);
            if ($mrp <= $price) {
                $mrp = round($price * 1.30);
            }
            $discountPercent = $mrp > 0 ? round((($mrp - $price) / $mrp) * 100) : 25;

            // Generate deterministic rating (4.6 to 4.9) and review count based on product ID
            $rating = 4.5 + ((crc32((string) $product->id) % 5) / 10);
            $reviewsCount = 45 + (crc32($product->name ?? '') % 400);

            // Badges
            $badge = null;
            if ($product->id % 5 === 0) {
                $badge = 'Amazon\'s Choice';
            } elseif ($product->id % 3 === 0) {
                $badge = 'Best Seller';
            } elseif (str_contains(strtolower($product->name), 'colorvu') || str_contains(strtolower($product->name), '5 mp')) {
                $badge = '4K Pro Series';
            } elseif ($discountPercent >= 28) {
                $badge = 'Limited Deal';
            }

            $product->amazon_mrp = $mrp;
            $product->amazon_discount = $discountPercent;
            $product->amazon_rating = number_format($rating, 1);
            $product->amazon_reviews = $reviewsCount;
            $product->amazon_badge = $badge;

            return $product;
        });

        // Category Counters
        $categoryCounts = [
            'all' => Product::where('is_active', true)->count(),
            'smart_wifi' => Product::where('is_active', true)->where(function ($q) {
                $q->where('name', 'like', '%PTZ%')->orWhere('name', 'like', '%Dome%')->orWhere('name', 'like', '%Wi-Fi%');
            })->count(),
            'outdoor_bullet' => Product::where('is_active', true)->where(function ($q) {
                $q->where('name', 'like', '%Bullet%')->orWhere('name', 'like', '%Outdoor%');
            })->count(),
            'color_night' => Product::where('is_active', true)->where(function ($q) {
                $q->where('name', 'like', '%ColorVu%')->orWhere('name', 'like', '%Full-Color%')->orWhere('name', 'like', '%5 MP%');
            })->count(),
            'nvr_kits' => Product::where('is_active', true)->where(function ($q) {
                $q->where('name', 'like', '%NVR%')->orWhere('name', 'like', '%DVR%');
            })->count(),
            'storage' => Product::where('is_active', true)->where(function ($q) {
                $q->where('name', 'like', '%Hard Disk%')->orWhere('name', 'like', '%HDD%');
            })->count(),
            'accessories' => Product::where('is_active', true)->where(function ($q) {
                $q->where('name', 'like', '%PoE%')->orWhere('name', 'like', '%Switch%')->orWhere('name', 'like', '%Cable%')->orWhere('name', 'like', '%Rack%');
            })->count(),
        ];

        return view('welcome', compact(
            'products',
            'search',
            'category',
            'sort',
            'categoryCounts'
        ));
    }

    /**
     * Handle quick guest inquiry, package quote, or free site survey consultation.
     */
    public function inquire(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone'         => ['required', 'string', 'max:30'],
            'email'         => ['nullable', 'email', 'max:255'],
            'site_address'  => ['nullable', 'string', 'max:500'],
            'service_type'  => ['nullable', 'string', 'max:100'],
            'product_name'  => ['nullable', 'string', 'max:255'],
            'property_type' => ['nullable', 'string', 'max:100'],
            'camera_count'  => ['nullable', 'string', 'max:50'],
            'notes'         => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        // Check if existing lead exists with this phone or email
        $lead = null;
        if (!empty($validated['phone'])) {
            $lead = Lead::where('phone', $validated['phone'])->first();
        }
        if (!$lead && !empty($validated['email'])) {
            $lead = Lead::where('email', $validated['email'])->first();
        }

        $serviceTitle = $validated['service_type'] ?? 'CCTV Package Inquiry';
        $productNote = !empty($validated['product_name']) ? "Product: {$validated['product_name']}\n" : '';
        $propNote = !empty($validated['property_type']) ? "Property: {$validated['property_type']}\n" : '';
        $camNote = !empty($validated['camera_count']) ? "Required Cameras: {$validated['camera_count']}\n" : '';
        $customNote = !empty($validated['notes']) ? "Customer Note: {$validated['notes']}\n" : '';

        $fullNotes = "Storefront Inquiry Received:\n" .
            "• Type: {$serviceTitle}\n" .
            $productNote .
            $propNote .
            $camNote .
            $customNote .
            "• Submitted At: " . now()->format('Y-m-d H:i:s');

        if (!$lead) {
            $lead = Lead::create([
                'customer_name' => $validated['customer_name'],
                'phone'         => $validated['phone'],
                'email'         => $validated['email'] ?? null,
                'site_address'  => $validated['site_address'] ?? 'Customer Site',
                'source'        => 'website',
                'status'        => 'new',
                'notes'         => $fullNotes,
            ]);

            if ($user && !$user->lead_id) {
                $user->update(['lead_id' => $lead->id]);
            }
        } else {
            $lead->update([
                'customer_name' => $validated['customer_name'] ?: $lead->customer_name,
                'notes'         => $lead->notes . "\n\n---\n" . $fullNotes,
                'status'        => 'contacted',
            ]);
            if (!empty($validated['site_address']) && ($lead->site_address === 'Customer Site' || empty($lead->site_address))) {
                $lead->update(['site_address' => $validated['site_address']]);
            }
        }

        // Generate a Service Ticket or Task for immediate CRM follow-up
        $yearMonth = date('Ym');
        $lastTicket = ServiceTicket::where('ticket_no', 'like', "TCK-{$yearMonth}-%")->latest('id')->first();
        $seq = 1;
        if ($lastTicket) {
            $parts = explode('-', $lastTicket->ticket_no);
            $seq = isset($parts[2]) ? ((int) $parts[2]) + 1 : 1;
        }
        $ticketNo = sprintf('TCK-%s-%04d', $yearMonth, $seq);

        ServiceTicket::create([
            'ticket_no'     => $ticketNo,
            'lead_id'       => $lead->id,
            'created_by_id' => $user ? $user->id : null,
            'title'         => "{$serviceTitle} - {$validated['customer_name']}",
            'issue_type'    => 'other',
            'priority'      => 'high',
            'status'        => 'open',
            'description'   => $fullNotes,
            'billing_type'  => 'billable',
        ]);

        return redirect()->back()->with('status', "Thank you, {$validated['customer_name']}! Your request for {$serviceTitle} has been received. Our security specialist will contact you on {$validated['phone']} within 15 minutes. (Ref #{$ticketNo})");
    }
}
