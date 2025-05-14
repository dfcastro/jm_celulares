@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Histórico Unificado de Movimentações de Estoque') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}

        <h1>Histórico Unificado de Movimentações de Estoque</h1>

        <a href="{{ route('estoque.index') }}" class="btn btn-secondary mb-3">Voltar para Lista de Peças</a>

        @if($movimentacoesPaginadas->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Peça</th>
                        <th>Quantidade</th>
                        <th>Data/Hora</th>
                        <th>Observações</th>
                        <th>Relacionado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimentacoesPaginadas as $movimento)
                        <tr>
                            <td>
                                @if($movimento['tipo'] == 'Entrada')
                                    <span class="badge bg-success">Entrada</span>
                                @else
                                    <span class="badge bg-danger">Saída</span>
                                @endif
                            </td>
                            <td>{{ $movimento['peca'] }}</td>
                            <td>{{ $movimento['quantidade'] }}</td>
                            <td>{{ $movimento['data']->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $movimento['observacoes'] ?? '-' }}</td>
                            <td>{{ $movimento['relacionado'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Adiciona os links de paginação --}}
            <div class="d-flex justify-content-center mt-4">
                {{ $movimentacoesPaginadas->links() }}
            </div>

        @else
            <div class="alert alert-info">Nenhuma movimentação de estoque registrada.</div>
        @endif

    </div>
    
    @endsection

{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush