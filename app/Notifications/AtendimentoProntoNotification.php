<?php

namespace App\Notifications;

use App\Models\Atendimento;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str; // <<<<<<<<<<<<<< ADICIONE/VERIFIQUE ESTA LINHA

class AtendimentoProntoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Atendimento $atendimento;

    public function __construct(Atendimento $atendimento)
    {
        $this->atendimento = $atendimento;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $codigoConsulta = $this->atendimento->codigo_consulta;
        $urlConsulta = route('consulta.index');
        $nomeLoja = config('app.name', 'JM Celulares');
        $numeroAtendimento = $this->atendimento->id;
        $nomeCliente = $this->atendimento->cliente ? $this->atendimento->cliente->nome_completo : 'Prezado(a) Cliente';
        $descricaoAparelho = $this->atendimento->descricao_aparelho;
        
        // Usando Str::limit() corretamente agora
        $problemaRelatado = Str::limit($this->atendimento->problema_relatado, 150); 
        $laudoTecnico = $this->atendimento->laudo_tecnico ? Str::limit($this->atendimento->laudo_tecnico, 200) : null;

        $mailMessage = (new MailMessage)
                    ->subject("{$nomeLoja} - Seu Atendimento #{$numeroAtendimento} está Pronto!")
                    ->greeting("Olá, {$nomeCliente}!")
                    ->line("Boas notícias! O serviço para o seu aparelho ({$descricaoAparelho}), referente ao atendimento de código **#{$numeroAtendimento}**, foi concluído e está pronto para retirada em nossa loja.")
                    ->line("Problema que você relatou: \"{$problemaRelatado}\"");

        if ($laudoTecnico) {
            $mailMessage->line(new HtmlString("<strong>Solução/Observações do Técnico:</strong><br>" . nl2br(e($laudoTecnico))));
        }

        $mailMessage->line("Você pode obter mais detalhes ou confirmar o status do seu atendimento usando o código de consulta: **{$codigoConsulta}** em nosso site.")
                    ->action('Consultar Status Online', $urlConsulta)
                    ->line("Agradecemos a sua preferência e aguardamos sua visita para retirada!");

        $enderecoLoja = "Alameda Capião José Custódio, 130 - Centro - Monte Azul - MG"; // Substitua
        $telefoneLoja = "(38) 99269-6404"; // Substitua
        $horarioLoja = "Seg-Sex: 9h às 18h | Sáb: 9h às 13h"; // Substitua

        $mailMessage->salutation(new HtmlString(
            "Atenciosamente,<br>" .
            "Equipe {$nomeLoja}<br>" .
            "<small style='color:#777;'>{$enderecoLoja}<br>" .
            "Telefone: {$telefoneLoja}<br>" .
            "Horário: {$horarioLoja}</small>"
        ));

        return $mailMessage;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'atendimento_id' => $this->atendimento->id,
            'cliente_id' => $this->atendimento->cliente_id,
            'mensagem' => "Seu atendimento #{$this->atendimento->id} está pronto para retirada.",
            'codigo_consulta' => $this->atendimento->codigo_consulta,
            'url_consulta' => route('consulta.index'),
        ];
    }
}