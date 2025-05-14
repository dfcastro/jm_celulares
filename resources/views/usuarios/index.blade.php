{{-- resources/views/usuarios/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Gerenciar Usuários')

@section('content')
    <div class="container mt-0">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Gerenciar Usuários do Sistema</h1>
            <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
                <i class="bi bi-person-plus"></i> Novo Usuário
            </a>
        </div>

        @if(session('error'))
             <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id }}</td>
                            <td>{{ $usuario->name }}</td>
                            <td>{{ $usuario->email }}</td>
                            <td>
                                @if($usuario->tipo_usuario == 'admin')
                                    <span class="badge bg-danger">Administrador</span>
                                @elseif($usuario->tipo_usuario == 'tecnico')
                                    <span class="badge bg-info text-dark">Técnico</span>
                                @elseif($usuario->tipo_usuario == 'atendente')
                                    <span class="badge bg-success">Atendente</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($usuario->tipo_usuario) }}</span>
                                @endif
                            </td>
                            {{-- CORREÇÃO APLICADA AQUI --}}
                            <td>
                                {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y H:i') : 'Data não disponível' }}
                            </td>
                            <td>
                                {{-- ... botões de ação ... --}}
                                <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-warning btn-sm" title="Editar"><i class="bi bi-pencil"></i></a>
                                @if(Auth::user()->id !== $usuario->id)
                                    <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Excluir"><i class="bi bi-trash"></i></button>
                                    </form>
                                @else
                                    <button class="btn btn-danger btn-sm" disabled title="Não pode excluir a si mesmo"><i class="bi bi-trash"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Nenhum usuário cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($usuarios->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush