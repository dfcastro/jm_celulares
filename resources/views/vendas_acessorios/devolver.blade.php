@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Devolver Venda') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}
        <h1>Devolver Venda #{{ $vendas_acessorio->id }}</h1>
        <p>Cliente: {{ $vendas_acessorio->cliente->nome_completo ?? 'Venda Balcão' }}</p>
        <p>Data da Venda: {{ $vendas_acessorio->data_venda->format('d/m/Y') }}</p>
        <p>Valor Total da Venda: {{ 'R$ ' . number_format($vendas_acessorio->valor_total, 2, ',', '.') }}</p>

        {{-- Mensagens de Sucesso/Erro --}}
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Bloco para exibir erros de validação --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('vendas-acessorios.devolver.processar', $vendas_acessorio->id) }}" method="POST">
            @csrf

            <div class="card mb-4">
                <div class="card-header">Itens Vendidos (para devolução)</div>
                <div class="card-body">
                    @forelse ($vendas_acessorio->itensVendidos as $itemVendido)
                        <div class="row mb-3 align-items-center border-bottom pb-2">
                            <div class="col-md-5">
                                <strong>Peça:</strong> {{ $itemVendido->nome }} ({{ $itemVendido->modelo_compativel ?? 'N/A' }})
                                <input type="hidden" name="itens_devolver[{{ $loop->index }}][estoque_id]" value="{{ $itemVendido->id }}">
                            </div>
                            <div class="col-md-3">
                                <p class="mb-0">Qtd Vendida: {{ $itemVendido->pivot->quantidade }}</p>
                                <p class="mb-0">Preço Unitário: {{ 'R$ ' . number_format($itemVendido->pivot->preco_unitario_venda, 2, ',', '.') }}</p>
                                <p class="mb-0">Desconto: {{ 'R$ ' . number_format($itemVendido->pivot->desconto, 2, ',', '.') }}</p>
                            </div>
                            <div class="col-md-2">
                                <label for="quantidade_devolver_{{ $loop->index }}" class="form-label">Qtd a Devolver</label>
                                <input type="number" class="form-control" id="quantidade_devolver_{{ $loop->index }}"
                                    name="itens_devolver[{{ $loop->index }}][quantidade_devolver]"
                                    value="{{ old('itens_devolver.' . $loop->index . '.quantidade_devolver', 0) }}"
                                    min="0" max="{{ $itemVendido->pivot->quantidade }}"
                                    data-item-id="{{ $itemVendido->id }}">
                                @error('itens_devolver.' . $loop->index . '.quantidade_devolver')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-2">
                                {{-- Aqui você poderia adicionar um checkbox para "Selecionar para Devolver Tudo" --}}
                            </div>
                        </div>
                    @empty
                        <p>Nenhum item encontrado para esta venda.</p>
                    @endforelse
                </div>
            </div>

            <div class="mb-3">
                <label for="observacoes_devolucao" class="form-label">Observações da Devolução (Opcional)</label>
                <textarea class="form-control" id="observacoes_devolucao" name="observacoes_devolucao" rows="3">{{ old('observacoes_devolucao') }}</textarea>
                @error('observacoes_devolucao')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary me-2">Registrar Devolução</button>
            <a href="{{ route('vendas-acessorios.show', $vendas_acessorio->id) }}" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>
  
   
    {{-- Se precisar de autocomplete, adicione jQuery UI JS aqui, mas para este formulário não é necessário --}}
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> --}}

   


@endsection
@push('scripts')
<script>
        $(document).ready(function() {
            // Opcional: Adicionar lógica JavaScript para calcular valor total de devolução
            // ou para "selecionar tudo" se necessário.
        });
    </script>
    @endpush
{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush