@props([
    'title' => null,
])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' · ' : '' }}Tune Up · Long Range Precision Training</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#17222E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500;600;700;800&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @include('partials.site-styles')
    @livewireStyles
</head>
<body>
    <x-site.nav />

    <main>
        {{ $slot }}
    </main>

    <x-site.footer />
    <x-site.toast />

    @livewireScripts
    <script>
    (function(){
      // mobile menu
      var ham=document.getElementById('hamburger'), menu=document.getElementById('mobileMenu');
      if(ham && menu){
        function closeMenu(){menu.classList.remove('open');ham.setAttribute('aria-expanded','false');}
        ham.addEventListener('click',function(){
          var open=menu.classList.toggle('open');
          ham.setAttribute('aria-expanded',open?'true':'false');
        });
        menu.querySelectorAll('a').forEach(function(a){a.addEventListener('click',closeMenu);});
      }

      // cart + toast (presentational for now — the interactive cart lands in a later commit)
      var count=0, badge=document.getElementById('cartBadge');
      var toast=document.getElementById('toast'), toastMsg=document.getElementById('toastMsg'), tTimer;
      function showToast(msg){
        if(!toast) return;
        toastMsg.textContent=msg; toast.classList.add('show');
        clearTimeout(tTimer); tTimer=setTimeout(function(){toast.classList.remove('show');},2600);
      }
      function bump(){if(!badge) return; count++; badge.textContent=count; badge.classList.add('show');
        badge.style.animation='none'; void badge.offsetWidth; badge.style.animation='';}
      document.querySelectorAll('.add').forEach(function(b){
        b.addEventListener('click',function(){bump(); showToast('Added · '+b.dataset.name);});
      });
      document.querySelectorAll('.book').forEach(function(b){
        b.addEventListener('click',function(){showToast('Seat request started · '+b.dataset.course);});
      });
      var cartBtn=document.getElementById('cartBtn');
      if(cartBtn){cartBtn.addEventListener('click',function(){
        showToast(count?('Cart · '+count+' item'+(count>1?'s':'')):'Your cart is empty');
      });}

      // reveal on scroll
      var reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var els=document.querySelectorAll('.reveal');
      if(reduce||!('IntersectionObserver'in window)){els.forEach(function(e){e.classList.add('in');});}
      else{
        var io=new IntersectionObserver(function(es){
          es.forEach(function(en){if(en.isIntersecting){en.target.classList.add('in');io.unobserve(en.target);}});
        },{threshold:.12, rootMargin:'0px 0px -8% 0px'});
        els.forEach(function(e){io.observe(e);});
      }
    })();
    </script>
</body>
</html>
