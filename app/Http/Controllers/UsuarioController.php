<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Para Rule::in e unique()->ignore()
use Auth;
use Illuminate\Validation\Rules; // Para as regras de senha do Laravel
use Illuminate\Validation\Rules\Password; // Para as regras de senha do Laravel



class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Por enquanto, não vamos restringir quem pode ver a lista,
        // mas o ideal é que apenas admins vejam. Faremos isso com middleware depois.
        $usuarios = User::orderBy('name')->paginate(10);
        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Tipos de usuário para o select no formulário
        $tiposUsuario = ['admin' => 'Administrador', 'tecnico' => 'Técnico', 'atendente' => 'Atendente'];
        return view('usuarios.create', compact('tiposUsuario'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class], // Garante email único na tabela users
            'password' => ['required', 'confirmed', Rules\Password::defaults()], // Usa as regras de senha padrão do Laravel
            'tipo_usuario' => ['required', 'string', Rule::in(['admin', 'tecnico', 'atendente'])],
        ], [
            'tipo_usuario.in' => 'O tipo de usuário selecionado é inválido.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Importante: Hashear a senha
            'tipo_usuario' => $request->tipo_usuario,
        ]);

       return redirect()->route('usuarios.index')  ->with('success', "Usuário '{$user->name}' criado com sucesso!");
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $usuario) // Route Model Binding: $usuario será a instância do User a ser editada
    {
        // Tipos de usuário para o select no formulário
        $tiposUsuario = ['admin' => 'Administrador', 'tecnico' => 'Técnico', 'atendente' => 'Atendente'];
        return view('usuarios.edit', compact('usuario', 'tiposUsuario'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($usuario->id)], // Ignora o email atual do usuário na verificação de unicidade
            'tipo_usuario' => ['required', 'string', Rule::in(['admin', 'tecnico', 'atendente'])],
            'password' => ['nullable', 'confirmed', Password::defaults()], // Senha é opcional na atualização; se preenchida, deve ser confirmada
        ], [
            'tipo_usuario.in' => 'O tipo de usuário selecionado é inválido.'
        ]);

        // Prepara os dados para atualização
        $dadosAtualizar = [
            'name' => $request->name,
            'email' => $request->email,
            'tipo_usuario' => $request->tipo_usuario,
        ];

        // Se uma nova senha foi fornecida, hasheia e adiciona aos dados para atualizar
        if ($request->filled('password')) {
            $dadosAtualizar['password'] = Hash::make($request->password);
        }

        $usuario->update($dadosAtualizar);
        return redirect()->route('usuarios.index')
                         ->with('success', "Usuário '{$usuario->name}' atualizado com sucesso!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $usuario) // Route Model Binding
    {
        // Impedir que o usuário se auto-exclua
        if (Auth::id() === $usuario->id) {
            return redirect()->route('usuarios.index')->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        // Impedir a exclusão do último administrador (opcional, mas recomendado)
        if ($usuario->tipo_usuario === 'admin') {
            // Conta quantos outros administradores existem
            $adminCount = User::where('tipo_usuario', 'admin')->where('id', '!=', $usuario->id)->count();
            if ($adminCount === 0) {
                return redirect()->route('usuarios.index')->with('error', 'Não é possível excluir o último administrador do sistema.');
            }
        }

        // Opcional: Reatribuir atendimentos antes de excluir um técnico? Ou definir tecnico_id como null?
        // Se um técnico for excluído, o que acontece com os atendimentos atribuídos a ele?
        // Por padrão, se houver uma chave estrangeira em 'atendimentos' para 'users' (tecnico_id)
        // e não houver 'onDelete('set null')' ou 'onDelete('cascade')', o banco impedirá a exclusão.
        // Verifique sua migration de atendimentos para 'tecnico_id'. Se for constrained():
        // $table->foreignId('tecnico_id')->nullable()->constrained('users');
        // Se for nullable, o Laravel/banco podem permitir a deleção do usuário e setar tecnico_id para NULL nos atendimentos.
        // Se não for nullable e não tiver onDelete('cascade'), a exclusão do usuário falhará se ele tiver atendimentos.

        // Exemplo de como desvincular atendimentos (se necessário e se a FK permitir null):
        // if ($usuario->tipo_usuario === 'tecnico') {
        //     Atendimento::where('tecnico_id', $usuario->id)->update(['tecnico_id' => null]);
        // }

        $nomeUsuarioExcluido = $usuario->name;
        $usuario->delete();
        return redirect()->route('usuarios.index')
                         ->with('success', "Usuário '{$nomeUsuarioExcluido}' excluído com sucesso.");
    }
}
