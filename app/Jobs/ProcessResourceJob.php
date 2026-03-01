<?php

namespace App\Jobs;

use App\Models\Resource;
use App\Jobs\ProcessPageExtractionJob;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessResourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Resource $resource) {}

    public function handle()
    {
        $this->resource->update([
            'status' => 'processing',
            'error_message' => null
        ]);

        try {
            $fullPath = storage_path('app/private/' . $this->resource->path);

            if (!file_exists($fullPath)) {
                throw new Exception("File not found at: {$fullPath}");
            }

            $numPages = $this->getPdfPageCount($fullPath);

            Log::info("Dispatching {$numPages} page jobs for Resource {$this->resource->id}");

            for ($i = 1; $i <= $numPages; $i++) {
                ProcessPageExtractionJob::dispatch($this->resource, $i, $this->resource->force_ocr ?? false);
            }

            $this->resource->update(['status' => 'processing']);

        } catch (Exception $e) {
            Log::error('ProcessResourceJob failed', [
                'resource_id' => $this->resource->id,
                'error' => $e->getMessage(),
            ]);

            $this->resource->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function getPdfPageCount(string $pdfPath): int
    {
        $cmd = "pdfinfo " . escapeshellarg($pdfPath) . " | grep Pages:";
        exec($cmd, $output, $return);

        if ($return !== 0 || empty($output)) {
            throw new Exception("Failed to get PDF page count.");
        }

        $parts = preg_split('/\s+/', trim($output[0]));
        return (int) end($parts);
    }
}
