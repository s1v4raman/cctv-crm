<?php

namespace App\Console\Commands;

use App\Services\InboundEmailTicketService;
use Illuminate\Console\Command;

class InboundEmailSupportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:inbound-email 
                            {--email= : Customer email address} 
                            {--name= : Customer full name} 
                            {--subject= : Email subject} 
                            {--body= : Email body description}
                            {--priority= : Ticket priority (low, medium, high, critical)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingest an inbound customer support email and create a CRM Service Ticket';

    /**
     * Execute the console command.
     */
    public function handle(InboundEmailTicketService $service): int
    {
        $email = $this->option('email') ?: $this->ask('Enter Customer Email Address', 'client@example.com');
        $name = $this->option('name') ?: $this->ask('Enter Customer Name', 'Rajesh Sharma');
        $subject = $this->option('subject') ?: $this->ask('Enter Email Subject', 'Camera 3 showing black screen');
        $body = $this->option('body') ?: $this->ask('Enter Email Message', 'The main warehouse camera has stopped recording since yesterday.');
        $priority = $this->option('priority');

        $ticket = $service->processEmail([
            'email'    => $email,
            'name'     => $name,
            'subject'  => $subject,
            'body'     => $body,
            'priority' => $priority,
        ]);

        $this->info("✅ Inbound support email processed successfully!");
        $this->table(
            ['Field', 'Value'],
            [
                ['Ticket Number', $ticket->ticket_no],
                ['Customer Name', $ticket->lead->customer_name],
                ['Customer Email', $ticket->lead->email],
                ['Priority', $ticket->priority_label],
                ['Issue Category', $ticket->issue_type_label],
                ['Billing Type', $ticket->billing_type],
                ['Status', $ticket->status_label],
                ['Created At', $ticket->created_at->format('Y-m-d H:i:s')],
            ]
        );

        return Command::SUCCESS;
    }
}
