<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Orçamento #{{ $orcamento->id }}</title>
    <style>
        @page {
            margin: 0.8cm 1.2cm; /* Margens da página */
            font-family: 'DejaVu Sans', sans-serif; /* Fonte que suporta mais caracteres */
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            font-size: 9pt; /* Tamanho de fonte base */
            color: #333;
            line-height: 1.3;
        }
        .header-pdf {
            width: 100%;
            border-bottom: 1.5px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
            text-align: center; /* Centraliza o header */
        }
        .header-pdf .logo-area {
             text-align: left; float: left; width: 60%;
        }
        .header-pdf .info-empresa-pdf {
            font-size: 8pt;
            line-height: 1.2;
        }
        .header-pdf .info-empresa-pdf h1 {
            margin: 0 0 2px 0;
            font-size: 16pt;
            color: #000;
        }
        .header-pdf .orcamento-id-area {
            text-align: right; float: right; width: 38%;
        }
        .header-pdf .orcamento-id-area h2 {
            margin: 5px 0 0 0;
            font-size: 11pt;
            color: #000;
        }
        .header-pdf .orcamento-id-area p {
            margin: 2px 0;
            font-size: 8pt;
        }

        .clearfix::after { content: ""; clear: both; display: table; }

        .section-pdf {
            margin-bottom: 12px;
            padding: 8px;
            border: 1px solid #e0e0e0;
            border-radius: 3px;
            page-break-inside: avoid; /* Tenta não quebrar a seção no meio da página */
        }
        .section-pdf h3 {
            font-size: 10pt;
            margin: 0 0 6px 0;
            padding-bottom: 4px;
            border-bottom: 1px solid #ccc;
            color: #111;
        }
        .details-table { width: 100%; margin-bottom: 8px; }
        .details-table td { padding: 1px 0; vertical-align: top; font-size: 8.5pt; }
        .details-table td.label { font-weight: bold; width: 150px; color: #555;} /* Ajuste a largura do label */

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            font-size: 8pt;
            text-align: left;
        }
        .items-table th { background-color: #f2f2f2; font-weight: bold; }
        .items-table td.qty, .items-table td.price, .items-table td.subtotal { text-align: right; }
        .items-table td.desc { width: 50%; }


        .totals-section-pdf { margin-top: 10px; padding-top: 8px; border-top: 1px solid #555; }
        .totals-table { width: 50%; float: right; border-collapse: collapse; } /* Alinhada à direita */
        .totals-table td { padding: 3px 5px; font-size: 8.5pt; }
        .totals-table td.label { text-align: right; font-weight: bold; }
        .totals-table td.value { text-align: right; }
        .totals-table tr.grand-total td { font-weight: bold; font-size: 10pt; border-top: 1px solid #999; padding-top: 5px; }

        .footer-pdf {
            position: fixed;
            bottom: -0.5cm; /* Ajuste para não cortar */
            left: 0cm;
            right: 0cm;
            height: 1cm;
            text-align: center;
            font-size: 7.5pt;
            color: #777;
        }
        .footer-pdf .page-info::before { content: "Página " counter(page) " de " counter(pages); }
        .footer-pdf .print-date { float: left; }

        .terms-pdf { margin-top: 15px; font-size: 7.5pt; text-align: justify; line-height: 1.2; }
        pre { white-space: pre-wrap; font-family: inherit; margin: 0; font-size: 8.5pt; }
    </style>
</head>
<body>

    <div class="header-pdf clearfix">
        <div class="logo-area">
            {{-- Se você tiver um logo: <img src="{{ public_path('images/logo_jm_celulares.png') }}" alt="Logo" style="max-height: 45px; margin-bottom: 5px;"> --}}
            <div class="info-empresa-pdf">
                <h1>{{ $nomeEmpresa }}</h1>
                <p>{{ $enderecoEmpresa }}</p>
                <p>Telefone: {{ $telefoneEmpresa }} | Email: {{ $emailEmpresa }}</p>
            </div>
        </div>
        <div class="orcamento-id-area">
            <h2>ORÇAMENTO #{{ $orcamento->id }}</h2>
            <p>Data de Emissão: {{ $orcamento->data_emissao->format('d/m/Y') }}</p>
            @if($orcamento->data_validade)
            <p>Válido até: {{ $orcamento->data_validade->format('d/m/Y') }}</p>
            @elseif($orcamento->validade_dias)
            <p>Validade: {{ $orcamento->validade_dias }} dias</p>
            @endif
        </div>
    </div>

    <div class="section-pdf">
        <h3>Dados do Cliente e Aparelho</h3>
        <table class="details-table">
            <tr>
                <td class="label">Cliente:</td>
                <td>
                    @if($orcamento->cliente)
                        {{ $orcamento->cliente->nome_completo }}
                    @else
                        {{ $orcamento->nome_cliente_avulso ?? 'Não informado' }}
                    @endif
                </td>
            </tr>
            @if($orcamento->cliente && $orcamento->cliente->cpf_cnpj)
            <tr><td class="label">CPF/CNPJ:</td><td>{{ $orcamento->cliente->cpf_cnpj }}</td></tr>
            @endif
            <tr>
                <td class="label">Telefone:</td>
                <td>{{ $orcamento->cliente->telefone ?? ($orcamento->telefone_cliente_avulso ?? 'Não informado') }}</td>
            </tr>
            @if($orcamento->cliente?->email || $orcamento->email_cliente_avulso)
            <tr>
                <td class="label">Email:</td>
                <td>{{ $orcamento->cliente->email ?? ($orcamento->email_cliente_avulso ?? 'Não informado') }}</td>
            </tr>
            @endif
            <tr><td colspan="2">&nbsp;</td></tr> {{-- Linha de espaço --}}
            <tr><td class="label">Aparelho:</td><td>{{ $orcamento->descricao_aparelho }}</td></tr>
            <tr><td class="label">Problema Relatado:</td><td><pre>{{ $orcamento->problema_relatado_cliente }}</pre></td></tr>
            @if($orcamento->tempo_estimado_servico)
            <tr><td class="label">Tempo Estimado:</td><td>{{ $orcamento->tempo_estimado_servico }}</td></tr>
            @endif
        </table>
    </div>

    <div class="section-pdf">
        <h3>Itens do Orçamento (Serviços e Peças)</h3>
        @if($orcamento->itens->isNotEmpty())
            <table class="items-table">
                <thead>
                    <tr>
                        <th class="desc">Descrição</th>
                        <th class="qty">Qtd.</th>
                        <th class="price">Val. Unit.</th>
                        <th class="subtotal">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orcamento->itens as $item)
                    <tr>
                        <td class="desc">
                            {{ $item->tipo_item == 'peca' && $item->estoque ? $item->estoque->nome : $item->descricao_item_manual }}
                            @if($item->tipo_item == 'peca' && $item->estoque && $item->estoque->modelo_compativel)
                                <small style="color: #555; font-size: 7pt;"><br>Modelo: {{ $item->estoque->modelo_compativel }}</small>
                            @endif
                        </td>
                        <td class="qty">{{ $item->quantidade }}</td>
                        <td class="price">R$ {{ number_format($item->valor_unitario, 2, ',', '.') }}</td>
                        <td class="subtotal">R$ {{ number_format($item->subtotal_item, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhum item detalhado neste orçamento.</p>
        @endif
    </div>

    <div class="totals-section-pdf clearfix">
        <table class="totals-table">
            @if($orcamento->valor_total_servicos > 0)
            <tr><td class="label">Total Serviços:</td><td class="value">R$ {{ number_format($orcamento->valor_total_servicos, 2, ',', '.') }}</td></tr>
            @endif
            @if($orcamento->valor_total_pecas > 0)
            <tr><td class="label">Total Peças:</td><td class="value">R$ {{ number_format($orcamento->valor_total_pecas, 2, ',', '.') }}</td></tr>
            @endif
            <tr><td class="label">Subtotal:</td><td class="value">R$ {{ number_format($orcamento->sub_total, 2, ',', '.') }}</td></tr>
            @if($orcamento->desconto_valor > 0)
                @php
                    $valorDescontoCalculadoPdf = 0;
                    if ($orcamento->desconto_tipo == 'percentual') {
                        $valorDescontoCalculadoPdf = ($orcamento->sub_total * $orcamento->desconto_valor) / 100;
                        $textoDesconto = "Desconto (" . number_format($orcamento->desconto_valor, 0) . "%):";
                    } else {
                        $valorDescontoCalculadoPdf = $orcamento->desconto_valor;
                        $textoDesconto = "Desconto (Fixo):";
                    }
                @endphp
                <tr><td class="label" style="color: #c00;">{{ $textoDesconto }}</td><td class="value" style="color: #c00;">- R$ {{ number_format($valorDescontoCalculadoPdf, 2, ',', '.') }}</td></tr>
            @endif
            <tr class="grand-total">
                <td class="label">VALOR FINAL:</td>
                <td class="value">R$ {{ number_format($orcamento->valor_final, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    <div class="clearfix"></div>


    @if($orcamento->termos_condicoes)
    <div class="terms-pdf section-pdf" style="margin-top: 15px; border-top: 1px solid #ccc; padding-top: 8px;">
        <h3>Termos e Condições</h3>
        <pre>{{ $orcamento->termos_condicoes }}</pre>
    </div>
    @endif

    <div class="footer-pdf">
        <span class="print-date">Impresso em: {{ $dataImpressao->format('d/m/Y H:i') }}</span>
        <span class="page-info"></span> {{-- O CSS cuida de "Página X de Y" --}}
    </div>

</body>
</html>