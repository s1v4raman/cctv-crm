<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rma_claims', function (Blueprint $table) {
            $table->id();
            $table->string('rma_no')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers');
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('installed_equipment_id')->nullable()->constrained('installed_equipment')->nullOnDelete();
            $table->foreignId('service_ticket_id')->nullable()->constrained('service_tickets')->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            
            $table->string('faulty_serial_number');
            $table->string('faulty_mac_address')->nullable();
            $table->text('issue_description');
            $table->string('fault_category')->default('sensor_defect'); // no_power, sensor_defect, ir_led_failure, firmware_crash, port_damage, lens_blur, other
            $table->enum('warranty_status_at_claim', ['under_warranty', 'out_of_warranty', 'extended_warranty'])->default('under_warranty');
            $table->enum('status', ['draft', 'shipped_to_vendor', 'in_vendor_repair', 'replaced', 'repaired', 'credit_note', 'rejected', 'closed'])->default('draft');

            // Vendor Tracking & Shipping Details
            $table->string('vendor_rma_ref')->nullable();
            $table->string('shipping_courier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->date('dispatched_date')->nullable();
            $table->date('expected_return_date')->nullable();
            $table->date('received_from_vendor_date')->nullable();

            // Resolution & Inward Details
            $table->enum('resolution_type', ['none', 'replacement', 'repaired_unit', 'credit_note', 'rejected_damage'])->default('none');
            $table->string('replacement_serial_number')->nullable();
            $table->string('replacement_mac_address')->nullable();
            $table->date('replacement_warranty_expiry')->nullable();
            $table->text('vendor_repair_notes')->nullable();
            $table->decimal('service_cost', 10, 2)->default(0);

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rma_claims');
    }
};
