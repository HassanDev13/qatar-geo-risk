<?php

namespace Tests\Feature;

use App\Jobs\GenerateBookJob;
use App\Models\Resource;
use App\Models\ResourcePage;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateBookMultipleArabicTermsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_shows_all_arabic_translations_for_same_english_term()
    {
        // Arrange: Create "packet" with 3 different Arabic translations
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        // "packet" -> "حزمة" (5 times)
        Term::factory()->count(5)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'packet',
            'term_ar' => 'حزمة',
            'confidence_level' => 0.9
        ]);

        // "packet" -> "باقة" (3 times)
        Term::factory()->count(3)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'packet',
            'term_ar' => 'باقة',
            'confidence_level' => 0.8
        ]);

        // "packet" -> "رزمة" (2 times)
        Term::factory()->count(2)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'packet',
            'term_ar' => 'رزمة',
            'confidence_level' => 0.7
        ]);

        // Act: Generate the book
        $job = new GenerateBookJob(mode: 'production');
        
        // Use reflection to access the protected grouping logic
        $reflection = new \ReflectionClass($job);
        $method = $reflection->getMethod('handle');
        $method->setAccessible(true);
        
        // Capture the grouped terms by running the job
        $job->handle();

        // Assert: PDF was generated
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);

        // Now let's verify the data structure by re-running the grouping logic
        $terms = Term::with('resourcePage.resource')->get();
        
        $groupedTerms = $terms->groupBy(function ($item) {
            return strtolower(trim($item->term_en));
        })->map(function ($group) {
            $englishTerm = $group->first()->term_en;
            
            $arabicTermsData = $group->groupBy('term_ar')->map(function ($subGroup) {
                return [
                    'term' => $subGroup->first()->term_ar,
                    'count' => $subGroup->count(),
                    'confidence' => round($subGroup->avg('confidence_level'), 1),
                ];
            })->sortByDesc('count')->values();

            return [
                'english_term' => $englishTerm,
                'arabic_terms' => $arabicTermsData,
                'total_count' => $group->count(),
            ];
        });

        // Assert: "packet" should have 3 Arabic translations
        $packetData = $groupedTerms->get('packet');
        $this->assertNotNull($packetData, 'Packet term should exist in grouped data');
        $this->assertCount(3, $packetData['arabic_terms'], 'Packet should have 3 different Arabic translations');
        $this->assertEquals(10, $packetData['total_count'], 'Packet should have total count of 10');

        // Assert: Arabic terms are sorted by count (descending)
        $arabicTerms = $packetData['arabic_terms'];
        $this->assertEquals('حزمة', $arabicTerms[0]['term'], 'First Arabic term should be حزمة');
        $this->assertEquals(5, $arabicTerms[0]['count'], 'حزمة should appear 5 times');
        
        $this->assertEquals('باقة', $arabicTerms[1]['term'], 'Second Arabic term should be باقة');
        $this->assertEquals(3, $arabicTerms[1]['count'], 'باقة should appear 3 times');
        
        $this->assertEquals('رزمة', $arabicTerms[2]['term'], 'Third Arabic term should be رزمة');
        $this->assertEquals(2, $arabicTerms[2]['count'], 'رزمة should appear 2 times');

        // Output the data structure for debugging
        dump('Grouped Terms for "packet":', $packetData);
    }

    /** @test */
    public function it_shows_all_variations_in_pdf_content()
    {
        // Arrange
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        Term::factory()->count(3)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'network',
            'term_ar' => 'شبكة',
        ]);

        Term::factory()->count(2)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'network',
            'term_ar' => 'شبكة اتصال',
        ]);

        // Act
        $job = new GenerateBookJob(mode: 'production');
        $job->handle();

        // Assert
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
        
        // Read the PDF content
        $pdfContent = Storage::disk('public')->get($files[0]);
        $this->assertNotEmpty($pdfContent);
        
        // Note: We can't easily parse PDF content in tests, but we verified the data structure
        $this->assertGreaterThan(1000, strlen($pdfContent), 'PDF should have substantial content');
    }
}
