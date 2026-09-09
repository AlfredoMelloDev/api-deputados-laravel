<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $deputy->name }} · Deputados em Dados</title>
    <style>
        :root { --ink:#17231d; --muted:#647069; --paper:#f4f1e9; --card:#fffdf8; --green:#164b35; --lime:#d9ef8b; --line:#d8ddd6; }
        * { box-sizing:border-box; }
        body { margin:0; background:var(--paper); color:var(--ink); font-family:Inter, ui-sans-serif, system-ui, sans-serif; }
        .wrap { width:min(1180px, calc(100% - 32px)); margin:auto; }
        header { padding:24px 0 65px; background:var(--green); color:white; }
        .back { display:inline-block; margin-bottom:28px; color:#d7e3dc; text-decoration:none; font-weight:700; }
        .profile { display:flex; align-items:center; gap:28px; }
        .profile img { width:150px; height:175px; border-radius:18px; object-fit:cover; object-position:top; background:#dce3dc; border:3px solid #ffffff33; }
        .tag { display:inline-block; padding:6px 10px; border-radius:99px; background:var(--lime); color:#20351d; font-size:12px; font-weight:850; }
        h1 { margin:12px 0 7px; font:500 clamp(34px,5vw,62px)/1 Georgia, serif; }
        .meta { color:#d7e3dc; }
        .stats { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-top:-35px; }
        .stat { padding:22px; border:1px solid var(--line); border-radius:16px; background:var(--card); box-shadow:0 10px 30px #1a332312; }
        .stat span { display:block; color:var(--muted); font-size:13px; }
        .stat strong { display:block; margin-top:6px; color:var(--green); font:500 29px Georgia, serif; }
        .section-head { display:flex; justify-content:space-between; align-items:end; gap:15px; margin:38px 0 17px; }
        h2 { margin:0; font:500 31px Georgia, serif; }
        .section-head p { margin:5px 0 0; color:var(--muted); }
        .clear { color:var(--green); font-weight:700; text-decoration:none; }
        form { display:grid; grid-template-columns:repeat(6,1fr) auto; gap:10px; padding:16px; background:var(--card); border:1px solid var(--line); border-radius:15px; margin-bottom:18px; }
        input, select, button { min-height:46px; padding:0 13px; border-radius:9px; font:inherit; }
        input, select { width:100%; background:white; color:var(--ink); border:1px solid var(--line); }
        .supplier { grid-column:span 2; }
        button { padding-inline:22px; border:0; background:var(--green); color:white; font-weight:750; cursor:pointer; }
        .table-box { overflow:auto; background:var(--card); border:1px solid var(--line); border-radius:15px; }
        table { width:100%; border-collapse:collapse; min-width:820px; }
        th, td { padding:15px; border-bottom:1px solid var(--line); text-align:left; }
        th { color:var(--muted); font-size:11px; letter-spacing:.08em; text-transform:uppercase; background:#f9f7f1; }
        td { font-size:14px; }
        td strong { display:block; }
        td small { color:var(--muted); }
        .money { white-space:nowrap; color:var(--green); font-weight:800; }
        .document { color:var(--green); font-weight:700; }
        .empty { padding:50px; text-align:center; color:var(--muted); }
        nav { display:flex; justify-content:center; gap:7px; padding:24px 0 50px; }
        nav a, nav span { min-width:38px; padding:9px 11px; text-align:center; border:1px solid var(--line); border-radius:9px; color:var(--green); text-decoration:none; background:var(--card); }
        nav span.active { color:white; background:var(--green); border-color:var(--green); }
        @media(max-width:1000px) { form { grid-template-columns:repeat(2,1fr); } .supplier { grid-column:span 2; } }
        @media(max-width:700px) { .profile { align-items:flex-start; } .profile img { width:105px; height:130px; } form { grid-template-columns:1fr; } .supplier { grid-column:auto; } .section-head { align-items:start; flex-direction:column; } }
    </style>
</head>
<body>
<header>
    <div class="wrap">
        <a class="back" href="{{ route('deputies.index') }}">← Voltar para todos os deputados</a>
        <div class="profile">
            <img src="{{ $deputy->photo_url }}" alt="Foto de {{ $deputy->name }}">
            <div><span class="tag">{{ $deputy->party_acronym ?: 'Sem partido' }}</span><h1>{{ $deputy->name }}</h1><div class="meta">{{ $deputy->state_acronym }} · Legislatura {{ $deputy->legislature_id }} · ID {{ $deputy->camara_id }}</div></div>
        </div>
    </div>
</header>
<main class="wrap">
    <section class="stats">
        <div class="stat"><span>Despesas no filtro atual</span><strong>{{ number_format($expenseCount, 0, ',', '.') }}</strong></div>
        <div class="stat"><span>Valor líquido total</span><strong>R$ {{ number_format($expenseTotal, 2, ',', '.') }}</strong></div>
    </section>
    <section class="section-head">
        <div><h2>Despesas parlamentares</h2><p>Documentos importados da API oficial da Câmara.</p></div>
        @if(request()->hasAny(['year', 'month', 'type', 'supplier', 'date_from', 'date_to']))<a class="clear" href="{{ route('deputies.show', $deputy) }}">Limpar filtros</a>@endif
    </section>
    <form method="GET" action="{{ route('deputies.show', $deputy) }}">
        <select name="year"><option value="">Todos os anos</option>@foreach($years as $year)<option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>@endforeach</select>
        <select name="month"><option value="">Todos os meses</option>@foreach([1=>'Janeiro',2=>'Fevereiro',3=>'Março',4=>'Abril',5=>'Maio',6=>'Junho',7=>'Julho',8=>'Agosto',9=>'Setembro',10=>'Outubro',11=>'Novembro',12=>'Dezembro'] as $number=>$month)<option value="{{ $number }}" @selected((string) request('month') === (string) $number)>{{ $month }}</option>@endforeach</select>
        <select name="type"><option value="">Todos os tipos</option>@foreach($types as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ $type }}</option>@endforeach</select>
        <input class="supplier" type="search" name="supplier" value="{{ request('supplier') }}" placeholder="Buscar fornecedor">
        <input type="date" name="date_from" value="{{ request('date_from') }}" aria-label="Data inicial">
        <input type="date" name="date_to" value="{{ request('date_to') }}" aria-label="Data final">
        <button type="submit">Filtrar</button>
    </form>
    <div class="table-box">
        @if($expenses->isEmpty())
            <div class="empty"><strong>Nenhuma despesa encontrada.</strong><br>Não há registros para os filtros selecionados.</div>
        @else
            <table><thead><tr><th>Data</th><th>Tipo</th><th>Fornecedor</th><th>Documento</th><th>Valor líquido</th></tr></thead><tbody>
            @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->document_date?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $expense->expense_type }}</td>
                    <td><strong>{{ $expense->supplier_name }}</strong><small>{{ $expense->supplier_tax_id }}</small></td>
                    <td>@if($expense->document_url)<a class="document" href="{{ $expense->document_url }}" target="_blank" rel="noopener">Ver documento ↗</a>@else{{ $expense->document_number ?: '—' }}@endif</td>
                    <td class="money">R$ {{ number_format((float) $expense->net_value, 2, ',', '.') }}</td>
                </tr>
            @endforeach
            </tbody></table>
        @endif
    </div>
    @if($expenses->hasPages())
        <nav aria-label="Paginação">
            @if($expenses->onFirstPage())<span>‹</span>@else<a href="{{ $expenses->previousPageUrl() }}">‹</a>@endif
            @foreach($expenses->getUrlRange(max(1,$expenses->currentPage()-2),min($expenses->lastPage(),$expenses->currentPage()+2)) as $page=>$url)
                @if($page === $expenses->currentPage())<span class="active">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
            @endforeach
            @if($expenses->hasMorePages())<a href="{{ $expenses->nextPageUrl() }}">›</a>@else<span>›</span>@endif
        </nav>
    @endif
</main>
</body>
</html>
