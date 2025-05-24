@extends('layouts.public') {{-- USA O NOVO LAYOUT PÚBLICO --}}

@section('title', 'Consultar Status do Atendimento - JM Celulares')

@section('content-public') {{-- CONTEÚDO DA PÁGINA --}}
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header text-white" style="background-color: var(--jm-laranja);"> {{-- Usando a cor do
                    cartão --}}
                    <h4 class="my-0 text-center"><i class="bi bi-search me-2"></i>Consultar Status do Reparo</h4>
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
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
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

                    <p class="text-muted text-center mb-4">
                        Digite o código de consulta (Ex: 12345-2025) fornecido no momento da abertura do seu atendimento
                        para verificar o status atual do seu aparelho.
                    </p>

                    <form action="{{ route('consulta.status') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="codigo_consulta" class="form-label fw-bold">Código de Consulta:</label>
                            <input type="text"
                                class="form-control form-control-lg @error('codigo_consulta') is-invalid @enderror"
                                id="codigo_consulta" name="codigo_consulta" value="{{ old('codigo_consulta') }}"
                                placeholder="Ex: XXXXX-{{date('Y')}}" required maxlength="15"> {{-- Ajustei maxlength se o
                            código for maior --}}
                            @error('codigo_consulta')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"><i
                                    class="bi bi-binoculars-fill me-2"></i>Consultar Agora</button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                        <a href="{{ route('site.home') }}" class="btn btn-outline-secondary"><i
                                class="bi bi-arrow-left-circle"></i> Voltar para a Página Inicial</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts-public')
    <script>
        // Se precisar de alguma máscara ou script específico para esta página, coloque aqui.
        // Exemplo: máscara para o código de consulta, se ele tiver um formato fixo.
        $(document).ready(function () {
            $('#codigo_consulta').mask('00000-0000');
        });
    </script>
@endpush