<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Ordem de Serviço - Atendimento #{{ $atendimento->id }}</title>
<style>
body { font-family: 'DejaVu Sans', sans-serif; margin: 0; font-size: 12px; color: #333; }
.container { width: 90%; margin: 20px auto; }
.header, .footer { text-align: center; margin-bottom: 20px; }
.header h1 { margin: 0; font-size: 20px; }
.header p { margin: 2px 0; font-size: 10px; }
.section { margin-bottom: 20px; padding: 10px; border: 1px solid #eee; border-radius: 4px; }
.section h2 { font-size: 16px; margin-top: 0; padding-bottom: 5px; border-bottom: 1px solid #ccc; margin-bottom: 10px; }
table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
th, td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 11px; }
th { background-color: #f9f9f9; font-weight: bold; }
.text-right { text-align: right; }
.text-center { text-align: center; }
.label { font-weight: bold; }
.total-section { margin-top: 20px; padding-top: 10px; border-top: 2px solid #333; }
.total-section td { font-size: 13px; }
.signature-section { margin-top: 50px; }
.signature-line { border-bottom: 1px solid #000; width: 250px; margin: 40px auto 5px auto; }
.problem-box, .laudo-box { min-height: 60px; padding: 8px; border: 1px solid #eee; background-color: #fdfdfd; white-space: pre-wrap; margin-top: 5px; }
.disclaimer { font-size: 9px; text-align: center; margin-top: 30px; color: #777; }
</style>
</head>
<body>
<div class="container">
<div class="header">
<h1>{{  'JM Celulares' }}</h1>
<p>{{  'Alameda capitão José Custódio, 130, Centro - Monte Azul - MG' }}</p>
<p>Telefone: {{ '(38) 99269-6404' }} | Email: jmcelular@dominio.com</p>
<hr>
<h2>ORDEM DE SERVIÇO - ATENDIMENTO #{{ $atendimento->id }}</h2>
</div>   

        <div class="section">
            <h2>Dados do Cliente</h2>
            <p><span class="label">Cliente:</span> {{ $atendimento->cliente->nome_completo ?? 'N/A' }}</p>
            <p><span class="label">CPF/CNPJ:</span> {{ $atendimento->cliente->cpf_cnpj ?? 'N/A' }}</p>
            <p><span class="label">Telefone/Contato:</span> {{ $atendimento->cliente->telefone ?? $atendimento->celular ?? 'N/A' }}</p>
            <p><span class="label">Email:</span> {{ $atendimento->cliente->email ?? 'N/A' }}</p>
        </div>

        <div class="section">
            <h2>Dados do Atendimento e Equipamento</h2>
            <p><span class="label">Data de Entrada:</span> {{ $atendimento->data_entrada->format('d/m/Y H:i') }}</p>
            <p><span class="label">Equipamento/Celular:</span> {{ $atendimento->descricao_aparelho }}</p>
            <p><span class="label">Status Atual:</span> {{ $atendimento->status }}</p>
            <p><span class="label">Técnico Responsável:</span> {{ $atendimento->tecnico->name ?? 'Não atribuído' }}</p>
            <p><span class="label">Problema Relatado:</span></p>
            <div class="problem-box">{{ $atendimento->problema_relatado }}</div>
        </div>

        @if($atendimento->laudo_tecnico)
        <div class="section">
            <h2>Laudo Técnico / Solução Aplicada</h2>
            <div class="laudo-box">{{ $atendimento->laudo_tecnico }}</div>
        </div>
        @endif

        @if($atendimento->saidasEstoque && $atendimento->saidasEstoque->isNotEmpty())
        <div class="section">
            <h2>Peças Utilizadas</h2>
            <table>
                <thead>
                    <tr>
                        <th>Cód.</th>
                        <th>Peça</th>
                        <th>Marca</th>
                        <th class="text-center">Qtd.</th>
                        <th class="text-right">Preço Unit. (R$)</th>
                        <th class="text-right">Subtotal (R$)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($atendimento->saidasEstoque as $saida)
                        @if($saida->estoque)
                        <tr>
                            <td>{{ $saida->estoque->id }}</td>
                            <td>{{ $saida->estoque->nome }} {{ $saida->estoque->modelo_compativel ? '('.$saida->estoque->modelo_compativel.')' : '' }}</td>
                            <td>{{ $saida->estoque->marca ?? 'N/A' }}</td>
                            <td class="text-center">{{ $saida->quantidade }}</td>
                            <td class="text-right">{{ number_format($saida->estoque->preco_venda ?? 0, 2, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($saida->quantidade * ($saida->estoque->preco_venda ?? 0), 2, ',', '.') }}</td>
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
                        <td class="label">Valor Mão de Obra/Serviços:</td>
                        <td class="text-right">R$ {{ number_format($atendimento->valor_servico ?? 0, 2, ',', '.') }}</td>
                    </tr>
                    @if(($atendimento->desconto_servico ?? 0) > 0)
                    <tr>
                        <td class="label">Desconto sobre Serviços:</td>
                        <td class="text-right text-danger">- R$ {{ number_format($atendimento->desconto_servico ?? 0, 2, ',', '.') }}</td>
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
                    <tr>
                        <td class="label" style="font-size: 1.2em;">VALOR TOTAL:</td>
                        <td class="text-right" style="font-size: 1.2em; font-weight: bold;">R$ {{ number_format($valorTotalAtendimento, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

         @if($atendimento->observacoes)
        <div class="section">
            <h2>Observações Internas / Adicionais</h2>
            <p style="white-space: pre-wrap;">{{ $atendimento->observacoes }}</p>
        </div>
        @endif

        <div class="signature-section">
            <p class="text-center">Declaro estar ciente e de acordo com os serviços e valores descritos.</p>
            <div class="signature-line"></div>
            <p class="text-center">Assinatura do Cliente</p>
        </div>

        <div class="footer">
            <p>Data da Impressão: {{ $dataImpressao->format('d/m/Y H:i:s') }} | Código de Consulta: {{ $atendimento->codigo_consulta }}</p>
        </div>

         <div class="disclaimer">
            TERMOS E CONDIÇÕES (Exemplo): A garantia das peças é de 90 dias, cobrindo defeitos de fabricação. Danos por mau uso, contato com líquidos ou quedas não são cobertos pela garantia. Aparelhos não retirados em até 90 dias após a conclusão do serviço poderão ser descartados para cobrir custos.
        </div>
    </div>
</body>
</html>