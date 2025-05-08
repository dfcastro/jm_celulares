<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;
    protected $fillable = ['nome_completo', 'cpf_cnpj', 'endereco', 'telefone', 'email'];
    public function atendimentos(): HasMany
    {
        return $this->hasMany(Atendimento::class);
    }
}