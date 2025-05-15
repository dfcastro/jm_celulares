<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>OS Atendimento #{{ $atendimento->id }}</title>
    <style>
        Reset básico e configuração da página @page {
            margin: 0.75cm;/ Reduzindo margens da página /
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;/ Fonte com bom suporte a caracteres especiais / margin: 0;
            font-size: 9pt;/ Reduzindo tamanho da fonte base / color: #333;
            line-height: 1.3;/ Espaçamento entre linhas um pouco menor /
        }

        .container {
            width: 100%;/ Ocupa a largura da margem da página / margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;/ Reduzido / border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0 0 2px 0;
            font-size: 16pt;/ Reduzido /
        }

        .header p {
            margin: 1px 0;
            font-size: 8pt;/ Reduzido /
        }

        .header .os-title {
            font-size: 11pt;
            font-weight: bold;
            margin-top: 5px;
            margin-bottom: 0;
        }

        .section {
            margin-bottom: 10px;/ Reduzido / padding: 8px;/ Reduzido / border: 1px solid #eee;
            border-radius: 3px;/ Menor /
        }

        .section h2 {
            font-size: 10pt;/ Reduzido / margin-top: 0;
            padding-bottom: 3px;/ Reduzido / border-bottom: 1px solid #ddd;
            margin-bottom: 5px;/ Reduzido /
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;/ Reduzido /
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px;/ Reduzido / text-align: left;
            font-size: 8pt;/ Reduzido / vertical-align: top;/ Alinha no topo para melhor uso do espaço /
        }

        th {
            background-color: #f5f5f5;/ Mais suave */ font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .label {
            font-weight: bold;
        }

        .problem-box,
        .laudo-box,
        .obs-box {
            min-height: 30px;
            /* Reduzido */
            max-height: 100px;
            /* Limita altura máxima para evitar expansão excessiva */
            overflow: hidden;
            /* Esconde o excesso se o conteúdo for muito grande */
            padding: 5px;
            /* Reduzido */
            border: 1px solid #eee;
            background-color: #fdfdfd;
            white-space: pre-wrap;
            margin-top: 3px;
            font-size: 8pt;
            /* Reduzido */
        }

        .total-section {
            margin-top: 10px;
            /* Reduzido */
            padding-top: 5px;
            /* Reduzido */
            border-top: 1.5px solid #333;
            /* Linha um pouco mais fina */
        }

        .total-section td {
            font-size: 9pt;
            /* Um pouco maior para destaque */
            padding: 3px 4px;
        }

        .total-section .grand-total .label,
        .total-section .grand-total .value {
            font-size: 10pt;
            font-weight: bold;
        }

        .signature-section {
            margin-top: 25px;
            /* Reduzido */
            page-break-inside: avoid;
            /* Tenta evitar quebra de página aqui */
        }

        .signature-line {
            border-bottom: 1px solid #000;
            width: 220px;
            /* Reduzido */
            margin: 20px auto 5px auto;
            /* Reduzido */
        }

        .signature-section p {
            font-size: 8pt;
        }

        .footer-pdf {
            /* Renomeado para evitar conflito com .footer do site */
            text-align: center;
            font-size: 7pt;
            /* Reduzido */
            color: #555;
            position: fixed;
            /* Para tentar fixar no final da página */
            bottom: 0.25cm;
            /* Próximo à margem inferior */
            left: 0.75cm;
            right: 0.75cm;
            height: 1cm;
        }

        .disclaimer {
            font-size: 7pt;
            /* Reduzido */
            text-align: justify;
            /* Justificado para melhor aproveitamento do espaço */
            margin-top: 15px;
            /* Reduzido */
            color: #666;
            page-break-inside: avoid;
        }

        /* Layout de duas colunas para dados do cliente e atendimento */
        .two-columns {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .column {
            display: table-cell;
            width: 50%;
            padding-right: 10px;
            vertical-align: top;
        }

        .column:last-child {
            padding-right: 0;
            padding-left: 10px;
        }

        .column p {
            margin: 2px 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            {{-- Tente usar uma imagem de logo menor, se tiver --}}
            {{-- <img src="{{ asset('images/logo_jm_pdf.png') }}" alt="Logo"
                style="max-height: 50px; margin-bottom: 5px;"> --}}
            <h1>{{ $nomeEmpresa ?? 'JM Celulares' }}</h1>
            <p>{{ $enderecoEmpresa ?? 'Alameda capitão José Custódio, 130, Centro - Monte Azul - MG' }}</p>
            <p>Telefone: {{ $telefoneEmpresa ?? '(38) 99269-6404' }} | Email: {{ $emailEmpresa ??
                'contato@jmcelulares.com.br' }}</p>
            <p class="os-title">ORDEM DE SERVIÇO - ATENDIMENTO #{{ $atendimento->id }}</p>
        </div>

        <div class="two-columns section">
            <div class="column">
                <h2>Dados do Cliente</h2>
                <p><span class="label">Cliente:</span> {{ $atendimento->cliente->nome_completo ?? 'N/A' }}</p>
                <p><span class="label">CPF/CNPJ:</span> {{ $atendimento->cliente->cpf_cnpj ?? 'N/A' }}</p>
                <p><span class="label">Telefone:</span> {{ $atendimento->cliente->telefone ?? 'N/A' }}</p>
                @if($atendimento->cliente && $atendimento->cliente->email)
                    <p><span class="label">Email:</span> {{ $atendimento->cliente->email }}</p>
                @endif
            </div>
            <div class="column">
                <h2>Dados do Atendimento</h2>
                <p><span class="label">Data Entrada:</span> {{ $atendimento->data_entrada->format('d/m/Y H:i') }}</p>
                <p><span class="label">Equipamento:</span> {{ Str::limit($atendimento->descricao_aparelho, 50) }}</p>
                <p><span class="label">Status:</span> {{ $atendimento->status }}</p>
                <p><span class="label">Técnico:</span> {{ $atendimento->tecnico->name ?? 'Não atribuído' }}</p>
            </div>
        </div>

        <div class="section">
            <h2>Problema Relatado</h2>
            <div class="problem-box">{{ $atendimento->problema_relatado }}</div>
        </div>

        @if($atendimento->laudo_tecnico)
            <div class="section">
                <h2>Laudo Técnico / Solução Aplicada</h2>
                <div class="laudo-box">{{ $atendimento->laudo_tecnico }}</div>
            </div>
        @endif

        @if($atendimento->observacoes)
            <div class="section">
                <h2>Observações Adicionais</h2>
                <div class="obs-box">{{ $atendimento->observacoes }}</div>
            </div>
        @endif

        @if($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
            <div class="section">
                <h2>Peças Utilizadas</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Peça (Modelo)</th>
                            <th class="text-center">Qtd.</th>
                            <th class="text-right">Preço Un. (R$)</th>
                            <th class="text-right">Subtotal (R$)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($atendimento->saidasEstoque as $saida)
                            @if($saida->estoque)
                                <tr>
                                    <td>{{ Str::limit($saida->estoque->nome . ($saida->estoque->modelo_compativel ? ' (' . $saida->estoque->modelo_compativel . ')' : ''), 45) }}
                                    </td>
                                    <td class="text-center">{{ $saida->quantidade }}</td>
                                    <td class="text-right">{{ number_format($saida->estoque->preco_venda ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-right">
                                        {{ number_format($saida->quantidade * ($saida->estoque->preco_venda ?? 0), 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <div class="section total-section">
            <h2>Valores</h2>
            <table>
                <tbody>
                    <tr>
                        <td class="label">Mão de Obra/Serviços:</td>
                        <td class="text-right">R$ {{ number_format($atendimento->valor_servico ?? 0, 2, ',', '.') }}
                        </td>
                    </tr>
                    @if(($atendimento->desconto_servico ?? 0) > 0)
                        <tr>
                            <td class="label">Desconto Serviços:</td>
                            <td class="text-right" style="color: #d9534f;">- R$
                                {{ number_format($atendimento->desconto_servico ?? 0, 2, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td class="label">Subtotal Serviços:</td>
                        <td class="text-right">R$ {{ number_format($valorServicoLiquido, 2, ',', '.') }}</td>
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

        <div class="signature-section">
            <p class="text-center">Declaro estar ciente e de acordo com os serviços, valores e termos descritos.</p>
            <div class="signature-line"></div>
            <p class="text-center">{{ $atendimento->cliente->nome_completo ?? 'Assinatura do Cliente' }}</p>
        </div>

        <div class="disclaimer">
            TERMOS E CONDIÇÕES: A garantia das peças e serviços é de 90 dias, cobrindo defeitos de fabricação e do
            serviço prestado. Danos por mau uso, contato com líquidos, quedas ou intervenção de terceiros não são
            cobertos. Aparelhos não retirados em até 90 dias após a comunicação de conclusão do serviço poderão ser
            descartados ou vendidos para cobrir custos operacionais, conforme legislação vigente.
        </div>

        <div class="footer-pdf">
            <p>Impresso em: {{ $dataImpressao->format('d/m/Y H:i:s') }} | Código de Consulta Cliente:
                {{ $atendimento->codigo_consulta }}
            </p>
        </div>
    </div>
</body>

</html>