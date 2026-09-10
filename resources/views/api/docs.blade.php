<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Documentação da API · Deputados em Dados</title>
    <style>
        :root { --ink:#17231d; --muted:#647069; --paper:#f4f1e9; --card:#fffdf8; --green:#164b35; --lime:#d9ef8b; --line:#d8ddd6; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--paper); color:var(--ink); font-family:Inter,ui-sans-serif,system-ui,sans-serif; }
        header { padding:54px 20px; background:var(--green); color:white; }
        .wrap { width:min(980px,calc(100% - 32px)); margin:auto; }
        .eyebrow { color:var(--lime); font-size:12px; font-weight:800; letter-spacing:.14em; text-transform:uppercase; }
        h1 { margin:12px 0 10px; font:500 clamp(38px,6vw,64px)/1 Georgia,serif; }
        header p { max-width:680px; color:#d7e3dc; line-height:1.6; }
        nav { display:flex; gap:12px; margin-top:22px; }
        nav a { padding:10px 14px; border:1px solid #ffffff55; border-radius:9px; color:white; text-decoration:none; font-weight:700; }
        main { padding:38px 0 70px; }
        h2 { margin:34px 0 8px; font:500 30px Georgia,serif; }
        .intro { color:var(--muted); line-height:1.65; }
        .endpoint { margin-top:14px; padding:20px; border:1px solid var(--line); border-radius:15px; background:var(--card); }
        .method { display:inline-block; padding:5px 8px; border-radius:6px; background:var(--lime); font-size:11px; font-weight:900; }
        code { color:var(--green); font:600 13px ui-monospace,SFMono-Regular,Consolas,monospace; }
        .endpoint h3 { display:inline; margin-left:9px; font-size:15px; }
        .endpoint p { margin:13px 0; color:var(--muted); }
        .params { display:flex; flex-wrap:wrap; gap:7px; }
        .params span { padding:5px 8px; border:1px solid var(--line); border-radius:7px; background:white; font:12px ui-monospace,monospace; }
        pre { overflow:auto; padding:16px; border-radius:10px; background:#102d21; color:#e5f1e9; font-size:12px; line-height:1.55; }
        .note { margin-top:30px; padding:18px; border-left:4px solid var(--green); background:var(--card); color:var(--muted); }
    </style>
</head>
<body>
<header><div class="wrap"><div class="eyebrow">Deputados em Dados · API v1</div><h1>Documentação da API</h1><p>Consulte deputados e despesas parlamentares em JSON, com filtros, paginação e identificadores oficiais da Câmara.</p><nav><a href="{{ route('deputies.index') }}">← Voltar ao painel</a><a href="{{ route('api.openapi') }}">OpenAPI YAML</a></nav></div></header>
<main class="wrap">
    <p class="intro">A URL base é <code>{{ url('/api/v1') }}</code>. As respostas paginadas possuem as propriedades <code>data</code>, <code>links</code> e <code>meta</code>. O limite é de 60 requisições por minuto e 100 itens por página.</p>
    <h2>Endpoints</h2>
    <section class="endpoint"><span class="method">GET</span><h3><code>/deputados</code></h3><p>Lista e pesquisa deputados, incluindo o resumo de despesas.</p><div class="params"><span>nome</span><span>partido</span><span>uf</span><span>ano_despesas</span><span>por_pagina</span></div><pre>GET {{ url('/api/v1/deputados?nome=Ana&partido=PT&uf=SP&ano_despesas=2025') }}</pre></section>
    <section class="endpoint"><span class="method">GET</span><h3><code>/deputados/{idDaCamara}</code></h3><p>Retorna um deputado pelo identificador oficial da Câmara.</p><pre>GET {{ url('/api/v1/deputados/74646') }}</pre></section>
    <section class="endpoint"><span class="method">GET</span><h3><code>/deputados/{idDaCamara}/despesas</code></h3><p>Lista as despesas do deputado com filtros combináveis.</p><div class="params"><span>ano</span><span>mes</span><span>tipo</span><span>fornecedor</span><span>data_inicial</span><span>data_final</span><span>por_pagina</span></div><pre>GET {{ url('/api/v1/deputados/74646/despesas?ano=2025&mes=3&fornecedor=Posto') }}</pre></section>
    <div class="note"><strong>Erros</strong><br>Filtros inválidos retornam HTTP 422 com os campos incorretos. Deputados inexistentes retornam HTTP 404. Ao ultrapassar o limite de requisições, a API retorna HTTP 429.</div>
</main>
</body>
</html>
