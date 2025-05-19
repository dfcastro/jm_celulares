<?php

namespace App\Notifications;

use App\Models\Orcamento;
use App\Models\Cliente;
use App\Models\OrcamentoItem;
use App\Models\Estoque;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OrcamentoParaClienteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Orcamento $orcamentoData; // Mantém os dados limpos do orçamento
    // REMOVER: public $pdfData;
    // REMOVER: public $pdfFileName;

    private function cleanString($string) { /* ... (função cleanString como antes) ... */
        if (is_null($string)) { return null; }
        $string = (string)$string;
        $cleanedString = @iconv('UTF-8', 'UTF-8//IGNORE', $string);
        if ($cleanedString === false) { $cleanedString = mb_convert_encoding($string, 'UTF-8', 'UTF-8'); }
        if (empty($cleanedString) && !empty($string)) { $cleanedString = $string; }
        $cleanedString = preg_replace('/[^\PC\s\p{L}\p{N}\p{P}\p{Z}]/u', '', $cleanedString ?? '');
        if (class_exists('Normalizer')) {
            $normalizedString = \Normalizer::normalize($cleanedString ?? '', \Normalizer::FORM_C);
            if ($normalizedString !== false) { $cleanedString = $normalizedString; }
        }
        return mb_convert_encoding($cleanedString, 'UTF-8', 'UTF-8');
    }

    public function __construct(Orcamento $orcamentoOriginal)
    {
        Log::info("[Notificação Construtor] Iniciando para Orçamento ID {$orcamentoOriginal->id}");
        // Limpeza de dados para $this->orcamentoData (como no seu último código)
        $this->orcamentoData = new Orcamento();
        $this->orcamentoData->id = $orcamentoOriginal->id;
        foreach ($orcamentoOriginal->getAttributes() as $key => $value) {
            if (in_array($key, ['created_at', 'updated_at', 'data_emissao', 'data_validade', 'data_aprovacao', 'data_reprovacao', 'data_cancelamento'])) {
                $this->orcamentoData->setAttribute($key, $value ? Carbon::parse($value) : null);
            } elseif (is_string($value)) {
                $this->orcamentoData->setAttribute($key, $this->cleanString($value));
            } else {
                $this->orcamentoData->setAttribute($key, $value);
            }
        }
        if ($orcamentoOriginal->relationLoaded('cliente') && $orcamentoOriginal->cliente) {
            $clienteOriginal = $orcamentoOriginal->cliente;
            $clienteLimpo = new Cliente(); $clienteLimpo->id = $clienteOriginal->id;
            foreach ($clienteOriginal->getAttributes() as $key => $value) {
                if (is_string($value)) { $clienteLimpo->setAttribute($key, $this->cleanString($value)); }
                else { $clienteLimpo->setAttribute($key, $value); }
            }
            $this->orcamentoData->setRelation('cliente', $clienteLimpo);
        }
        $itensLimpados = new \Illuminate\Database\Eloquent\Collection();
        if ($orcamentoOriginal->relationLoaded('itens')) {
            foreach ($orcamentoOriginal->itens as $itemOriginal) {
                $itemLimpo = new OrcamentoItem(); $itemLimpo->id = $itemOriginal->id;
                foreach ($itemOriginal->getAttributes() as $key => $value) {
                    if (is_string($value)) { $itemLimpo->setAttribute($key, $this->cleanString($value)); }
                    else { $itemLimpo->setAttribute($key, $value); }
                }
                if ($itemOriginal->relationLoaded('estoque') && $itemOriginal->estoque) {
                    $estoqueOriginal = $itemOriginal->estoque;
                    $estoqueLimpo = new Estoque(); $estoqueLimpo->id = $estoqueOriginal->id;
                    foreach ($estoqueOriginal->getAttributes() as $key => $value) {
                        if (is_string($value)) { $estoqueLimpo->setAttribute($key, $this->cleanString($value)); }
                        else { $estoqueLimpo->setAttribute($key, $value); }
                    }
                    $itemLimpo->setRelation('estoque', $estoqueLimpo);
                }
                $itensLimpados->add($itemLimpo);
            }
        }
        $this->orcamentoData->setRelation('itens', $itensLimpados);
        if ($orcamentoOriginal->relationLoaded('criadoPor') && $orcamentoOriginal->criadoPor) {
            $criadoPorOriginal = $orcamentoOriginal->criadoPor;
            $criadoPorLimpo = new User(); $criadoPorLimpo->id = $criadoPorOriginal->id;
            $criadoPorLimpo->name = $this->cleanString($criadoPorOriginal->name);
            $this->orcamentoData->setRelation('criadoPor', $criadoPorLimpo);
        }
        Log::info("[Notificação Construtor] Fim para Orçamento ID {$this->orcamentoData->id}. Dados limpos e armazenados em \$this->orcamentoData.");
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        Log::info("[Notificação ToMail] Iniciando para Orçamento ID {$this->orcamentoData->id}");
        // Gerar o PDF AQUI, dentro do método toMail
        $pdfData = null;
        $pdfFileName = 'orcamento.pdf'; // Nome padrão

        try {
            // O $this->orcamentoData já deve ter as relações carregadas e limpas pelo construtor
            // Se não, carregue-as aqui: $this->orcamentoData->loadMissing(['cliente', 'criadoPor', 'itens.estoque']);
            $dadosParaPdf = [
                'orcamento' => $this->orcamentoData,
                'dataImpressao' => Carbon::now(),
                'nomeEmpresa' => $this->cleanString(config('app.name', 'JM Celulares')),
                'enderecoEmpresa' => $this->cleanString('Alameda Capitão José Custódio, 130, Centro - Monte Azul - MG'),
                'telefoneEmpresa' => $this->cleanString('(38) 99269-6404'),
                'emailEmpresa' => $this->cleanString('contato@jmcelulares.com.br'),
            ];
            $pdf = Pdf::loadView('orcamentos.pdf_template', $dadosParaPdf);
            $pdfData = $pdf->output();

            $nomeClientePdf = $this->orcamentoData->cliente->nome_completo ?? $this->orcamentoData->nome_cliente_avulso ?? 'orcamento';
            $pdfFileName = 'Orcamento_' . $this->orcamentoData->id . '_' . Str::slug($this->cleanString($nomeClientePdf), '_') . '.pdf';
            Log::info("[Notificação ToMail] PDF gerado para Orçamento ID {$this->orcamentoData->id}");
        } catch (\Exception $e) {
            Log::error("[Notificação ToMail] ERRO ao gerar PDF para Orçamento ID {$this->orcamentoData->id}: " . $e->getMessage());
            // O e-mail será enviado sem o anexo
        }

        $nomeCliente = $this->orcamentoData->cliente->nome_completo ?? $this->orcamentoData->nome_cliente_avulso ?? 'Prezado(a) Cliente';
        $numeroOrcamento = $this->orcamentoData->id;
        $nomeEmpresa = $this->cleanString(config('app.name', 'JM Celulares'));
        $descricaoAparelhoMail = $this->orcamentoData->descricao_aparelho;

        $mailMessage = (new MailMessage)
                    ->subject("{$nomeEmpresa} - Orçamento #{$numeroOrcamento}")
                    ->greeting("Olá, {$nomeCliente}!")
                    ->line("Segue em anexo o orçamento solicitado para o aparelho: **{$descricaoAparelhoMail}**.")
                    ->line("Número do Orçamento: **#{$numeroOrcamento}**");

        if ($this->orcamentoData->data_validade) {
            $dataValidade = $this->orcamentoData->data_validade;
            $dataValidadeFormatada = ($dataValidade instanceof Carbon) ? $dataValidade->format('d/m/Y') : Carbon::parse((string)$dataValidade)->format('d/m/Y');
            $mailMessage->line("Este orçamento é válido até: **" . $dataValidadeFormatada . "**.");
        } elseif ($this->orcamentoData->validade_dias) {
            $mailMessage->line("Este orçamento é válido por **{$this->orcamentoData->validade_dias} dias** a partir da data de emissão.");
        }

        $mailMessage->line("Para aprovar ou em caso de dúvidas, por favor, entre em contato conosco respondendo a este e-mail ou através de nossos canais de atendimento.")
                    ->line("Telefone: " . $this->cleanString(config('app.company_phone', '(38) 99269-6404')))
                    ->salutation("Atenciosamente,\nEquipe {$nomeEmpresa}");

        if ($pdfData) { // Anexa apenas se o PDF foi gerado com sucesso
            $mailMessage->attachData($pdfData, $pdfFileName, [
                'mime' => 'application/pdf',
            ]);
        } else {
            $mailMessage->line("\nNota: Houve um problema ao gerar o anexo PDF deste orçamento. Por favor, entre em contato para obter uma cópia ou mais detalhes.");
            Log::warning("[Notificação ToMail] E-mail para orçamento #{$numeroOrcamento} enviado SEM anexo PDF devido a erro na geração.");
        }
        Log::info("[Notificação ToMail] Mensagem de e-mail montada para Orçamento ID {$this->orcamentoData->id}");
        return $mailMessage;
    }

    public function toArray(object $notifiable): array
    {
        // ... (método toArray como estava, usando $this->orcamentoData e cleanString)
        $emailCliente = $this->orcamentoData->cliente->email ?? $this->orcamentoData->email_cliente_avulso ?? null;
        $emailDestinatario = $this->cleanString($emailCliente ?? 'E-mail não disponível');
        $descricaoAparelhoArray = $this->orcamentoData->descricao_aparelho;

        return [
            'orcamento_id' => $this->orcamentoData->id,
            'assunto' => "Orçamento #{$this->orcamentoData->id} enviado para o cliente.",
            'mensagem' => "O orçamento para o aparelho {$descricaoAparelhoArray} foi enviado por e-mail para {$emailDestinatario}.",
            'link_orcamento' => route('orcamentos.show', $this->orcamentoData->id),
        ];
    }
}