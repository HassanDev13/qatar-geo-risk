<?php

namespace App\Jobs;

use App\Models\Term;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Mpdf\Mpdf;

class GenerateBookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200; // 20 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?array $termIds = null,
        public string $mode = 'test',
        public ?User $user = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Fetch Terms with all details
        $query = Term::with('resourcePage.resource');

        if (!empty($this->termIds)) {
            // When specific terms are selected, include ALL variants of those English terms
            // This ensures if user selects "packet -> حزمة", we also get "packet -> باقة", etc.
            $selectedTerms = Term::whereIn('id', $this->termIds)->get();
            $englishTerms = $selectedTerms->pluck('term_en')
                ->map(fn($term) => strtolower(trim($term)))
                ->unique()
                ->values()
                ->toArray();
            
            // Fetch ALL terms that match these English terms (case-insensitive)
            if (!empty($englishTerms)) {
                $query->whereIn(
                    \DB::raw('LOWER(TRIM(term_en))'),
                    $englishTerms
                );
            }
        } elseif ($this->mode === 'test') {
            // Test mode: Select random terms, then include ALL variants of those English terms
            // This ensures if we randomly pick "packet -> حزمة", we also get "packet -> باقة", etc.
            $randomTerms = Term::inRandomOrder()->limit(1000)->get();
            $englishTerms = $randomTerms->pluck('term_en')
                ->map(fn($term) => strtolower(trim($term)))
                ->unique()
                ->values()
                ->toArray();
            
            // Fetch ALL terms that match these English terms (case-insensitive)
            if (!empty($englishTerms)) {
                $query->whereIn(
                    \DB::raw('LOWER(TRIM(term_en))'),
                    $englishTerms
                );
            }
        } else {
            // Production: All terms
            // No filter needed
        }

        $terms = $query->get();


        // 2. Group and Sort - Count by unique resources
        // Group by English term (case-insensitive)
        $groupedTerms = $terms->groupBy(function ($item) {
            return strtolower(trim($item->term_en));
        })->map(function ($group) {
            $englishTerm = $group->first()->term_en;
            
            // Group by Arabic term and count unique resources
            $arabicTermsData = $group->groupBy('term_ar')->map(function ($subGroup) use ($group) {
                // Get unique resource IDs for this Arabic term
                $uniqueResources = $subGroup->map(function ($term) {
                    return $term->resourcePage->resource_id;
                })->unique();
                
                // Collect and group sources by resource
                $sourcesByResource = [];
                foreach ($subGroup as $term) {
                    $resourceName = $term->resourcePage->resource->name ?? 'Unknown';
                    $pageNumber = $term->resourcePage->page_number ?? 0;
                    
                    if (!isset($sourcesByResource[$resourceName])) {
                        $sourcesByResource[$resourceName] = [];
                    }
                    if (!in_array($pageNumber, $sourcesByResource[$resourceName])) {
                        $sourcesByResource[$resourceName][] = $pageNumber;
                    }
                }
                
                // Format sources: "Resource Name (pages: 1, 5, 10)"
                $sources = [];
                foreach ($sourcesByResource as $resourceName => $pages) {
                    sort($pages);
                    $sources[] = [
                        'resource_name' => $resourceName,
                        'pages' => implode(', ', $pages)
                    ];
                }
                
                $count = $uniqueResources->count();
                $totalCount = $group->map(fn($t) => $t->resourcePage->resource_id)->unique()->count();
                $confidence = round($subGroup->avg('confidence_level'), 1);
                
                // Calculate frequency percentage
                $frequency = $totalCount > 0 ? round(($count / $totalCount) * 100) : 0;
                
                // Quality stars (1-5 based on confidence)
                $stars = ceil($confidence / 2);
                
                // Consistency rating (how many different resources agree on this translation)
                $consistency = $count >= 5 ? 'عالي' : ($count >= 3 ? 'متوسط' : 'منخفض');
                
                return [
                    'term' => $subGroup->first()->term_ar,
                    'count' => $count,
                    'confidence' => $confidence,
                    'sources' => $sources,
                    'frequency' => $frequency, // NEW: Percentage
                    'stars' => $stars, // NEW: Quality stars (1-5)
                    'consistency' => $consistency, // NEW: Consistency rating
                ];
            })->sortByDesc('count')->values();
            
            // Mark the most common translation ONLY if:
            // 1. There are multiple Arabic terms (> 1)
            // 2. The first term has a higher count than the second (no tie)
            if ($arabicTermsData->count() > 1) {
                $firstCount = $arabicTermsData[0]['count'];
                $secondCount = $arabicTermsData[1]['count'];
                
                $arabicTermsData = $arabicTermsData->map(function ($item, $index) use ($firstCount, $secondCount) {
                    // Only mark as most common if it's the first AND has higher count than second
                    $item['is_most_common'] = ($index === 0 && $firstCount > $secondCount);
                    return $item;
                });
            } else {
                // Single term - don't mark as most common
                $arabicTermsData = $arabicTermsData->map(function ($item) {
                    $item['is_most_common'] = false;
                    return $item;
                });
            }

            // Total count is also by unique resources for this English term
            $totalUniqueResources = $group->map(function ($term) {
                return $term->resourcePage->resource_id;
            })->unique();

            return [
                'english_term' => $englishTerm,
                'arabic_terms' => $arabicTermsData,
                'total_count' => $totalUniqueResources->count(), // Count unique resources
            ];
        })->sortKeys(); // Sort English terms alphabetically

        // 3. Calculate overall statistics
        $statistics = [
            'total_terms' => $terms->count(),
            'unique_english_terms' => $groupedTerms->count(),
            'unique_arabic_terms' => $terms->pluck('term_ar')->unique()->count(),
            'avg_confidence' => round($terms->avg('confidence_level'), 2),
            'total_positive_feedback' => $terms->sum('positive_feedback_count'),
            'total_negative_feedback' => $terms->sum('negative_feedback_count'),
            'generation_date' => now()->format('Y-m-d H:i:s'),
            'mode' => $this->mode,
        ];

        // 4. Generate PDF
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        // Write cover page and statistics separately
        $coverHtml = view('pdf.book-cover', [
            'statistics' => $statistics,
        ])->render();
        $mpdf->WriteHTML($coverHtml);

        // Add Introduction page
        $resources = \App\Models\Resource::all();
        $introHtml = view('pdf.book-intro', [
            'statistics' => $statistics,
            'resources' => $resources,
        ])->render();
        $mpdf->WriteHTML($introHtml);

        // Prepare data for Table of Contents
        $alphabeticalGroups = $groupedTerms->groupBy(function($item, $key) {
            return strtoupper(substr($key, 0, 1));
        });
        
        $letterCounts = $alphabeticalGroups->map(function($group) {
            return $group->count();
        })->toArray();

        // Generate Table of Contents
        $tocHtml = view('pdf.book-toc', [
            'letterCounts' => $letterCounts,
            'alphabeticalGroups' => $alphabeticalGroups,
        ])->render();
        $mpdf->WriteHTML($tocHtml);


        // Process terms in alphabetical chunks to avoid HTML size limit
        foreach ($alphabeticalGroups as $letter => $termsInLetter) {
            $chunkHtml = view('pdf.book-section', [
                'letter' => $letter,
                'termsInLetter' => $termsInLetter,
            ])->render();
            $mpdf->WriteHTML($chunkHtml);
        }
        
        // Add Conclusion page
        $conclusionHtml = view('pdf.book-conclusion', [
            'statistics' => $statistics,
            'resources' => $resources,
        ])->render();
        $mpdf->WriteHTML($conclusionHtml);
        
        // 4. Save PDF
        $fileName = 'book_' . now()->format('Y_m_d_H_i_s') . '.pdf';
        $path = 'books/' . $fileName;
        Storage::disk('public')->put($path, $mpdf->Output('', 'S'));

        // 5. Notify User
        if ($this->user) {
            Notification::make()
                ->title('Book Generated Successfully')
                ->success()
                ->body('The book has been generated. Click below to download.')
                ->actions([
                    Action::make('download')
                        ->button()
                        ->url(Storage::url($path), shouldOpenInNewTab: true),
                ])
                ->sendToDatabase($this->user);
        }
    }

    /**
     * Generate CSV content from grouped terms
     */
    private function generateCSV($groupedTerms): string
    {
        $csv = [];
        
        // Header row
        $csv[] = [
            'English Term',
            'Arabic Term',
            'Frequency %',
            'Count (Resources)',
            'Confidence',
            'Quality Stars',
            'Consistency',
            'Most Common',
            'Sources'
        ];
        
        // Data rows
        foreach ($groupedTerms as $englishTerm => $termData) {
            foreach ($termData['arabic_terms'] as $arabicData) {
                $sources = collect($arabicData['sources'] ?? [])
                    ->map(fn($s) => $s['resource_name'] . ' (p.' . $s['page_number'] . ')')
                    ->join('; ');
                
                $csv[] = [
                    $termData['english_term'],
                    $arabicData['term'],
                    ($arabicData['frequency'] ?? 0) . '%',
                    $arabicData['count'],
                    $arabicData['confidence'] . '/10',
                    str_repeat('⭐', $arabicData['stars'] ?? 0),
                    $arabicData['consistency'] ?? '',
                    ($arabicData['is_most_common'] ?? false) ? 'Yes' : 'No',
                    $sources
                ];
            }
        }
        
        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csv as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }
}
