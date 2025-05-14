<?php

return [

    // ... (outras mensagens de validação padrão, como 'required', 'min', 'max', etc.) ...

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'nome' => [
            'unique' => 'Já existe uma peça com o mesmo nome e modelo compatível cadastrados.', // Esta é a linha principal para o erro composto
        ],
        // Se a validação for única para a combinação, esta linha genérica para 'nome'
        // é suficiente. Não precisa da outra mais específica se esta já é clara.
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'nome' => 'nome',
        'modelo_compativel' => 'Modelo Compatível',
        // Adicione outros atributos aqui conforme necessário
    ],

];