<?php

namespace App\Jobs;

use App\Models\ResourcePage;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ProcessPageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected ResourcePage $page) {}

    public function handle()
    {
        $this->page->update(['status' => 'processing', 'error_message' => null]);

        try {
            $fullPath = storage_path('app/' . $this->page->image_path);

            $tesseract = new TesseractOCR($fullPath);
            $tesseract->lang('ara+eng')
                ->psm(4)
                ->config('tessedit_unrej_any_wd', '1')
                ->config('preserve_interword_spaces', '1')
                ->oem(1);

            // Run for Text
            $ocrText = $tesseract->run();
            $ocrText = $this->removeArabicDiacritics($ocrText);
            $this->page->update([
                'text' => trim($ocrText),
                'status' => 'done'
            ]);

            $this->checkResourceCompletion();

        } catch (Exception $e) {
            Log::error('ProcessPageJob failed', ['page_id' => $this->page->id, 'error' => $e->getMessage()]);
            $this->page->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
    function removeArabicDiacritics($text)
    {
        return preg_replace('/[\x{0610}-\x{061A}\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $text);
    }
    protected function checkResourceCompletion()
    {
        $resource = $this->page->resource()->with('pages')->first();

        $allDone = !$resource->pages()->where('status', '!=', 'done')->exists();

        if ($allDone) {
            $resource->update(['status' => 'done']);
        }
    }
}
