<style>
    :root { --deep:#0f3d2c; --deep-2:#0a2d20; --acid:#d9ef8b; --canvas:#f1f0e9; --surface:#fbfaf5; --stroke:#d2d8d1; --text:#14231c; --soft:#64736b; }
    body { background-color:var(--canvas); background-image:radial-gradient(#173d2d12 0.7px,transparent 0.7px); background-size:18px 18px; }
    .site-topbar { position:relative; z-index:2; display:flex; justify-content:space-between; align-items:center; padding:0 0 34px; }
    .site-brand { display:flex; align-items:center; gap:10px; color:white; text-decoration:none; }
    .site-brand-mark { display:grid; width:38px; height:38px; place-items:center; border:1px solid #ffffff3b; border-radius:11px; background:#ffffff08; color:var(--acid); }
    .site-brand-mark svg { width:29px; height:29px; }
    .site-brand-copy { display:grid; gap:1px; }
    .site-brand-copy strong { font-size:12px; letter-spacing:.08em; text-transform:uppercase; }
    .site-brand-copy small { color:#bcd0c5; font-size:9px; }
    .site-nav { display:flex; align-items:center; gap:5px; margin:0; padding:0; }
    .site-nav a { min-width:auto; padding:8px 11px; border:0; border-radius:8px; background:transparent; color:#d8e4dd; font-size:11px; font-weight:700; text-decoration:none; }
    .site-nav a:hover,.site-nav a.active { background:#ffffff12; color:white; }
    .site-nav a.outline { margin-left:5px; border:1px solid #ffffff3b; }
    .section-kicker { display:block; margin-bottom:8px; color:var(--green); font-size:9px; font-weight:850; letter-spacing:.16em; text-transform:uppercase; }
    .dashboard header { position:relative; overflow:hidden; padding:24px 20px 96px; background:var(--deep); }
    .dashboard header::after { content:""; position:absolute; right:-100px; top:-180px; width:520px; height:520px; border:1px solid #d9ef8b24; border-radius:50%; box-shadow:0 0 0 70px #d9ef8b08,0 0 0 140px #d9ef8b05; }
    .dashboard .hero-grid { position:relative; z-index:1; display:grid; grid-template-columns:1.4fr .6fr; gap:60px; align-items:end; }
    .dashboard h1 { max-width:780px; font-size:clamp(46px,6.4vw,82px); letter-spacing:-.035em; }
    .dashboard header p { max-width:680px; font-size:14px; }
    .dashboard .hero-aside { padding:0 0 8px 20px; border-left:1px solid #ffffff29; }
    .dashboard .hero-aside span,.dashboard .hero-aside strong { display:block; }
    .dashboard .hero-aside span { color:#a9c0b4; font-size:9px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
    .dashboard .hero-aside strong { margin:7px 0 5px; color:var(--acid); font:500 32px Georgia,serif; }
    .dashboard .hero-aside small { color:#c6d5cd; font-size:10px; line-height:1.5; }
    .dashboard .filters { margin-top:-48px; padding:10px; border-color:#c6cec8; border-radius:15px; box-shadow:0 18px 55px #18302417; }
    .dashboard .filters input,.dashboard .filters select { min-height:54px; border:0; border-right:1px solid var(--line); border-radius:7px; background:transparent; font-size:12px; }
    .dashboard .filters button { min-height:54px; border-radius:10px; }
    .dashboard .analytics-head { margin:0 2px 16px; padding:38px 2px 0; }
    .dashboard .analytics-head h2,.dashboard .summary h2 { font-size:35px; letter-spacing:-.02em; }
    .dashboard .analytics { grid-template-columns:minmax(0,3fr) minmax(380px,2fr); grid-template-rows:repeat(3,minmax(86px,auto)); gap:0; overflow:hidden; border:1px solid var(--line); border-radius:18px; background:var(--card); }
    .dashboard .metric,.dashboard .ranking { min-height:0; border:0; border-radius:0; background:transparent; }
    .dashboard .metric { position:relative; grid-column:1; display:grid; grid-template-columns:34px minmax(180px,1fr) clamp(230px,25vw,300px); align-items:center; gap:16px; padding:17px 24px; border-right:1px solid var(--line); border-bottom:1px solid var(--line); }
    .dashboard .metric:nth-child(3) { border-bottom:0; }
    .dashboard .metric-index { position:static; color:#aeb9b1; font:700 9px ui-monospace,monospace; font-style:normal; }
    .dashboard .metric strong { display:flex; min-height:36px; margin:0; align-items:center; justify-content:flex-end; color:var(--green); font:500 27px/1 Georgia,serif; font-variant-numeric:lining-nums tabular-nums; letter-spacing:-.02em; text-align:right; }
    .dashboard .ranking { grid-column:2; grid-row:1/4; padding:24px; }
    .dashboard .insights { margin-top:14px; }
    .dashboard .insight,.dashboard .history { border-radius:18px; box-shadow:none; }
    .dashboard .insight { min-height:285px; padding:24px; }
    .dashboard .grid { grid-template-columns:repeat(3,1fr); }
    .dashboard article { min-height:178px; border-radius:14px; box-shadow:none; }
    .dashboard article:hover { border-color:#9cb0a4; box-shadow:0 13px 30px #1a33230d; }
    .dashboard article img { width:38%; filter:saturate(.82); }
    .dashboard .info { padding:17px 15px; }
    .dashboard article h3 { font-size:19px; }
    .dashboard .tag { padding:4px 7px; border-radius:6px; font-size:9px; }
    .dashboard .expense strong { font-size:15px; }

    .deputy-page header { position:relative; overflow:hidden; padding:22px 0 82px; background:var(--deep); }
    .deputy-page header::after { content:""; position:absolute; right:-80px; bottom:-240px; width:500px; height:500px; border:1px solid #d9ef8b24; border-radius:50%; box-shadow:0 0 0 60px #d9ef8b08; }
    .deputy-page .site-topbar { padding-bottom:35px; }
    .deputy-page .back { margin:0; padding:8px 0; font-size:11px; }
    .deputy-page .profile { position:relative; z-index:1; }
    .deputy-page .profile img { width:132px; height:154px; border:1px solid #ffffff52; border-radius:14px; filter:saturate(.82); }
    .deputy-page h1 { font-size:clamp(40px,5vw,68px); letter-spacing:-.035em; }
    .deputy-page main { position:relative; z-index:2; }
    .deputy-page .stats { position:relative; gap:0; overflow:hidden; border:1px solid var(--line); border-radius:17px; background:var(--card); box-shadow:0 18px 50px #18302412; }
    .deputy-page .stat { display:grid; grid-template-columns:minmax(120px,1fr) minmax(170px,auto); min-height:128px; align-items:center; gap:18px; padding:25px; border:0; border-right:1px solid var(--line); border-radius:0; box-shadow:none; }
    .deputy-page .stat strong { min-width:170px; margin:0; color:var(--green); font:500 27px/1 Georgia,serif; font-variant-numeric:lining-nums tabular-nums; letter-spacing:-.02em; text-align:right; white-space:nowrap; }
    .deputy-page .stat:last-child { border-right:0; }
    .deputy-page .section-head { margin-top:52px; }
    .deputy-page form { padding:10px; border-radius:15px; box-shadow:none; }
    .deputy-page form input,.deputy-page form select { border-color:transparent; border-right-color:var(--line); background:transparent; font-size:11px; }
    .deputy-page form button { grid-column:1; justify-self:start; width:auto; min-width:140px; min-height:42px; margin-top:8px; padding:0 20px; border-radius:9px; }
    .deputy-page .table-box { border-radius:16px; }
    .deputy-page th { padding:14px 18px; background:#e9eee9; color:#53645b; font-size:9px; }
    .deputy-page td { padding:17px 18px; font-size:12px; }
    .deputy-page tbody tr:hover { background:#f3f7f3; }

    .docs-page header { position:relative; overflow:hidden; padding:22px 20px 76px; background:var(--deep); }
    .docs-page header::after { content:"API"; position:absolute; right:-22px; bottom:-60px; color:#ffffff08; font:700 190px/1 ui-monospace,monospace; letter-spacing:-.1em; }
    .docs-page .site-topbar { padding-bottom:46px; }
    .docs-page h1 { font-size:clamp(46px,7vw,78px); letter-spacing:-.04em; }
    .docs-page header p { font-size:13px; }
    .docs-page header nav { margin:0; }
    .docs-page main { display:grid; grid-template-columns:220px 1fr; gap:50px; width:min(1080px,calc(100% - 32px)); }
    .docs-page .docs-intro { position:sticky; top:20px; align-self:start; }
    .docs-page .intro { margin:0; font-size:12px; }
    .docs-page .docs-content h2 { margin-top:0; }
    .docs-page .endpoint { padding:23px; border-radius:16px; box-shadow:none; }
    .docs-page pre { border:1px solid #274d3b; background:var(--deep-2); }

    @media(max-width:900px) {
        .dashboard .hero-grid { grid-template-columns:1fr; gap:28px; }
        .dashboard .hero-aside { display:none; }
        .dashboard .analytics { grid-template-columns:repeat(2,1fr); grid-template-rows:auto; }
        .dashboard .metric { grid-column:auto; display:block; min-height:138px; padding:22px; }
        .dashboard .metric:nth-child(2) { border-right:0; }
        .dashboard .metric:nth-child(3) { border-bottom:0; }
        .dashboard .metric-index { position:absolute; right:16px; top:14px; }
        .dashboard .metric strong { display:block; min-height:0; margin-top:23px; text-align:left; }
        .dashboard .ranking { grid-column:1/-1; grid-row:auto; border-top:1px solid var(--line); }
        .docs-page main { grid-template-columns:1fr; }
        .docs-page .docs-intro { position:static; }
    }
    @media(max-width:760px) {
        .deputy-page .stats { grid-template-columns:1fr; }
        .deputy-page .stat { display:block; min-height:110px; border-right:0; border-bottom:1px solid var(--line); }
        .deputy-page .stat:last-child { border-bottom:0; }
        .deputy-page .stat strong { min-width:0; margin-top:12px; text-align:left; }
    }
    @media(max-width:600px) {
        .site-topbar { padding-bottom:26px; }
        .site-brand-copy small,.site-nav a:not(.active):not(.outline) { display:none; }
        .dashboard header { padding-inline:16px; padding-bottom:78px; }
        .dashboard h1 { font-size:44px; }
        .dashboard .analytics { grid-template-columns:1fr; }
        .dashboard .metric { border-right:0; border-bottom:1px solid var(--line); }
        .dashboard .grid { grid-template-columns:1fr; }
        .deputy-page .site-topbar { padding-bottom:28px; }
        .deputy-page .profile { gap:17px; }
        .deputy-page h1 { font-size:34px; }
        .docs-page header { padding-inline:16px; }
    }
</style>
