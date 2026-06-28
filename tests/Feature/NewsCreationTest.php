<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NewsCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Заголовок новини',
            'excerpt' => 'Короткий опис новини',
            'content' => '<p>Повний текст новини з HTML розміткою</p>',
            'image_url' => 'https://example.com/image.jpg',
            'author' => "Ім'я Автора",
            'published_at' => '2024-06-28T14:30:00',
        ], $overrides);
    }

    public function test_guests_cannot_create_articles(): void
    {
        $this->postJson('/api/news', $this->validPayload())
            ->assertUnauthorized();

        $this->assertDatabaseCount('news', 0);
    }

    public function test_authenticated_user_can_create_an_article(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/news', $this->validPayload());

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Заголовок новини')
            ->assertJsonPath('data.slug', 'zagolovok-novini');

        $this->assertDatabaseHas('news', [
            'title' => 'Заголовок новини',
            'author' => "Ім'я Автора",
        ]);
    }

    public function test_slug_is_made_unique_on_collision(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/news', $this->validPayload())->assertCreated();
        $this->postJson('/api/news', $this->validPayload())
            ->assertCreated()
            ->assertJsonPath('data.slug', 'zagolovok-novini-2');
    }

    public function test_excerpt_is_derived_from_content_when_omitted(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/news', $this->validPayload([
            'excerpt' => null,
            'content' => '<p>'.str_repeat('довгий текст ', 40).'</p>',
        ]));

        $response->assertCreated();
        $excerpt = $response->json('data.excerpt');

        $this->assertNotEmpty($excerpt);
        $this->assertStringNotContainsString('<p>', $excerpt);
        $this->assertLessThanOrEqual(160, mb_strlen($excerpt));
    }

    public function test_published_at_defaults_to_now_when_omitted(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/news', $this->validPayload(['published_at' => null]))
            ->assertCreated();

        $this->assertNotNull(News::first()->published_at);
    }

    public function test_validation_rejects_missing_required_fields(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/news', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content', 'author']);
    }

    public function test_validation_rejects_a_malformed_image_url(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/news', $this->validPayload(['image_url' => 'not-a-url']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('image_url');
    }
}
