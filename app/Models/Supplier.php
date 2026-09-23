<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'gst_number',
        'address',
        'city',
        'state',
        'payment_terms',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class)->latest();
    }

    public function rmaClaims(): HasMany
    {
        return $this->hasMany(RmaClaim::class)->latest();
    }

    public function vendorPayments(): HasMany
    {
        return $this->hasMany(VendorPayment::class)->latest('payment_date');
    }

    public function totalPurchaseVolume(): float
    {
        return (float) $this->purchaseOrders()
            ->whereIn('status', ['ordered', 'partially_received', 'received'])
            ->sum('total');
    }

    public function balancePayable(): float
    {
        $orders = $this->purchaseOrders()
            ->whereIn('status', ['ordered', 'partially_received', 'received'])
            ->get();

        $total = (float) $orders->sum('total');
        $paid = (float) $orders->sum('amount_paid');

        return max(0.0, $total - $paid);
    }
}
