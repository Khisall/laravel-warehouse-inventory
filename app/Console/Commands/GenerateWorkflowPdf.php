<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateWorkflowPdf extends Command
{
    protected $signature = 'docs:generate-pdf';
    protected $description = 'Generate standalone PDF documentation for warehouse workflow';

    public function handle(): int
    {
        $this->info('Rendering Blade view docs.workflow...');
        $html = view('docs.workflow')->render();

        $docsDir = public_path('docs');
        if (!file_exists($docsDir)) {
            mkdir($docsDir, 0755, true);
        }

        $tempHtml = $docsDir . DIRECTORY_SEPARATOR . 'temp_workflow.html';
        $outputPdf = $docsDir . DIRECTORY_SEPARATOR . 'DOKUMENTASI_WORKFLOW_SISTEM_GUDANG_INVENTORY.pdf';

        file_put_contents($tempHtml, $html);
        $this->info("Generated temp HTML ({filesize($tempHtml)} bytes)");

        // Search for Microsoft Edge
        $edgePaths = [
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        ];

        $edgeExe = null;
        foreach ($edgePaths as $path) {
            if (file_exists($path)) {
                $edgeExe = $path;
                break;
            }
        }

        if (!$edgeExe) {
            $this->error('Microsoft Edge executable not found.');
            return 1;
        }

        $this->info("Using Edge binary at: {$edgeExe}");
        $this->info('Exporting PDF via Headless Chromium...');

        $cmd = sprintf(
            '"%s" --headless=new --disable-gpu --run-all-compositor-stages-before-draw --no-pdf-header-footer --print-to-pdf="%s" "%s"',
            $edgeExe,
            $outputPdf,
            $tempHtml
        );

        exec($cmd, $output, $returnCode);

        if (file_exists($outputPdf)) {
            $pdfSize = round(filesize($outputPdf) / 1024, 2);
            $this->info("SUCCESS! PDF generated successfully: {$outputPdf} ({$pdfSize} KB)");
            @unlink($tempHtml);
            return 0;
        }

        $this->error("Failed to generate PDF. Exit code: {$returnCode}");
        return 1;
    }
}
