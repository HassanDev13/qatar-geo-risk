<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Exception;

class ExtractTermsAction
{
    public function execute(UploadedFile $file, bool $useGpt, bool $useTesseract, ?int $page): array
    {
        $path = $file->store('uploads');
        $text = '';

        try {
            Log::info('ExtractTermsAction started', [
                'file' => $file->getClientOriginalName(),
                'use_tesseract' => $useTesseract,
                'use_gpt' => $useGpt,
                'page' => $page
            ]);

            if ($useTesseract) {
                $text = $this->extractWithTesseract($path, $page);
            }

            if ($useGpt) {
                $prompt = $this->generatePrompt($text, $page);
                $text = $this->processWithGpt($prompt);
            }

            Log::info('Extraction and processing completed', [
                'length' => strlen($text),
            ]);
        } catch (Exception $e) {
            Log::error('Error during term extraction', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => 'An error occurred while extracting terms.',
                'details' => $e->getMessage(),
            ];
        }

        return [
            'text' => $text,
            'source' => basename($path),
        ];
    }

    protected function extractWithTesseract(string $path, ?int $page): string
    {
        $fullPath = storage_path('app/private/' . $path);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $text = '';

        try {
            if ($extension === 'pdf') {
                Log::info('PDF detected — converting to images', ['path' => $fullPath]);

                $outputDir = storage_path('app/tmp/pdf_pages_' . uniqid());
                if (!is_dir($outputDir)) {
                    mkdir($outputDir, 0777, true);
                }

                if ($page !== null) {
                    $command = "pdftoppm -png -r 300 -f {$page} -l {$page} "
                        . escapeshellarg($fullPath) . " "
                        . escapeshellarg($outputDir . '/page');
                } else {
                    $command = "pdftoppm -png -r 300 "
                        . escapeshellarg($fullPath) . " "
                        . escapeshellarg($outputDir . '/page');
                }

                exec($command, $output, $returnCode);

                if ($returnCode !== 0) {
                    throw new Exception('Failed to convert PDF to images.');
                }

                $images = glob("{$outputDir}/page-*.png");

                if (empty($images)) {
                    throw new Exception('No images were generated from PDF.');
                }

                foreach ($images as $imagePath) {
                    if (!file_exists($imagePath)) {
                        continue;
                    }
                    $ocrText = (new TesseractOCR($imagePath))
                        ->lang('ara+eng')
                        ->run();
                    $text .= "\n" . trim($ocrText);
                    unlink($imagePath);
                }

                @rmdir($outputDir);

                Log::info('PDF OCR completed', [
                    'chars_extracted' => strlen($text),
                ]);
            } else {
                Log::info('Image file detected — starting OCR', ['path' => $fullPath]);

                $text = (new TesseractOCR($fullPath))
                    ->lang('ara+eng')
                    ->run();

                $text = trim($text);

                Log::info('Image OCR completed', [
                    'chars_extracted' => strlen($text),
                ]);
            }

            return $text;
        } catch (Exception $e) {
            Log::error('Tesseract OCR failed', [
                'path' => $fullPath,
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Tesseract OCR extraction failed: ' . $e->getMessage());
        }
    }

    protected function generatePrompt(string $text, string $source): string
    {
        Log::debug('Generating GPT prompt', ['chars_in_text' => strlen($text)]);

        return <<<PROMPT
IMPORTANT: You are a precise terminology extraction assistant. Extract ONLY the specialized terms that are LITERALLY PRESENT in the text below. 

Do NOT generate, suggest, translate, or infer any terms that are not explicitly written in the text. If the text contains no specialized terms, return an empty result.

For each term found, provide a CSV-formatted line with the following columns:

source,english_term,arabic_term,review_status

- "source" should contain the source file or page name: {$source}
- "english_term" should be the term in English AS IT APPEARS in the text
- "arabic_term" should be the term in Arabic AS IT APPEARS in the text
- "review_status" should be "matched" if both English and Arabic appear together, "needs review" if only one language is present

Only include terms where BOTH English and Arabic versions are present in the text. Do not translate missing terms.
Do not include explanations or numbering. Output only CSV lines.

Text:
---
{$text}
---
PROMPT;
    }

    protected function processWithGpt(string $prompt): string
    {
        try {
            Log::info('Preparing to send prompt to GPT', [
                'prompt_length' => strlen($prompt),
            ]);

            return "Generated GPT prompt:\n\n" . $prompt;
        } catch (Exception $e) {
            Log::error('GPT processing failed', ['error' => $e->getMessage()]);
            throw new Exception('GPT processing failed: ' . $e->getMessage());
        }
    }

    public function executeText(string $text, string $source = 'unknown', bool $useGpt = false): array
    {
        try {
            Log::info('ExtractTermsAction.executeText started', ['source' => $source, 'chars' => strlen($text)]);

            $result = [
                'text' => $text,
                'source' => $source,
            ];

            if ($useGpt) {
                $prompt = $this->generatePrompt($text, $source);
                $gptOutput = $this->processWithGpt($prompt);
                $result['gpt'] = $gptOutput;
            }

            return $result;
        } catch (Exception $e) {
            Log::error('ExtractTermsAction.executeText error', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }
}
