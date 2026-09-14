<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_render_with_disclosure(): void
    {
        foreach (['/legal', '/privacy', '/terms', '/shipping', '/refunds'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('Supplier details')
                ->assertSee(route('legal.privacy'), false)
                ->assertSee(route('legal.shipping'), false)
                ->assertSee(route('legal.refunds'), false)
                ->assertSee(route('legal.terms'), false);
        }
    }

    public function test_sitemap_lists_all_legal_documents(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('/legal', $xml);
        $this->assertStringContainsString('/privacy', $xml);
        $this->assertStringContainsString('/terms', $xml);
        $this->assertStringContainsString('/shipping', $xml);
        $this->assertStringContainsString('/refunds', $xml);
    }

    public function test_llms_txt_lists_legal_documents_and_does_not_claim_no_contact(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/llms.txt')
            ->assertOk()
            ->assertSee('Shipping Policy', false)
            ->assertSee('Returns & Refunds', false)
            ->assertDontSee('No published email or phone');

        $this->get('/llms-full.txt')
            ->assertOk()
            ->assertSee('Shipping Policy', false)
            ->assertDontSee('No published email or phone');
    }
}
