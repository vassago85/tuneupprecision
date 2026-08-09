{{-- Single source of truth for public-site CSS, extracted verbatim from the approved mockup (tune-up-precision.html). Do not inline styles per-component. --}}
<style>
  :root{
    --charcoal:#2C3E50;
    --charcoal-deep:#1E2C39;
    --copper:#D45B2E;
    --copper-deep:#B4471F;
    --copper-deeper:#93381A;
    --gray:#5C6B72;
    --base:#EFEFEA;
    --base-2:#E6E6DE;
    --paper:#F8F8F4;
    --ink:#22303C;
    --muted:#5C6B72;
    --line:rgba(44,62,80,.16);
    --line-soft:rgba(44,62,80,.09);
    --shadow:0 1px 2px rgba(31,44,57,.06), 0 10px 30px -12px rgba(31,44,57,.22);
    --shadow-lg:0 24px 60px -22px rgba(31,44,57,.42);
    --r:14px;
    --maxw:1180px;
    --disp:'Saira Condensed', 'Arial Narrow', sans-serif;
    --body:'IBM Plex Sans', system-ui, sans-serif;
    --mono:'IBM Plex Mono', ui-monospace, monospace;
  }
  *{box-sizing:border-box}
  html{scroll-behavior:smooth}
  @media (prefers-reduced-motion:reduce){html{scroll-behavior:auto}}
  body{
    margin:0;background:var(--base);color:var(--ink);
    font-family:var(--body);font-size:17px;line-height:1.6;
    -webkit-font-smoothing:antialiased;text-rendering:optimizeLegibility;
  }
  h1,h2,h3,h4{font-family:var(--disp);font-weight:700;line-height:.98;margin:0;text-transform:uppercase;letter-spacing:.005em}
  a{color:inherit;text-decoration:none}
  img{max-width:100%;display:block}
  .wrap{max-width:var(--maxw);margin:0 auto;padding:0 26px}
  .mono{font-family:var(--mono)}
  .eyebrow{
    font-family:var(--mono);font-size:12px;font-weight:500;letter-spacing:.22em;
    text-transform:uppercase;color:var(--copper-deeper);display:inline-flex;align-items:center;gap:10px;
  }
  .eyebrow::before{content:"";width:26px;height:1px;background:var(--copper-deeper);display:inline-block;opacity:.7}
  .btn{
    font-family:var(--disp);text-transform:uppercase;letter-spacing:.04em;font-weight:700;font-size:16px;
    display:inline-flex;align-items:center;gap:9px;cursor:pointer;border:1px solid transparent;
    padding:13px 22px 12px;border-radius:10px;transition:transform .16s ease, background .18s ease, box-shadow .18s ease, color .18s ease;
  }
  .btn:active{transform:translateY(1px)}
  .btn-primary{background:var(--copper-deep);color:#fff;box-shadow:0 8px 20px -10px rgba(180,71,31,.7)}
  .btn-primary:hover{background:var(--copper-deeper)}
  .btn-ghost{background:transparent;color:var(--charcoal);border-color:var(--line)}
  .btn-ghost:hover{border-color:var(--charcoal);background:rgba(44,62,80,.04)}
  .btn-dark{background:var(--charcoal);color:#fff}
  .btn-dark:hover{background:var(--charcoal-deep)}
  .btn svg{width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:2}
  :focus-visible{outline:2.5px solid var(--copper);outline-offset:3px;border-radius:4px}

  /* ---------- reticle divider ---------- */
  .reticle-rule{display:flex;align-items:center;gap:0;color:var(--gray);padding:6px 0}
  .reticle-rule .line{flex:1;height:1px;background:linear-gradient(90deg,transparent,var(--line) 12%,var(--line) 88%,transparent)}
  .reticle-rule .hash{display:flex;align-items:center;gap:6px;padding:0 14px}
  .reticle-rule .hash i{width:1px;height:9px;background:var(--gray);opacity:.5;display:block}
  .reticle-rule .hash i.tall{height:15px;opacity:.85}
  .reticle-rule .dot{width:7px;height:7px;border-radius:50%;background:var(--copper);box-shadow:0 0 0 4px rgba(212,91,46,.14)}

  /* ---------- nav ---------- */
  header.nav{position:sticky;top:0;z-index:60;background:rgba(239,239,234,.82);backdrop-filter:blur(12px);border-bottom:1px solid var(--line-soft)}
  .nav-inner{display:flex;align-items:center;gap:24px;height:70px}
  .brand{display:flex;align-items:center;gap:12px}
  .brand .mark{width:34px;height:34px;flex:none}
  .brand .wm{display:flex;flex-direction:column;line-height:1}
  .brand .wm b{font-family:var(--disp);font-weight:800;font-size:22px;letter-spacing:.02em;color:var(--charcoal)}
  .brand .wm span{font-family:var(--mono);font-size:9px;letter-spacing:.28em;color:var(--gray);margin-top:3px}
  nav.links{display:flex;gap:28px;margin-left:auto}
  nav.links a{font-family:var(--disp);text-transform:uppercase;font-weight:600;font-size:15px;letter-spacing:.03em;color:var(--charcoal);position:relative;padding:4px 0}
  nav.links a::after{content:"";position:absolute;left:0;bottom:-2px;height:2px;width:0;background:var(--copper);transition:width .2s ease}
  nav.links a:hover::after{width:100%}
  .nav-actions{display:flex;align-items:center;gap:14px}
  .cart-btn{position:relative;width:42px;height:42px;border-radius:10px;border:1px solid var(--line);background:var(--paper);display:grid;place-items:center;cursor:pointer;color:var(--charcoal)}
  .cart-btn:hover{border-color:var(--charcoal)}
  .cart-btn svg{width:19px;height:19px;stroke:currentColor;fill:none;stroke-width:1.8}
  .cart-badge{position:absolute;top:-7px;right:-7px;min-width:19px;height:19px;padding:0 5px;border-radius:20px;background:var(--copper-deep);color:#fff;font-family:var(--mono);font-size:11px;font-weight:600;display:grid;place-items:center;transform:scale(0);transition:transform .2s cubic-bezier(.2,1.4,.4,1)}
  .cart-badge.show{transform:scale(1)}
  .hamburger{display:none;width:42px;height:42px;border-radius:10px;border:1px solid var(--line);background:var(--paper);cursor:pointer;padding:0;color:var(--charcoal)}
  .hamburger svg{width:22px;height:22px;stroke:currentColor;stroke-width:2;fill:none}

  /* ---------- hero ---------- */
  .hero{position:relative;overflow:hidden;padding:64px 0 30px}
  .hero-grid{display:grid;grid-template-columns:1.08fr .92fr;gap:44px;align-items:center}
  .hero h1{font-size:clamp(48px,7.4vw,92px);color:var(--charcoal);font-weight:800}
  .hero h1 .cop{color:var(--copper)}
  .hero p.lead{font-size:19px;color:var(--muted);max-width:46ch;margin:22px 0 30px}
  .hero-cta{display:flex;gap:14px;flex-wrap:wrap}
  .hero-data{display:flex;gap:0;margin-top:34px;border:1px solid var(--line);border-radius:12px;background:var(--paper);overflow:hidden}
  .hero-data .cell{padding:14px 18px;flex:1;border-right:1px solid var(--line-soft)}
  .hero-data .cell:last-child{border-right:0}
  .hero-data .k{font-family:var(--mono);font-size:10px;letter-spacing:.16em;color:var(--gray);text-transform:uppercase}
  .hero-data .v{font-family:var(--disp);font-weight:700;font-size:20px;color:var(--charcoal);margin-top:3px}
  .hero-badge{position:relative;display:grid;place-items:center}
  .hero-badge img{width:min(100%,440px);height:auto;filter:drop-shadow(0 18px 34px rgba(31,44,57,.22))}
  .hero-badge .ret{position:absolute;inset:0;pointer-events:none}
  .hero-badge .ret .h,.hero-badge .ret .v{position:absolute;background:var(--line)}
  .hero-badge .ret .h{left:-4%;right:-4%;top:50%;height:1px}
  .hero-badge .ret .v{top:-4%;bottom:-4%;left:50%;width:1px}
  .hero-badge .ret .fdot{position:absolute;top:50%;left:50%;width:9px;height:9px;margin:-4.5px;border-radius:50%;background:var(--copper)}
  .ridge{position:absolute;left:0;right:0;bottom:0;height:120px;pointer-events:none;opacity:.5}

  @keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(212,91,46,.5)}50%{box-shadow:0 0 0 9px rgba(212,91,46,0)}}
  .hero-badge .ret .fdot{animation:pulse 3.2s ease-in-out infinite}
  @media (prefers-reduced-motion:reduce){.hero-badge .ret .fdot{animation:none}}

  /* ---------- section shell ---------- */
  section{padding:76px 0}
  .sec-head{max-width:640px;margin-bottom:42px}
  .sec-head h2{font-size:clamp(32px,4.4vw,50px);color:var(--charcoal);margin-top:14px;font-weight:800}
  .sec-head p{color:var(--muted);margin-top:14px;font-size:17.5px}

  /* value strip */
  .values{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
  .val{background:var(--paper);border:1px solid var(--line);border-radius:var(--r);padding:24px 22px;transition:transform .2s ease, box-shadow .2s ease}
  .val:hover{transform:translateY(-4px);box-shadow:var(--shadow)}
  .val .ic{width:40px;height:40px;border-radius:10px;background:rgba(44,62,80,.06);display:grid;place-items:center;margin-bottom:16px;color:var(--copper)}
  .val .ic svg{width:22px;height:22px;stroke:currentColor;fill:none;stroke-width:1.7}
  .val h3{font-size:22px;color:var(--charcoal);margin-bottom:7px}
  .val p{font-size:14.5px;color:var(--muted);margin:0;line-height:1.5}

  /* ---------- courses ---------- */
  #courses{background:var(--base-2)}
  .courses{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;align-items:stretch}
  .course{background:var(--paper);border:1px solid var(--line);border-radius:16px;padding:26px 24px 24px;display:flex;flex-direction:column;position:relative;transition:transform .2s ease, box-shadow .2s ease}
  .course:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg)}
  .course.feat{border-color:var(--copper);box-shadow:0 20px 44px -24px rgba(212,91,46,.5)}
  .course .tag{position:absolute;top:-11px;right:20px;background:var(--copper-deep);color:#fff;font-family:var(--mono);font-size:10px;letter-spacing:.14em;text-transform:uppercase;padding:5px 11px;border-radius:20px}
  .course .lvl{font-family:var(--mono);font-size:11px;letter-spacing:.18em;color:var(--gray);text-transform:uppercase}
  .course h3{font-size:29px;color:var(--charcoal);margin:8px 0 8px}
  .course .desc{font-size:14.5px;color:var(--muted);min-height:44px;margin-bottom:18px}
  .spec{border-top:1px solid var(--line-soft);border-bottom:1px solid var(--line-soft);padding:14px 0;margin-bottom:20px;display:grid;gap:9px}
  .spec .row{display:flex;justify-content:space-between;align-items:baseline;gap:12px}
  .spec .row .k{font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--gray)}
  .spec .row .v{font-family:var(--mono);font-size:13px;color:var(--charcoal);font-weight:500;text-align:right}
  .course .price{display:flex;align-items:baseline;gap:8px;margin-bottom:16px}
  .course .price b{font-family:var(--disp);font-weight:800;font-size:36px;color:var(--charcoal);line-height:1}
  .course .price s{font-family:var(--mono);font-size:12px;color:var(--muted);text-decoration:none}
  .course .btn{width:100%;justify-content:center;margin-top:auto}
  .course .fully{width:100%;justify-content:center;margin-top:auto;background:var(--base-2);color:var(--muted);border:1px solid var(--line);cursor:not-allowed}
  /* training-type filter chips */
  .type-filter{display:flex;flex-wrap:wrap;gap:10px;margin:0 0 30px}
  .type-filter a{font-family:var(--disp);text-transform:uppercase;letter-spacing:.03em;font-weight:600;font-size:14px;color:var(--charcoal);background:var(--paper);border:1px solid var(--line);border-radius:10px;padding:9px 16px 8px;transition:.16s}
  .type-filter a:hover{border-color:var(--charcoal)}
  .type-filter a.active{background:var(--copper-deep);color:#fff;border-color:var(--copper-deep);box-shadow:0 8px 20px -12px rgba(180,71,31,.7)}
  /* dated event agenda */
  .course .evt-date{font-family:var(--mono);font-size:12px;letter-spacing:.1em;text-transform:uppercase;color:var(--copper-deep);display:flex;align-items:center;gap:9px;margin-bottom:4px}
  .course .evt-date::before{content:"";width:16px;height:1px;background:var(--copper-deep);opacity:.7}
  .month-head{display:flex;align-items:center;gap:16px;margin:6px 0 22px}
  .month-head h3{font-size:26px;color:var(--charcoal);white-space:nowrap}
  .month-head .rule{flex:1;height:1px;background:linear-gradient(90deg,var(--line),transparent)}
  .month-head:not(:first-of-type){margin-top:20px}
  .schedule-empty{background:var(--paper);border:1px dashed var(--line);border-radius:16px;padding:40px 28px;text-align:center;color:var(--muted);font-family:var(--mono);font-size:14px}
  .private{margin-top:20px;background:var(--charcoal);color:#fff;border-radius:16px;padding:26px 30px;display:flex;align-items:center;gap:26px;flex-wrap:wrap}
  .private .txt{flex:1;min-width:240px}
  .private h3{color:#fff;font-size:28px;margin-bottom:6px}
  .private p{color:rgba(255,255,255,.72);margin:0;font-size:15px}
  .private .p2{display:flex;align-items:center;gap:20px;flex-wrap:wrap}
  .private .p2 .amt{font-family:var(--disp);font-weight:800;font-size:32px;color:#fff}
  .private .p2 .amt s{font-family:var(--mono);font-weight:400;font-size:12px;color:rgba(255,255,255,.6);display:block;letter-spacing:.1em;text-decoration:none}

  /* ---------- about ---------- */
  .about-grid{display:grid;grid-template-columns:.85fr 1.15fr;gap:44px;align-items:center}
  .about-photo{position:relative;border-radius:16px;overflow:hidden;background:var(--charcoal);aspect-ratio:4/5;border:1px solid var(--line);display:grid;place-items:center}
  .about-photo .silh{color:rgba(255,255,255,.14);width:66%}
  .about-photo .photo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center 42%}
  .about-photo .cap{position:absolute;left:16px;bottom:16px;font-family:var(--mono);font-size:11px;letter-spacing:.12em;color:rgba(255,255,255,.55);text-transform:uppercase}
  .about-photo .frame{position:absolute;inset:14px;border:1px solid rgba(255,255,255,.16);border-radius:8px;pointer-events:none}
  .about-photo .frame::before,.about-photo .frame::after{content:"";position:absolute;background:var(--copper)}
  .about-photo .frame::before{top:50%;left:-1px;right:-1px;height:1px;opacity:.5}
  .about-photo .frame::after{left:50%;top:-1px;bottom:-1px;width:1px;opacity:.5}
  .about-copy h2{font-size:clamp(30px,4vw,46px);color:var(--charcoal);margin:14px 0 18px;font-weight:800}
  .about-copy p{color:var(--muted);margin:0 0 16px}
  .cred-line{display:flex;flex-wrap:wrap;align-items:center;margin:0 0 20px;font-family:var(--mono);font-size:12px;letter-spacing:.06em;text-transform:uppercase;color:var(--gray);line-height:1.7}
  .cred-line span:not(:last-child)::after{content:"·";color:var(--copper-deep);margin:0 12px;font-weight:700}
  .creds{display:flex;gap:14px;flex-wrap:wrap;margin-top:24px}
  .cred{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:14px 18px;min-width:140px}
  .cred .n{font-family:var(--disp);font-weight:800;font-size:30px;color:var(--copper);line-height:1}
  .cred .l{font-family:var(--mono);font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--gray);margin-top:5px}

  /* ---------- process ---------- */
  #process{background:var(--charcoal);color:#fff}
  #process .sec-head h2{color:#fff}
  #process .sec-head p{color:rgba(255,255,255,.66)}
  #process .eyebrow{color:#F09A72}
  #process .eyebrow::before{background:#F09A72}
  .steps{display:grid;grid-template-columns:repeat(5,1fr);gap:0;border-top:1px solid rgba(255,255,255,.14)}
  .step{padding:26px 20px 8px;border-right:1px solid rgba(255,255,255,.1);position:relative}
  .step:last-child{border-right:0}
  .step .no{font-family:var(--mono);font-size:12px;color:#F09A72;letter-spacing:.1em}
  .step .no::before{content:"";position:absolute;top:-1px;left:0;width:40px;height:2px;background:#F09A72}
  .step h3{font-size:22px;color:#fff;margin:16px 0 8px}
  .step p{font-size:13.5px;color:rgba(255,255,255,.6);margin:0}
  .proc-tabbar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:22px}
  .proc-tabbar button{font-family:var(--disp);text-transform:uppercase;letter-spacing:.04em;font-weight:700;font-size:15px;color:rgba(255,255,255,.62);background:transparent;border:1px solid rgba(255,255,255,.16);border-radius:10px;padding:9px 20px;cursor:pointer;transition:color .16s ease, background .18s ease, border-color .18s ease}
  .proc-tabbar button:hover{color:#fff;border-color:rgba(255,255,255,.34)}
  .proc-tabbar button.active{color:#fff;background:var(--copper-deep);border-color:var(--copper-deep)}
  .proc-meta{font-family:var(--mono);font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#F09A72;margin:0 0 18px}
  [x-cloak]{display:none!important}

  /* ---------- shop ---------- */
  .shop-top{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;flex-wrap:wrap;margin-bottom:34px}
  .shop-top .sec-head{margin-bottom:0}
  .shop{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
  .prod{background:var(--paper);border:1px solid var(--line);border-radius:14px;overflow:hidden;display:flex;flex-direction:column;transition:transform .2s ease, box-shadow .2s ease}
  .prod:hover{transform:translateY(-4px);box-shadow:var(--shadow)}
  .prod .img{aspect-ratio:1/1;background:var(--base-2);position:relative;display:grid;place-items:center;color:var(--gray);border-bottom:1px solid var(--line-soft);overflow:hidden}
  .prod .img img{width:100%;height:100%;object-fit:cover}
  .prod .img svg{width:44%;height:44%;stroke:currentColor;fill:none;stroke-width:1.3;opacity:.55}
  .prod .img .badge{position:absolute;top:10px;left:10px;font-family:var(--mono);font-size:9.5px;letter-spacing:.12em;text-transform:uppercase;background:var(--charcoal);color:#fff;padding:4px 8px;border-radius:6px}
  .prod .body{padding:16px 16px 18px;display:flex;flex-direction:column;flex:1}
  .prod .cat{font-family:var(--mono);font-size:10px;letter-spacing:.14em;text-transform:uppercase;color:var(--gray)}
  .prod h3{font-size:20px;color:var(--charcoal);margin:6px 0 12px;line-height:1.05}
  .prod .foot{display:flex;align-items:center;justify-content:space-between;margin-top:auto;gap:10px}
  .prod .pr{font-family:var(--disp);font-weight:800;font-size:23px;color:var(--charcoal)}
  .add{font-family:var(--disp);text-transform:uppercase;font-weight:700;font-size:13px;letter-spacing:.04em;background:var(--charcoal);color:#fff;border:none;border-radius:9px;padding:9px 13px;cursor:pointer;display:inline-flex;gap:6px;align-items:center;transition:background .18s ease}
  .add:hover{background:var(--copper)}
  .add svg{width:14px;height:14px;stroke:currentColor;fill:none;stroke-width:2}

  /* ---------- final cta ---------- */
  .cta-band{background:linear-gradient(135deg,var(--charcoal),var(--charcoal-deep));color:#fff;border-radius:20px;padding:52px 48px;display:flex;align-items:center;justify-content:space-between;gap:36px;flex-wrap:wrap;position:relative;overflow:hidden}
  .cta-band .ret{position:absolute;right:-40px;top:50%;transform:translateY(-50%);width:280px;height:280px;opacity:.16;pointer-events:none}
  .cta-band .l{max-width:560px;position:relative}
  .cta-band .eyebrow{color:#F09A72}.cta-band .eyebrow::before{background:#F09A72}
  .cta-band h2{font-size:clamp(30px,4vw,48px);color:#fff;margin:14px 0 12px;font-weight:800}
  .cta-band p{color:rgba(255,255,255,.7);margin:0}
  .cta-band .r{display:flex;flex-direction:column;gap:12px;position:relative}
  .cta-band .r .note{font-family:var(--mono);font-size:12px;color:rgba(255,255,255,.55);letter-spacing:.08em}

  /* ---------- footer ---------- */
  footer{padding:60px 0 34px}
  .foot-grid{display:grid;grid-template-columns:1.4fr 1fr 1fr 1fr;gap:36px;padding-bottom:36px;border-bottom:1px solid var(--line)}
  .foot-brand .brand{margin-bottom:16px}
  .foot-brand p{color:var(--muted);font-size:14.5px;max-width:34ch;margin:0 0 18px}
  .socials{display:flex;gap:10px}
  .socials a{width:40px;height:40px;border-radius:10px;border:1px solid var(--line);display:grid;place-items:center;color:var(--charcoal);transition:.18s}
  .socials a:hover{background:var(--charcoal);color:#fff;border-color:var(--charcoal)}
  .socials svg{width:18px;height:18px;stroke:currentColor;fill:none;stroke-width:1.7}
  .foot-col h3{font-family:var(--disp);text-transform:uppercase;font-size:15px;letter-spacing:.08em;color:var(--charcoal);margin:0 0 16px}
  .foot-col a,.foot-col p{display:block;color:var(--muted);font-size:14.5px;margin-bottom:10px}
  .foot-col a:hover{color:var(--copper)}
  .foot-bottom{display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;padding-top:22px;font-family:var(--mono);font-size:11.5px;color:var(--gray);letter-spacing:.04em}

  /* ---------- toast ---------- */
  .toast{position:fixed;bottom:24px;left:50%;transform:translateX(-50%) translateY(140%);background:var(--charcoal);color:#fff;padding:13px 20px;border-radius:12px;font-family:var(--mono);font-size:13px;letter-spacing:.03em;z-index:200;box-shadow:var(--shadow-lg);transition:transform .3s cubic-bezier(.2,1.2,.4,1);display:flex;align-items:center;gap:10px}
  .toast.show{transform:translateX(-50%) translateY(0)}
  .toast .d{width:8px;height:8px;border-radius:50%;background:var(--copper)}

  /* ---------- reveal ---------- */
  .reveal{opacity:0;transform:translateY(22px);transition:opacity .6s ease, transform .6s ease}
  .reveal.in{opacity:1;transform:none}
  @media (prefers-reduced-motion:reduce){.reveal{opacity:1;transform:none;transition:none}}

  /* ---------- mobile ---------- */
  .mobile-menu{display:none}
  @media (max-width:960px){
    .hero-grid{grid-template-columns:1fr;gap:30px}
    .hero-badge{order:-1;max-width:360px;margin:0 auto}
    .values{grid-template-columns:repeat(2,1fr)}
    .courses{grid-template-columns:1fr;gap:16px}
    .course.feat{order:-1}
    .about-grid{grid-template-columns:1fr;gap:28px}
    .about-photo{max-width:340px}
    .steps{grid-template-columns:1fr 1fr;border-top:0}
    .step{border:1px solid rgba(255,255,255,.1);border-radius:12px;margin-bottom:12px}
    .shop{grid-template-columns:repeat(2,1fr)}
    .foot-grid{grid-template-columns:1fr 1fr}
  }
  @media (max-width:720px){
    nav.links,.nav-actions .btn{display:none}
    .hamburger{display:grid;place-items:center}
    .hero-data{flex-wrap:wrap}.hero-data .cell{min-width:50%;border-bottom:1px solid var(--line-soft)}
    .mobile-menu.open{display:block;position:fixed;inset:70px 0 0;background:var(--base);z-index:55;padding:26px;border-top:1px solid var(--line)}
    .mobile-menu a{display:block;font-family:var(--disp);text-transform:uppercase;font-size:26px;color:var(--charcoal);padding:16px 0;border-bottom:1px solid var(--line-soft)}
    .mobile-menu .btn{margin-top:22px;width:100%;justify-content:center}
    .mobile-menu a.btn-primary{color:#fff}
    .cta-band{padding:40px 28px}
    .private{padding:24px}
  }
  @media (max-width:520px){
    .values,.shop,.foot-grid{grid-template-columns:1fr}
    .steps{grid-template-columns:1fr}
    .wrap{padding:0 18px}
  }
</style>
