<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Certifique-se de que Rule está importado
use Illuminate\Support\Facades\Validator; // NOVO: Importar o Facade Validator
use Illuminate\Support\Facades\Gate;


class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cliente::query();

        if ($request->has('busca') && !empty($request->input('busca'))) {
            $searchTerm = $request->input('busca');
            $query->where('nome_completo', 'like', '%' . $searchTerm . '%')
                ->orWhere('cpf_cnpj', 'like', '%' . $searchTerm . '%');
        }

        $clientes = $query->paginate(10);
        $clientes->appends($request->query());

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Preparar os dados e limpar máscaras
        $data = $request->all();

        if (isset($data['cpf_cnpj'])) {
            $data['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $data['cpf_cnpj']);
        }
        if (isset($data['cep'])) {
            $data['cep'] = preg_replace('/[^0-9]/', '', $data['cep']);
        }
        if (isset($data['telefone'])) {
            $data['telefone'] = preg_replace('/[^0-9]/', '', $data['telefone']);
        }

        // 2. Definir regras de validação
        $rules = [
            'nome_completo' => 'required|string|max:255|',
            'cpf_cnpj' => ['required', 'string', 'max:20', Rule::unique('clientes', 'cpf_cnpj')],
            'telefone' => 'nullable|string|max:15', // Ajustado para permitir mais caracteres após remover máscara
            'email' => 'nullable|email|max:255|unique:clientes,email',
            'cep' => 'nullable|string|max:8', // Apenas números
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20', // Aumentado um pouco
            'bairro' => 'nullable|string|max:100',
            'cidade' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:2',
            'complemento' => 'nullable|string|max:255',
        ];

        // 3. Definir mensagens de validação customizadas
        $messages = [
            'nome_completo.required' => 'O campo nome completo é obrigatório.',
            'cpf_cnpj.required' => 'O campo CPF/CNPJ é obrigatório.',
            'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado no sistema.',
            'email.unique' => 'Este endereço de e-mail já está cadastrado.',
            'email.email' => 'Por favor, insira um endereço de e-mail válido.',
            // Adicione outras mensagens customizadas se desejar
        ];

        // 4. Executar o validador
        $validator = Validator::make($data, $rules, $messages);

        // 5. Se a validação falhar
        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) { // Verifica se é uma requisição AJAX ou espera JSON
                return response()->json([
                    'success' => false,
                    'message' => 'Erro de validação.', // Mensagem genérica
                    'errors' => $validator->errors()
                ], 422); // HTTP status 422 Unprocessable Entity
            }
            // Para requisições normais, redireciona de volta com erros e input antigo
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // 6. Se a validação passar, criar o cliente
        // Garantir que apenas os campos fillable sejam passados para create
        // O modelo Cliente já deve ter os campos corretos em $fillable
        $cliente = Cliente::create($data);

        // 7. Responder de acordo com o tipo de requisição
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente cadastrado com sucesso!',
                'cliente' => [ // Envia apenas os dados necessários para o JavaScript do modal
                    'id' => $cliente->id,
                    'nome_completo' => $cliente->nome_completo,
                    'cpf_cnpj' => $cliente->cpf_cnpj, // Pode ser útil para o label
                    'telefone' => $cliente->telefone, // Envia o telefone limpo
                    'email' => $cliente->email,
                ]
            ], 201); // HTTP status 201 Created
        }

        // Para requisições normais, redireciona para a lista de clientes
        return redirect()->route('clientes.index')
        ->with('success', "Cliente '{$cliente->nome_completo}' (ID: {$cliente->id}) cadastrado com sucesso!");
    }
    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        // Limpar máscaras antes da validação de unicidade e atualização
        $data = $request->all();
        if (isset($data['cpf_cnpj'])) {
            $data['cpf_cnpj'] = preg_replace('/[^0-9]/', '', $data['cpf_cnpj']);
        }
        if (isset($data['telefone'])) {
            $data['telefone'] = preg_replace('/[^0-9]/', '', $data['telefone']);
        }

        // Validação de unicidade para update
        $validator = Validator::make($data, [
            'cpf_cnpj' => [
                'required',
                'string',
                'max:20',
                Rule::unique('clientes', 'cpf_cnpj')->ignore($cliente->id), // Ignora o próprio cliente
            ],
            'email' => 'nullable|string|email|max:255|unique:clientes,email,' . $cliente->id,
        ], [
            'cpf_cnpj.unique' => 'O CPF/CNPJ informado já está cadastrado.',
            'email.unique' => 'O e-mail informado já está cadastrado.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Validação completa dos dados limpos
        Validator::make($data, [
            'nome_completo' => 'required|string|max:255',
            'telefone' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
        ], [
            'cpf_cnpj.unique' => 'O CPF/CNPJ informado já está cadastrado.',
            'email.unique' => 'O e-mail informado já está cadastrado.',
        ])->validate();

        $cliente->update($data);

        return redirect()->route('clientes.index')
        ->with('success', "Cliente '{$cliente->nome_completo}' (ID: {$cliente->id}) atualizado com sucesso!");
    }
    /**
     * Remove the specified resource from storage.
     */
    // app/Http/Controllers/ClienteController.php
    public function destroy(Cliente $cliente)
    {
        // Proteção com Gate
        if (Gate::denies('is-admin')) {
            return redirect()->route('clientes.index')->with('error', 'Apenas administradores podem excluir clientes.');
        }

        if ($cliente->atendimentos()->exists()) {
            return redirect()->route('clientes.index')->with('error', 'Este cliente possui atendimentos registrados e não pode ser excluído.');
        }

        $nomeClienteExcluido = $cliente->nome_completo; // Salva o nome antes de excluir
        $clienteIdExcluido = $cliente->id;
        $cliente->delete();
        return redirect()->route('clientes.index')
                         ->with('success', "Cliente '{$nomeClienteExcluido}' (ID: {$clienteIdExcluido}) excluído com sucesso!");
    }

    public function autocomplete(Request $request)
    {
        $search = $request->get('search');
        $clientes = Cliente::where('nome_completo', 'LIKE', '%' . $search . '%')
            ->orWhere('cpf_cnpj', 'LIKE', '%' . $search . '%')
            ->limit(10)
            // Adicionei os campos que usamos no select e para preenchimento
            ->get(['id', 'nome_completo', 'cpf_cnpj', 'telefone', 'email']); // Adicione outros se precisar

        // Mapear para o formato que o jQuery UI espera e que usamos no JS
        $formattedClientes = $clientes->map(function ($cliente) {
            return [
                'label' => $cliente->nome_completo . ' (' . $cliente->cpf_cnpj . ')', // O que é mostrado na lista
                'value' => $cliente->nome_completo, // O que vai para o campo de texto após selecionar
                'id' => $cliente->id,
                'telefone' => $cliente->telefone,
                'email' => $cliente->email // Se quiser pré-preencher email do cliente
            ];
        });
        return response()->json($formattedClientes);
    }

}