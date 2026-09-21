<div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-500">
                Quotation Status
            </p>

            <p class="mt-1 text-lg font-bold capitalize text-gray-900">
                {{ $quotation->status }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">

            {{-- Draft status --}}
            @if ($quotation->status === 'draft')
                <a href="{{ route('quotations.edit', $quotation) }}"
                   style="display: inline-flex; align-items: center; justify-content: center; background-color: #ffffff; color: #4338ca !important; border: 1px solid #4338ca; border-radius: 6px; padding: 9px 15px; font-size: 13px; font-weight: 700; text-decoration: none;">
                    Edit Quotation
                </a>

                <form method="POST"
                      action="{{ route('quotations.markSent', $quotation) }}">
                    @csrf
                    @method('PATCH')

                    <button type="submit"
                            style="display: inline-flex; align-items: center; justify-content: center; background-color: #4338ca; color: #ffffff !important; border: 1px solid #312e81; border-radius: 6px; padding: 9px 15px; font-size: 13px; font-weight: 700; cursor: pointer;">
                        Mark as Sent
                    </button>
                </form>
            @endif

            {{-- Sent status --}}
            @if ($quotation->status === 'sent')
                <form method="POST"
                      action="{{ route('quotations.accept', $quotation) }}">
                    @csrf

                    <button type="submit"
                            onclick="return confirm('Confirm that the customer accepted this quotation?');"
                            style="display: inline-flex; align-items: center; justify-content: center; background-color: #15803d; color: #ffffff !important; border: 1px solid #166534; border-radius: 6px; padding: 9px 15px; font-size: 13px; font-weight: 700; cursor: pointer;">
                        Mark as Accepted
                    </button>
                </form>

                <details class="relative">
                    <summary style="display: inline-flex; align-items: center; justify-content: center; background-color: #dc2626; color: #ffffff !important; border: 1px solid #b91c1c; border-radius: 6px; padding: 9px 15px; font-size: 13px; font-weight: 700; cursor: pointer;">
                        Mark as Rejected
                    </summary>

                    <form method="POST"
                          action="{{ route('quotations.reject', $quotation) }}"
                          class="absolute right-0 z-50 mt-3 w-80 rounded-md border border-red-200 bg-white p-4 shadow-xl">
                        @csrf

                        <label for="rejection_reason"
                               class="block text-sm font-semibold text-gray-700">
                            Rejection Reason
                        </label>

                        <textarea id="rejection_reason"
                                  name="rejection_reason"
                                  rows="3"
                                  required
                                  placeholder="Example: Price too high"
                                  class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>

                        <div class="mt-3 flex justify-end">
                            <button type="submit"
                                    style="display: inline-flex; align-items: center; justify-content: center; background-color: #dc2626; color: #ffffff !important; border: 1px solid #b91c1c; border-radius: 6px; padding: 9px 15px; font-size: 13px; font-weight: 700; cursor: pointer;">
                                Confirm Rejection
                            </button>
                        </div>
                    </form>
                </details>
            @endif

            {{-- Accepted status --}}
            @if ($quotation->status === 'accepted')
                @if ($quotation->installationJob)
                    <a href="{{ route('jobs.show', $quotation->installationJob) }}"
                       style="display: inline-flex; align-items: center; justify-content: center; background-color: #374151; color: #ffffff !important; border: 1px solid #1f2937; border-radius: 6px; padding: 9px 15px; font-size: 13px; font-weight: 700; text-decoration: none;">
                        View Job {{ $quotation->installationJob->job_no }}
                    </a>
                @else
                    <form method="POST"
                          action="{{ route('jobs.store', $quotation) }}">
                        @csrf

                        <button type="submit"
                                style="display: inline-flex; align-items: center; justify-content: center; background-color: #4338ca; color: #ffffff !important; border: 1px solid #312e81; border-radius: 6px; padding: 9px 15px; font-size: 13px; font-weight: 700; cursor: pointer;">
                            Create Installation Job
                        </button>
                    </form>
                @endif
            @endif

            {{-- Rejected status --}}
            @if ($quotation->status === 'rejected')
                <span style="display: inline-flex; align-items: center; border-radius: 9999px; background-color: #fee2e2; color: #b91c1c; padding: 8px 14px; font-size: 13px; font-weight: 700;">
                    Customer Rejected This Quotation
                </span>
            @endif

            {{-- Expired status --}}
            @if ($quotation->status === 'expired')
                <span style="display: inline-flex; align-items: center; border-radius: 9999px; background-color: #ffedd5; color: #c2410c; padding: 8px 14px; font-size: 13px; font-weight: 700;">
                    Quotation Expired
                </span>
            @endif
        </div>
    </div>

    @if ($quotation->status === 'rejected' && $quotation->rejection_reason)
        <div class="mt-4 rounded-md border border-red-200 bg-red-50 p-4">
            <p class="text-sm font-bold text-red-800">
                Rejection Reason
            </p>

            <p class="mt-1 text-sm text-red-700">
                {{ $quotation->rejection_reason }}
            </p>
        </div>
    @endif
</div>