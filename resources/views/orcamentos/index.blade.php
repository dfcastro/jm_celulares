@extends('layouts.app')

@section('title', 'Orçamentos')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- jQuery UI CSS já deve estar no layouts.app --}}
    <style>
        .status-badge-orc {
            font-size: 0.8em;
            padding: 0.35em 0.65em;
            vertical-align: middle;
        }

        .card-header-filtros {
            background-color: #f8f9fa;
            /* Um cinza claro para o cabeçalho dos filtros */
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <h1><i class="bi bi-file-earmark-text-fill"></i> Lista de Orçamentos</h1>
            <a href="{{ route('orcamentos.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle-fill"></i> Novo Orçamento
            </a>
        </div>

        {{-- Mensagens de Feedback --}}

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- Card de Filtros --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header card-header-filtros">
                <h5 class="my-0"><i class="bi bi-funnel-fill"></i> Filtros de Pesquisa</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('orcamentos.index') }}" method="GET">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6 col-lg-4">
                            <label for="filtro_cliente_search" class="form-label form-label-sm">Cliente Cadastrado:</label>
                            <input type="text" class="form-control form-control-sm" id="filtro_cliente_search"
                                name="filtro_cliente_search_display" {{-- Para o autocomplete --}}
                                placeholder="Nome ou CPF/CNPJ do cliente"
                                value="{{ request('filtro_cliente_search_display', optional(App\Models\Cliente::find(request('filtro_cliente_id')))->nome_completo) }}">
                            <input type="hidden" id="filtro_cliente_id" name="filtro_cliente_id"
                                value="{{ request('filtro_cliente_id') }}">
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="filtro_cliente_nome_avulso" class="form-label form-label-sm">Ou Cliente Avulso
                                (Nome):</label>
                            <input type="text" class="form-control form-control-sm" id="filtro_cliente_nome_avulso"
                                name="filtro_cliente_nome_avulso" placeholder="Nome do cliente avulso"
                                value="{{ request('filtro_cliente_nome_avulso') }}">
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="filtro_status" class="form-label form-label-sm">Status:</label>
                            <select class="form-select form-select-sm" id="filtro_status" name="filtro_status">
                                <option value="">Todos os Status</option>
                                @foreach ($statusParaFiltro as $status)
                                    <option value="{{ $status }}" {{ request('filtro_status') == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="data_emissao_de" class="form-label form-label-sm">Emissão De:</label>
                            <input type="date" class="form-control form-control-sm" id="data_emissao_de"
                                name="data_emissao_de" value="{{ request('data_emissao_de') }}">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="data_emissao_ate" class="form-label form-label-sm">Emissão Até:</label>
                            <input type="date" class="form-control form-control-sm" id="data_emissao_ate"
                                name="data_emissao_ate" value="{{ request('data_emissao_ate') }}">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label for="filtro_criado_por_id" class="form-label form-label-sm">Criado por:</label>
                            <select class="form-select form-select-sm" id="filtro_criado_por_id"
                                name="filtro_criado_por_id">
                                <option value="">Todos os Usuários</option>
                                @foreach ($usuariosParaFiltro as $usuario)
                                    <option value="{{ $usuario->id }}" {{ request('filtro_criado_por_id') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 col-lg-3 d-flex align-items-end">
                            <button class="btn btn-primary btn-sm me-2 w-100 mb-1 mb-lg-0" type="submit"><i
                                    class="bi bi-search"></i> Filtrar</button>
                            <a href="{{ route('orcamentos.index') }}" class="btn btn-secondary btn-sm w-100"><i
                                    class="bi bi-eraser-fill"></i> Limpar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabela de Orçamentos --}}
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered table-sm">
                {{-- Thead e Tbody como estavam antes --}}
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Aparelho</th>
                        <th>Data Emissão</th>
                        <th>Validade</th>
                        <th class="text-end">Valor Final (R$)</th>
                        <th class="text-center">Status</th>
                        <th>Criado Por</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orcamentos as $orcamento)
                        <tr>
                            <td>{{ $orcamento->id }}</td>
                            <td>
                                @if($orcamento->cliente)
                                    <a
                                        href="{{ route('clientes.show', $orcamento->cliente->id) }}">{{ Str::limit($orcamento->cliente->nome_completo, 25) }}</a>
                                @else
                                    {{ Str::limit($orcamento->nome_cliente_avulso, 25) ?? 'N/A (Avulso)' }}
                                    @if($orcamento->telefone_cliente_avulso) <small class="d-block text-muted">Tel:
                                    {{ $orcamento->telefone_cliente_avulso }}</small> @endif
                                @endif
                            </td>
                            <td>{{ Str::limit($orcamento->descricao_aparelho, 30) }}</td>
                            <td>{{ $orcamento->data_emissao->format('d/m/Y') }}</td>
                            <td>{{ $orcamento->data_validade ? $orcamento->data_validade->format('d/m/Y') : ($orcamento->validade_dias ? $orcamento->validade_dias . ' dias' : 'N/A') }}
                            </td>
                            <td class="text-end fw-bold">{{ number_format($orcamento->valor_final, 2, ',', '.') }}</td>
                            <td class="text-center">
                                @php
                                    $statusClass = '';
                                    if ($orcamento->status === 'Aprovado')
                                        $statusClass = 'bg-success';
                                    elseif ($orcamento->status === 'Reprovado')
                                        $statusClass = 'bg-danger';
                                    elseif ($orcamento->status === 'Cancelado')
                                        $statusClass = 'bg-secondary';
                                    elseif ($orcamento->status === 'Aguardando Aprovação')
                                        $statusClass = 'bg-warning text-dark';
                                    elseif ($orcamento->status === 'Em Elaboração')
                                        $statusClass = 'bg-info text-dark';
                                    elseif ($orcamento->status === 'Convertido em OS')
                                        $statusClass = 'bg-primary';
                                    else
                                        $statusClass = 'bg-light text-dark';
                                @endphp
                                <span class="badge rounded-pill status-badge-orc {{ $statusClass }}">
                                    {{ $orcamento->status }}
                                </span>
                            </td>
                            <td>{{ $orcamento->criadoPor->name ?? 'N/A' }}</td>
                            <td class="text-center">
                                <a href="{{ route('orcamentos.show', $orcamento->id) }}" class="btn btn-info btn-sm"
                                    title="Ver Detalhes">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                                @if(in_array($orcamento->status, ['Em Elaboração']))
                                    <a href="{{ route('orcamentos.edit', $orcamento->id) }}" class="btn btn-warning btn-sm"
                                        title="Editar">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                @endif
                                @if(!in_array($orcamento->status, ['Cancelado', 'Convertido em OS', 'Reprovado', 'Aprovado']))
                                    @if(!($orcamento->status == 'Aprovado' && $orcamento->atendimento_id_convertido))
                                        <form action="{{ route('orcamentos.destroy', $orcamento->id) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Tem certeza que deseja cancelar este orçamento?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Cancelar Orçamento">
                                                <i class="bi bi-x-circle-fill"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">Nenhum orçamento encontrado com os filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($orcamentos->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $orcamentos->links() }} {{-- appends($request->query()) já foi feito no controller --}}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery UI já devem estar no seu layout base (app.blade.php) --}}
    <script>
        $(document).ready(function () {
            let clienteSelecionadoFiltro = ($('#filtro_cliente_id').val() !== '');

            $("#filtro_cliente_search").autocomplete({
                source: function (request, response) {
                    clienteSelecionadoFiltro = false; // Reseta a flag
                    $.ajax({
                        url: "{{ route('clientes.autocomplete') }}",
                        dataType: "json",
                        data: { search: request.term },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    label: item.label,
                                    value: item.value, // Nome do cliente
                                    id: item.id
                                };
                            }));
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    if (ui.item && ui.item.id) {
                        $('#filtro_cliente_search').val(ui.item.value); // Nome no campo visível
                        $('#filtro_cliente_id').val(ui.item.id);       // ID no campo hidden
                        clienteSelecionadoFiltro = true;
                        $('#filtro_cliente_nome_avulso').val(''); // Limpa o campo de nome avulso
                    }
                    return false;
                },
                change: function (event, ui) {
                    if (!clienteSelecionadoFiltro && !ui.item) {
                        $('#filtro_cliente_id').val(''); // Limpa o ID se nada foi selecionado
                    }
                }
            });

            // Se o campo de busca de cliente cadastrado for limpo manualmente, limpar o ID
            $("#filtro_cliente_search").on('input', function () {
                if ($(this).val() === '') {
                    $('#filtro_cliente_id').val('');
                    clienteSelecionadoFiltro = false;
                } else {
                    if (!clienteSelecionadoFiltro) { // Se está digitando e não veio de uma seleção
                        $('#filtro_cliente_id').val('');
                    }
                }
            });

            // Se um nome de cliente avulso for digitado, limpar a seleção de cliente cadastrado
            $("#filtro_cliente_nome_avulso").on('input', function () {
                if ($(this).val() !== '') {
                    $('#filtro_cliente_search').val('');
                    $('#filtro_cliente_id').val('');
                    clienteSelecionadoFiltro = false;
                }
            });


        });
    </script>
@endpush