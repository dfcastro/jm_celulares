{{-- resources/views/saidas_estoque/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detalhes da Saída de Estoque #' . $saidaEstoque->id)

@section('content')

    <div class="container mt-0">
        <h1>Detalhes da Saída de Estoque</h1>
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
        <div class="card">
            <div class="card-header">
                <h5 class="my-0">Saída #{{ $saidaEstoque->id }}</h5>
            </div>
            <div class="card-body">
                <p class="card-text">
                    <strong>Peça:</strong>
                    @if ($saidaEstoque->estoque) {{-- <<<<<<<<<<<<<< VERIFICAÇÃO --}}
                        <a href="{{ route('estoque.show', $saidaEstoque->estoque->id) }}">
                            {{ $saidaEstoque->estoque->nome }}
                        </a>
                        ({{ $saidaEstoque->estoque->modelo_compativel ?? 'Modelo não especificado' }})
                        <small class="d-block text-muted">Marca: {{ $saidaEstoque->estoque->marca ?? 'N/A' }} | Tipo: {{ $saidaEstoque->estoque->tipo ?? 'N/A' }}</small>
                    @else
                        <span class="text-danger">Peça não encontrada ou removida do estoque.</span>
                    @endif
                </p>
                <p class="card-text">
                    <strong>Atendimento Vinculado:</strong>
                    @if ($saidaEstoque->atendimento) {{-- <<<<<<<<<<<<<< VERIFICAÇÃO --}}
                        <a href="{{ route('atendimentos.show', $saidaEstoque->atendimento->id) }}">
                            #{{ $saidaEstoque->atendimento->id }} -
                            @if($saidaEstoque->atendimento->cliente)
                                {{ $saidaEstoque->atendimento->cliente->nome_completo }}
                            @else
                                Cliente não informado
                            @endif
                            ({{ $saidaEstoque->atendimento->celular ?? 'Celular não informado' }})
                        </a>
                        <small class="d-block text-muted">Status Atend.: {{ $saidaEstoque->atendimento->status ?? 'N/A' }}</small>
                    @else
                        <span class="text-muted fst-italic">Não Vinculado a um Atendimento</span>
                    @endif
                </p>
                <p class="card-text"><strong>Quantidade Retirada:</strong> {{ $saidaEstoque->quantidade }}</p>
                <p class="card-text"><strong>Data de Saída:</strong> {{ $saidaEstoque->data_saida ? $saidaEstoque->data_saida->format('d/m/Y H:i:s') : 'N/A' }}</p>
                <p class="card-text"><strong>Observações da Saída:</strong></p>
                <div class="p-2 border rounded bg-light mb-3" style="white-space: pre-wrap;">{{ $saidaEstoque->observacoes ?? 'Nenhuma observação.' }}</div>

                <p class="card-text"><small class="text-muted">Registrado em: {{ $saidaEstoque->created_at ? $saidaEstoque->created_at->format('d/m/Y H:i:s') : 'N/A' }}</small></p>
                <p class="card-text"><small class="text-muted">Última atualização: {{ $saidaEstoque->updated_at ? $saidaEstoque->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</small></p>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('saidas-estoque.index') }}" class="btn btn-primary"><i class="bi bi-list-ul"></i> Voltar para Saídas</a>
                {{-- A edição de SaidaEstoque geralmente não é permitida para manter a integridade do histórico.
                     Se precisar corrigir, geralmente se exclui e refaz, ou se cria uma "entrada de ajuste".
                <a href="{{-- route('saidas-estoque.edit', $saidaEstoque->id) --}}            
                {{--  <form action="{{ route('saidas-estoque.destroy', ['saida_estoque' => $saidaEstoque->id]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta saída? A quantidade será retornada ao estoque.')">
                        <i class="bi bi-trash"></i> Excluir Saída (Estornar Estoque)
                    </button>
                </form>--}}  
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush