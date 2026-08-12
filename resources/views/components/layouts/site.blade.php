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

      // ambient loop bands (e.g. the merch strip on the landing page): only
      // play the <video> when it scrolls into view, and only if the browser
      // actually lets us autoplay (iOS Low Power Mode / older Safari can
      // refuse; in that case we just leave the poster showing).
      var reducedMotion=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      document.querySelectorAll('[data-loop]').forEach(function(frame){
        var video=frame.querySelector('video.loop-video');
        if(!video||reducedMotion) return;
        var attempted=false;
        function tryPlay(){
          if(attempted) return; attempted=true;
          var p=video.play();
          if(p&&typeof p.then==='function'){
            p.then(function(){video.classList.add('playing');})
             .catch(function(){/* keep the poster, no console noise */});
          }else{
            video.classList.add('playing');
          }
        }
        if('IntersectionObserver'in window){
          var vo=new IntersectionObserver(function(es){
            es.forEach(function(en){
              if(en.isIntersecting){tryPlay();}
              else if(!video.paused){try{video.pause();}catch(e){}}
            });
          },{threshold:.25});
          vo.observe(frame);
        }else{
          tryPlay();
        }
      });

      // video facade: replace the thumbnail with the real player only on click,
      // so 20 embedded iframes/<video> tags don't slam the page on load.
      document.querySelectorAll('.video-facade[data-embed]').forEach(function(btn){
        btn.addEventListener('click',function(e){
          var url=btn.getAttribute('data-embed');
          if(!url) return;
          e.preventDefault();
          var native=btn.getAttribute('data-native')==='1';
          var el;
          if(native){
            el=document.createElement('video');
            el.src=url; el.controls=true; el.autoplay=true; el.setAttribute('playsinline','');
            el.style.width='100%'; el.style.height='100%'; el.style.display='block';
          }else{
            el=document.createElement('iframe');
            el.src=url;
            el.setAttribute('title', btn.getAttribute('aria-label')||'Video');
            el.setAttribute('frameborder','0');
            el.setAttribute('allow','accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
            el.setAttribute('allowfullscreen','');
            el.style.width='100%'; el.style.height='100%'; el.style.display='block'; el.style.border='0';
          }
          btn.replaceWith(el);
        });
      });
    })();
    </script>
</body>
</html>
