<?php

namespace App\Jobs;

use App\Models\ResourcePage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class ProcessPageForGPTJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;

    protected ResourcePage $page;

    /**
     * Create a new job instance.
     *
     * @param ResourcePage $page
     */
    public function __construct(ResourcePage $page)
    {
        $this->page = $page;
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [5, 15, 30];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $text = $this->page->text ?? "";
        if (empty(trim($text))) {
            Log::warning(
                "ResourcePage {$this->page->id} has empty text. Skipping GPT processing.",
            );
            return;
        }

        $prompt =
            "Analyze OCR text from a technical terminology table. Poor OCR quality with corrupted structure.\n\n";

        // Concise OCR reconstruction rules
        $prompt .= "OCR RECONSTRUCTION RULES:\n";
        $prompt .=
            "1. Recombine separated Arabic letters: 'ا ل ب ا ي ت' → 'البايت'\n";
        $prompt .= "2. Fix definite articles: 'ا ل' → 'الـ'\n";
        $prompt .=
            "3. Reattach diacritics and fix technical terms: 'ر ا م' → 'رام'\n";
        $prompt .=
            "4. Don't match transliterations: 'processor'/'بروسيسور' = WRONG\n";
        $prompt .=
            "5. Match semantic translations only: 'processor'/'معالج' = CORRECT\n\n";

        $prompt .= "PROCESS:\n";
        $prompt .= "1. Extract English terms (fix OCR errors)\n";
        $prompt .= "2. Extract + reconstruct Arabic terms\n";
        $prompt .= "3. Match by meaning (not transliteration)\n";
        $prompt .= "4. Assign confidence 1-10 based on match quality\n\n";

        $jsonInstruction = "OUTPUT (JSON only, no markdown):\n";
        $jsonInstruction .= "{\n";
        $jsonInstruction .= "  \"verified_pairs\": [\n";
        $jsonInstruction .= "    {\n";
        $jsonInstruction .= "      \"term_en\": \"english_term\",\n";
        $jsonInstruction .=
            "      \"term_ar_reconstructed\": \"arabic_term\",\n";
        $jsonInstruction .= "      \"confidence_level\": 9\n";
        $jsonInstruction .= "    }\n";
        $jsonInstruction .= "  ]\n";
        $jsonInstruction .= "}\n\n";

        $jsonInstruction .=
            "CONFIDENCE: 9-10=exact, 7-8=strong, 5-6=reasonable, 3-4=uncertain, 1-2=weak\n";
        $jsonInstruction .=
            "Include only verified semantic matches (not transliterations).\n\n";

        $fullPrompt =
            $prompt . $jsonInstruction . "RAW OCR TEXT TO ANALYZE:\n" . $text;

            // New instruction to filter out non-relevant texts
        $fullPrompt .= "\n- Only extract terms where both English and Arabic terms exist in the same context (sentence or phrase).";
        $fullPrompt .= "\n- Ignore texts like introductions, tables of contents, or anything that doesn't contain both an English term and its corresponding Arabic translation.";
    
        // Debug: Log the text being analyzed
        Log::info(
            "ProcessPageForGPTJob - Page {$this->page->id} - Text length: " .
                strlen($text),
        );
        Log::debug(
            "ProcessPageForGPTJob - Page {$this->page->id} - First 500 chars of text: " .
                substr($text, 0, 500),
        );

        $response = OpenAI::chat()->create([
            "model" => env("OPENAI_MODEL", "deepseek-chat"),
            "messages" => [
                [
                    "role" => "system",
                    "content" =>
                        "Expert in matching English-Arabic technical terms from corrupted OCR text. Reconstruct Arabic by recombining separated letters and fixing OCR errors. Match by semantic meaning only (never transliterations). Assign confidence 1-10.",
                ],
                ["role" => "user", "content" => $fullPrompt],
            ],
            "temperature" => 0.2, // Lower temperature for more consistent reconstruction
            "max_tokens" => 8000, // Increased for detailed reconstruction notes
        ]);

        $gptOutput = $response->choices[0]->message->content ?? "";
        $finishReason = $response->choices[0]->finish_reason ?? null;

        // Check if response was truncated due to token limit
        if ($finishReason === "length") {
            Log::warning(
                "ProcessPageForGPTJob - Page {$this->page->id} - Response truncated due to max_tokens limit. Consider reducing input text or increasing max_tokens.",
            );
        }

        // Debug: Log GPT output
        Log::debug(
            "ProcessPageForGPTJob - Page {$this->page->id} - GPT Output: " .
                $gptOutput,
        );

        // Clean up the GPT output - remove markdown code blocks if present
        $cleanedOutput = $gptOutput;

        // Remove ```json and ``` markers
        $cleanedOutput = preg_replace("/^```json\s*/m", "", $cleanedOutput);
        $cleanedOutput = preg_replace("/^```\s*/m", "", $cleanedOutput);
        $cleanedOutput = trim($cleanedOutput);

        // Attempt to parse JSON
        $terms = json_decode($cleanedOutput, true);

        // If direct decode fails, try to extract JSON object
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Try to find JSON object (not just array)
            if (preg_match("/\{.*\}/s", $cleanedOutput, $matches)) {
                $terms = json_decode($matches[0], true);
            }

            // If still fails, try to find just the verified_pairs array
            if (
                json_last_error() !== JSON_ERROR_NONE &&
                preg_match(
                    '/"verified_pairs"\s*:\s*(\[.*?\])/s',
                    $cleanedOutput,
                    $matches,
                )
            ) {
                $pairsJson = '{"verified_pairs":' . $matches[1] . "}";
                $terms = json_decode($pairsJson, true);
            }
        }

        // Check if we have valid terms data
        if (json_last_error() === JSON_ERROR_NONE && is_array($terms)) {
            // Handle both old and new JSON formats
            $verifiedPairs = [];
            if (isset($terms["verified_pairs"])) {
                // New format with verified_pairs
                $verifiedPairs = $terms["verified_pairs"];
            } else {
                // Old format - direct array
                $verifiedPairs = $terms;
            }

            // Filter terms to only include those with both English and Arabic
            $validTerms = [];
            foreach ($verifiedPairs as $term) {
                // Handle both term_ar and term_ar_reconstructed
                $termAr =
                    $term["term_ar_reconstructed"] ??
                    ($term["term_ar"] ?? null);
                $confidenceLevel = $term["confidence_level"] ?? null;

                if (
                    isset($term["term_en"]) &&
                    !empty(trim($term["term_en"])) &&
                    $termAr &&
                    !empty(trim($termAr))
                ) {
                    $validTerms[] = [
                        "term_en" => $term["term_en"],
                        "term_ar" => $termAr,
                        "confidence_level" => $confidenceLevel,
                    ];
                }
            }

            // Skip the page if no valid terms found
            if (empty($validTerms)) {
                Log::warning(
                    "ResourcePage {$this->page->id} has no terms with both English and Arabic. Skipping page.",
                );
                $this->page->update([
                    "gpt_text" => $gptOutput,
                    "status" => "done",
                    "error_message" =>
                        "No terms with both English and Arabic translations found",
                ]);
                return;
            }

            // Save only valid terms
            foreach ($validTerms as $term) {
                $this->page->terms()->create([
                    "term_en" => $term["term_en"],
                    "term_ar" => $term["term_ar"],
                    "confidence_level" => $term["confidence_level"],
                ]);
            }

            Log::info(
                "ResourcePage {$this->page->id} processed with " .
                    count($validTerms) .
                    " valid terms.",
            );
        } else {
            $jsonError = json_last_error_msg();
            $errorDetails = [
                "json_error" => $jsonError,
                "finish_reason" => $finishReason ?? "unknown",
                "output_length" => strlen($gptOutput),
                "first_100_chars" => substr($gptOutput, 0, 100),
                "last_100_chars" => substr($gptOutput, -100),
            ];

            Log::warning(
                "Failed to parse JSON from GPT output for page {$this->page->id}.",
                $errorDetails,
            );

            $errorMessage = "Failed to parse JSON from GPT output: {$jsonError}";
            if ($finishReason === "length") {
                $errorMessage .= " (Response was truncated due to token limit)";
            }

            $this->page->update([
                "gpt_text" => $gptOutput,
                "status" => "error",
                "error_message" => $errorMessage,
            ]);
            return;
        }

        $this->page->update([
            "gpt_text" => $gptOutput,
            "status" => "done",
        ]);

        Log::info(
            "ResourcePage {$this->page->id} processed successfully with GPT.",
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error(
            "ProcessPageForGPTJob failed for page {$this->page->id} after all retries",
            [
                "error" => $exception->getMessage(),
            ],
        );

        $this->page->update([
            "status" => "error",
            "error_message" => $exception->getMessage(),
        ]);
    }
}
