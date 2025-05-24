@extends('layouts.app')

@section('title', 'Fechar Caixa #' . $caixa->id)

@push('styles')
<style>
    .saldo-display {
        font-size: 1.2rem;
        font-weight: 500;
    }
    .saldo-calculado {
        background-color: #e9ecef; /* Um cinza claro para destacar que é calculado */
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
    }
</style>
@endpush

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
<div class="container mt-0"> {{-- Removido mt-4 para alinhar com outras views --}}
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-danger text-white"> {{-- Cor de perigo para indicar uma ação final --}}
                    <h4 class="mb-0"><i class="bi bi-lock-fill me-2"></i>Confirmar Fechamento do Caixa #{{ $caixa->id }}</h4>
                </div>
                <div class="card-body p-4">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-secondary small mb-4">
                        <p class="mb-1"><strong>Caixa Aberto em:</strong> {{ $caixa->data_abertura_formatada }} por {{ $caixa->usuarioAbertura->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Saldo Inicial Registrado:</strong> R$ {{ number_format($caixa->saldo_inicial, 2, ',', '.') }}</p>
                        <hr class="my-2">
                        <p class="mb-0"><strong>Saldo Final Calculado pelo Sistema:</strong></p>
                        <div class="saldo-calculado text-center py-2">
                            <strong class="saldo-display text-primary">R$ {{ number_format($saldoFinalCalculado, 2, ',', '.') }}</strong>
                        </div>
                        <small class="form-text text-muted">Este valor é a soma do saldo inicial com todas as entradas, menos todas as saídas registradas neste caixa.</small>
                    </div>

                    <form action="{{ route('caixa.fechar', $caixa->id) }}" method="POST" id="formFecharCaixa">
                        @csrf
                        {{-- O método HTTP será POST, o Laravel faz o resto --}}

                        <div class="mb-3">
                            <label for="saldo_final_informado" class="form-label">Saldo Final Contado (Dinheiro e Outros Meios) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control form-control-lg @error('saldo_final_informado') is-invalid @enderror"
                                       id="saldo_final_informado" name="saldo_final_informado"
                                       value="{{ old('saldo_final_informado') }}"
                                       step="0.01" min="0" required
                                       aria-label="Saldo final contado em reais"
                                       placeholder="0.00">
                            </div>
                            @error('saldo_final_informado')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Informe o valor total que você contou fisicamente no caixa (dinheiro, valores de cartão, PIX, etc., que entraram neste caixa).</small>
                        </div>

                        <div class="mb-3">
                            <label for="observacoes_fechamento" class="form-label">Observações de Fechamento (Opcional)</label>
                            <textarea class="form-control @error('observacoes_fechamento') is-invalid @enderror"
                                      id="observacoes_fechamento" name="observacoes_fechamento"
                                      rows="3" placeholder="Alguma observação sobre o fechamento, diferenças, etc.">{{ old('observacoes_fechamento') }}</textarea>
                            @error('observacoes_fechamento')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning small mt-4">
                            <strong>Atenção:</strong> Ao confirmar o fechamento, este caixa não poderá mais receber novas movimentações. Certifique-se de que todos os valores e informações estão corretos.
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="{{ route('caixa.show', $caixa->id) }}" class="btn btn-secondary me-md-2">
                                <i class="bi bi-x-circle me-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-danger" id="btnConfirmarFechamento">
                                <i class="bi bi-check-circle-fill me-2"></i>Confirmar Fechamento do Caixa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Opcional: Adicionar um listener para desabilitar o botão de submissão após o primeiro clique
    // para evitar envios duplicados, caso o processamento demore.
    const formFecharCaixa = document.getElementById('formFecharCaixa');
    const btnConfirmarFechamento = document.getElementById('btnConfirmarFechamento');

    if (formFecharCaixa && btnConfirmarFechamento) {
        formFecharCaixa.addEventListener('submit', function() {
            btnConfirmarFechamento.disabled = true;
            btnConfirmarFechamento.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Fechando...';
        });
    }
</script>
@endpush