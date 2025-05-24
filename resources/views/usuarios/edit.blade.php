{{-- resources/views/usuarios/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Editar Usuário: ' . $usuario->name)

@section('content')
    <div class="container mt-0">
        <h1>Editar Usuário: <span class="text-primary">{{ $usuario->name }}</span></h1>
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
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
            @csrf
            @method('PUT') {{-- Método HTTP para atualização --}}
            <div class="card">
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $usuario->name) }}" required>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $usuario->email) }}" required>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <hr>
                    <p class="text-muted small">Deixe os campos de senha em branco para não alterar a senha atual.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Nova Senha</label>
                            <input type="password" class="form-control" id="password" name="password">
                            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Nova Senha</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                    <hr>

                    <div class="mb-3">
                        <label for="tipo_usuario" class="form-label">Tipo de Usuário <span class="text-danger">*</span></label>
                        <select class="form-select" id="tipo_usuario" name="tipo_usuario" required {{ Auth::user()->id === $usuario->id && $usuario->tipo_usuario === 'admin' && App\Models\User::where('tipo_usuario', 'admin')->count() <= 1 ? 'disabled' : '' }}>
                            <option value="">Selecione um tipo</option>
                            @foreach ($tiposUsuario as $key => $value)
                                <option value="{{ $key }}" {{ old('tipo_usuario', $usuario->tipo_usuario) == $key ? 'selected' : '' }}>
                                    {{ $value }}
                                </option>
                            @endforeach
                        </select>
                        @if(Auth::user()->id === $usuario->id && $usuario->tipo_usuario === 'admin' && App\Models\User::where('tipo_usuario', 'admin')->count() <= 1)
                            <small class="form-text text-warning">Você não pode alterar seu próprio tipo se for o único administrador.</small>
                            <input type="hidden" name="tipo_usuario" value="{{ $usuario->tipo_usuario }}"> {{-- Envia o tipo atual se desabilitado --}}
                        @endif
                        @error('tipo_usuario') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Salvar Alterações</button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush