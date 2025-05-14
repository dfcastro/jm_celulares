<?php

namespace App\Http\Controllers;

use App\Models\Atendimento;
use Illuminate\Http\Request;

class ConsultaStatusController extends Controller
{
    public function index()
    {
        return view('consulta_status.index');
    }

    public function consultar(Request $request)
    {
        $request->validate([
            'codigo_consulta' => 'required|string|max:10',
        ]);

        $atendimento = Atendimento::where('codigo_consulta', $request->codigo_consulta)->first();

        if ($atendimento) {
            return view('consulta_status.resultado', compact('atendimento'));
        } else {
            return back()->withErrors(['codigo_consulta' => 'Código de consulta inválido.'])->withInput();
        }
    }
}