@extends('layouts.app') {{-- Estende o layout base --}}

@section('title', 'Lista de Vendas de Acessórios') {{-- Título da página --}}

@section('content') {{-- Conteúdo principal da página --}}
    <div class="container mt-0"> {{-- Removi mt-5 pois o layout já tem padding --}}

        <h1>Lista de Vendas de Acessórios</h1>



        {{-- Botão para Nova Venda --}}
        <a href="{{ route('vendas-acessorios.create') }}" class="btn btn-primary mb-3 me-2">Registrar Nova Venda</a>

        {{-- Botão para voltar para o Estoque (Lista de Peças) --}}
        <a href="{{ route('estoque.index') }}" class="btn btn-secondary mb-3">Voltar para Estoque</a>


        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Cliente</th>
                    <th>Valor Total</th>
                    <th>Forma Pagamento</th>
                    <th>Observações</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                {{-- Loop sobre as vendas paginadas recebidas do controlador --}}
                @forelse ($vendas as $venda)
                    <tr>
                        <td>{{ $venda->id }}</td>
                        <td>{{ $venda->data_venda->format('d/m/Y') }}</td> {{-- Usamos format() porque data_venda é um Carbon
                        graças ao $casts no modelo --}}
                        <td>
                            @if($venda->cliente)
                                {{ $venda->cliente->nome_completo }}
                            @else
                                Venda Balcão
                            @endif
                        </td>
                        <td>{{ 'R$ ' . number_format($venda->valor_total, 2, ',', '.') }}</td> {{-- Formata o valor --}}
                        <td>{{ $venda->forma_pagamento ?? '-' }}</td>
                        <td>{{ $venda->observacoes ?? '-' }}</td>
                        <td>
                            {{-- Link para ver os detalhes da venda --}}
                            <a href="{{ route('vendas-acessorios.show', $venda->id) }}" class="btn btn-info btn-sm">Detalhes</a>
                            {{-- Formulário de exclusão (se decidir permitir) --}}
                            {{-- <form action="{{ route('vendas-acessorios.destroy', $venda->id) }}" method="POST"
                                class="d-inline"> --}}
                                {{-- @csrf --}}
                                {{-- @method('DELETE') --}}
                                {{-- <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Tem certeza que deseja excluir esta venda?')">Excluir</button> --}}
                                {{-- </form> --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Nenhuma venda de acessório registrada.</td>
                    </tr> {{-- ATUALIZAR colspan --}}
                @endforelse
            </tbody>
        </table>

        {{-- Links de paginação --}}
        {{ $vendas->links() }}

    </div>

   
    {{-- Inclua jQuery e jQuery UI JS apenas se for usar autocomplete na lista (improvável) --}}
    {{--
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    {{--
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script> --}}


@endsection

{{-- Para esta página, não precisamos de @push('styles') ou @push('scripts') geralmente --}}
{{-- Mas se você fosse adicionar ícones do Bootstrap, por exemplo: --}}
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush