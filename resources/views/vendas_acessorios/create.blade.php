{{-- resources/views/vendas_acessorios/create.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nova Venda de Acessório</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Inclua jQuery UI CSS para o autocomplete de cliente --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
     <style>
        /* Estilo para garantir que o autocomplete apareça sobre outros elementos se houverem (como modais) */
        .ui-autocomplete {
            z-index: 1050; /* Valor alto para sobrepor outros elementos */
        }
        /* Estilo para campos hidden visualmente, mas acessíveis a leitores de tela */
        .visually-hidden {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0,0,0,0) !important;
            white-space: nowrap !important;
            border: 0 !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Registrar Nova Venda de Acessório</h1>

        {{-- Bloco para exibir erros de validação gerais --}}
        {{-- O Laravel preenche a variável $errors automaticamente --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulário principal de Venda --}}
        <form action="{{ route('vendas-acessorios.store') }}" method="POST">
            @csrf {{-- Diretiva Blade para incluir o token CSRF (segurança) --}}

            {{-- Card: Dados da Venda Principal --}}
            <div class="card mb-4">
                <div class="card-header">Dados da Venda</div>
                <div class="card-body">
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="data_venda" class="form-label">Data da Venda</label>
                            {{-- Input de data, preenchido com old() em caso de erro, padrão data atual --}}
                            <input type="date" class="form-control" id="data_venda" name="data_venda" value="{{ old('data_venda', date('Y-m-d')) }}" required>
                             {{-- Exibir erro específico para este campo --}}
                            @error('data_venda')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cliente_nome" class="form-label">Cliente (Opcional)</label>
                            {{-- Input de texto para o nome/busca do cliente com autocomplete --}}
                            <input type="text" class="form-control" id="cliente_nome" name="cliente_nome" placeholder="Digite o nome ou CPF/CNPJ do cliente" value="{{ old('cliente_nome') }}">
                             {{-- Campo hidden para guardar o ID do cliente selecionado pelo autocomplete --}}
                            <input type="hidden" id="cliente_id" name="cliente_id" value="{{ old('cliente_id') }}">
                             {{-- Exibir erro específico para o campo cliente_id (se a validação falhar, ex: cliente não existe) --}}
                            @error('cliente_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                             {{-- Opcional: Exibir erro geral para a seção de itens se a validação de estoque falhar na lista de itens --}}
                             {{-- @error('itens')
                                 <div class="text-danger">{{ $message }}</div>
                             @enderror --}}
                             {{-- Opcional: Exibir erro customizado específico, se usar ValidationException::withMessages(['seu_campo_customizado' => ...]) --}}
                             {{-- @error('quantidade_indisponivel')
                                  <div class="text-danger">{{ $message }}</div>
                             @enderror --}}
                        </div>
                    </div>

                      <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="forma_pagamento" class="form-label">Forma de Pagamento (Opcional)</label>
                            {{-- Input de texto atual --}}
                            <input type="text" class="form-control" id="forma_pagamento" name="forma_pagamento" value="{{ old('forma_pagamento') }}">
                             @error('forma_pagamento')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                         <div class="col-md-6 mb-3">
                             <label for="valor_total" class="form-label">Valor Total (Será calculado)</label>
                             {{-- Input somente leitura para exibir o valor total calculado via JavaScript --}}
                            <input type="text" class="form-control" id="valor_total" name="valor_total" value="{{ old('valor_total', '0.00') }}" readonly>
                              {{-- Não há erro de validação para este campo, pois é calculado no frontend/backend --}}
                         </div>
                    </div>

                     <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações (Opcional)</label>
                        {{-- Textarea para observações, preenchido com old() --}}
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3">{{ old('observacoes') }}</textarea>
                         @error('observacoes')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Card: Itens Vendidos (Seção Dinâmica) --}}
             <div class="card mb-4">
                <div class="card-header">Itens Vendidos</div>
                <div class="card-body">
                     {{-- Cabeçalho da tabela de itens para telas maiores --}}
                     <div class="row mb-2 d-none d-md-flex"> {{-- d-none d-md-flex: Esconde em telas pequenas, mostra como flexbox em telas médias e maiores --}}
                         <div class="col-md-4"><label class="form-label">Peça</label></div> {{-- Ajustado Largura --}}
                         <div class="col-md-2"><label class="form-label">Qtd</label></div>
                         <div class="col-md-2"><label class="form-label">Preço Unit</label></div> {{-- Ajustado Largura --}}
                         <div class="col-md-2"><label class="form-label">Desconto (R$)</label></div> {{-- NOVO Cabeçalho --}}
                         <div class="col-md-2"></div> {{-- Coluna vazia para o botão remover --}}
                     </div>

                    {{-- Container onde as linhas de itens serão adicionadas/removidas via JavaScript --}}
                    <div id="itens-venda-container">
                        {{-- As linhas de item serão inseridas aqui --}}

                         {{-- Se houver dados antigos (old input) após um erro de validação, pré-carregar os itens --}}
                         @if(old('itens'))
                            {{-- Loop sobre os dados antigos dos itens --}}
                            @foreach(old('itens') as $index => $itemData)
                                 {{-- Inclui a view parcial do template para cada item antigo --}}
                                 {{-- Passamos o índice, os dados antigos do item, e a lista de todos os itens de estoque (para o dropdown) --}}
                                 @include('vendas_acessorios._item_venda_template', ['index' => $index, 'itemData' => $itemData, 'itensEstoque' => $itensEstoque])
                            @endforeach
                        @endif
                    </div>

                    {{-- Botão para adicionar uma nova linha de item --}}
                    <button type="button" class="btn btn-secondary mt-3" id="adicionar-item">Adicionar Item</button>
                </div>
            </div>


            {{-- Botões de Ação do Formulário --}}
            <button type="submit" class="btn btn-primary me-2">Registrar Venda</button>
            {{-- Link para voltar para a lista de vendas --}}
            <a href="{{ route('vendas-acessorios.index') }}" class="btn btn-secondary">Cancelar</a>

        </form>

    </div>

    {{-- Scripts --}}
    {{-- Inclua jQuery (antes do jQuery UI) e jQuery UI JS para o autocomplete --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> {{-- Versão 3.6.0 ou superior é recomendada --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    {{-- Script do Bootstrap --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Script JavaScript Principal para a Dinâmica do Formulário de Venda --}}
    <script>
        $(document).ready(function () {
            // --- Configuração do Autocomplete de Cliente ---
            $("#cliente_nome").autocomplete({
                // Fonte de dados para o autocomplete (requisição AJAX para a rota de autocomplete de clientes)
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('clientes.autocomplete') }}", // Rota Laravel para buscar clientes por nome/CPF/CNPJ
                        type: 'GET',
                        dataType: "json", // Espera JSON como resposta
                        data: { search: request.term }, // Envia o texto digitado pelo usuário como parâmetro 'search'
                        success: function (data) {
                            // Mapeia os dados recebidos para o formato que o jQuery UI espera ({label, value})
                            response($.map(data, function (item) {
                                return {
                                    label: item.nome_completo + ' (' + item.cpf_cnpj + ')', // O que aparece no dropdown
                                    value: item.nome_completo, // Preenche o campo de texto ao selecionar
                                    id: item.id, // O ID do cliente (guardado para o campo hidden)
                                };
                            }));
                        }
                    });
                },
                // Função executada quando um item é selecionado no dropdown
                select: function (event, ui) {
                    $('#cliente_nome').val(ui.item.label); // Preenche o campo de texto com o label formatado
                    $('#cliente_id').val(ui.item.id); // Preenche o campo hidden com o ID do cliente
                    return false; // Impede que o jQuery UI altere o valor do campo de forma automática (já fizemos manualmente)
                },
                // Opcional: Lidar com caso o usuário não selecione do dropdown
                 change: function(event, ui) {
                     if (!ui.item) { // Se ui.item é nulo, significa que o usuário não selecionou do dropdown
                         $('#cliente_id').val(''); // Limpa o campo hidden para evitar um ID inválido
                          // Opcional: Você pode querer limpar o campo de texto ou exibir uma mensagem
                         // $(this).val(''); // Limpa o campo de texto também
                     }
                 }
            });

            // --- Configuração da Adição Dinâmica de Itens de Venda ---
             // Contador global para o índice dos itens.
             // Começa com o número de itens já presentes no container (se houver old input recarregado).
             let itemIndex = $('#itens-venda-container .item-venda').length;

             // Função para adicionar uma nova linha de item ao formulário
             // Pode receber dados opcionais para pré-popular a linha (usado com old input)
             function adicionarItem(itemData = null) {
                 // Faz uma requisição GET para a rota que renderiza o template de item
                 // Envia o índice atual e dados antigos (se existirem) como query parameters
                 // O Laravel irá renderizar a view parcial _item_venda_template.blade.php com os dados passados.
                 $.get("{{ route('vendas-acessorios.item_template') }}", { index: itemIndex, itemData: itemData }, function(html) {
                     // Adiciona o HTML renderizado (uma nova linha de item) ao container no formulário
                     $('#itens-venda-container').append(html);

                     // Incrementa o contador global para o próximo item
                     itemIndex++;

                     // Opcional: Lógica para pré-selecionar um item se a página veio com estoque_id na URL e este é o primeiro item adicionado via JS
                     // (Útil se você clica em 'Registrar Venda' na lista de peças e quer que o formulário de venda já venha com aquela peça selecionada)
                     // Esta lógica é mais complexa e pode depender de como você passa o estoque_id pela URL e o trata no create controller.
                     // Por enquanto, a lógica de pré-seleção já está no template _item_venda_template.blade.php para o caso de old input.
                 });
             }


             // Configuração para remover uma linha de item
             // Usa delegação de evento (.on) pois os botões 'Remover' são adicionados dinamicamente
             $('#itens-venda-container').on('click', '.remover-item', function() {
                 // Encontra o elemento pai mais próximo com a classe 'item-venda' (a linha inteira) e remove-o do DOM
                 $(this).closest('.item-venda').remove();
                 // Recalcula o valor total da venda após remover um item
                 calcularTotalVenda();
                 // Nota: A remoção de itens do meio pode criar lacunas nos índices dos arrays enviados (ex: itens[0], itens[2]).
                 // O backend do Laravel (controlador) lida bem com isso, processando o array associativo que chega.
             });

             // Função para calcular o valor total da venda somando os subtotais de todos os itens
             function calcularTotalVenda() {
                 let total = 0;
                 // Itera sobre cada linha de item de venda
                 $('#itens-venda-container .item-venda').each(function() {
                     // Encontra os inputs de quantidade, preço unitário E DESCONTO dentro desta linha
                     const quantidade = parseFloat($(this).find('.item-quantidade').val()) || 0; // Converte o valor para float, usa 0 se for inválido/vazio
                     const precoUnitario = parseFloat($(this).find('.item-preco-unitario').val()) || 0; // Converte o valor para float, usa 0 se for inválido/vazio
                     const descontoItem = parseFloat($(this).find('.item-desconto').val()) || 0; // NOVO: Pega o valor do campo de desconto em R$

                     // Calcula o subtotal para este item: (quantidade * preço unitário) - desconto
                     // Garante que o resultado não seja negativo
                     const subtotalItem = Math.max(0, (quantidade * precoUnitario) - descontoItem);


                     total += subtotalItem; // Soma o subtotal deste item ao total geral
                 });
                 // Atualiza o campo de Valor Total (id="valor_total") com o valor calculado, formatado para 2 casas decimais
                 $('#valor_total').val(total.toFixed(2));
             }

             // Configuração para recalcular o valor total quando a quantidade, o preço unitário OU o DESCONTO de qualquer item mudam
             // Usa delegação de evento nos inputs de quantidade, preço unitário e desconto dentro do container de itens
             $('#itens-venda-container').on('input', '.item-quantidade, .item-preco-unitario, .item-desconto', calcularTotalVenda); // ADICIONADO .item-desconto


             // Configuração opcional para pré-popular o preço unitário no campo ao selecionar uma peça no dropdown
             // Usa delegação de evento no dropdown de peça
              $('#itens-venda-container').on('change', '.item-estoque-id', function() {
                  // Encontra a opção (<option>) que foi selecionada no dropdown
                  const selectedOption = $(this).find('option:selected');
                  // Busca o valor do atributo data-preco-venda desta opção (que foi preenchido no template Blade)
                  const precoVenda = selectedOption.data('preco-venda') || '0.00';
                  // Encontra o campo de preço unitário (.item-preco-unitario) dentro da mesma linha de item e preenche seu valor
                  $(this).closest('.item-venda').find('.item-preco-unitario').val(parseFloat(precoVenda).toFixed(2));

                   // Opcional: Lógica para buscar e armazenar estoque disponível para validação no frontend (mais complexo)
                   // const estoqueDisponivel = selectedOption.data('estoque-disponivel');
                   // $(this).closest('.item-venda').find('.item-estoque-disponivel-field').val(estoqueDisponivel);

                  // Recalcula o valor total da venda após mudar a peça (e consequentemente o preço unitário padrão)
                  calcularTotalVenda();
              });


             // --- Inicialização do Formulário ---

             // Configura o que acontece ao clicar no botão "Adicionar Item"
             $('#adicionar-item').on('click', function() {
                 adicionarItem(); // Chama a função para adicionar uma nova linha de item
             });

             // Se não houver itens pré-carregados no container (isso acontece se o formulário foi submetido com erro e recarregado sem itens válidos),
             // adiciona uma linha de item inicial vazia para começar.
             if (itemIndex === 0) {
                 adicionarItem();
             } else {
                 // Se houver itens pré-carregados (old input), recalcula o valor total inicial com base neles.
                 calcularTotalVenda();
             }

        }); // Fim do $(document).ready
    </script>
</body>
</html>