@php
    $isEditing = $product->exists;
@endphp

<form method="POST"
      action="{{ $isEditing ? route('products.update', $product) : route('products.store') }}"
      style="width: 100%; padding-bottom: 90px;">

    @csrf

    @if ($isEditing)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div style="margin-bottom: 24px; border: 1px solid #fecaca; background-color: #fef2f2; border-radius: 6px; padding: 16px; color: #991b1b;">
            <p style="margin: 0; font-size: 14px; font-weight: 700;">
                Please correct the highlighted product fields.
            </p>

            <ul style="margin-top: 8px; margin-bottom: 0; padding-left: 20px; font-size: 14px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
                Product / Service Name
            </label>

            <input type="text"
                   id="name"
                   name="name"
                   required
                   value="{{ old('name', $product->name) }}"
                   placeholder="Hikvision 4 MP Bullet Camera"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sku" class="block text-sm font-medium text-gray-700">
                SKU / Product Code
            </label>

            <input type="text"
                   id="sku"
                   name="sku"
                   value="{{ old('sku', $product->sku) }}"
                   placeholder="HK-4MP-BULLET"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

            @error('sku')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="unit" class="block text-sm font-medium text-gray-700">
                Unit
            </label>

            <select id="unit"
                    name="unit"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['Nos', 'Mtr', 'Box', 'Set', 'Job'] as $unit)
                    <option value="{{ $unit }}"
                        @selected(old('unit', $product->unit ?: 'Nos') === $unit)>
                        {{ $unit }}
                    </option>
                @endforeach
            </select>

            @error('unit')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="cost_price" class="block text-sm font-medium text-gray-700">
                Cost Price (₹)
            </label>

            <input type="number"
                   id="cost_price"
                   name="cost_price"
                   min="0"
                   step="0.01"
                   required
                   value="{{ old('cost_price', $product->cost_price ?? 0) }}"
                   placeholder="2900.00"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

            @error('cost_price')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="unit_price" class="block text-sm font-medium text-gray-700">
                Customer Unit Price (₹)
            </label>

            <input type="number"
                   id="unit_price"
                   name="unit_price"
                   min="0"
                   step="0.01"
                   required
                   value="{{ old('unit_price', $product->unit_price ?? 0) }}"
                   placeholder="3500.00"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

            @error('unit_price')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-end">
            <label class="mb-1 inline-flex items-center gap-3">
                <input type="hidden" name="is_active" value="0">

                <input type="checkbox"
                       name="is_active"
                       value="1"
                       @checked((int) old('is_active', $product->exists ? $product->is_active : 1) === 1)
                       style="height: 18px; width: 18px; accent-color: #4338ca;">

                <span class="text-sm font-semibold text-gray-700">
                    Available for new quotations
                </span>
            </label>
        </div>
    </div>

    <div class="mt-6">
        <label for="description" class="block text-sm font-medium text-gray-700">
            Description / Specification
        </label>

        <textarea id="description"
                  name="description"
                  rows="5"
                  placeholder="4 MP outdoor IR bullet CCTV camera"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $product->description) }}</textarea>

        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div style="position: sticky; bottom: 0; z-index: 30; display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 32px; border-top: 1px solid #d1d5db; background-color: #ffffff; padding: 18px 0 4px; box-shadow: 0 -8px 14px rgba(255,255,255,0.96);">
        <a href="{{ route('products.index') }}"
           style="display: inline-flex; align-items: center; justify-content: center; background-color: #ffffff; color: #1f2937 !important; border: 1px solid #9ca3af; border-radius: 6px; padding: 12px 22px; font-size: 14px; font-weight: 700; line-height: 20px; min-width: 100px; text-decoration: none; font-family: Arial, sans-serif; visibility: visible; opacity: 1;">
            Cancel
        </a>

        <button type="submit"
                style="display: inline-flex; align-items: center; justify-content: center; background-color: #4338ca !important; color: #ffffff !important; border: 1px solid #312e81; border-radius: 6px; padding: 12px 24px; font-size: 14px; font-weight: 700; line-height: 20px; min-width: 155px; font-family: Arial, sans-serif; visibility: visible; opacity: 1; cursor: pointer;">
            {{ $isEditing ? 'Update Product' : 'Save Product' }}
        </button>
    </div>
</form>