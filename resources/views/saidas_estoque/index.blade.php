{{-- resources/views/saidas_estoque/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Saídas de Estoque')

@section('content')
    <div class="container mt-0">
        <h1>Saídas de Estoque</h1>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <a href="{{ route('saidas-estoque.create') }}" class="btn btn-primary">
                <i class="bi bi-dash-circle"></i> Nova Saída
            </a>
            <a href="{{ route('estoque.index') }}" class="btn btn-secondary">
                <i class="bi bi-box-seam"></i> Voltar para Estoque
            </a>
        </div>

        {{-- Formulário de Filtros --}}
        <form action="{{ route('saidas-estoque.index') }}" method="GET" class="mb-4 p-3 border rounded bg-light">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="data_inicial_filtro" class="form-label">Data Saída De:</label>
                    <input type="date" class="form-control form-control-sm" id="data_inicial_filtro"
                        name="data_inicial_filtro" value="{{ request('data_inicial_filtro') }}">
                </div>
                <div class="col-md-3">
                    <label for="data_final_filtro" class="form-label">Data Saída Até:</label>
                    <input type="date" class="form-control form-control-sm" id="data_final_filtro" name="data_final_filtro"
                        value="{{ request('data_final_filtro') }}">
                </div>
                <div class="col-md-3">
                    <label for="filtro_peca_nome" class="form-label">Peça:</label>
                    <input type="text" class="form-control form-control-sm" id="filtro_peca_nome" placeholder="Nome da peça"
                        value="{{ request('filtro_peca_nome') }}">
                    <input type="hidden" id="filtro_peca_id" name="filtro_peca_id" value="{{ request('filtro_peca_id') }}">
                </div>
                <div class="col-md-3">
                    <label for="filtro_atendimento_info" class="form-label">Atendimento (ID/Cliente):</label>
                    <input type="text" class="form-control form-control-sm" id="filtro_atendimento_info"
                        placeholder="ID, Cliente, Celular" value="{{ request('filtro_atendimento_info') }}">
                    <input type="hidden" id="filtro_atendimento_id" name="filtro_atendimento_id"
                        value="{{ request('filtro_atendimento_id') }}">
                    {{-- Adicionar opção para "Não Vinculado" manualmente ou no select se for um select --}}
                </div>
            </div>
            <div class="row g-3 mt-2 align-items-end">
                <div class="col-md-6">
                    <label for="busca_obs_saida" class="form-label">Buscar em Observações:</label>
                    <input type="text" class="form-control form-control-sm" id="busca_obs_saida" name="busca_obs_saida"
                        value="{{ request('busca_obs_saida') }}" placeholder="Termo na observação...">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button class="btn btn-primary btn-sm me-2" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
                    <a href="{{ route('saidas-estoque.index') }}" class="btn btn-secondary btn-sm"><i
                            class="bi bi-eraser"></i> Limpar</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID Saída</th>
                        <th>Peça</th>
                        <th>Modelo/Marca</th>
                        <th>Atendimento (Cliente)</th>
                        <th class="text-center">Qtd.</th>
                        <th>Data Saída</th>
                        <th>Observações</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($saidas as $saida)
                        <tr>
                            <td>{{ $saida->id }}</td>
                            <td>
                                @if($saida->estoque)
                                    <a href="{{ route('estoque.show', $saida->estoque->id) }}">{{ $saida->estoque->nome }}</a>
                                @else
                                    Peça não encontrada
                                @endif
                            </td>
                            <td>
                                @if($saida->estoque)
                                    <small class="d-block">{{ $saida->estoque->modelo_compativel ?? 'N/A' }}</small>
                                    <small class="d-block text-muted">Marca: {{ $saida->estoque->marca ?? 'N/A' }}</small>
                                @endif
                            </td>
                            <td>
                                @if ($saida->atendimento)
                                    <a href="{{ route('atendimentos.show', $saida->atendimento->id) }}">
                                        #{{ $saida->atendimento->id }}
                                        @if($saida->atendimento->cliente)
                                            - {{ $saida->atendimento->cliente->nome_completo }}
                                        @endif
                                    </a>
                                @else
                                    <span class="text-muted fst-italic">Não Vinculado</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $saida->quantidade }}</td>
                            <td>{{ $saida->data_saida->format('d/m/Y H:i') }}</td>
                            <td>{{ Str::limit($saida->observacoes, 50) ?? '-' }}</td>
                            <td>
                                <a href="{{ route('saidas-estoque.show', $saida->id) }}" class="btn btn-info btn-sm"
                                    title="Detalhes"><i class="bi bi-eye"></i></a>
                                {{-- Edição de Saída de Estoque está desabilitada por padrão no resource SaidaEstoqueController
                                --}}
                                {{-- <a href="{{ route('saidas-estoque.edit', $saida->id) }}" class="btn btn-warning btn-sm"
                                    title="Editar"><i class="bi bi-pencil"></i></a> --}}
                                <form action="{{ route('saidas-estoque.destroy', $saida->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Excluir Saída (Estorna Estoque)"
                                        onclick="return confirm('Tem certeza que deseja excluir esta saída? A quantidade será retornada ao estoque.')"><i
                                            class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Nenhuma saída de estoque registrada com os filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($saidas->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $saidas->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- jQuery UI CSS já está no layout base --}}
