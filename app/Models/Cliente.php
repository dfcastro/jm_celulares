<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;


class Cliente extends Model
{
    use HasFactory;
    use HasFactory, Notifiable;
    protected $fillable = [
        'nome_completo',
        'cpf_cnpj',
        // Campos de endereço existentes
        'endereco', // Manter se você não o removeu na migração
        'telefone',
        'email',
        // NOVO: Campos de endereço detalhados
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function atendimentos(): HasMany
    {
        return $this->hasMany(Atendimento::class);
    }
    public function setNomeCompletoAttribute($value) // O nome do método DEVE ser setNomeCompletoAttribute
    {
        // mb_convert_case com MB_CASE_TITLE funciona bem para nomes próprios,
        // mas pode ter problemas com preposições "da", "de", "dos".
        // Str::title é geralmente melhor para nomes próprios.
        $this->attributes['nome_completo'] = Str::title(mb_strtolower($value, 'UTF-8'));
    }
}
