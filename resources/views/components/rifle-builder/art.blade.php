@props(['key' => 'extra'])
@php
    $map = [
        'barrelled' => 'ba',
        'action' => 'action',
        'barrel' => 'barrel',
        'chassis' => 'chassis',
        'trigger' => 'trigger',
        'chassis_accessory' => 'acc',
        'rail' => 'rail',
        'optic' => 'optic',
        'mount' => 'mount',
        'bipod' => 'bipod',
        'muzzle' => 'muzzle',
        'extra' => 'extra',
        'gunsmithing' => 'extra',
    ];
    $art = $map[$key] ?? 'extra';
@endphp
@include('components.rifle-builder.art.'.$art)