@endpush

@push('scripts')
    {{-- jQuery e jQuery UI já estão no layout base --}}
    <script>
        $(document).ready(function () {
            // Autocomplete para Peça no Filtro
            $("#filtro_peca_nome").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('estoque.autocomplete') }}", // Sua rota de autocomplete de estoque existente
                        dataType: "json",
                        data: { search: request.term },
                        success: function (data) { response(data); }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    $("#filtro_peca_nome").val(ui.item.value); // Mostra o nome
                    $("#filtro_peca_id").val(ui.item.id);     // Guarda o ID
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) { $("#filtro_peca_id").val(''); }
                }
            });
            // Limpar ID se o campo de nome da peça for limpo manualmente
            $("#filtro_peca_nome").on('input', function () {
                if ($(this).val() === '') { $("#filtro_peca_id").val(''); }
            });


            // Autocomplete para Atendimento no Filtro
            // Precisamos de uma rota de autocomplete para atendimentos.
            // Vamos supor que você crie uma: route('atendimentos.autocomplete')
            // que retorne { label: '#ID - Cliente (Celular)', value: '#ID - Cliente', id: ID_ATENDIMENTO }
            $("#filtro_atendimento_info").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('atendimentos.autocomplete') }}", // <<< ROTA CORRIGIDA AQUI
                        dataType: "json",
                        data: {
                            search_autocomplete: request.term
                        },
                        success: function (dataFromServer) { // Renomeado para clareza
                            // O map no JS agora processa o 'dataFromServer' que é a coleção de Atendimentos
                            response($.map(dataFromServer, function (item) {
                                let label = `#${item.id}`;
                                if (item.cliente) { // Verifica se o cliente existe (pode ser null)
                                    label += ` - ${item.cliente.nome_completo}`;
                                } else {
                                    label += ` - Cliente não associado`;
                                }
                                if (item.celular) label += ` (${item.celular})`;

                                return {
                                    label: label,
                                    value: `#${item.id} - ${item.cliente ? item.cliente.nome_completo : 'Sem Cliente'}`,
                                    id: item.id
                                };
                            }));
                        },
                        // ...
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    $("#filtro_atendimento_info").val(ui.item.value);
                    $("#filtro_atendimento_id").val(ui.item.id);
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) { $("#filtro_atendimento_id").val(''); }
                }
            });
            // Limpar ID se o campo de info do atendimento for limpo manualmente
            $("#filtro_atendimento_info").on('input', function () {
                if ($(this).val() === '') { $("#filtro_atendimento_id").val(''); }
            });

            // Adicionar "Não Vinculado" como uma opção manual se necessário, ou tratar no controller com ID 0
            // Se quiser uma forma mais elegante, o select para atendimento_id poderia ser:
            // <select name="filtro_atendimento_id"> <option value="">Todos</option> <option value="0">Não Vinculado</option> ... outros ... </select>
            // E aí o campo de texto seria só para buscar e selecionar IDs de atendimentos existentes.

        });
    </script>
@endpush