@extends('layouts.app')

@section('title', 'Abrir Novo Caixa')

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
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Abrir Novo Caixa</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('caixa.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="saldo_inicial" class="form-label">Saldo Inicial (Troco)</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control @error('saldo_inicial') is-invalid @enderror"
                                       id="saldo_inicial" name="saldo_inicial"
                                       value="{{ old('saldo_inicial', '0.00') }}"
                                       step="0.01" min="0" required
                                       aria-label="Saldo inicial em reais">
                            </div>
                            @error('saldo_inicial')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="observacoes_abertura" class="form-label">Observações (Opcional)</label>
                            <textarea class="form-control @error('observacoes_abertura') is-invalid @enderror"
                                      id="observacoes_abertura" name="observacoes_abertura"
                                      rows="3">{{ old('observacoes_abertura') }}</textarea>
                            @error('observacoes_abertura')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary me-md-2">Cancelar</a>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Abrir Caixa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
    {{-- Se você usa Bootstrap Icons e ele não está global --}}
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"> --}}
@endpush