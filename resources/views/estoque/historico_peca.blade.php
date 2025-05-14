@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Histórico de peças') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}
        <h1>Histórico de Movimentação para: {{ $estoque->nome }}</h1>
        <p>Modelo Compatível: {{ $estoque->modelo_compativel ?? 'Não especificado' }} | Quantidade Atual:
            {{ $estoque->quantidade }}</p>

        <a href="{{ route('estoque.index') }}" class="btn btn-secondary mb-3">Voltar para Lista de Peças</a>
        {{-- Link para histórico unificado (se quiser adicionar) --}}
        {{-- <a href="{{ route('estoque.historico_unificado') }}" class="btn btn-secondary mb-3">Ver Histórico Geral</a>
        --}}


        @if($movimentacoes->count() > 0)
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Quantidade</th>
                        <th>Data/Hora</th>
                        <th>Observações</th>
                        <th>Relacionado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movimentacoes as $movimento)
                        <tr>
                            <td>
                                @if($movimento['tipo'] == 'Entrada')
                                    <span class="badge bg-success">Entrada</span>
                                @else
                                    <span class="badge bg-danger">Saída</span>
                                @endif
                            </td>
                            <td>{{ $movimento['quantidade'] }}</td>
                            {{-- Garante a formatação da data, acessando a propriedade data diretamente --}}
                            <td>{{ $movimento['data']->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $movimento['observacoes'] ?? '-' }}</td>
                            <td>{{ $movimento['relacionado'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{-- Nota: Esta listagem não tem paginação. Para muitos itens, considere paginar. --}}
        @else
            <div class="alert alert-info">Nenhuma movimentação registrada para esta peça.</div>
        @endif

    </div>
    
@endsection

{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush