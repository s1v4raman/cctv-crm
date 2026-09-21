<?php

namespace App\Http\Controllers;

use App\Services\InboundEmailTicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InboundEmailSupportController extends Controller
{
    protected InboundEmailTicketService $emailTicketService;

    public function __construct(InboundEmailTicketService $emailTicketService)
    {
        $this->emailTicketService = $emailTicketService;
    }

    /**
     * Webhook endpoint for inbound email services (SendGrid, Mailgun, Postmark, AWS SES, or custom forwarder).
     */
    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        // Support various webhook formats
        $data = [
            'from'    => $request->input('from') ?? $request->input('sender') ?? $request->input('From'),
            'email'   => $request->input('email') ?? $request->input('from_email'),
            'name'    => $request->input('name') ?? $request->input('from_name'),
            'subject' => $request->input('subject') ?? $request->input('Subject') ?? 'CCTV Inbound Email Support',
            'body'    => $request->input('body') ?? $request->input('text') ?? $request->input('html') ?? $request->input('stripped-text') ?? $request->input('Body'),
            'phone'   => $request->input('phone'),
        ];

        $ticket = $this->emailTicketService->processEmail($data);

        return response()->json([
            'success'   => true,
            'message'   => 'Inbound support email converted into ticket.',
            'ticket_no' => $ticket->ticket_no,
            'ticket_id' => $ticket->id,
            'lead'      => $ticket->lead->customer_name,
            'priority'  => $ticket->priority,
            'issue_type'=> $ticket->issue_type,
        ], 201);
    }

    /**
     * UI Simulation / Manual Email ingestion from CRM admin screen.
     */
    public function simulate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'subject'       => ['required', 'string', 'max:255'],
            'body'          => ['required', 'string', 'max:5000'],
            'priority'      => ['nullable', 'in:low,medium,high,critical'],
            'issue_type'    => ['nullable', 'string'],
        ]);

        $ticket = $this->emailTicketService->processEmail([
            'name'       => $validated['customer_name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'] ?? null,
            'subject'    => $validated['subject'],
            'body'       => $validated['body'],
            'priority'   => $validated['priority'] ?? null,
            'issue_type' => $validated['issue_type'] ?? null,
        ]);

        return redirect()->route('service-tickets.show', $ticket)
            ->with('success', "Inbound email from {$validated['customer_name']} successfully converted to Ticket #{$ticket->ticket_no}!");
    }
}
