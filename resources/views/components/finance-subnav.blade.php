{{-- Menu for the Finance module. Everything but Invoices is for admin and staff; salaries and payroll are admin-only. --}}
@props(['active' => null])

@php
    $office = auth()->user()?->isOffice();
    $admin = auth()->user()?->isAdmin();
    $items = array_values(array_filter([
        $office ? ['label' => 'Overview', 'url' => route('finance.hub'), 'active' => request()->routeIs('finance.hub')] : null,
        ['label' => 'Invoices', 'url' => route('invoices.index'), 'active' => request()->routeIs('invoices.*')],
        $office ? ['label' => 'Receivables', 'url' => route('finance.receivables.index'), 'active' => request()->routeIs('finance.receivables.*')] : null,
        $office ? ['label' => 'Payables', 'url' => route('finance.payables.index'), 'active' => request()->routeIs('finance.payables.*')] : null,
        $office ? ['label' => 'GST', 'url' => route('finance.gst.index'), 'active' => request()->routeIs('finance.gst.*')] : null,
        $office ? ['label' => 'Job costing', 'url' => route('finance.job-costing.index'), 'active' => request()->routeIs('finance.job-costing.*')] : null,
        $office ? ['label' => 'Petty cash', 'url' => route('finance.petty_cash.index'), 'active' => request()->routeIs('finance.petty_cash.*')] : null,
        $admin ? ['label' => 'Salaries', 'url' => route('finance.salaries.index'), 'active' => request()->routeIs('finance.salaries.*')] : null,
        $admin ? ['label' => 'Payroll', 'url' => route('finance.payroll.index'), 'active' => request()->routeIs('finance.payroll.*')] : null,
    ]));
@endphp

<x-section-nav :items="$items" label="Finance" />
