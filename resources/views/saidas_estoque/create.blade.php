@extends('layouts.app')

@section('title', 'Nova Saída de Estoque')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    {{-- jQuery UI CSS já deve estar no layouts.app --}}
    <style>
        .info-selecao { /* Classe comum para as divs de informação */
            font-size: 0.875em;
            background-color: #f8f9fa;
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            margin-top: 0.5rem;
            border: 1px solid #e9ecef;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-0">
        <h1><i class="bi bi-box-arrow-up"></i> Nova Saída de Estoque</h1>

        {{-- Bloco para exibir erros de validação e mensagens de sessão --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <strong><i class="bi bi-exclamation-triangle-fill"></i> Ops! Verifique os erros abaixo:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        {{-- Adicione outros feedbacks de sessão aqui se necessário --}}

        <form action="{{ route('saidas-estoque.store') }}" method="POST" class="mt-3">
            @csrf
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="my-1"><i class="bi bi-pencil-square"></i> Detalhes da Saída</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="estoque_nome_saida" class="form-label">Peça/Acessório <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('estoque_id') is-invalid @enderror" id="estoque_nome_saida"
                            placeholder="Comece a digitar o nome, modelo ou ID da peça..." value="{{ old('estoque_nome_saida') }}" required>
                        <input type="hidden" id="estoque_id_saida" name="estoque_id" value="{{ old('estoque_id', $selectedEstoqueId ?? '') }}">
                        <small class="form-text text-muted">Selecione o item que está saindo do estoque.</small>
                        @error('estoque_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <div id="info_peca_selecionada_saida" class="info-selecao" style="display: none;">
                            {-- Informações da peça selecionada --}
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantidade_saida" class="form-label">Quantidade a Retirar <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('quantidade') is-invalid @enderror" id="quantidade_saida" name="quantidade"
                                value="{{ old('quantidade', 1) }}" min="1" required>
                            @error('quantidade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small id="aviso_quantidade_saida" class="form-text text-danger" style="display: none;"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="data_saida" class="form-label">Data de Saída <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('data_saida') is-invalid @enderror" id="data_saida" name="data_saida"
                                value="{{ old('data_saida', date('Y-m-d')) }}" required>
                            @error('data_saida')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="atendimento_info_saida" class="form-label">Vincular ao Atendimento (Opcional)</label>
                        <input type="text" class="form-control @error('atendimento_id') is-invalid @enderror" id="atendimento_info_saida"
                            placeholder="ID, Cliente ou Aparelho do Atendimento"
                            value="{{ old('atendimento_info_saida', $atendimentoSelecionado ? ('#' . $atendimentoSelecionado->id . ' - ' . ($atendimentoSelecionado->cliente->nome_completo ?? 'Sem Cliente')) : '' ) }}">
                        <input type="hidden" id="atendimento_id_saida" name="atendimento_id" value="{{ old('atendimento_id', $selectedAtendimentoId ?? '') }}">
                        @error('atendimento_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        <div id="info_atendimento_selecionado_saida" class="info-selecao" style="display: none;">
                            {-- Informações do atendimento selecionado --}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observacoes_saida" class="form-label">Observações (Opcional)</label>
                        <textarea class="form-control @error('observacoes') is-invalid @enderror" id="observacoes_saida" name="observacoes"
                            rows="3" placeholder="Ex: Uso interno, Peça para garantia, etc.">{{ old('observacoes') }}</textarea>
                        @error('observacoes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end">
                <a href="{{ route('saidas-estoque.index') }}" class="btn btn-secondary me-2"><i class="bi bi-x-circle"></i> Cancelar</a>
                <button type="submit" class="btn btn-success" id="btnRegistrarSaida"><i class="bi bi-check-circle-fill"></i> Registrar Saída</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- jQuery e jQuery UI já devem estar no layouts.app.blade.php --}}
    <script>
        $(document).ready(function () {
            let estoqueDisponivelSaida = 0; // Variável para armazenar a quantidade disponível da peça selecionada
            let pecaSelecionadaParaSaida = false; // Flag para controlar se uma peça válida foi selecionada

            function exibirInfoPecaSaida(item) {
                var infoDiv = $('#info_peca_selecionada_saida');
                var campoQuantidadeSaida = $('#quantidade_saida');
                var avisoQuantidadeSaida = $('#aviso_quantidade_saida');
                var btnRegistrar = $('#btnRegistrarSaida');

                if (item && typeof item.id !== 'undefined') {
                    pecaSelecionadaParaSaida = true;
                    estoqueDisponivelSaida = parseInt(item.quantidade_disponivel) || 0;
                    var tipoItemDisplay = item.tipo_formatado || item.tipo || 'N/D'; // Usa tipo_formatado se disponível

                    var infoHtml = '<strong>ID:</strong> ' + item.id +
                                   ' | <strong>Tipo:</strong> ' + tipoItemDisplay +
                                   ' | <strong class="' + (estoqueDisponivelSaida > 0 ? 'text-success' : 'text-danger') + '">Qtd. Disp.: ' + estoqueDisponivelSaida + '</strong>';
                    infoDiv.html(infoHtml).show();
                    campoQuantidadeSaida.attr('max', estoqueDisponivelSaida);

                    if (estoqueDisponivelSaida === 0) {
                        campoQuantidadeSaida.val(0).prop('readonly', true);
                        avisoQuantidadeSaida.text('Estoque esgotado para este item.').show();
                        btnRegistrar.prop('disabled', true);
                    } else {
                        campoQuantidadeSaida.prop('readonly', false);
                        // Mantém o valor atual do campo quantidade se for válido, ou old(), ou 1 como default
                        let valorAtualQtd = parseInt(campoQuantidadeSaida.val());
                        if (isNaN(valorAtualQtd) || valorAtualQtd <= 0 || valorAtualQtd > estoqueDisponivelSaida) {
                            if ('{{ old("quantidade") }}' && parseInt('{{ old("quantidade") }}') <= estoqueDisponivelSaida) {
                                campoQuantidadeSaida.val(parseInt('{{ old("quantidade") }}'));
                            } else if (campoQuantidadeSaida.val() === '' || parseInt(campoQuantidadeSaida.val()) === 0){ // Evita setar 1 se já tem old()
                                campoQuantidadeSaida.val(1);
                            } else if (valorAtualQtd > estoqueDisponivelSaida) {
                                 campoQuantidadeSaida.val(estoqueDisponivelSaida);
                            }
                        }
                        avisoQuantidadeSaida.hide().text('');
                        // Dispara o evento input para revalidar o botão
                        campoQuantidadeSaida.trigger('input');
                    }
                } else {
                    pecaSelecionadaParaSaida = false;
                    estoqueDisponivelSaida = 0;
                    infoDiv.hide().html('');
                    campoQuantidadeSaida.removeAttr('max').prop('readonly', false);
                    avisoQuantidadeSaida.hide().text('');
                    // Se não há peça selecionada, o botão deve ser desabilitado
                    btnRegistrar.prop('disabled', true);
                }
            }

            $("#estoque_nome_saida").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('estoque.autocomplete') }}",
                        type: 'GET', dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function (data) { response(data); },
                        error: function() { response([]); }
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                    $('#estoque_nome_saida').val(ui.item.value);
                    $('#estoque_id_saida').val(ui.item.id);
                    exibirInfoPecaSaida(ui.item);
                    $('#quantidade_saida').focus().trigger('input'); // Foca e revalida a quantidade
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) {
                        // Se o campo foi limpo ou um valor inválido foi digitado E não é uma seleção de autocomplete
                        if ($('#estoque_nome_saida').val() === '' || !pecaSelecionadaParaSaida ) {
                             $('#estoque_id_saida').val('');
                             exibirInfoPecaSaida(null);
                        }
                        // Se um item válido foi selecionado, e o texto mudou mas ainda é o 'value' desse item,
                        // não fazer nada aqui, pois exibirInfoPecaSaida já foi chamado no select.
                    } else {
                         // Garante que a informação é exibida se ui.item for válido (ex: colando texto que corresponde)
                        exibirInfoPecaSaida(ui.item);
                    }
                     $('#quantidade_saida').trigger('input');
                }
            });

            $('#quantidade_saida').on('input', function() {
                var quantidadeDesejada = parseInt($(this).val()) || 0;
                var avisoQuantidadeSaida = $('#aviso_quantidade_saida');
                var btnRegistrar = $('#btnRegistrarSaida');
                var pecaIdSelecionada = $('#estoque_id_saida').val();

                if (!pecaIdSelecionada) {
                    avisoQuantidadeSaida.text('Selecione uma peça/acessório primeiro.').show();
                    btnRegistrar.prop('disabled', true);
                    return;
                }

                if (estoqueDisponivelSaida === 0) {
                    avisoQuantidadeSaida.text('Estoque esgotado para o item selecionado.').show();
                    btnRegistrar.prop('disabled', true);
                    return; // Não precisa checar quantidade desejada se não há estoque
                }

                if (quantidadeDesejada <= 0) {
                    avisoQuantidadeSaida.text('A quantidade a retirar deve ser pelo menos 1.').show();
                    btnRegistrar.prop('disabled', true);
                } else if (quantidadeDesejada > estoqueDisponivelSaida) {
                    avisoQuantidadeSaida.text('Quantidade excede o estoque disponível (' + estoqueDisponivelSaida + ').').show();
                    btnRegistrar.prop('disabled', true);
                } else {
                    avisoQuantidadeSaida.hide().text('');
                    btnRegistrar.prop('disabled', false);
                }
            });

            $("#estoque_nome_saida").on('input', function() {
                if ($(this).val() === '') {
                    $('#estoque_id_saida').val('');
                    exibirInfoPecaSaida(null); // Limpa info e reseta campo quantidade e botão
                    $('#quantidade_saida').val(1); // Opcional: resetar quantidade para 1
                    // A validação da quantidade será refeita por exibirInfoPecaSaida(null) e o listener de input
                }
                // Se o usuário está digitando e ainda não selecionou um item, a flag pecaSelecionadaParaSaida será false.
                // O evento change do autocomplete cuidará de limpar o ID se a digitação não resultar numa seleção.
            });

            // Autocomplete para Atendimento
            function exibirInfoAtendimentoSaida(item) {
                var infoDiv = $('#info_atendimento_selecionado_saida');
                if (item && item.id) {
                    var clienteNome = (item.cliente && item.cliente.nome_completo) ? item.cliente.nome_completo : 'Cliente não associado';
                    var descAparelho = item.descricao_aparelho || 'N/D';
                    var descAparelhoTruncada = descAparelho.length > 30 ? descAparelho.substring(0, 30) + "..." : descAparelho;

                    var infoHtml = '<strong>OS ID:</strong> ' + item.id +
                                   ' | <strong>Cliente:</strong> ' + clienteNome +
                                   ' | <strong>Aparelho:</strong> ' + descAparelhoTruncada +
                                   ' | <strong>Status:</strong> ' + (item.status || 'N/D');
                    infoDiv.html(infoHtml).show();
                } else {
                    infoDiv.hide().html('');
                }
            }

            let atendimentoSelecionadoSaida = false;
            $("#atendimento_info_saida").autocomplete({
                source: function(request, response) {
                    atendimentoSelecionadoSaida = false; // Reseta a flag antes de cada busca
                    $.ajax({
                        url: "{{ route('atendimentos.autocomplete') }}",
                        dataType: "json",
                        data: { search_autocomplete: request.term },
                        success: function(dataFromServer) {
                            response($.map(dataFromServer, function(item) {
                                let label = `#${item.id}`;
                                if (item.cliente) {
                                    label += ` - ${item.cliente.nome_completo}`;
                                } else {
                                    label += ` - Cliente não associado`;
                                }
                                if (item.descricao_aparelho) {
                                    let descAparelho = item.descricao_aparelho;
                                    let descTruncada = descAparelho.length > 25 ? descAparelho.substring(0, 25) + "..." : descAparelho;
                                    label += ` (${descTruncada})`;
                                }
                                if (item.status) {
                                    label += ` [${item.status}]`;
                                }
                                return {
                                    label: label,
                                    value: `#${item.id} - ${item.cliente ? item.cliente.nome_completo : 'Sem Cliente'}`,
                                    id: item.id,
                                    full_item: item // Passa o objeto completo
                                };
                            }));
                        },
                        error: function(){ response([]); }
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    $("#atendimento_info_saida").val(ui.item.value);
                    $("#atendimento_id_saida").val(ui.item.id);
                    exibirInfoAtendimentoSaida(ui.item.full_item);
                    atendimentoSelecionadoSaida = true; // Marca que um item válido foi selecionado
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) { // Se o campo foi limpo ou um valor inválido foi digitado
                        $("#atendimento_id_saida").val('');
                        exibirInfoAtendimentoSaida(null);
                    }
                    // Não precisa de 'else' aqui, pois se ui.item for válido, o select já tratou
                }
            });

             $("#atendimento_info_saida").on('input', function() {
                if ($(this).val() === '') {
                    $("#atendimento_id_saida").val('');
                    exibirInfoAtendimentoSaida(null);
                    atendimentoSelecionadoSaida = false;
                } else if (!atendimentoSelecionadoSaida) { // Se está digitando e não veio de uma seleção
                    $('#atendimento_id_saida').val('');
                    // Não limpa a div de info aqui, pois o usuário pode estar buscando
                }
            });

            // Lógica de pré-população (se houver valores old() ou passados pelo controller)
            const currentSelectedEstoqueId = $('#estoque_id_saida').val();
            if (currentSelectedEstoqueId) {
                $.ajax({
                    url: "{{ route('estoque.autocomplete') }}", type: 'GET', dataType: "json",
                    data: { search: currentSelectedEstoqueId },
                    success: function (data) {
                        if (data.length > 0) {
                            const item = data.find(i => i.id == currentSelectedEstoqueId);
                            if (item) {
                                if (!$('#estoque_nome_saida').val()) { // Só preenche se estiver vazio (evita sobrescrever old())
                                     $('#estoque_nome_saida').val(item.value);
                                }
                                exibirInfoPecaSaida(item);
                                $('#quantidade_saida').trigger('input'); // Revalida
                            } else { $('#estoque_id_saida').val(''); exibirInfoPecaSaida(null); }
                        } else { $('#estoque_id_saida').val(''); exibirInfoPecaSaida(null); }
                    },
                    error: function() {
                        console.error("Erro ao buscar dados da peça pré-selecionada para saída.");
                        $('#estoque_id_saida').val(''); exibirInfoPecaSaida(null);
                    }
                });
            } else {
                 // Se não há estoque ID, desabilita o botão de saída inicialmente
                 $('#btnRegistrarSaida').prop('disabled', true);
            }


            const initialAtendimentoId = "{{ old('atendimento_id', $selectedAtendimentoId ?? '') }}";
            const initialAtendimentoNome = "{{ old('atendimento_info_saida', $atendimentoSelecionado ? ('#' . $atendimentoSelecionado->id . ' - ' . (optional($atendimentoSelecionado->cliente)->nome_completo ?? 'Sem Cliente')) : '' ) }}";

            if (initialAtendimentoId && initialAtendimentoNome) {
                 $('#atendimento_info_saida').val(initialAtendimentoNome); // Preenche o campo de display
                 $('#atendimento_id_saida').val(initialAtendimentoId);   // Preenche o hidden
                 // Busca os dados completos para exibir na div de info
                 $.ajax({
                    url: "{{ route('atendimentos.autocomplete') }}", type: 'GET', dataType: "json",
                    data: { search_autocomplete: initialAtendimentoId }, // Busca pelo ID
                    success: function (dataFromServer) {
                        if (dataFromServer.length > 0) {
                            const item = dataFromServer.find(i => i.id == initialAtendimentoId);
                            if (item) {
                                exibirInfoAtendimentoSaida(item);
                                atendimentoSelecionadoSaida = true;
                            }
                        }
                    },
                     error: function() { console.error("Erro ao buscar dados do atendimento pré-selecionado.");}
                });
            }
            // Dispara o evento de input na quantidade para validar o botão ao carregar a página
            // Isso é importante se a quantidade tiver um valor 'old' ou padrão '1'
            $('#quantidade_saida').trigger('input');

        });
    </script>
@endpush