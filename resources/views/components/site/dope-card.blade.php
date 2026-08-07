@props([
    // Associative array of label => value, rendered as the mono "DOPE" spec block.
    'rows' => [],
])
{{-- The mono spec / DOPE block, extracted from the approved mockup (.spec). --}}
<div class="spec">
  @foreach ($rows as $k => $v)
    <div class="row"><span class="k">{{ $k }}</span><span class="v">{{ $v }}</span></div>
  @endforeach
</div>
