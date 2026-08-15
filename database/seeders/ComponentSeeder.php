<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ComponentSelectionMode;
use App\Models\Component;
use App\Models\ComponentCategory;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComponentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = $this->categories();

        foreach ($categories as $index => $data) {
            $items = $data['items'];
            unset($data['items']);

            $category = ComponentCategory::updateOrCreate(
                ['key' => $data['key']],
                [
                    ...$data,
                    'sort_order' => $index + 1,
                ],
            );

            foreach ($items as $itemIndex => $item) {
                Component::updateOrCreate(
                    ['slug' => $item['slug']],
                    [
                        'component_category_id' => $category->id,
                        'brand' => $item['brand'],
                        'name' => $item['name'],
                        'specs' => $item['specs'],
                        'price_cents' => Money::toCents($item['price']),
                        'cost_cents' => Money::toCents($item['cost']),
                        'footprint' => $item['footprint'] ?? null,
                        'fits_footprints' => $item['fits_footprints'] ?? null,
                        'tube_diameter' => $item['tube_diameter'] ?? null,
                        'fits_tube_diameters' => $item['fits_tube_diameters'] ?? null,
                        'is_active' => true,
                        'is_automatic' => $item['is_automatic'] ?? false,
                        'allows_quantity' => $item['allows_quantity'] ?? false,
                        'sort_order' => $itemIndex + 1,
                    ],
                );
            }
        }
    }

    /**
     * Port of the prototype CATALOGUE + AUTO labour lines.
     *
     * @return list<array<string, mixed>>
     */
    protected function categories(): array
    {
        return [
            [
                'key' => 'barrelled',
                'name' => 'Barrelled Action',
                'hint' => 'Factory or pre-fit barrelled actions — action and barrel already married up, headspaced and ready to drop in. Fastest route to a finished rifle.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => false,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('Bergara', 'B-14 HMR Barrelled Action', 24500, 19200, ['6.5 CM · 24" · 1:8', 'Threaded 5/8x24', 'Rem 700 footprint'], footprint: 'rem700'),
                    $this->item('Tikka', 'T3x TACT A1 Barrelled Action', 32000, 25400, ['.308 Win · 24" · 1:11', 'Threaded 5/8x24', 'Tikka footprint'], footprint: 'tikka'),
                    $this->item('Impact', '737R + Bartlein Pre-fit', 62000, 50500, ['6mm Dasher · 26" · 1:7.5', 'Straight-fluted, brake-ready', 'Rem 700 footprint'], footprint: 'rem700'),
                    $this->item('Zermatt', 'TL3 + Proof Carbon Pre-fit', 68000, 55800, ['6.5 CM · 26" · 1:8', 'Carbon-wrapped Sendero', 'Rem 700 footprint'], footprint: 'rem700'),
                    $this->item('Ruger', 'American Gen II Barrelled Action', 15900, 12400, ['6.5 CM · 20" · 1:8', 'Cold hammer forged', 'Budget entry'], footprint: 'ruger'),
                ],
            ],
            [
                'key' => 'action',
                'name' => 'Action',
                'hint' => 'Pick the receiver first — it sets the footprint, so it decides which chassis and stocks will fit.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => false,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('Bergara', 'B-14 Action', 14500, 11400, ['Rem 700 footprint', 'Short action', 'Integral rec. lug'], footprint: 'rem700'),
                    $this->item('Tikka', 'T3x CTR Action', 16500, 13100, ['Tikka footprint', 'Short action', 'Factory rail option'], footprint: 'tikka'),
                    $this->item('Curtis', 'Vector Custom Action', 36000, 29400, ['Rem 700 footprint', 'Interchangeable bolt head', '20 MOA integral rail'], footprint: 'rem700'),
                    $this->item('Impact', '737R Action', 38000, 31000, ['Rem 700 footprint', 'Integral rail + lug', 'Fastest lead time'], footprint: 'rem700'),
                    $this->item('Zermatt', 'TL3 Action', 42000, 34200, ['Rem 700 footprint', 'Tool-less bolt head swap', 'Nitride finish'], footprint: 'rem700'),
                    $this->item('Defiance', 'Deviant Tactical', 45000, 36900, ['Rem 700 footprint', 'Integral 20 MOA rail', 'Cerakote finish'], footprint: 'rem700'),
                ],
            ],
            [
                'key' => 'barrel',
                'name' => 'Barrel',
                'hint' => 'Choose the blank, then set chambering, length and twist. Chambering, fitting and headspacing labour is added automatically.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => false,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('Benchmark', '#6 Contour Blank', 11900, 8900, ['Cut rifled', 'Stainless', 'Best value match blank']),
                    $this->item('Shilen', 'Select Match', 12800, 9700, ['Button rifled', 'Stainless', 'Proven club-level blank']),
                    $this->item('Bartlein', '5R Heavy Palma', 14500, 11200, ['Single-point cut', '5R rifling', 'PRS favourite']),
                    $this->item('Krieger', 'Heavy Varmint Cut Rifled', 15800, 12300, ['Single-point cut', 'Stainless', 'Match grade']),
                    $this->item('Proof Research', 'Carbon Fibre Sendero', 24500, 19800, ['Carbon wrapped', '~40% lighter', 'Heat-stable groups']),
                ],
            ],
            [
                'key' => 'chassis',
                'name' => 'Chassis / Stock',
                'hint' => 'Filtered to what fits your action footprint. Incompatible options are greyed out.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => false,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('MDT', 'LSS-XL Gen2', 12900, 10100, ['Chassis + folding stock', 'M-LOK forend', 'Entry-level chassis'], fitsFootprints: ['rem700', 'tikka', 'ruger']),
                    $this->item('KRG', 'Bravo', 11500, 9000, ['Glass-filled polymer', 'Adj. cheek + LOP', 'Best budget stock'], fitsFootprints: ['rem700', 'tikka']),
                    $this->item('MDT', 'XRS Chassis', 16500, 13100, ['Full-length forend', 'Arca compatible', 'Weight-kit ready'], fitsFootprints: ['rem700', 'tikka']),
                    $this->item('KRG', 'Whiskey-3 Gen 6', 19500, 15600, ['Folding option', 'Modular forend', 'Bag-friendly'], fitsFootprints: ['rem700', 'tikka']),
                    $this->item('Manners', 'T6A Carbon Stock', 26000, 21000, ['Carbon composite', 'Mini-chassis inlet', 'Requires bedding'], fitsFootprints: ['rem700']),
                    $this->item('MDT', 'ACC Premier', 34000, 27900, ['Integrated weight system', 'Arca forend', 'Comp-ready'], fitsFootprints: ['rem700', 'tikka']),
                    $this->item('Cadex', 'Field Comp M-LOK', 36000, 29600, ['Folding, adj. everything', 'DX2 trigger-guard', 'Full alloy'], fitsFootprints: ['rem700']),
                    $this->item('MPA', 'BA Comp Chassis', 38000, 31200, ['Aluminium billet', 'Inline recoil path', 'Match dominant'], fitsFootprints: ['rem700']),
                    $this->item('MDT', 'ACC Elite', 42000, 34500, ['Top-tier weight system', 'Full arca', 'PRS podium chassis'], fitsFootprints: ['rem700', 'tikka']),
                ],
            ],
            [
                'key' => 'trigger',
                'name' => 'Trigger',
                'hint' => 'Optional. The single biggest felt improvement per rand on most factory rifles.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => true,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('Factory', 'Keep Factory Trigger', 0, 0, ['No change', 'Adjust in-house', 'R0']),
                    $this->item('TriggerTech', 'Special Single-Stage', 4600, 3600, ['1.5–4 lb', 'FRT technology', 'Best value upgrade']),
                    $this->item('Timney', 'Calvin Elite', 6900, 5500, ['8 oz – 2 lb', 'Interchangeable shoes', 'Crisp break']),
                    $this->item('TriggerTech', 'Diamond Single-Stage', 7400, 5900, ['4 oz – 2 lb', 'Zero creep', 'Comp standard']),
                    $this->item("Bix'n Andy", 'TacSport Pro Two-Stage', 9800, 8000, ['Ball-bearing sear', 'Two-stage', 'Benchrest feel']),
                ],
            ],
            [
                'key' => 'chassis_accessory',
                'name' => 'Chassis Accessories',
                'hint' => 'Optional. Grips, internal and external weights, arca rails, bag riders — the balance and positional-shooting kit.',
                'selection_mode' => ComponentSelectionMode::Multi,
                'is_optional' => true,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('MDT', 'Vertical Grip', 1450, 1050, ['AR-pattern', 'Rubberised', 'Comfort upgrade']),
                    $this->item('MDT', 'Elite Grip', 1950, 1450, ['Adjustable palm shelf', 'Vertical geometry', 'Positional shooting']),
                    $this->item('MDT', 'Internal Forend Weight Kit', 2400, 1750, ['+1.1 kg internal', 'Hidden in forend', 'Recoil management']),
                    $this->item('MDT', 'External Barrel Weight', 3200, 2400, ['Clamp-on', 'Tunes barrel harmonics', 'Front-end balance']),
                    $this->item('MDT', 'Buttstock Weight Kit', 1800, 1300, ['Rear balance', 'Stackable', 'Reduces muzzle rise']),
                    $this->item('MDT', 'Arca / Swiss Rail 12"', 1650, 1200, ['M-LOK mount', 'Tripod ready', 'Bipod slide']),
                    $this->item('MDT', 'Barricade Stop / Bag Rider', 1200, 850, ['Arca mounted', 'Positional support', 'PRS staple']),
                    $this->item('Area 419', 'Thumb Rest', 850, 600, ['Repeatable hand position', 'M-LOK', 'Small win']),
                    $this->item('MDT', 'Night Vision / Bridge Rail', 2900, 2200, ['Forward optic bridge', 'Pic interface', 'NV / clip-on ready']),
                    $this->item('MDT', 'Enhanced Bolt Release', 750, 520, ['Oversized paddle', 'Gloved use', 'Quick swap']),
                ],
            ],
            [
                'key' => 'rail',
                'name' => 'Picatinny Rail',
                'hint' => 'Skip if your action already has an integral rail. More MOA = more elevation before you run out of turret at distance.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => false,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('Integral', 'Rail Already On The Action', 0, 0, ['No separate rail needed', '20 MOA built in', 'No charge']),
                    $this->item('MDT', 'One-Piece Rail — 0 MOA', 950, 680, ['0 MOA', '7075 alloy', 'Sub-600 m use']),
                    $this->item('MDT', 'One-Piece Rail — 20 MOA', 1150, 820, ['20 MOA', '7075 alloy', 'Standard choice']),
                    $this->item('Area 419', 'One-Piece Rail — 30 MOA', 1350, 980, ['30 MOA', 'Steel', 'Long-range elevation']),
                    $this->item('Area 419', 'ELR Rail — 45 MOA', 1850, 1400, ['45 MOA', 'Steel', '1500 m+ builds']),
                ],
            ],
            [
                'key' => 'optic',
                'name' => 'Optic',
                'hint' => 'Tube diameter here filters the mounts in the next step.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => false,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('Client', 'Customer Supplied Optic', 0, 0, ['No charge', 'Fitted & zeroed', 'Confirm tube size'], tubeDiameter: '34'),
                    $this->item('Vortex', 'Diamondback Tactical 6-24x50', 14500, 11300, ['FFP · EBR-2C MRAD', '30 mm tube', 'Entry FFP'], tubeDiameter: '30'),
                    $this->item('Element', 'Nexus 5-20x50', 26500, 21000, ['FFP · APR-2D MRAD', '34 mm tube', 'Great glass/rand'], tubeDiameter: '34'),
                    $this->item('Vortex', 'Viper PST Gen II 5-25x50', 28900, 23000, ['FFP · EBR-7C MRAD', '30 mm tube', 'Club-match workhorse'], tubeDiameter: '30'),
                    $this->item('Athlon', 'Ares ETR 4.5-30x56', 32000, 25600, ['FFP · APLR MRAD', '34 mm tube', 'Big zoom range'], tubeDiameter: '34'),
                    $this->item('Vortex', 'Razor HD Gen III 6-36x56', 74000, 60500, ['FFP · EBR-7D MRAD', '34 mm tube', 'Podium glass'], tubeDiameter: '34'),
                    $this->item('Kahles', 'K525i 5-25x56', 89000, 73000, ['FFP · SKMR4', '34 mm tube', 'PRS benchmark'], tubeDiameter: '34'),
                    $this->item('Nightforce', 'ATACR 7-35x56', 95000, 78000, ['FFP · MIL-XT', '34 mm tube', 'Bombproof'], tubeDiameter: '34'),
                    $this->item('ZCO', 'ZC527 5-27x56', 125000, 104000, ['FFP · MPCT3', '36 mm tube', 'Top of the tree'], tubeDiameter: '36'),
                ],
            ],
            [
                'key' => 'mount',
                'name' => 'Rings / Unimount',
                'hint' => 'Filtered to your optic tube size.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => false,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('Client', 'Customer Supplied Mount', 0, 0, ['No charge', 'Fitted & torqued', 'R0'], fitsTubes: ['30', '34', '36']),
                    $this->item('MDT', 'Unimount 30 mm', 3900, 2900, ['One-piece', '20 MOA option', 'Value pick'], fitsTubes: ['30']),
                    $this->item('Vortex', 'Precision Matched Rings 34 mm', 4200, 3200, ['Ring set', 'Multiple heights', 'Clean fit'], fitsTubes: ['34']),
                    $this->item('ARC', 'M10 Rings 34 mm', 6800, 5300, ['Ring set', 'Very repeatable', 'Comp standard'], fitsTubes: ['34']),
                    $this->item('Spuhr', 'SP-4602 Unimount 34 mm', 11500, 9300, ['One-piece 20 MOA', 'Integrated level', 'The gold standard'], fitsTubes: ['34']),
                    $this->item('Spuhr', 'SP-5002 Unimount 36 mm', 12900, 10500, ['One-piece 20 MOA', '36 mm ZCO fit', 'Accessory ready'], fitsTubes: ['36']),
                ],
            ],
            [
                'key' => 'bipod',
                'name' => 'Bipod',
                'hint' => 'Optional. Check your forend interface — arca-direct saves an adapter.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => true,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('None', 'No Bipod', 0, 0, ['Skip this step', 'Add later', 'R0']),
                    $this->item('Harris', 'S-BRM 6-9" Swivel', 4900, 3700, ['Notched legs', 'Pan & cant', 'The classic']),
                    $this->item('Accu-Tac', 'SR-5 Quick Detach', 8900, 7000, ['Alloy, 45° legs', 'Very stable', 'Direct arca option']),
                    $this->item('Atlas', 'BT10 V8', 9800, 7900, ['5-position legs', 'Pan/cant lock', 'Match standard']),
                    $this->item('MDT', 'GRND-POD', 11500, 9200, ['Wide stance', 'Loads hard', 'PRS built']),
                    $this->item('Ckye-Pod', 'GEN2 Double Pull', 14900, 12200, ['Fully adjustable legs', 'Barricade capable', 'Top of the class']),
                ],
            ],
            [
                'key' => 'muzzle',
                'name' => 'Muzzle Device',
                'hint' => 'Optional. A brake needs the muzzle threaded — add the threading line if the barrel is not already cut.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => true,
                'allows_quantity' => false,
                'is_hidden' => false,
                'items' => [
                    $this->item('None', 'Thread Protector Only', 250, 150, ['5/8x24 cap', 'Bare muzzle', 'Suppressor later']),
                    $this->item('Service', 'Thread & Re-crown 5/8x24', 850, 400, ['In-house labour', '11° target crown', 'Needed for brake/can']),
                    $this->item('MDT', 'Elite Muzzle Brake', 3200, 2450, ['Self-timing', 'Good recoil cut', 'Tunable']),
                    $this->item('APA', 'Little Bastard Gen 3', 4900, 3900, ['Flat recoil impulse', 'Very loud', 'Comp favourite']),
                    $this->item('Area 419', 'Hellfire Self-Timing', 5600, 4500, ['Adapter system', 'Swap to can', 'Best-in-class']),
                    $this->item('Service', 'Suppressor Prep & Fit', 1400, 600, ['Thread + fit check', 'Alignment verified', 'Can not included']),
                ],
            ],
            [
                'key' => 'extra',
                'name' => 'Extras, Services & Consumables',
                'hint' => 'Optional. Magazines, finishes, gunsmithing services, load development, transport and licence admin.',
                'selection_mode' => ComponentSelectionMode::Multi,
                'is_optional' => true,
                'allows_quantity' => true,
                'is_hidden' => false,
                'items' => [
                    $this->item('Magpul/AICS', 'AICS 10rd Magazine', 1450, 1050, ['Per magazine', 'Short action', 'Most builds take 2–3'], allowsQuantity: true),
                    $this->item('MDT', 'AICS 12rd Metal Magazine', 1650, 1250, ['Per magazine', 'Steel body', 'Comp reliable'], allowsQuantity: true),
                    $this->item('Service', 'Tactical Bolt Knob + Fitting', 1200, 700, ['Oversized knob', 'Fitted in-house', 'Faster cycling']),
                    $this->item('Service', 'Action Truing & Blueprinting', 3500, 1600, ['Faced, lapped, trued', 'Factory actions', 'Accuracy insurance']),
                    $this->item('Service', 'Pillar & Glass Bedding', 2800, 1200, ['For composite stocks', '48 h cure', 'Repeatable zero']),
                    $this->item('Service', 'Cerakote — Single Colour', 3900, 2100, ['Barrelled action', 'Your colour choice', 'Corrosion protection']),
                    $this->item('Service', 'Cerakote — Camo / Multi', 6500, 3600, ['Up to 3 colours', 'Stencilled pattern', 'Chassis + barrel']),
                    $this->item('Service', 'Load Development & Zero (50 rds)', 4500, 2400, ['Ladder + seating depth', '100 m zero', 'DOPE to 600 m']),
                    $this->item('Redding/Hornady', 'Reloading Die Set', 3200, 2500, ['Full-length + seater', 'Your chambering', 'Match dies']),
                    $this->item('Lapua/Peterson', 'Brass — 100 pcs', 2800, 2200, ['Match brass', 'Your chambering', 'One lot number']),
                    $this->item('Send It / Vortex', 'Scope Level', 650, 430, ['Anti-cant', '30/34 mm', 'Cheap accuracy']),
                    $this->item('Wheeler/Fix It', 'Torque Wrench Set', 1900, 1400, ['10–70 in-lb', 'Bit set', 'Mount your own glass']),
                    $this->item('Armageddon', 'Rear Squeeze Bag', 1600, 1150, ['Game changer fill', 'Waxed canvas', 'Rear support']),
                    $this->item('Pelican-style', 'Hard Case — Wheeled', 4800, 3600, ['Foam cut to build', 'Lockable', 'Travel ready']),
                    $this->item('Eberlestock', 'Drag Bag / Rifle Bag', 3600, 2700, ['Padded', 'Range transport', 'Mag pockets']),
                    $this->item('Service', 'Cleaning Kit + Bore Guide', 1400, 950, ['Rod, jags, guide', 'Calibre specific', 'Protect the throat']),
                    $this->item('Service', 'DOPE Card Kit + Holder', 450, 250, ['Arm/stock holder', 'Printed card', 'Comes zeroed']),
                    $this->item('Service', 'Courier & Insurance', 650, 450, ['Nationwide', 'Insured in transit', 'Dealer-to-dealer']),
                    $this->item('Service', 'Licence Motivation & Transfer Admin', 950, 0, ['SAPS 271/518 assist', 'Motivation letter', 'Dealer stock transfer']),
                ],
            ],
            [
                'key' => 'gunsmithing',
                'name' => 'Gunsmithing',
                'hint' => 'Automatic in-house labour. Not shown as a picker step.',
                'selection_mode' => ComponentSelectionMode::Single,
                'is_optional' => false,
                'allows_quantity' => false,
                'is_hidden' => true,
                'items' => [
                    [
                        'slug' => 'chambering-fitting-headspacing',
                        'brand' => 'Tune Up',
                        'name' => 'Chambering, Fitting & Headspacing',
                        'price' => 4500,
                        'cost' => 1500,
                        'specs' => ['In-house labour', 'Added when a barrel is chambered'],
                        'is_automatic' => true,
                    ],
                    [
                        'slug' => 'assembly-torque-function-check',
                        'brand' => 'Tune Up',
                        'name' => 'Assembly, Torque & Function Check',
                        'price' => 1800,
                        'cost' => 600,
                        'specs' => ['Torqued to spec, headspace & function verified'],
                        'is_automatic' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  list<string>  $specs
     * @param  list<string>|null  $fitsFootprints
     * @param  list<string>|null  $fitsTubes
     * @return array<string, mixed>
     */
    protected function item(
        string $brand,
        string $name,
        int $price,
        int $cost,
        array $specs,
        ?string $footprint = null,
        ?array $fitsFootprints = null,
        ?string $tubeDiameter = null,
        ?array $fitsTubes = null,
        bool $allowsQuantity = false,
    ): array {
        return [
            'slug' => Str::slug($brand.' '.$name),
            'brand' => $brand,
            'name' => $name,
            'price' => $price,
            'cost' => $cost,
            'specs' => $specs,
            'footprint' => $footprint,
            'fits_footprints' => $fitsFootprints,
            'tube_diameter' => $tubeDiameter,
            'fits_tube_diameters' => $fitsTubes,
            'allows_quantity' => $allowsQuantity,
        ];
    }
}
