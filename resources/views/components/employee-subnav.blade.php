{{-- Menu for the Employees module. Salaries and payroll are admin-only. --}}
@props(['active' => null])

@php
    $items = [
        ['label' => 'Overview', 'url' => route('employee.hub'), 'active' => request()->routeIs('employee.hub')],
        ['label' => 'Attendance', 'url' => route('attendance.index'), 'active' => request()->routeIs('attendance.index')],
        ['label' => 'Leave', 'url' => route('leaves.index'), 'active' => request()->routeIs('leaves.*')],
        ['label' => 'Expense claims', 'url' => route('finance.expenses.index'), 'active' => request()->routeIs('finance.expenses.*')],
    ];
    if (auth()->user()?->isAdmin()) {
        $items[] = ['label' => 'Salaries', 'url' => route('finance.salaries.index'), 'active' => request()->routeIs('finance.salaries.*')];
        $items[] = ['label' => 'Payroll', 'url' => route('finance.payroll.index'), 'active' => request()->routeIs('finance.payroll.*')];
    }
@endphp

<x-section-nav :items="$items" label="Employees" />
