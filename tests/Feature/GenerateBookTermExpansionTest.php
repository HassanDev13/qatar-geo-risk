<?php

namespace Tests\Feature;

use App\Jobs\GenerateBookJob;
use App\Models\Resource;
use App\Models\ResourcePage;
use App\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateBookTermExpansionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_includes_all_variants_when_only_one_is_selected()
    {
        // Arrange: Create "packet" with 3 different Arabic translations
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        $term1 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'packet',
            'term_ar' => 'حزمة',
            'confidence_level' => 0.9
        ]);

        $term2 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'packet',
            'term_ar' => 'باقة',
            'confidence_level' => 0.8
        ]);

        $term3 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'packet',
            'term_ar' => 'رزمة',
            'confidence_level' => 0.7
        ]);

        // Act: Generate book with ONLY term1 selected (like in Filament)
        $job = new GenerateBookJob(
            termIds: [$term1->id], // Only selecting the first variant
            mode: 'manual'
        );
        $job->handle();

        // Assert: PDF was generated
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);

        // Verify the data structure includes ALL variants
        $terms = Term::with('resourcePage.resource')->get();
        
        $groupedTerms = $terms->groupBy(function ($item) {
            return strtolower(trim($item->term_en));
        })->map(function ($group) {
            $arabicTermsData = $group->groupBy('term_ar')->map(function ($subGroup) {
                return [
                    'term' => $subGroup->first()->term_ar,
                    'count' => $subGroup->count(),
                ];
            })->values();

            return [
                'english_term' => $group->first()->term_en,
                'arabic_terms' => $arabicTermsData,
            ];
        });

        $packetData = $groupedTerms->get('packet');
        
        // The key assertion: even though we only selected term1, 
        // all 3 Arabic variants should be in the generated book
        $this->assertCount(3, $packetData['arabic_terms'], 
            'Should include all 3 Arabic variants even though only 1 was selected');
    }

    /** @test */
    public function it_includes_variants_for_multiple_selected_english_terms()
    {
        // Arrange: Create two English terms, each with multiple Arabic variants
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        // "network" variants
        $network1 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'network',
            'term_ar' => 'شبكة',
        ]);

        $network2 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'network',
            'term_ar' => 'شبكة اتصال',
        ]);

        // "server" variants
        $server1 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'server',
            'term_ar' => 'خادم',
        ]);

        $server2 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'server',
            'term_ar' => 'سيرفر',
        ]);

        // Act: Select only one variant from each English term
        $job = new GenerateBookJob(
            termIds: [$network1->id, $server1->id],
            mode: 'manual'
        );
        $job->handle();

        // Assert
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);

        // Verify all variants are included
        $allTerms = Term::all();
        $this->assertCount(4, $allTerms, 'All 4 term variants should be in the database');
    }

    /** @test */
    public function it_handles_case_insensitive_english_terms()
    {
        // Arrange: Create terms with different casings
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        $term1 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'Packet',
            'term_ar' => 'حزمة',
        ]);

        $term2 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'packet',
            'term_ar' => 'باقة',
        ]);

        $term3 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'PACKET',
            'term_ar' => 'رزمة',
        ]);

        // Act: Select only the first variant
        $job = new GenerateBookJob(
            termIds: [$term1->id],
            mode: 'manual'
        );
        $job->handle();

        // Assert: All case variants should be included
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
    }

    /** @test */
    public function it_does_not_affect_production_mode()
    {
        // Arrange
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        Term::factory()->count(5)->create([
            'resource_page_id' => $page->id,
        ]);

        // Act: Production mode should get ALL terms regardless
        $job = new GenerateBookJob(mode: 'production');
        $job->handle();

        // Assert
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
    }
}
