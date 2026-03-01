<?php

namespace App\Actions;

use App\Models\Term;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\DomCrawler\Crawler;
use Exception;

class ScrapeWebAction
{
    /**
     * Execute web scraping for terms
     *
     * @param string $url
     * @param array $options
     * @return array
     * @throws ValidationException
     */
    public function execute(string $url, array $options = []): array
    {
        Log::info('ScrapeWebAction started', [
            'url' => $url,
            'options' => $options,
        ]);

        // Validate URL
        $validator = Validator::make(['url' => $url], [
            'url' => 'required|url|max:500',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        try {
            // Fetch webpage content
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Connection' => 'keep-alive',
                ])
                ->get($url);

            if (!$response->successful()) {
                throw new Exception("Failed to fetch URL. Status: {$response->status()}");
            }

            $html = $response->body();
            $crawler = new Crawler($html, $url);

            // Extract terms based on configuration
            $extractionMethod = $options['extraction_method'] ?? 'auto';
            $terms = $this->extractTerms($crawler, $extractionMethod, $options);

            // Process and save terms
            $processedTerms = $this->processTerms($terms, $url, $options);

            $result = [
                'url' => $url,
                'total_terms_found' => count($terms),
                'terms_processed' => count($processedTerms),
                'terms' => $processedTerms,
                'success' => true,
            ];

            Log::info('ScrapeWebAction completed', $result);

            return $result;

        } catch (Exception $e) {
            Log::error('ScrapeWebAction failed', [
                'url' => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception("Web scraping failed: " . $e->getMessage());
        }
    }

    /**
     * Extract terms from HTML using different methods
     *
     * @param Crawler $crawler
     * @param string $method
     * @param array $options
     * @return array
     */
    private function extractTerms(Crawler $crawler, string $method, array $options): array
    {
        $terms = [];

        switch ($method) {
            case 'table':
                $terms = $this->extractFromTable($crawler, $options);
                break;

            case 'list':
                $terms = $this->extractFromList($crawler, $options);
                break;

            case 'glossary':
                $terms = $this->extractFromGlossary($crawler, $options);
                break;

            case 'keywords':
                $terms = $this->extractKeywords($crawler, $options);
                break;

            case 'auto':
            default:
                // Try multiple methods
                $terms = $this->extractFromTable($crawler, $options);
                if (empty($terms)) {
                    $terms = $this->extractFromList($crawler, $options);
                }
                if (empty($terms)) {
                    $terms = $this->extractFromGlossary($crawler, $options);
                }
                if (empty($terms)) {
                    $terms = $this->extractKeywords($crawler, $options);
                }
                break;
        }

        return $terms;
    }

    /**
     * Extract terms from HTML tables (common for glossaries)
     *
     * @param Crawler $crawler
     * @param array $options
     * @return array
     */
    private function extractFromTable(Crawler $crawler, array $options): array
    {
        $terms = [];
        $tableSelector = $options['table_selector'] ?? 'table';

        $crawler->filter($tableSelector)->each(function (Crawler $table) use (&$terms, $options) {
            $rows = $table->filter('tr');

            $rows->each(function (Crawler $row) use (&$terms) {
                $cells = $row->filter('td, th');

                if ($cells->count() >= 2) {
                    $termEn = trim($cells->eq(0)->text());
                    $termAr = trim($cells->eq(1)->text());

                    if (!empty($termEn) && !empty($termAr)) {
                        $terms[] = [
                            'term_en' => $termEn,
                            'term_ar' => $termAr,
                            'source' => 'table',
                        ];
                    }
                }
            });
        });

        return $terms;
    }

    /**
     * Extract terms from lists (ul, ol, dl)
     *
     * @param Crawler $crawler
     * @param array $options
     * @return array
     */
    private function extractFromList(Crawler $crawler, array $options): array
    {
        $terms = [];
        $listSelector = $options['list_selector'] ?? 'ul, ol, dl';

        $crawler->filter($listSelector)->each(function (Crawler $list) use (&$terms) {
            // Try to extract from definition lists (dl)
            $list->filter('dt, dd')->each(function (Crawler $item) use (&$terms) {
                $text = trim($item->text());

                // Simple pattern matching for English/Arabic pairs
                if (preg_match('/^([a-zA-Z\s]+)[:\-]\s*(.+)$/', $text, $matches)) {
                    $termEn = trim($matches[1]);
                    $termAr = trim($matches[2]);

                    // Check if second part contains Arabic characters
                    if (preg_match('/[\x{0600}-\x{06FF}]/u', $termAr)) {
                        $terms[] = [
                            'term_en' => $termEn,
                            'term_ar' => $termAr,
                            'source' => 'list',
                        ];
                    }
                }
            });

            // Try to extract from regular lists
            $list->filter('li')->each(function (Crawler $item) use (&$terms) {
                $text = trim($item->text());

                // Pattern for terms separated by dash or colon
                if (preg_match('/^([a-zA-Z\s]+)[:\-]\s*(.+)$/', $text, $matches)) {
                    $termEn = trim($matches[1]);
                    $termAr = trim($matches[2]);

                    if (preg_match('/[\x{0600}-\x{06FF}]/u', $termAr)) {
                        $terms[] = [
                            'term_en' => $termEn,
                            'term_ar' => $termAr,
                            'source' => 'list',
                        ];
                    }
                }
            });
        });

        return $terms;
    }

    /**
     * Extract terms from glossary-like structures
     *
     * @param Crawler $crawler
     * @param array $options
     * @return array
     */
    private function extractFromGlossary(Crawler $crawler, array $options): array
    {
        $terms = [];

        // Look for glossary sections
        $glossarySelectors = [
            '[class*="glossary"]',
            '[id*="glossary"]',
            '[class*="term"]',
            '[id*="term"]',
            'h2, h3, h4:contains("glossary")',
            'h2, h3, h4:contains("terms")',
            'h2, h3, h4:contains("dictionary")',
        ];

        foreach ($glossarySelectors as $selector) {
            try {
                $crawler->filter($selector)->each(function (Crawler $element) use (&$terms) {
                    // Get the following content
                    $content = $element->nextAll()->text();

                    // Extract term pairs using various patterns
                    $patterns = [
                        '/([A-Z][a-zA-Z\s]+):\s*([\x{0600}-\x{06FF}\s]+)/u',
                        '/([A-Z][a-zA-Z\s]+)\s*[-\u2013]\s*([\x{0600}-\x{06FF}\s]+)/u',
                        '/([A-Z][a-zA-Z\s]+)\s*\(([\x{0600}-\x{06FF}\s]+)\)/u',
                    ];

                    foreach ($patterns as $pattern) {
                        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
                            foreach ($matches as $match) {
                                $terms[] = [
                                    'term_en' => trim($match[1]),
                                    'term_ar' => trim($match[2]),
                                    'source' => 'glossary',
                                ];
                            }
                        }
                    }
                });
            } catch (Exception $e) {
                // Skip invalid selectors
                continue;
            }
        }

        return $terms;
    }

    /**
     * Extract keywords from page content
     *
     * @param Crawler $crawler
     * @param array $options
     * @return array
     */
    private function extractKeywords(Crawler $crawler, array $options): array
    {
        $terms = [];

        // Get main content (excluding nav, footer, etc.)
        $contentSelectors = [
            'main',
            'article',
            '.content',
            '#content',
            '.main-content',
            '.post-content',
        ];

        $content = '';
        foreach ($contentSelectors as $selector) {
            try {
                if ($crawler->filter($selector)->count() > 0) {
                    $content = $crawler->filter($selector)->text();
                    break;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        // Fallback to body if no specific content area found
        if (empty($content)) {
            $content = $crawler->filter('body')->text();
        }

        // Extract potential English terms (capitalized words or phrases)
        if (preg_match_all('/\b([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\b/', $content, $matches)) {
            $englishTerms = array_unique($matches[1]);

            // Filter out common non-terms
            $commonWords = ['The', 'And', 'For', 'With', 'This', 'That', 'These', 'Those'];
            $englishTerms = array_filter($englishTerms, function ($term) use ($commonWords) {
                return !in_array($term, $commonWords) && strlen($term) > 3;
            });

            // For each English term, look for nearby Arabic text
            foreach ($englishTerms as $englishTerm) {
                // Find the position of the term
                $pos = strpos($content, $englishTerm);
                if ($pos !== false) {
                    // Extract surrounding text
                    $context = substr($content, max(0, $pos - 100), 200);

                    // Look for Arabic text in the context
                    if (preg_match('/[\x{0600}-\x{06FF}]+/u', $context, $arabicMatches)) {
                        $terms[] = [
                            'term_en' => $englishTerm,
                            'term_ar' => $arabicMatches[0],
                            'source' => 'keywords',
                            'confidence' => 0.5,
                        ];
                    }
                }
            }
        }

        return $terms;
    }

    /**
     * Process extracted terms and save to database
     *
     * @param array $terms
     * @param string $url
     * @param array $options
     * @return array
     */
    private function processTerms(array $terms, string $url, array $options): array
    {
        $processedTerms = [];
        $skipDuplicates = $options['skip_duplicates'] ?? true;
        $autoSave = $options['auto_save'] ?? false;

        foreach ($terms as $termData) {
            try {
                // Validate term data
                $validator = Validator::make($termData, [
                    'term_en' => 'required|string|max:255',
                    'term_ar' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    continue;
                }

                $termEn = trim($termData['term_en']);
                $termAr = trim($termData['term_ar']);

                // Check for duplicates
                if ($skipDuplicates) {
                    $existingTerm = Term::where('term_en', $termEn)
                        ->orWhere('term_ar', $termAr)
                        ->first();

                    if ($existingTerm) {
                        continue;
                    }
                }

                // Prepare term data
                $termRecord = [
                    'term_en' => $termEn,
                    'term_ar' => $termAr,
                    'status' => 'unverified',
                    'resource_page_id' => $options['resource_page_id'] ?? null,
                    'confidence_level' => $termData['confidence'] ?? 0.7,
                    'source_url' => $url,
                    'source_type' => $termData['source'] ?? 'web_scrape',
                ];

                // Save to database if auto-save is enabled
                if ($autoSave) {
                    $term = Term::create($termRecord);
                    $termRecord['id'] = $term->id;
                }

                $processedTerms[] = $termRecord;

            } catch (Exception $e) {
                Log::error('Error processing scraped term', [
                    'term_data' => $termData,
                    'error' => $e->getMessage(),
                ]);
                continue;
            }
        }

        return $processedTerms;
    }

    /**
     * Test if a URL is scrapeable
     *
     * @param string $url
     * @return array
     */
    public function testUrl(string $url): array
    {
        try {
            $response = Http::timeout(10)->head($url);

            return [
                'url' => $url,
                'accessible' => $response->successful(),
                'status_code' => $response->status(),
                'content_type' => $response->header('Content-Type'),
            ];
        } catch (Exception $e) {
            return [
                'url' => $url,
                'accessible' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
