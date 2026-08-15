<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\RiflePlatform;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\RifleBuilder\BuildSelection;
use App\Services\RifleBuildService;
use Database\Seeders\ComponentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RifleBuildServiceTest extends TestCase
{
    use RefreshDatabase;

    private RifleBuildService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ComponentSeeder::class);
        $this->service = app(RifleBuildService::class);
    }

    public function test_vat_is_derived_from_the_discounted_incl_total_without_compounding(): void
    {
        $barrelled = $this->catalogueItem('bergara-b-14-hmr-barrelled-action');
        $result = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: ['barrelled' => $barrelled->id],
        ));

        $this->assertSame($result->totalCents, $result->exVatCents + $result->vatCents);
        $this->assertSame((int) round($result->totalCents / 1.15), $result->exVatCents);
        $this->assertGreaterThan(0, $result->vatCents);
    }

    public function test_percent_and_flat_discount_apply_to_incl_subtotal_and_clamp_at_zero(): void
    {
        $barrelled = $this->catalogueItem('bergara-b-14-hmr-barrelled-action');

        $percent = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: ['barrelled' => $barrelled->id],
            discountPercent: 10,
        ));
        $this->assertSame((int) round($percent->subtotalCents * 0.10), $percent->discountCents);
        $this->assertSame($percent->subtotalCents - $percent->discountCents, $percent->totalCents);

        $flat = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: ['barrelled' => $barrelled->id],
            discountAmountCents: 50000,
        ));
        $this->assertSame(50000, $flat->discountCents);

        $both = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: ['barrelled' => $barrelled->id],
            discountPercent: 10,
            discountAmountCents: 50000,
        ));
        $this->assertSame(
            (int) round($both->subtotalCents * 0.10) + 50000,
            $both->discountCents,
        );

        $over = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: ['barrelled' => $barrelled->id],
            discountPercent: 100,
            discountAmountCents: 999999,
        ));
        $this->assertSame($over->subtotalCents, $over->discountCents);
        $this->assertSame(0, $over->totalCents);
        $this->assertSame(0, $over->exVatCents);
        $this->assertSame(0, $over->vatCents);
    }

    public function test_assembly_labour_is_added_when_a_core_component_is_selected(): void
    {
        $barrelled = $this->catalogueItem('bergara-b-14-hmr-barrelled-action');
        $result = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: ['barrelled' => $barrelled->id],
        ));

        $labour = collect($result->lines)->firstWhere('description', 'Assembly, Torque & Function Check');
        $this->assertNotNull($labour);
        $this->assertSame('Gunsmithing', $labour->group);
        $this->assertTrue($labour->isAutomatic);
        $this->assertSame(180000, $labour->unitPriceCents);

        $empty = $this->service->evaluate(new BuildSelection);
        $this->assertNull(collect($empty->lines)->firstWhere('description', 'Assembly, Torque & Function Check'));
    }

    public function test_chambering_labour_is_added_only_for_separate_platform_with_a_barrel(): void
    {
        $action = $this->catalogueItem('bergara-b-14-action');
        $barrel = $this->catalogueItem('benchmark-6-contour-blank');

        $withBarrel = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Separate,
            singles: ['action' => $action->id, 'barrel' => $barrel->id],
        ));
        $this->assertNotNull(collect($withBarrel->lines)->firstWhere('description', 'Chambering, Fitting & Headspacing'));
        $this->assertSame(450000, collect($withBarrel->lines)->firstWhere('description', 'Chambering, Fitting & Headspacing')->unitPriceCents);

        $actionOnly = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Separate,
            singles: ['action' => $action->id],
        ));
        $this->assertNull(collect($actionOnly->lines)->firstWhere('description', 'Chambering, Fitting & Headspacing'));

        $barrelled = $this->catalogueItem('bergara-b-14-hmr-barrelled-action');
        $factory = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: ['barrelled' => $barrelled->id],
        ));
        $this->assertNull(collect($factory->lines)->firstWhere('description', 'Chambering, Fitting & Headspacing'));
    }

    public function test_incompatible_chassis_are_disabled_and_cleared_when_footprint_changes(): void
    {
        $tikka = $this->catalogueItem('tikka-t3x-ctr-action');
        $manners = $this->catalogueItem('manners-t6a-carbon-stock'); // rem700 only
        $mdt = $this->catalogueItem('mdt-lss-xl-gen2'); // rem700 + tikka + ruger

        $result = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Separate,
            singles: [
                'action' => $tikka->id,
                'chassis' => $manners->id,
            ],
        ));

        $this->assertArrayNotHasKey('chassis', $result->selection->singles);
        $this->assertSame('Does not fit Tikka footprint', $result->disabledReasons[$manners->id] ?? null);
        $this->assertArrayNotHasKey($mdt->id, $result->disabledReasons);
    }

    public function test_incompatible_mounts_are_disabled_and_cleared_when_tube_changes(): void
    {
        $zco = $this->catalogueItem('zco-zc527-5-27x56'); // 36 mm
        $spuhr34 = $this->catalogueItem('spuhr-sp-4602-unimount-34-mm');
        $spuhr36 = $this->catalogueItem('spuhr-sp-5002-unimount-36-mm');

        $result = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: [
                'optic' => $zco->id,
                'mount' => $spuhr34->id,
            ],
        ));

        $this->assertArrayNotHasKey('mount', $result->selection->singles);
        $this->assertSame('Tube size mismatch — optic is 36 mm', $result->disabledReasons[$spuhr34->id] ?? null);
        $this->assertArrayNotHasKey($spuhr36->id, $result->disabledReasons);
    }

    public function test_lead_time_rolls_up_to_a_range_and_falls_back_to_the_default(): void
    {
        $empty = $this->service->evaluate(new BuildSelection);
        $this->assertSame('8–12 weeks', $empty->leadTime);

        $category = ComponentCategory::query()->where('key', 'barrelled')->firstOrFail();
        $slow = Component::factory()->create([
            'component_category_id' => $category->id,
            'brand' => 'Slow',
            'name' => 'Long Lead Barrelled',
            'slug' => 'slow-long-lead-barrelled',
            'price_cents' => 100000,
            'cost_cents' => 80000,
            'footprint' => 'rem700',
            'lead_time_weeks' => 10,
        ]);

        $result = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: ['barrelled' => $slow->id],
        ));

        $this->assertSame('10–14 weeks', $result->leadTime);
    }

    public function test_inactive_components_are_omitted_from_evaluation(): void
    {
        $category = ComponentCategory::query()->where('key', 'trigger')->firstOrFail();
        $hidden = Component::factory()->inactive()->create([
            'component_category_id' => $category->id,
            'brand' => 'Ghost',
            'name' => 'Hidden Trigger',
            'slug' => 'ghost-hidden-trigger',
            'price_cents' => 99900,
        ]);

        $result = $this->service->evaluate(new BuildSelection(
            singles: ['trigger' => $hidden->id],
        ));

        $this->assertNull(collect($result->lines)->firstWhere('componentId', $hidden->id));
        $this->assertFalse($this->service->loadAvailable()->contains('id', $hidden->id));
    }

    public function test_action_flagged_requires_aftermarket_trigger_disables_factory_and_needs_choice(): void
    {
        $action = $this->catalogueItem('bergara-b-14-action');
        $action->update(['requires_aftermarket_trigger' => true]);

        $factoryTrigger = $this->catalogueItem('factory-keep-factory-trigger');
        $aftermarket = $this->catalogueItem('triggertech-special-single-stage');

        $noTrigger = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Separate,
            singles: ['action' => $action->id],
        ));
        $this->assertTrue($noTrigger->requiresAftermarketTrigger);
        $this->assertTrue($noTrigger->needsTriggerChoice);
        $this->assertSame(
            'This action requires an aftermarket trigger',
            $noTrigger->disabledReasons[$factoryTrigger->id] ?? null,
        );

        $selectingFactory = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Separate,
            singles: ['action' => $action->id, 'trigger' => $factoryTrigger->id],
        ));
        $this->assertArrayNotHasKey('trigger', $selectingFactory->selection->singles);
        $this->assertTrue($selectingFactory->needsTriggerChoice);

        $withAftermarket = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Separate,
            singles: ['action' => $action->id, 'trigger' => $aftermarket->id],
        ));
        $this->assertFalse($withAftermarket->needsTriggerChoice);
        $this->assertArrayNotHasKey($aftermarket->id, $withAftermarket->disabledReasons);
    }

    public function test_flag_also_applies_to_barrelled_actions(): void
    {
        $barrelled = $this->catalogueItem('bergara-b-14-hmr-barrelled-action');
        $barrelled->update(['requires_aftermarket_trigger' => true]);

        $result = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Barrelled,
            singles: ['barrelled' => $barrelled->id],
        ));

        $this->assertTrue($result->requiresAftermarketTrigger);
        $this->assertTrue($result->needsTriggerChoice);
    }

    public function test_unflagged_actions_still_allow_the_factory_trigger_option(): void
    {
        $action = $this->catalogueItem('bergara-b-14-action');
        $factory = $this->catalogueItem('factory-keep-factory-trigger');

        $result = $this->service->evaluate(new BuildSelection(
            platform: RiflePlatform::Separate,
            singles: ['action' => $action->id, 'trigger' => $factory->id],
        ));

        $this->assertFalse($result->requiresAftermarketTrigger);
        $this->assertFalse($result->needsTriggerChoice);
        $this->assertSame($factory->id, $result->selection->singles['trigger'] ?? null);
        $this->assertArrayNotHasKey($factory->id, $result->disabledReasons);
    }

    public function test_persisting_a_quote_snapshots_lines_so_catalogue_edits_do_not_change_it(): void
    {
        $barrelled = $this->catalogueItem('bergara-b-14-hmr-barrelled-action');
        $quote = $this->service->persistQuote(
            new BuildSelection(
                platform: RiflePlatform::Barrelled,
                singles: ['barrelled' => $barrelled->id],
            ),
            [
                'customer_name' => 'Sam Shooter',
                'customer_email' => 'sam@example.com',
            ],
        );

        $this->assertMatchesRegularExpression('/^TU-\d{4}-\d{4}$/', $quote->reference);
        $this->assertNotEmpty($quote->lines);
        $originalTotal = $quote->total_cents;

        $barrelled->update(['price_cents' => 1]);

        $this->assertSame($originalTotal, $quote->fresh()->total_cents);
        $this->assertSame(
            $quote->lines()->where('component_id', $barrelled->id)->value('unit_price_cents'),
            2450000,
        );
    }

    private function catalogueItem(string $slug): Component
    {
        return Component::query()->where('slug', $slug)->firstOrFail();
    }
}
