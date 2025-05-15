<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>OS Atendimento #{{ $atendimento->id }} - {{ $atendimento->cliente->nome_completo ?? 'Cliente' }}</title>
<style>
    @page {
        margin: 0.8cm 1cm; /* topo/baixo, esquerda/direita */
    }
    body {
        font-family: 'DejaVu Sans', sans-serif;
        margin: 0;
        font-size: 8.5pt;
        color: #2c2c2c;
        line-height: 1.25;
    }

    /* --- CABEÇALHO --- */
    .header-pdf-wrapper { /* Novo wrapper para o cabeçalho */
        width: 100%;
        border-bottom: 1.5px solid #444;
        padding-bottom: 7px;
        margin-bottom: 12px;
    }
    .header-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed; /* Ajuda a respeitar as larguras das colunas */
    }
    .header-table td {
        vertical-align: top;
        padding: 0;
        border: none;
    }
    .logo-info-cell { /* Célula da esquerda: Logo e Infos da Loja */
        width: 60%; /* Ajuste conforme necessário */
        text-align: left;
    }
    .logo-info-cell img.logo {
        max-height: 38px; /* Ajuste o tamanho do seu logo */
        margin-bottom: 4px;
    }
    .logo-info-cell h1 {
        margin: 0 0 1px 0;
        font-size: 15pt; /* Nome da empresa */
        color: #111;
    }
    .logo-info-cell p.store-contact {
        margin: 0;
        font-size: 7.5pt;
        line-height: 1.2;
        color: #333;
    }
    .os-title-header { /* "ORDEM DE SERVIÇO #XX" */
        font-size: 10pt;
        font-weight: bold;
        margin-top: 5px;
        text-align: left;
        color: #000;
    }

    .consulta-info-cell { /* Célula da direita: Código, Link, QRCode */
        width: 40%; /* Ajuste conforme necessário */
        text-align: right; /* Alinha o conteúdo da célula à direita */
    }
    .consulta-info-cell .codigo-consulta-box {
        border: 1px solid #005cbf; /* Borda azul escura */
        background-color: #e7f0ff; /* Fundo azul bem clarinho */
        color: #004085;      /* Cor do texto dentro da caixa */
        padding: 5px 8px;     /* Aumenta padding interno */
        border-radius: 4px;
        margin-bottom: 4px;
        display: inline-block; /* Para a caixa se ajustar ao conteúdo */
        text-align: center;
        min-width: 120px; /* Largura mínima para a caixa */
    }
    .consulta-info-cell .codigo-consulta-box .label {
        font-size: 7pt;
        display: block;
        margin-bottom: 2px;
        font-weight: normal;
    }
    .consulta-info-cell .codigo-consulta-box .codigo {
        font-size: 14pt; /* Código BEM destacado */
        font-weight: bold;
        letter-spacing: 0.5px;
    }
    .consulta-info-cell .link-consulta {
        font-size: 7pt;
        margin-top: 3px;
        margin-bottom: 3px;
        display: block; /* Para ocupar a linha */
        text-align: right;
    }
    .consulta-info-cell .link-consulta a {
        color: #0056b3;
        text-decoration: none;
        font-weight: bold;
    }
    .consulta-info-cell .qr-code-image {
        width: 55px !important;  /* Tamanho da imagem do QRCode */
        height: 55px !important; /* Tamanho da imagem do QRCode */
        margin-top: 2px;
        display: block; /* Para margin-left: auto funcionar */
        margin-left: auto; /* Alinha a imagem à direita dentro desta div */
    }
    .consulta-info-cell p.qr-caption {
        font-size: 6pt;
        margin: 1px 0 0 0;
        text-align: center;
    }


    /* --- RODAPÉ (Simples para número de página e data) --- */
    #footer-pdf {
        position: fixed;
        bottom: -0.3cm; /* Ajuste para não cortar */
        left: 0cm;
        right: 0cm;
        height: 1cm;
        text-align: center;
        font-size: 7pt;
        color: #555;
    }
    #footer-pdf .page-info {
        width: 100%;
    }
    #footer-pdf .page-info .print-date { float: left; }
    #footer-pdf .page-info .page-number:after { content: "Página " counter(page) " de " counter(pages); float: right; }
    #footer-pdf .clearfix::after { content: ""; clear: both; display: table; }


    /* --- Demais Estilos (Seções, Tabelas, etc.) --- */
    /* Mantenha seus estilos otimizados para .section, .data-table, .problem-box, etc. como na versão anterior que visava uma página */
    .content-area { /* Não é mais necessário um padding-top grande aqui */ }
    .section { margin-bottom: 7px; padding: 5px; border: 0.8px solid #f0f0f0; border-radius: 2px; page-break-inside: avoid; background-color: #fff; }
    .section h2 { font-size: 8.5pt; margin: 0 0 3px 0; padding-bottom: 2px; border-bottom: 0.8px solid #e0e0e0; color: #111; }
    .two-columns { display: table !important; width: 100% !important; table-layout: fixed !important; border-spacing: 5px 0; border-collapse: separate; }
    .column { display: table-cell !important; width: 49% !important; vertical-align: top; }
    .column p { margin: 1px 0; font-size: 7.5pt; line-height: 1.2; }
    .label { font-weight: bold; }

    table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
    .data-table th, .data-table td { border: 0.8px solid #e0e0e0; padding: 2px; font-size: 7pt; vertical-align: top; }
    .data-table th { background-color: #f9f9f9; font-weight: bold; }
    .text-right { text-align: right !important; }
    .text-center { text-align: center !important; }

    .problem-box, .laudo-box, .obs-box {
        min-height: 20px; max-height: 60px; overflow: hidden;
        padding: 3px; border: 0.8px solid #f0f0f0; background-color: #fdfdfd;
        white-space: pre-wrap; margin-top: 1px; font-size: 7pt;
    }

    .total-section { margin-top: 4px; padding-top: 2px; border-top: 0.8px solid #333; }
    .total-section table { margin-bottom: 0; }
    .total-section td { font-size: 7.5pt; padding: 1px 2px; }
    .total-section .grand-total .label,
    .total-section .grand-total .value { font-size: 9pt; font-weight: bold; padding: 1.5px; }

    .signature-section { margin-top: 10px; page-break-inside: avoid; text-align: center; }
    .signature-section p { font-size: 7pt; margin: 0; }
    .signature-line { border-bottom: 0.5px solid #333; width: 170px; margin: 10px auto 1px auto; }

    .disclaimer { font-size: 6pt; text-align: justify; margin-top: 8px; color: #444; line-height: 1.1; page-break-inside: avoid;}

</style>
</head>
<body>
    <div class="header-pdf-wrapper">
        <table class="header-table">
            <tr>
                <td class="logo-info-cell">
                    {{-- <img src="{{ public_path('images/logo_real.png') }}" alt="JM Celulares" class="logo"> --}}
                    <h1>{{ $nomeEmpresa ?? 'JM Celulares' }}</h1>
                    <p class="store-contact">{{ $enderecoEmpresa ?? 'Endereço da Loja' }}</p>
                    <p class="store-contact">Telefone: {{ $telefoneEmpresa ?? 'Telefone' }} | Email: {{ $emailEmpresa ?? 'Email' }}</p>
                    <p class="os-title-header">ORDEM DE SERVIÇO #{{ $atendimento->id }}</p>
                </td>
                <td class="consulta-info-cell">
                    <div class="codigo-consulta-box">
                        <span class="label">CÓDIGO DE CONSULTA</span>
                        <span class="codigo">{{ $atendimento->codigo_consulta }}</span>
                    </div>
                    <div class="link-consulta">
                        <p>Consulte em: <a href="{{ $urlConsultaSite }}">{{ Str::limit(Str::replaceFirst('http://', '', Str::replaceFirst('https://', '', $urlConsultaSite)), 30) }}</a></p>
                    </div>
                    @if(file_exists(public_path('images/qrcode_consulta.png')))
                        <div class="qr-code-header">
                            <img class="qr-code-image" src="{{ public_path('images/qrcode_consulta.png') }}" alt="QR Code">
                            <p class="qr-caption">Aponte a câmera</p>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="content-area">
        <div class="container-pdf">
            {{-- DADOS DO CLIENTE E ATENDIMENTO --}}
            <div class="two-columns section" style="margin-top: 0; padding-top:0; border-top:none;">
                <div class="column" style="padding-right: 3px;">
                    <h2>Dados do Cliente</h2>
                    <p><span class="label">Cliente:</span> {{ Str::limit($atendimento->cliente->nome_completo ?? 'N/A', 35) }}</p>
                    <p><span class="label">CPF/CNPJ:</span> {{ $atendimento->cliente->cpf_cnpj ?? 'N/A' }}</p>
                    <p><span class="label">Telefone:</span> {{ $atendimento->cliente->telefone ?? ($atendimento->celular_contato_cliente ?? 'N/A') }}</p>
                    @if($atendimento->cliente && $atendimento->cliente->email)
                    <p><span class="label">Email:</span> {{ Str::limit($atendimento->cliente->email, 30) }}</p>
                    @endif
                </div>
                <div class="column" style="padding-left: 3px;">
                    <h2>Dados do Atendimento</h2>
                    <p><span class="label">Data Entrada:</span> {{ $atendimento->data_entrada->format('d/m/y H:i') }}</p>
                    <p><span class="label">Equipamento:</span> {{ Str::limit($atendimento->descricao_aparelho, 30) }}</p>
                    <p><span class="label">Status:</span> {{ $atendimento->status }}</p>
                    <p><span class="label">Técnico:</span> {{ Str::limit($atendimento->tecnico->name ?? 'N/A', 18) }}</p>
                </div>
            </div>

            {{-- PROBLEMA, LAUDO, OBSERVAÇÕES --}}
            <div class="section">
                <h2>Problema Relatado</h2>
                <div class="problem-box">{{ Str::limit($atendimento->problema_relatado, 250) }}</div>
            </div>
            @if($atendimento->laudo_tecnico)
            <div class="section">
                <h2>Laudo Técnico / Solução</h2>
                <div class="laudo-box">{{ Str::limit($atendimento->laudo_tecnico, 250) }}</div>
            </div>
            @endif
            @if($atendimento->observacoes)
            <div class="section">
                <h2>Observações Adicionais</h2>
                <div class="obs-box">{{ Str::limit($atendimento->observacoes, 200) }}</div>
            </div>
            @endif

            {{-- PEÇAS UTILIZADAS --}}
            @if($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
            <div class="section">
                <h2>Peças Utilizadas (Até 5 itens)</h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Peça (Modelo)</th>
                            <th class="text-center" style="width:10%;">Qtd.</th>
                            <th class="text-right" style="width:23%;">Preço Un.</th>
                            <th class="text-right" style="width:23%;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($atendimento->saidasEstoque->take(5) as $saida)
                            @if($saida->estoque)
                            <tr>
                                <td>{{ Str::limit($saida->estoque->nome . ($saida->estoque->modelo_compativel ? ' ('.$saida->estoque->modelo_compativel.')' : ''), 28) }}</td>
                                <td class="text-center">{{ $saida->quantidade }}</td>
                                <td class="text-right">R${{ number_format($saida->estoque->preco_venda ?? 0, 2, ',', '.') }}</td>
                                <td class="text-right">R${{ number_format($saida->quantidade * ($saida->estoque->preco_venda ?? 0), 2, ',', '.') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        @if($atendimento->saidasEstoque->count() > 5)
                            <tr><td colspan="4" style="font-size: 6pt; text-align:center; padding: 1px;"><i>Mais {{ $atendimento->saidasEstoque->count() - 5 }} peça(s) no sistema.</i></td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @endif

            {{-- VALORES --}}
            <div class="section total-section">
                <h2>Valores</h2>
                <table class="data-table">
                    <tbody>
                        <tr>
                            <td class="label" style="width:65%;">Mão de Obra/Serviços:</td>
                            <td class="text-right">R$ {{ number_format($atendimento->valor_servico ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        @if(($atendimento->desconto_servico ?? 0) > 0)
                        <tr>
                            <td class="label">Desconto Serviços:</td>
                            <td class="text-right" style="color: #c9302c;">- R$ {{ number_format($atendimento->desconto_servico ?? 0, 2, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Subtotal Serviços:</td>
                            <td class="text-right fw-bold">R$ {{ number_format($valorServicoLiquido, 2, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Total Peças:</td>
                            <td class="text-right">R$ {{ number_format($valorTotalPecas, 2, ',', '.') }}</td>
                        </tr>
                        <tr class="grand-total">
                            <td class="label">VALOR TOTAL:</td>
                            <td class="text-right value">R$ {{ number_format($valorTotalAtendimento, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- ASSINATURA --}}
            <div class="signature-section">
                <p>Declaro estar ciente e de acordo com os serviços, valores e termos descritos.</p>
                <div class="signature-line"></div>
                <p>{{ Str::limit($atendimento->cliente->nome_completo ?? 'Assinatura do Cliente', 35) }}</p>
            </div>

            {{-- DISCLAIMER E INFO DE IMPRESSÃO --}}
            <div class="disclaimer">
                TERMOS E CONDIÇÕES: Garantia de 90 dias para peças e serviços (defeitos de fabricação/serviço). Exclui-se mau uso, líquidos, quedas, intervenção de terceiros. Aparelhos não retirados em 90 dias após comunicação de conclusão poderão ser descartados/vendidos para cobrir custos, cf. legislação.
            </div>

        </div> {{-- Fim .container-pdf --}}
    </div> {{-- Fim .content-area --}}

    {{-- Rodapé Fixo para Informações de Impressão e Paginação --}}
    <div id="footer-pdf">
        <div class="page-info clearfix">
            <span class="print-date">Impresso em: {{ $dataImpressao->format('d/m/Y H:i') }}</span>
            <span class="page-number"></span>
        </div>
    </div>
</body>
</html>