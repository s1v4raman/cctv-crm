<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;

class GenerateSecurityGuidePdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:generate-pdf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the CCTV CRM Manual Security Verification Guide PDF';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Rendering Manual Security Verification Guide PDF...');

        $pdf = Pdf::loadView('pdf.security_verification_guide');
        $pdf->setPaper('a4', 'portrait');
        $bytes = $pdf->output();

        $dest1 = base_path('CCTV_CRM_Manual_Security_Verification_Guide.pdf');
        file_put_contents($dest1, $bytes);
        $this->info("Saved to project root: {$dest1} (" . strlen($bytes) . " bytes)");

        $artifactDir = 'C:/Users/ADMIN/.gemini/antigravity-ide/brain/4e599b33-c5e8-4a31-bdec-90963cc04e02';
        if (is_dir($artifactDir)) {
            $dest2 = $artifactDir . '/CCTV_CRM_Manual_Security_Verification_Guide.pdf';
            file_put_contents($dest2, $bytes);
            $this->info("Saved to artifacts directory: {$dest2}");
        }

        return self::SUCCESS;
    }
}
