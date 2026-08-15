<style>
:root{
  --charcoal: #2C3E50;
  --charcoal-deep: #1E2C39;
  --charcoal-d: var(--charcoal-deep);
  --copper: #D45B2E;
  --copper-deep: #B4471F;
  --copper-d: var(--copper-deep);
  --slate: #5C6B72;
  --gray: var(--slate);
  --base: #EFEFEA;
  --paper: #FFFFFF;
  --ink: #22303C;
  --line: #D8D8D2;
  --ok: #4E7A51;
  --radius: 2px;
  --disp: 'Saira Condensed', 'Arial Narrow', sans-serif;
  --body: 'IBM Plex Sans', system-ui, sans-serif;
  --mono: 'IBM Plex Mono', ui-monospace, monospace;
}
.rb-page{background:var(--base);color:var(--ink);font-family:var(--body);font-size:15px;line-height:1.55;-webkit-font-smoothing:antialiased;margin:0}
.rb-page h1,.rb-page h2,.rb-page h3,.rb-page h4,.rb-page .display{font-family:var(--disp);font-weight:700;letter-spacing:.01em;line-height:1.05;text-transform:uppercase;margin:0}
.rb-page .mono,.rb-page code,.rb-page .num{font-family:var(--mono);font-variant-numeric:tabular-nums}
.rb-page a{color:inherit;text-decoration:none}
.rb-page button,.rb-page select,.rb-page input,.rb-page textarea{font-family:inherit;font-size:inherit;color:inherit}
.rb-page :focus-visible{outline:2px solid var(--copper);outline-offset:2px}
.rb-page .wrap{max-width:1320px;margin:0 auto;padding:0 20px}
.rb-page header.nav{position:sticky;top:0;z-index:60;background:var(--charcoal);color:#fff;border-bottom:2px solid var(--copper)}
.rb-page .nav-in{display:flex;align-items:center;gap:18px;height:62px}
.rb-page .brand{display:flex;align-items:center;gap:10px}
.rb-page .brand .mark{width:26px;height:26px;flex:0 0 26px}
.rb-page .brand .txt{font-family:var(--disp);font-weight:700;font-size:19px;letter-spacing:.06em;line-height:1;text-transform:uppercase}
.rb-page .brand .txt small{display:block;font-family:var(--mono);font-size:9px;letter-spacing:.22em;color:var(--copper);font-weight:500;margin-top:3px}
.rb-page .nav-spacer{flex:1}
.rb-page .quoteref{font-family:var(--mono);font-size:11px;letter-spacing:.1em;color:#9fb0bd}
.rb-page .quoteref b{color:#fff;font-weight:500}
.rb-page .hero{background:var(--charcoal-deep);color:#fff;position:relative;overflow:hidden;padding:34px 0 0}
.rb-page .hero .retbg{position:absolute;right:-90px;top:-70px;width:420px;opacity:.10;color:var(--copper);pointer-events:none}
.rb-page .hero h1{font-size:clamp(30px,5vw,52px);color:#fff}
.rb-page .hero h1 em{font-style:normal;color:var(--copper)}
.rb-page .hero p{max-width:620px;color:#c3cdd4;margin-top:10px;font-size:15px}
.rb-page .hero .kicker{font-family:var(--mono);font-size:11px;letter-spacing:.24em;color:var(--copper);text-transform:uppercase;margin-bottom:10px}
.rb-page .strip{margin-top:26px;border-top:1px solid rgba(255,255,255,.14);background:rgba(0,0,0,.18)}
.rb-page .strip-in{display:flex;flex-wrap:wrap;font-family:var(--mono);font-size:11px;letter-spacing:.11em;text-transform:uppercase}
.rb-page .strip-in div{padding:11px 22px 11px 0;margin-right:22px;border-right:1px solid rgba(255,255,255,.12);color:#8fa2b0}
.rb-page .strip-in div:last-child{border-right:0}
.rb-page .strip-in b{color:#fff;font-weight:500}
.rb-page .builder{display:grid;grid-template-columns:minmax(0,1fr) 372px;gap:26px;padding:26px 0 60px;align-items:start}
.rb-page .builder>*{min-width:0}
@media(max-width:1080px){.rb-page .builder{grid-template-columns:minmax(0,1fr)}}
.rb-page .step{background:var(--paper);border:1px solid var(--line);border-radius:var(--radius);margin-bottom:14px}
.rb-page .step-hd{display:flex;align-items:center;gap:12px;padding:13px 16px;cursor:pointer;user-select:none;border:0;border-left:3px solid var(--line);background:transparent;width:100%;text-align:left}
.rb-page .step.done .step-hd{border-left-color:var(--copper)}
.rb-page .step-no{font-family:var(--mono);font-size:11px;color:#fff;background:var(--charcoal);width:24px;height:24px;display:grid;place-items:center;border-radius:2px;flex:0 0 24px}
.rb-page .step.done .step-no{background:var(--copper)}
.rb-page .step-ti{font-family:var(--disp);text-transform:uppercase;font-size:19px;font-weight:700;letter-spacing:.02em}
.rb-page .step-ti span.opt{font-family:var(--mono);font-size:9.5px;letter-spacing:.14em;color:var(--slate);margin-left:8px;font-weight:400;border:1px solid var(--line);padding:2px 5px;border-radius:2px;vertical-align:middle}
.rb-page .step-pick{margin-left:auto;font-family:var(--mono);font-size:11px;color:var(--slate);text-align:right;max-width:44%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.rb-page .step.done .step-pick{color:var(--charcoal)}
.rb-page .chev{width:16px;height:16px;flex:0 0 16px;transition:transform .18s;color:var(--slate)}
.rb-page .step.open .chev{transform:rotate(180deg)}
.rb-page .step-bd{padding:4px 16px 18px;border-top:1px solid var(--line)}
.rb-page .hint{font-size:12.5px;color:var(--slate);margin:12px 0 14px;border-left:2px solid var(--copper);padding-left:10px}
.rb-page .opts{display:grid;grid-template-columns:repeat(auto-fill,minmax(216px,1fr));gap:12px;margin-top:14px}
.rb-page .opt{position:relative;border:1px solid var(--line);border-radius:var(--radius);background:#fff;cursor:pointer;display:flex;flex-direction:column;text-align:left;padding:0;transition:border-color .15s,box-shadow .15s}
.rb-page .opt:hover{border-color:var(--charcoal)}
.rb-page .opt.sel{border-color:var(--copper);box-shadow:inset 0 0 0 1px var(--copper)}
.rb-page .opt.disabled{opacity:.42;cursor:not-allowed;filter:grayscale(1)}
.rb-page .opt .thumb{height:88px;background:#F4F4F0;border-bottom:1px solid var(--line);display:grid;place-items:center;color:var(--charcoal);overflow:hidden}
.rb-page .opt .thumb svg{width:88%;height:74%}
.rb-page .opt .thumb img{width:100%;height:100%;object-fit:cover}
.rb-page .opt .bd{padding:10px 11px 11px;display:flex;flex-direction:column;gap:5px;flex:1}
.rb-page .opt .brand{font-family:var(--mono);font-size:9.5px;letter-spacing:.15em;text-transform:uppercase;color:var(--copper)}
.rb-page .opt .nm{font-family:var(--disp);text-transform:uppercase;font-size:16px;line-height:1.1;font-weight:600}
.rb-page .opt .sp{font-family:var(--mono);font-size:10.5px;color:var(--slate);line-height:1.5}
.rb-page .opt .pr{margin-top:auto;font-family:var(--mono);font-size:14px;font-weight:600;padding-top:7px;border-top:1px dashed var(--line)}
.rb-page .opt .pr small{color:var(--slate);font-weight:400;font-size:9.5px;letter-spacing:.1em}
.rb-page .opt .tick{position:absolute;top:7px;right:7px;width:20px;height:20px;border-radius:50%;background:var(--copper);color:#fff;display:none;place-items:center;font-size:12px}
.rb-page .opt.sel .tick{display:grid}
.rb-page .opt .badge{position:absolute;top:7px;left:7px;background:var(--charcoal);color:#fff;font-family:var(--mono);font-size:9px;letter-spacing:.1em;padding:2px 6px;border-radius:2px}
.rb-page .opt .qty{display:flex;align-items:center;gap:6px;margin-top:6px}
.rb-page .opt .qty button{width:22px;height:22px;border:1px solid var(--line);background:#fff;cursor:pointer;font-family:var(--mono);line-height:1}
.rb-page .opt .qty input{width:38px;text-align:center;border:1px solid var(--line);font-family:var(--mono);padding:2px}
.rb-page .fitwarn{font-family:var(--mono);font-size:9.5px;color:#B03A2E;letter-spacing:.06em;margin-top:4px}
.rb-page .cfg{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;margin-top:14px;padding:12px;background:#F7F7F3;border:1px solid var(--line)}
.rb-page .cfg label{display:block;font-family:var(--mono);font-size:9.5px;letter-spacing:.15em;text-transform:uppercase;color:var(--slate);margin-bottom:4px}
.rb-page .cfg select,.rb-page .cfg input{width:100%;padding:7px 8px;border:1px solid var(--line);background:#fff;border-radius:2px;font-family:var(--mono);font-size:12.5px}
.rb-page .summary{position:sticky;top:78px}
.rb-page .dope{background:var(--charcoal);color:#fff;border:1px solid var(--charcoal);border-radius:var(--radius);overflow:hidden}
.rb-page .dope-hd{padding:12px 15px;border-bottom:1px solid rgba(255,255,255,.15);display:flex;align-items:center;gap:9px}
.rb-page .dope-hd h3{font-size:18px;color:#fff}
.rb-page .dope-hd .rt{margin-left:auto;font-family:var(--mono);font-size:10px;letter-spacing:.14em;color:var(--copper)}
.rb-page .dope-bd{padding:5px 15px 12px;max-height:44vh;overflow:auto}
.rb-page .line{display:flex;gap:10px;padding:7px 0;border-bottom:1px dotted rgba(255,255,255,.16);font-family:var(--mono);font-size:11.5px;align-items:flex-start}
.rb-page .line .lb{color:#8fa2b0;flex:0 0 84px;letter-spacing:.08em;text-transform:uppercase;font-size:9.5px;padding-top:2px}
.rb-page .line .vl{flex:1;color:#fff;line-height:1.4}
.rb-page .line .pv{text-align:right;white-space:nowrap;color:#fff}
.rb-page .empty{padding:22px 0;text-align:center;color:#7f93a3;font-family:var(--mono);font-size:11px;letter-spacing:.1em}
.rb-page .totals{background:var(--charcoal-deep);padding:12px 15px;border-top:2px solid var(--copper)}
.rb-page .trow{display:flex;justify-content:space-between;font-family:var(--mono);font-size:11.5px;color:#a9bac6;padding:3px 0}
.rb-page .trow.grand{font-size:22px;color:#fff;padding-top:9px;margin-top:6px;border-top:1px solid rgba(255,255,255,.18);align-items:baseline}
.rb-page .trow.grand span:first-child{font-size:10px;letter-spacing:.18em;color:var(--copper);text-transform:uppercase}
.rb-page .acts{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:12px 15px;background:var(--charcoal-deep);border-top:1px solid rgba(255,255,255,.12)}
.rb-page .btn{display:block;width:100%;text-align:center;padding:10px 12px;border:1px solid var(--copper);background:var(--copper);color:#fff;font-family:var(--mono);font-size:11px;letter-spacing:.13em;text-transform:uppercase;cursor:pointer;border-radius:2px}
.rb-page .btn:hover{background:var(--copper-deep);border-color:var(--copper-deep)}
.rb-page .btn.ghost{background:transparent;color:#fff;border-color:rgba(255,255,255,.35)}
.rb-page .btn.wide{grid-column:1/-1}
.rb-page .costline{font-family:var(--mono);font-size:9.5px;color:var(--slate);letter-spacing:.06em}
.rb-page .divider{display:flex;align-items:center;gap:14px;margin:36px 0 8px;color:var(--copper)}
.rb-page .divider .ln{height:1px;background:var(--line);flex:1}
.rb-page .divider svg{width:22px;height:22px;flex:0 0 22px}
.rb-page footer.rb-foot{background:var(--charcoal);color:#9fb0bd;margin-top:40px;padding:34px 0 26px;font-size:13px}
.rb-page footer.rb-foot .cols{display:grid;grid-template-columns:2fr 1fr 1fr;gap:26px}
.rb-page footer.rb-foot h5{font-family:var(--disp);text-transform:uppercase;color:#fff;font-size:15px;margin-bottom:8px}
.rb-page .toast{position:fixed;left:50%;bottom:26px;transform:translate(-50%,80px);background:var(--charcoal);color:#fff;border-left:3px solid var(--copper);padding:11px 18px;font-family:var(--mono);font-size:12px;z-index:90;opacity:0;transition:all .28s;pointer-events:none}
.rb-page .toast.on{transform:translate(-50%,0);opacity:1}
.rb-page .modal{position:fixed;inset:0;background:rgba(31,45,58,.72);z-index:100;display:none;padding:26px;overflow:auto}
.rb-page .modal.on{display:block}
.rb-page .modal-in{max-width:520px;margin:40px auto;background:#fff;border-top:4px solid var(--copper);padding:20px 22px 24px}
.rb-page .fld{margin-bottom:10px}
.rb-page .fld label{display:block;font-family:var(--mono);font-size:9px;letter-spacing:.15em;text-transform:uppercase;color:var(--slate);margin-bottom:3px}
.rb-page .fld input,.rb-page .fld textarea{width:100%;padding:8px;border:1px solid var(--line);border-radius:2px;font-family:var(--mono);font-size:13px}
@media(prefers-reduced-motion:reduce){.rb-page .toast{transition:none}.rb-page .chev{transition:none}html{scroll-behavior:auto}}
@media(max-width:760px){
  .rb-page .nav-in{height:auto;padding:10px 0;flex-wrap:wrap}
  .rb-page .quoteref{display:none}
  .rb-page .hero .retbg{display:none}
  .rb-page .opts{grid-template-columns:repeat(auto-fill,minmax(148px,1fr))}
  .rb-page .summary{position:static}
  .rb-page .step-pick{display:none}
}
/* Filament embed: reuse the same cards without the charcoal page chrome */
.fi-body .rb-picker .step{background:#fff;border:1px solid var(--line,#D8D8D2);margin-bottom:12px}
.fi-body .rb-picker .opts{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px}
.fi-body .rb-picker .opt{border:1px solid #D8D8D2;padding:0;background:#fff;cursor:pointer;text-align:left}
.fi-body .rb-picker .opt.sel{border-color:var(--copper,#D45B2E)}
.fi-body .rb-picker .opt.disabled{opacity:.4}
</style>
