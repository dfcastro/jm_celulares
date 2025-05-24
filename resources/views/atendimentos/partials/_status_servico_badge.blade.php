{{-- resources/views/atendimentos/partials/_status_servico_badge.blade.php --}}
<span id="statusServicoTexto" class="badge rounded-pill fs-6 {{ App\Models\Atendimento::getStatusClass($status_servico ?? 'Em aberto') }}">
    <i class="bi {{ App\Models\Atendimento::getStatusIcon($status_servico ?? 'Em aberto') }} me-1"></i>
    <span id="statusServicoNome">{{ $status_servico ?? 'Em aberto' }}</span>
</span>