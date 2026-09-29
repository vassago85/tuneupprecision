@props([
    'title' => null,
    'description' => null,
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'jsonLd' => null,
    'robots' => 'index, follow',
])
<!doctype html>
<html lang="en">
<head>
    @include('partials.google-tag')
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta
        :title="$title"
        :description="$description"
        :canonical="$canonical"
        :image="$image"
        :type="$type"
        :json-ld="$jsonLd"
        :robots="$robots"
    />
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="alternate" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">
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
    <x-shop.cart-drawer />
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

      var toast=document.getElementById('toast'), toastMsg=document.getElementById('toastMsg'), tTimer;
      function showToast(msg){
        if(!toast) return;
        toastMsg.textContent=msg; toast.classList.add('show');
        clearTimeout(tTimer); tTimer=setTimeout(function(){toast.classList.remove('show');},2600);
      }
      document.querySelectorAll('.book').forEach(function(b){
        b.addEventListener('click',function(){showToast('Seat request started · '+b.dataset.course);});
      });

      document.querySelectorAll('[data-share]').forEach(function(btn){
        btn.addEventListener('click',function(){
          var url=btn.getAttribute('data-share');
          var title=btn.getAttribute('data-share-title')||document.title;
          if(!url) return;
          if(navigator.share){
            navigator.share({title:title,url:url}).catch(function(){});
            return;
          }
          if(navigator.clipboard&&navigator.clipboard.writeText){
            navigator.clipboard.writeText(url).then(function(){showToast('Link copied');});
            return;
          }
          window.prompt('Copy this link',url);
        });
      });

      var drawer=document.getElementById('cartDrawer');
      var cartBtn=document.getElementById('cartBtn');
      function openCart(){
        if(!drawer) return;
        drawer.classList.add('open');
        drawer.setAttribute('aria-hidden','false');
        document.body.classList.add('cart-lock');
      }
      function closeCart(){
        if(!drawer) return;
        drawer.classList.remove('open');
        drawer.setAttribute('aria-hidden','true');
        document.body.classList.remove('cart-lock');
      }
      if(cartBtn){cartBtn.addEventListener('click',function(){drawer && drawer.classList.contains('open') ? closeCart() : openCart();});}
      if(drawer){
        drawer.querySelectorAll('[data-cart-close]').forEach(function(el){el.addEventListener('click',closeCart);});
        if(drawer.dataset.open==='1') openCart();
      }
      document.addEventListener('keydown',function(e){if(e.key==='Escape') closeCart();});

      // Spotlight cards: tilt toward the cursor and park the copper glow under it.
      var reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      var fine=window.matchMedia('(hover: hover) and (pointer: fine)').matches;
      if(fine && !reduce){
        document.querySelectorAll('.spot').forEach(function(card){
          var glow=document.createElement('span');
          glow.className='spot-glow';
          glow.setAttribute('aria-hidden','true');
          card.appendChild(glow);
          card.addEventListener('mousemove',function(e){
            var r=card.getBoundingClientRect();
            var px=(e.clientX-r.left)/r.width;
            var py=(e.clientY-r.top)/r.height;
            var rx=(0.5-py)*8;
            var ry=(px-0.5)*8;
            card.style.transform='perspective(900px) rotateX('+rx.toFixed(2)+'deg) rotateY('+ry.toFixed(2)+'deg)';
            glow.style.background='radial-gradient(ellipse at '+(px*100).toFixed(1)+'% '+(py*100).toFixed(1)+'%, rgba(212,91,46,.24), transparent 62%)';
          });
          card.addEventListener('mouseleave',function(){ card.style.transform=''; });
        });
      }

      // reveal on scroll
      var els=document.querySelectorAll('.reveal');
      if(reduce||!('IntersectionObserver'in window)){els.forEach(function(e){e.classList.add('in');});}
      else{
        var io=new IntersectionObserver(function(es){
          es.forEach(function(en){if(en.isIntersecting){en.target.classList.add('in');io.unobserve(en.target);}});
        },{threshold:.12, rootMargin:'0px 0px -8% 0px'});
        els.forEach(function(e){io.observe(e);});
      }

      // Ambient loop. iOS and Chrome Android only autoplay a muted inline
      // video that is actually visible, and only if we have not already
      // called pause() on it. Do not pause it for being below the fold —
      // that cancels autoplay, and a later play() from this observer is
      // not a user gesture so the phone leaves it stuck on the poster.
      var reducedMotion=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      document.querySelectorAll('[data-loop]').forEach(function(frame){
        var video=frame.querySelector('video.loop-video');
        var cue=frame.querySelector('.loop-play-cue');
        var pauseBtn=frame.querySelector('.loop-pause');
        if(!video||reducedMotion) return;

        video.muted=true;
        video.defaultMuted=true;
        video.playsInline=true;
        video.setAttribute('muted','muted');
        video.setAttribute('playsinline','');
        video.setAttribute('webkit-playsinline','');

        var inView=false;
        var userPaused=false;
        var retries=0;
        function markPlaying(){
          frame.classList.add('is-playing');
          frame.classList.remove('needs-gesture');
          video.classList.add('playing');
          retries=0;
        }
        function markNeedsGesture(){
          frame.classList.remove('is-playing');
          video.classList.remove('playing');
          frame.classList.add('needs-gesture');
        }
        function tryPlay(){
          if(userPaused||!inView) return;
          video.muted=true;
          var p=video.play();
          if(p&&typeof p.then==='function'){
            p.then(markPlaying).catch(function(){
              if(userPaused||!inView) return;
              if(retries<5){
                retries++;
                setTimeout(tryPlay, 300);
              }else{
                markNeedsGesture();
              }
            });
          }else if(!video.paused){
            markPlaying();
          }else{
            markNeedsGesture();
          }
        }
        function tryPause(){
          if(!video.paused){try{video.pause();}catch(e){}}
          markNeedsGesture();
        }

        video.addEventListener('playing',markPlaying);
        // Safari often ignores the loop attribute. Restart without treating
        // the gap as a failed autoplay.
        video.addEventListener('ended',function(){
          if(userPaused||!inView) return;
          try{video.currentTime=0;}catch(e){}
          retries=0;
          tryPlay();
        });
        video.addEventListener('canplay',function(){
          if(!userPaused&&inView&&video.paused) tryPlay();
        });

        // Do not preventDefault. Cancelling the tap drops the user-activation
        // iOS requires before it will honor play().
        function unlock(){
          userPaused=false;
          retries=0;
          inView=true;
          tryPlay();
        }
        if(cue) cue.addEventListener('click',unlock);
        if(pauseBtn) pauseBtn.addEventListener('click',function(e){
          e.stopPropagation();
          userPaused=true;
          tryPause();
        });
        frame.addEventListener('click',function(e){
          if(e.target.closest&&e.target.closest('a,button.loop-pause')) return;
          if(video.paused) unlock();
        });

        // A tap anywhere counts. Low Power Mode blocks autoplay until then,
        // including a tap that isn't on the video itself.
        function nudge(){
          if(userPaused||!inView||!video.paused) return;
          retries=0;
          tryPlay();
        }
        window.addEventListener('touchend',nudge,{passive:true});
        window.addEventListener('click',nudge);

        if(!video.paused) markPlaying();

        if('IntersectionObserver'in window){
          var vo=new IntersectionObserver(function(es){
            es.forEach(function(en){
              inView=en.isIntersecting;
              if(inView){
                retries=0;
                tryPlay();
              }
            });
          },{threshold:0.25});
          vo.observe(frame);
        }else{
          inView=true;
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
