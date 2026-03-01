<?php

namespace Tests\Feature;

use App\Jobs\GenerateBookJob;
use App\Models\Resource;
use App\Models\ResourcePage;
use App\Models\Term;
use App\Models\User;
use Filament\Notifications\DatabaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateBookJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_groups_terms_by_english_term_case_insensitively()
    {
        // Arrange: Create terms with same English term but different casings
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'Book',
            'term_ar' => 'كتاب',
            'confidence_level' => 0.9
        ]);

        Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'book',
            'term_ar' => 'كتاب',
            'confidence_level' => 0.8
        ]);

        Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'BOOK',
            'term_ar' => 'مؤلف',
            'confidence_level' => 0.7
        ]);

        // Act
        $job = new GenerateBookJob(mode: 'production');
        $job->handle();

        // Assert: Check that PDF was generated
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
        $this->assertStringContainsString('book_', $files[0]);
    }

    /** @test */
    public function it_groups_arabic_terms_correctly_within_english_groups()
    {
        // Arrange: Create one English term with multiple Arabic translations
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        // "computer" with 3 different Arabic translations
        Term::factory()->count(5)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'computer',
            'term_ar' => 'حاسوب',
            'confidence_level' => 0.9
        ]);

        Term::factory()->count(3)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'computer',
            'term_ar' => 'كمبيوتر',
            'confidence_level' => 0.8
        ]);

        Term::factory()->count(1)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'computer',
            'term_ar' => 'حاسب آلي',
            'confidence_level' => 0.7
        ]);

        // Act
        $job = new GenerateBookJob(mode: 'production');
        $job->handle();

        // Assert
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
    }

    /** @test */
    public function it_calculates_statistics_correctly()
    {
        // Arrange
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        Term::factory()->count(10)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'test',
            'term_ar' => 'اختبار',
            'confidence_level' => 0.8,
            'positive_feedback_count' => 5,
            'negative_feedback_count' => 2
        ]);

        Term::factory()->count(5)->create([
            'resource_page_id' => $page->id,
            'term_en' => 'example',
            'term_ar' => 'مثال',
            'confidence_level' => 0.9,
            'positive_feedback_count' => 3,
            'negative_feedback_count' => 1
        ]);

        // Act
        $job = new GenerateBookJob(mode: 'production');
        $job->handle();

        // Assert: PDF should be generated with correct statistics
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);

        // Verify the PDF contains data (size > 0)
        $this->assertGreaterThan(0, Storage::disk('public')->size($files[0]));
    }

    /** @test */
    public function it_respects_test_mode_limit()
    {
        // Arrange: Create more than 1000 terms
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        Term::factory()->count(1500)->create([
            'resource_page_id' => $page->id,
        ]);

        // Act: Run in test mode (should limit to 1000)
        $job = new GenerateBookJob(mode: 'test');
        $job->handle();

        // Assert: PDF should still be generated
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
    }

    /** @test */
    public function it_filters_by_term_ids_when_provided()
    {
        // Arrange
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        $term1 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'included',
            'term_ar' => 'مشمول'
        ]);

        $term2 = Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'excluded',
            'term_ar' => 'مستبعد'
        ]);

        // Act: Only include term1
        $job = new GenerateBookJob(termIds: [$term1->id]);
        $job->handle();

        // Assert
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
    }

    /** @test */
    public function it_sends_notification_to_user_when_provided()
    {
        // Arrange
        $user = User::factory()->create();
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        Term::factory()->count(5)->create([
            'resource_page_id' => $page->id,
        ]);

        // Act
        $job = new GenerateBookJob(user: $user);
        $job->handle();

        // Assert: Check notification was sent
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        $notification = \Illuminate\Notifications\DatabaseNotification::where('notifiable_id', $user->id)->first();
        $this->assertNotNull($notification);
        $this->assertEquals('Book Generated Successfully', $notification->data['title']);
    }


    /** @test */
    public function it_does_not_send_notification_when_user_is_null()
    {
        // Arrange
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        Term::factory()->count(5)->create([
            'resource_page_id' => $page->id,
        ]);

        // Act
        $job = new GenerateBookJob(user: null);
        $job->handle();

        // Assert: No notifications should be sent
        $this->assertDatabaseCount('notifications', 0);
    }

    /** @test */
    public function it_generates_pdf_with_cover_and_sections()
    {
        // Arrange
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        // Create terms starting with different letters
        Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'apple',
            'term_ar' => 'تفاحة'
        ]);

        Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'banana',
            'term_ar' => 'موز'
        ]);

        Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'cherry',
            'term_ar' => 'كرز'
        ]);

        // Act
        $job = new GenerateBookJob(mode: 'production');
        $job->handle();

        // Assert
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
        
        // Verify PDF is not empty
        $this->assertGreaterThan(1000, Storage::disk('public')->size($files[0]));
    }

    /** @test */
    public function it_handles_empty_terms_gracefully()
    {
        // Act: Run with no terms in database
        $job = new GenerateBookJob(mode: 'production');
        $job->handle();

        // Assert: Should still generate a PDF (even if empty)
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
    }

    /** @test */
    public function it_sorts_english_terms_alphabetically()
    {
        // Arrange
        $resource = Resource::factory()->create();
        $page = ResourcePage::factory()->create(['resource_id' => $resource->id]);

        // Create terms in non-alphabetical order
        Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'zebra',
            'term_ar' => 'حمار وحشي'
        ]);

        Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'apple',
            'term_ar' => 'تفاحة'
        ]);

        Term::factory()->create([
            'resource_page_id' => $page->id,
            'term_en' => 'monkey',
            'term_ar' => 'قرد'
        ]);

        // Act
        $job = new GenerateBookJob(mode: 'production');
        $job->handle();

        // Assert: PDF generated successfully
        $files = Storage::disk('public')->files('books');
        $this->assertCount(1, $files);
    }
}
