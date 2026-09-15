<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        .api-link { display:inline-block; margin-top:18px; padding:9px 13px; border:1px solid #ffffff55; border-radius:9px; color:white; font-size:13px; font-weight:700; text-decoration:none; }
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
        .sync-notice { display:flex; justify-content:space-between; align-items:center; gap:18px; margin-bottom:16px; padding:14px 16px; border:1px solid #d8c982; border-radius:14px; background:#fff9db; color:#594c13; }
        .sync-notice strong, .sync-notice small { display:block; }
        .sync-notice small { margin-top:3px; color:#75671f; }
        .sync-notice.complete { border-color:#b9d5c6; background:#eef8f2; color:#174b35; }
        .sync-notice.error { border-color:#e1b9ae; background:#fff4f0; color:#762d1d; }
        .sync-badge { flex:0 0 auto; padding:7px 10px; border-radius:99px; background:#ffffffa6; font-size:12px; font-weight:800; white-space:nowrap; }
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
        .alert { display:flex; gap:12px; margin-top:16px; padding:16px 18px; border:1px solid #e1b9ae; border-radius:14px; background:#fff4f0; color:#762d1d; }
        .alert strong { display:block; margin-bottom:3px; }
        .alert p { margin:0; color:#8b4a3d; font-size:13px; }
        .run-error { display:block; margin-top:4px; color:#9b3d2b; }
        .insights { display:grid; grid-template-columns:1.2fr 1fr; gap:14px; margin-top:16px; }
        .insight { padding:20px; background:var(--card); border:1px solid var(--line); border-radius:16px; }
        .insight h3 { margin:0 0 18px; font-size:16px; }
        .month-chart { display:grid; grid-template-columns:repeat(12,1fr); align-items:end; gap:7px; height:190px; padding-top:10px; }
        .month { display:flex; height:100%; flex-direction:column; justify-content:end; align-items:center; gap:6px; color:var(--muted); font-size:10px; }
        .month i { width:100%; min-height:3px; border-radius:5px 5px 2px 2px; background:var(--green); }
        .deputy-rank { display:grid; grid-template-columns:34px 1fr auto; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid var(--line); color:inherit; text-decoration:none; }
        .deputy-rank:last-child { border-bottom:0; }
        .deputy-rank img { width:34px; height:34px; border-radius:50%; object-fit:cover; object-position:top; background:#dce3dc; }
        .deputy-rank strong, .deputy-rank small { display:block; }
        .deputy-rank small { color:var(--muted); margin-top:2px; }
        .deputy-rank > strong { color:var(--green); font-size:12px; white-space:nowrap; }
        .assistant-toggle { position:fixed; right:24px; bottom:24px; z-index:20; display:flex; align-items:center; gap:11px; min-height:58px; padding:7px 18px 7px 8px; border:1px solid #ffffff38; border-radius:16px; background:#123e2d; box-shadow:0 16px 36px #123b2b3d; transition:transform .2s,box-shadow .2s; }
        .assistant-toggle:hover { transform:translateY(-2px); box-shadow:0 20px 42px #123b2b52; }
        .assistant-toggle-icon { display:grid; width:42px; height:42px; place-items:center; border-radius:11px; background:var(--lime); color:#123e2d; }
        .assistant-toggle-icon svg { width:20px; height:20px; }
        .assistant-toggle-copy { display:grid; gap:1px; text-align:left; }
        .assistant-toggle-copy strong { font-size:13px; }
        .assistant-toggle-copy small { color:#cfe0d7; font-size:10px; font-weight:500; }
        .assistant-panel { position:fixed; right:24px; bottom:94px; z-index:20; width:min(470px,calc(100vw - 32px)); max-height:min(720px,calc(100vh - 120px)); overflow:auto; border:1px solid #ffffff6b; border-radius:22px; background:#f8f6ef; box-shadow:0 28px 80px #0c241a42; animation:assistant-in .22s ease-out; }
        .assistant-panel[hidden] { display:none; }
        .assistant-head { position:relative; display:flex; justify-content:space-between; align-items:start; gap:15px; padding:22px 22px 20px; overflow:hidden; background:#123e2d; color:white; }
        .assistant-head::after { content:""; position:absolute; right:-36px; bottom:-58px; width:170px; height:170px; border:1px solid #d9ef8b38; border-radius:50%; box-shadow:0 0 0 24px #d9ef8b0d,0 0 0 50px #d9ef8b09; }
        .assistant-eyebrow { position:relative; z-index:1; display:flex; align-items:center; gap:7px; margin-bottom:8px; color:var(--lime); font-size:9px; font-weight:850; letter-spacing:.14em; text-transform:uppercase; }
        .assistant-eyebrow i { width:6px; height:6px; border-radius:50%; background:var(--lime); box-shadow:0 0 0 4px #d9ef8b1f; }
        .assistant-head h2 { position:relative; z-index:1; margin:0; font:500 25px Georgia,serif; }
        .assistant-head p { position:relative; z-index:1; max-width:310px; margin:6px 0 0; color:#cfe0d7; font-size:11px; line-height:1.5; }
        .assistant-close { position:relative; z-index:2; min-height:34px; padding:0 10px; border:1px solid #ffffff33; background:#ffffff0d; color:white; }
        .assistant-body { padding:19px 20px 20px; }
        .assistant-section-label { display:block; margin-bottom:9px; color:#718078; font-size:9px; font-weight:850; letter-spacing:.13em; text-transform:uppercase; }
        .assistant-suggestions { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:18px; }
        .assistant-suggestion { display:grid; gap:3px; min-height:66px; padding:11px 12px; border:1px solid #d8ddd6; border-radius:12px; background:#fffdf8; color:var(--ink); font-size:11px; font-weight:650; line-height:1.3; text-align:left; transition:border-color .18s,transform .18s,background .18s; }
        .assistant-suggestion:hover { transform:translateY(-1px); border-color:#9bb9a9; background:#f3f8f4; }
        .assistant-suggestion span { color:var(--green); font-size:9px; font-weight:850; letter-spacing:.09em; text-transform:uppercase; }
        .assistant-suggestion:last-child { grid-column:1/-1; min-height:52px; }
        .assistant-form { display:grid; grid-template-columns:1fr auto; gap:8px; padding:6px; border:1px solid #bfc9c2; border-radius:14px; background:white; box-shadow:0 7px 20px #17231d0a; }
        .assistant-form:focus-within { border-color:var(--green); box-shadow:0 0 0 3px #164b3512; }
        .assistant-form input { min-width:0; min-height:42px; padding:0 10px; border:0; outline:0; background:transparent; font-size:12px; }
        .assistant-form button { min-height:42px; padding:0 15px; border-radius:10px; font-size:11px; }
        .assistant-result { margin-top:16px; padding:16px; border:1px solid #d8ddd6; border-radius:15px; background:#fffdf8; }
        .assistant-result[hidden] { display:none; }
        .assistant-query { display:inline-block; max-width:90%; margin:0 0 15px auto; padding:8px 11px; border-radius:11px 11px 3px 11px; background:#edf3ee; color:#456052; font-size:10px; }
        .assistant-result h3 { margin:0 0 6px; font:500 18px Georgia,serif; }
        .assistant-answer { margin:0; color:var(--muted); font-size:11px; line-height:1.55; }
        .assistant-items { display:grid; gap:7px; margin-top:13px; counter-reset:ranking; }
        .assistant-item { display:grid; grid-template-columns:28px 1fr auto; gap:9px; align-items:center; padding:10px; border:1px solid #e1e5df; border-radius:11px; background:#fbfaf5; color:inherit; text-decoration:none; transition:border-color .18s,background .18s; }
        a.assistant-item:hover { border-color:#aac1b4; background:#f2f7f3; }
        .assistant-position { display:grid; width:27px; height:27px; place-items:center; border-radius:8px; background:#e8eee9; color:var(--green); font-size:9px; font-weight:850; }
        .assistant-item:first-child .assistant-position { background:var(--lime); color:#263a21; }
        .assistant-item strong,.assistant-item small { display:block; }
        .assistant-item-label { font-size:13px; line-height:1.28; }
        .assistant-item small { margin-top:3px; color:var(--muted); font-size:10px; line-height:1.25; }
        .assistant-value { color:var(--green); font-size:11px; line-height:1.2; white-space:nowrap; }
        .assistant-coverage { margin:12px 0 0; padding:8px 10px; border-radius:9px; background:#fff6d6; color:#75671f; font-size:9px; }
        .assistant-coverage.complete { background:#eef8f2; color:var(--green); }
        @keyframes assistant-in { from { opacity:0; transform:translateY(12px) scale(.98); } to { opacity:1; transform:none; } }
        @media (max-width:1100px) { .filters { grid-template-columns:2fr 1fr 1fr; } .filters button { grid-column:span 1; } }
        @media (max-width:900px) { .filters { grid-template-columns:1fr 1fr; } .grid { grid-template-columns:repeat(2,1fr); } .analytics { grid-template-columns:repeat(3,1fr); } .ranking { grid-column:1/-1; } .insights { grid-template-columns:1fr; } }
        @media (max-width:600px) { header { padding-top:35px; } .filters, .grid, .analytics { grid-template-columns:1fr; } .ranking { grid-column:auto; } .summary { align-items:start; flex-direction:column; } article { min-height:190px; } .metric strong { font-size:24px; } .month-chart { gap:3px; } .assistant-toggle { right:16px; bottom:16px; } .assistant-toggle-copy small { display:none; } .assistant-panel { right:8px; bottom:82px; width:calc(100vw - 16px); max-height:calc(100vh - 96px); border-radius:18px; } .assistant-suggestions { grid-template-columns:1fr; } .assistant-suggestion:last-child { grid-column:auto; } }
    </style>
</head>
<body>
<header>
    <div class="wrap">
        <div class="eyebrow">Câmara dos Deputados · Dados Abertos</div>
        <h1>Deputados em Dados</h1>
        <p>Consulte representantes, partidos, estados e despesas parlamentares em uma visão simples e transparente.</p>
        <a class="api-link" href="{{ route('api.docs') }}">Documentação da API →</a>
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
    @if($analyticsSyncRun?->status === 'processing')
        <aside class="sync-notice" role="status">
            <div><strong>Dados de {{ $analyticsYear }} em atualização</strong><small>Os indicadores são parciais enquanto as despesas dos deputados são importadas.</small></div>
            <span class="sync-badge">{{ $analyticsSyncRun->processed_deputies }}/{{ $analyticsSyncRun->total_deputies }} deputados</span>
        </aside>
    @elseif(in_array($analyticsSyncRun?->status, ['failed', 'completed_with_errors'], true))
        <aside class="sync-notice error" role="alert">
            <div><strong>Importação de {{ $analyticsYear }} concluída com pendências</strong><small>{{ $analyticsSyncRun->failed_jobs }} {{ $analyticsSyncRun->failed_jobs === 1 ? 'deputado não foi processado' : 'deputados não foram processados' }}. Os indicadores podem estar incompletos.</small></div>
            <span class="sync-badge">Dados parciais</span>
        </aside>
    @elseif($analyticsSyncRun?->status === 'completed')
        <aside class="sync-notice complete" role="status">
            <div><strong>Importação de {{ $analyticsYear }} concluída</strong><small>Todos os {{ $analyticsSyncRun->total_deputies }} deputados foram processados pela última sincronização.</small></div>
            <span class="sync-badge">Dados sincronizados</span>
        </aside>
    @elseif($analytics['count'] > 0)
        <aside class="sync-notice" role="status">
            <div><strong>Cobertura de {{ $analyticsYear }} não verificada</strong><small>Existem despesas importadas, mas não há uma sincronização completa registrada para este ano.</small></div>
            <span class="sync-badge">Verificação pendente</span>
        </aside>
    @endif
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
    <section class="insights">
        <div class="insight">
            <h3>Evolução mensal de {{ $analyticsYear }}</h3>
            @php($largestMonthTotal = max(1, (float) ($monthlyExpenses->max('total') ?? 0)))
            <div class="month-chart" aria-label="Gráfico mensal de despesas">
                @foreach([1=>'Jan',2=>'Fev',3=>'Mar',4=>'Abr',5=>'Mai',6=>'Jun',7=>'Jul',8=>'Ago',9=>'Set',10=>'Out',11=>'Nov',12=>'Dez'] as $monthNumber => $monthName)
                    @php($monthTotal = (float) ($monthlyExpenses->get($monthNumber)?->total ?? 0))
                    <div class="month" title="{{ $monthName }}: R$ {{ number_format($monthTotal, 2, ',', '.') }}"><i style="height:{{ ($monthTotal / $largestMonthTotal) * 150 }}px"></i><span>{{ $monthName }}</span></div>
                @endforeach
            </div>
        </div>
        <div class="insight">
            <h3>Deputados com maiores despesas</h3>
            @forelse($topDeputies as $rankedDeputy)
                <a class="deputy-rank" href="{{ route('deputies.show', $rankedDeputy->id) }}"><img src="{{ $rankedDeputy->photo_url }}" alt=""><span><strong>{{ $rankedDeputy->name }}</strong><small>{{ $rankedDeputy->party_acronym }} · {{ $rankedDeputy->state_acronym }}</small></span><strong>R$ {{ number_format((float) $rankedDeputy->total, 2, ',', '.') }}</strong></a>
            @empty
                <span class="location">Ainda não há despesas para este ano.</span>
            @endforelse
        </div>
    </section>
    @php($failedRun = $syncRuns->first(fn ($run) => in_array($run->status, ['failed', 'completed_with_errors'], true)))
    @if($failedRun)
        <aside class="alert" role="alert"><span>⚠</span><div><strong>A última sincronização apresentou falhas</strong><p>{{ $failedRun->last_error ?: 'Uma ou mais tarefas não puderam ser concluídas. Consulte o histórico.' }}</p></div></aside>
    @endif
    @if($syncRuns->isNotEmpty())
        <section class="history">
            <div class="history-head"><h3>Histórico de sincronizações</h3><span class="location">Processamento da API da Câmara</span></div>
            <div class="runs">
                @foreach($syncRuns as $run)
                    @php($progress = $run->total_deputies > 0 ? min(100, ($run->processed_deputies / $run->total_deputies) * 100) : 100)
                    <div class="run">
                        <strong>{{ $run->year }}</strong>
                        <div><div class="run-progress"><i style="width:{{ $progress }}%"></i></div><span class="location">{{ $run->processed_deputies }}/{{ $run->total_deputies }} deputados · {{ number_format($run->expenses_received, 0, ',', '.') }} despesas</span>@if($run->last_error)<small class="run-error">{{ $run->last_error }}</small>@endif</div>
                        <span class="status">{{ match($run->status) { 'completed' => 'Concluída', 'completed_with_errors' => 'Concluída com falhas', 'failed' => 'Falhou', default => 'Em andamento' } }}</span>
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
<button class="assistant-toggle" type="button" aria-expanded="false" aria-controls="expense-assistant">
    <span class="assistant-toggle-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5.5h16M4 12h10M4 18.5h7"/><circle cx="18" cy="15.5" r="3"/><path d="m20.2 17.7 2 2"/></svg></span>
    <span class="assistant-toggle-copy"><strong>Explorar os dados</strong><small>Consulte despesas em linguagem simples</small></span>
</button>
<aside class="assistant-panel" id="expense-assistant" aria-label="Assistente de despesas parlamentares" hidden>
    <div class="assistant-head"><div><div class="assistant-eyebrow"><i></i>Consulta aos dados oficiais</div><h2>O que você quer descobrir?</h2><p>Explore padrões nas despesas importadas da Câmara dos Deputados.</p></div><button class="assistant-close" type="button" aria-label="Fechar assistente">×</button></div>
    <div class="assistant-body">
        <span class="assistant-section-label">Comece por uma análise</span>
        <div class="assistant-suggestions">
            <button class="assistant-suggestion" type="button" data-question="Quem gastou mais com combustível?"><span>Categoria</span>Maior gasto com combustível</button>
            <button class="assistant-suggestion" type="button" data-question="Quem gastou mais com propaganda?"><span>Divulgação</span>Maior gasto com propaganda</button>
            <button class="assistant-suggestion" type="button" data-question="Quais são as maiores categorias?"><span>Visão geral</span>Ranking de categorias</button>
            <button class="assistant-suggestion" type="button" data-question="Qual partido gastou mais?"><span>Partidos</span>Comparar despesas</button>
            <button class="assistant-suggestion" type="button" data-question="Quais fornecedores receberam mais?"><span>Fornecedores</span>Quem recebeu os maiores valores</button>
        </div>
        <span class="assistant-section-label">Ou escreva sua pergunta</span>
        <form class="assistant-form">
            <input type="text" name="question" minlength="4" maxlength="300" placeholder="Ex.: Quem mais gastou com passagens?" aria-label="Pergunta sobre as despesas" required>
            <button type="submit">Consultar</button>
        </form>
        <div class="assistant-result" aria-live="polite" hidden>
            <p class="assistant-query"></p><h3></h3><p class="assistant-answer"></p><div class="assistant-items"></div><p class="assistant-coverage"></p>
        </div>
    </div>
</aside>
<script>
    (() => {
        const toggle = document.querySelector('.assistant-toggle');
        const panel = document.querySelector('.assistant-panel');
        const close = document.querySelector('.assistant-close');
        const form = document.querySelector('.assistant-form');
        const input = form.elements.question;
        const result = document.querySelector('.assistant-result');
        const query = result.querySelector('.assistant-query');
        const title = result.querySelector('h3');
        const answer = result.querySelector('.assistant-answer');
        const items = result.querySelector('.assistant-items');
        const coverage = result.querySelector('.assistant-coverage');

        const openPanel = () => { panel.hidden = false; toggle.setAttribute('aria-expanded', 'true'); input.focus(); };
        const closePanel = () => { panel.hidden = true; toggle.setAttribute('aria-expanded', 'false'); };
        toggle.addEventListener('click', () => panel.hidden ? openPanel() : closePanel());
        close.addEventListener('click', closePanel);
        document.querySelectorAll('.assistant-suggestion').forEach(button => button.addEventListener('click', () => { input.value = button.dataset.question; form.requestSubmit(); }));

        form.addEventListener('submit', async event => {
            event.preventDefault();
            result.hidden = false;
            query.textContent = input.value;
            title.textContent = 'Consultando os dados…';
            answer.textContent = '';
            items.replaceChildren();
            coverage.textContent = '';
            coverage.className = 'assistant-coverage';

            try {
                const response = await fetch(@json(route('assistant.ask')), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ question: input.value, year: @json($analyticsYear) }),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Não foi possível realizar a consulta.');

                title.textContent = data.title;
                answer.textContent = data.answer;
                data.items.forEach((item, index) => {
                    const row = document.createElement(item.url ? 'a' : 'div');
                    row.className = 'assistant-item';
                    if (item.url) row.href = item.url;
                    const position = document.createElement('span');
                    position.className = 'assistant-position';
                    position.textContent = `${index + 1}º`;
                    const identity = document.createElement('span');
                    const label = document.createElement('strong');
                    label.className = 'assistant-item-label';
                    label.textContent = item.label;
                    const detail = document.createElement('small');
                    detail.textContent = item.detail;
                    identity.append(label, detail);
                    const value = document.createElement('strong');
                    value.className = 'assistant-value';
                    value.textContent = item.value;
                    row.append(position, identity, value);
                    items.append(row);
                });
                coverage.textContent = data.coverage.message;
                coverage.classList.toggle('complete', data.coverage.status === 'complete');
            } catch (error) {
                title.textContent = 'Não consegui responder';
                answer.textContent = error.message;
            }
        });
    })();
</script>
</body>
</html>
