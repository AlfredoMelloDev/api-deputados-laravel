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
        .filters { position:relative; margin-top:-38px; padding:18px; background:var(--card); border:1px solid var(--line); border-radius:18px; box-shadow:0 14px 40px #1a33231a; display:grid; grid-template-columns:minmax(260px,2fr) minmax(150px,1fr) minmax(150px,1fr) minmax(125px,.75fr) auto; gap:12px; }
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
        .card-link { display:flex; width:100%; color:inherit; text-decoration:none; }
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
        .analytics-head { display:flex; justify-content:space-between; align-items:end; margin:38px 2px 16px; }
        .analytics-head h2 { margin:0; font:500 30px Georgia, serif; }
        .analytics-head p { margin:5px 0 0; color:var(--muted); }
        .analytics { display:grid; grid-template-columns:repeat(3,1fr) 2fr; gap:14px; }
        .metric, .ranking { padding:20px; background:var(--card); border:1px solid var(--line); border-radius:16px; }
        .metric span { color:var(--muted); font-size:12px; }
        .metric strong { display:block; margin-top:8px; color:var(--green); font:500 clamp(21px,1.7vw,27px) Georgia, serif; line-height:1.15; white-space:nowrap; }
        .ranking h3 { margin:0 0 14px; font-size:14px; }
        .rank { margin-top:10px; }
        .rank-label { display:flex; justify-content:space-between; gap:12px; font-size:11px; }
        .rank-label span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .bar { height:6px; margin-top:5px; overflow:hidden; border-radius:99px; background:#e8e9e3; }
        .bar i { display:block; height:100%; border-radius:inherit; background:var(--green); }
        .history { margin-top:16px; padding:20px; background:var(--card); border:1px solid var(--line); border-radius:16px; }
        .history-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
        .history-head h3 { margin:0; font-size:16px; }
        .runs { display:grid; gap:8px; }
        .run { display:grid; grid-template-columns:70px 1fr auto; align-items:center; gap:14px; padding:11px 0; border-top:1px solid var(--line); font-size:12px; }
        .run:first-child { border-top:0; }
        .run-progress { height:7px; overflow:hidden; border-radius:99px; background:#e8e9e3; }
        .run-progress i { display:block; height:100%; background:var(--green); }
        .status { color:var(--muted); white-space:nowrap; }
        @media (max-width:1100px) { .filters { grid-template-columns:2fr 1fr 1fr; } .filters button { grid-column:span 1; } }
        @media (max-width:900px) { .filters { grid-template-columns:1fr 1fr; } .grid { grid-template-columns:repeat(2,1fr); } .analytics { grid-template-columns:repeat(3,1fr); } .ranking { grid-column:1/-1; } }
        @media (max-width:600px) { header { padding-top:35px; } .filters, .grid, .analytics { grid-template-columns:1fr; } .ranking { grid-column:auto; } .summary { align-items:start; flex-direction:column; } article { min-height:190px; } .metric strong { font-size:24px; } }
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
        <select name="expense_year" aria-label="Ano das despesas"><option value="">Ano: {{ $analyticsYear }}</option>@foreach($availableYears as $year)<option value="{{ $year }}" @selected((string) request('expense_year') === (string) $year)>Ano: {{ $year }}</option>@endforeach</select>
        <button type="submit">Filtrar</button>
    </form>
    <section class="analytics-head"><div><h2>Visão geral de {{ $analyticsYear }}</h2><p>Indicadores atualizados a partir dos dados importados.</p></div></section>
    <section class="analytics">
        <div class="metric"><span>Valor líquido</span><strong>R$ {{ number_format($analytics['total'], 2, ',', '.') }}</strong></div>
        <div class="metric"><span>Despesas registradas</span><strong>{{ number_format($analytics['count'], 0, ',', '.') }}</strong></div>
        <div class="metric"><span>Deputados com gastos</span><strong>{{ number_format($analytics['deputies'], 0, ',', '.') }}</strong></div>
        <div class="ranking">
            <h3>Maiores categorias de despesa</h3>
            @php($largestTypeTotal = (float) ($topExpenseTypes->max('total') ?? 0))
            @forelse($topExpenseTypes as $expenseType)
                <div class="rank"><div class="rank-label"><span>{{ $expenseType->expense_type }}</span><strong>R$ {{ number_format((float) $expenseType->total, 2, ',', '.') }}</strong></div><div class="bar"><i style="width:{{ $largestTypeTotal > 0 ? ((float) $expenseType->total / $largestTypeTotal) * 100 : 0 }}%"></i></div></div>
            @empty
                <span class="location">Ainda não há despesas para este ano.</span>
            @endforelse
        </div>
    </section>
    @if($syncRuns->isNotEmpty())
        <section class="history">
            <div class="history-head"><h3>Histórico de sincronizações</h3><span class="location">Processamento da API da Câmara</span></div>
            <div class="runs">
                @foreach($syncRuns as $run)
                    @php($progress = $run->total_deputies > 0 ? min(100, ($run->processed_deputies / $run->total_deputies) * 100) : 100)
                    <div class="run">
                        <strong>{{ $run->year }}</strong>
                        <div><div class="run-progress"><i style="width:{{ $progress }}%"></i></div><span class="location">{{ $run->processed_deputies }}/{{ $run->total_deputies }} deputados · {{ number_format($run->expenses_received, 0, ',', '.') }} despesas</span></div>
                        <span class="status">{{ match($run->status) { 'completed' => 'Concluída', 'completed_with_errors' => 'Concluída com falhas', default => 'Em andamento' } }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    <section class="summary">
        <div><h2>Representantes</h2><p>{{ $deputies->total() }} {{ $deputies->total() === 1 ? 'deputado encontrado' : 'deputados encontrados' }}</p></div>
        @if(request()->hasAny(['search', 'party', 'state', 'expense_year']))<a class="clear" href="{{ route('deputies.index') }}">Limpar filtros</a>@endif
    </section>
    <section class="grid">
        @forelse($deputies as $deputy)
            <article>
                <a class="card-link" href="{{ route('deputies.show', $deputy) }}">
                    <img src="{{ $deputy->photo_url }}" alt="Foto de {{ $deputy->name }}" loading="lazy">
                    <div class="info">
                        <span class="tag">{{ $deputy->party_acronym ?: 'Sem partido' }}</span>
                        <h3>{{ $deputy->name }}</h3>
                        <span class="location">{{ $deputy->state_acronym ?: 'Estado não informado' }} · ID {{ $deputy->camara_id }}</span>
                        <div class="expense">{{ $deputy->expenses_count }} despesas importadas<strong>R$ {{ number_format((float) ($deputy->expenses_sum_net_value ?? 0), 2, ',', '.') }}</strong></div>
                    </div>
                </a>
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
