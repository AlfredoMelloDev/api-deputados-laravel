<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deputados em Dados</title>
    <style>
        :root { color-scheme: light; --ink:#17231d; --muted:#647069; --paper:#f4f1e9; --card:#fffdf8; --green:#164b35; --lime:#d9ef8b; --line:#d8ddd6; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--paper); color:var(--ink); font-family:Inter, ui-sans-serif, system-ui, sans-serif; }
        header { background:var(--green); color:white; padding:48px 20px 78px; }
        .wrap { width:min(1180px, calc(100% - 32px)); margin:auto; }
        .eyebrow { color:var(--lime); font-size:12px; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
        h1 { max-width:750px; margin:12px 0 10px; font-family:Georgia, serif; font-size:clamp(36px, 6vw, 68px); line-height:.98; font-weight:500; }
        header p { max-width:620px; margin:0; color:#d7e3dc; line-height:1.6; }
        .filters { position:relative; margin-top:-38px; padding:18px; background:var(--card); border:1px solid var(--line); border-radius:18px; box-shadow:0 14px 40px #1a33231a; display:grid; grid-template-columns:2fr 1fr 1fr auto; gap:12px; }
        input, select, button, .clear { min-height:48px; border-radius:10px; font:inherit; }
        input, select { width:100%; padding:0 14px; background:white; border:1px solid var(--line); color:var(--ink); }
        button { padding:0 22px; border:0; background:var(--green); color:white; font-weight:750; cursor:pointer; }
        .summary { display:flex; justify-content:space-between; align-items:end; gap:16px; padding:42px 2px 20px; }
        .summary h2 { margin:0; font:500 30px Georgia, serif; }
        .summary p { margin:5px 0 0; color:var(--muted); }
        .clear { color:var(--green); text-decoration:none; font-weight:700; }
        .grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; padding-bottom:30px; }
        article { overflow:hidden; display:flex; min-height:210px; background:var(--card); border:1px solid var(--line); border-radius:16px; transition:.2s; }
        article:hover { transform:translateY(-3px); box-shadow:0 12px 28px #1a332314; }
        article img { width:42%; object-fit:cover; object-position:top; background:#dce3dc; }
        .info { padding:20px 16px; display:flex; flex-direction:column; flex:1; }
        .tag { align-self:flex-start; padding:5px 8px; border-radius:99px; background:var(--lime); color:#20351d; font-size:11px; font-weight:850; }
        article h3 { margin:14px 0 5px; font:500 21px/1.15 Georgia, serif; }
        .location { color:var(--muted); font-size:13px; }
        .expense { margin-top:auto; padding-top:18px; border-top:1px solid var(--line); font-size:12px; color:var(--muted); }
        .expense strong { display:block; margin-top:3px; color:var(--green); font-size:17px; }
        nav { display:flex; justify-content:center; gap:7px; padding:8px 0 50px; }
        nav a, nav span { min-width:38px; padding:9px 11px; text-align:center; border:1px solid var(--line); border-radius:9px; color:var(--green); text-decoration:none; background:var(--card); }
        nav span.active { background:var(--green); color:white; border-color:var(--green); }
        .empty { grid-column:1/-1; padding:50px; text-align:center; background:var(--card); border:1px dashed var(--line); border-radius:16px; }
        @media (max-width:900px) { .filters { grid-template-columns:1fr 1fr; } .grid { grid-template-columns:repeat(2,1fr); } }
        @media (max-width:600px) { header { padding-top:35px; } .filters, .grid { grid-template-columns:1fr; } .summary { align-items:start; flex-direction:column; } article { min-height:190px; } }
    </style>
</head>
<body>
<header>
    <div class="wrap">
        <div class="eyebrow">Câmara dos Deputados · Dados Abertos</div>
        <h1>Deputados em Dados</h1>
        <p>Consulte representantes, partidos, estados e despesas parlamentares em uma visão simples e transparente.</p>
    </div>
</header>
<main class="wrap">
    <form class="filters" action="{{ route('deputies.index') }}" method="GET">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar pelo nome do deputado">
        <select name="party"><option value="">Todos os partidos</option>@foreach($parties as $party)<option value="{{ $party }}" @selected(request('party') === $party)>{{ $party }}</option>@endforeach</select>
        <select name="state"><option value="">Todos os estados</option>@foreach($states as $state)<option value="{{ $state }}" @selected(request('state') === $state)>{{ $state }}</option>@endforeach</select>
        <button type="submit">Filtrar</button>
    </form>
    <section class="summary">
        <div><h2>Representantes</h2><p>{{ $deputies->total() }} {{ $deputies->total() === 1 ? 'deputado encontrado' : 'deputados encontrados' }}</p></div>
        @if(request()->hasAny(['search', 'party', 'state']))<a class="clear" href="{{ route('deputies.index') }}">Limpar filtros</a>@endif
    </section>
    <section class="grid">
        @forelse($deputies as $deputy)
            <article>
                <img src="{{ $deputy->photo_url }}" alt="Foto de {{ $deputy->name }}" loading="lazy">
                <div class="info">
                    <span class="tag">{{ $deputy->party_acronym ?: 'Sem partido' }}</span>
                    <h3>{{ $deputy->name }}</h3>
                    <span class="location">{{ $deputy->state_acronym ?: 'Estado não informado' }} · ID {{ $deputy->camara_id }}</span>
                    <div class="expense">{{ $deputy->expenses_count }} despesas importadas<strong>R$ {{ number_format((float) ($deputy->expenses_sum_net_value ?? 0), 2, ',', '.') }}</strong></div>
                </div>
            </article>
        @empty
            <div class="empty"><strong>Nenhum deputado encontrado.</strong><br>Altere os filtros e tente novamente.</div>
        @endforelse
    </section>
    @if($deputies->hasPages())
        <nav aria-label="Paginação">
            @if($deputies->onFirstPage())<span>‹</span>@else<a href="{{ $deputies->previousPageUrl() }}">‹</a>@endif
            @foreach($deputies->getUrlRange(max(1, $deputies->currentPage()-2), min($deputies->lastPage(), $deputies->currentPage()+2)) as $page => $url)
                @if($page === $deputies->currentPage())<span class="active">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
            @endforeach
            @if($deputies->hasMorePages())<a href="{{ $deputies->nextPageUrl() }}">›</a>@else<span>›</span>@endif
        </nav>
    @endif
</main>
</body>
</html>
