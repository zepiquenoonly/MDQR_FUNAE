<?php

return [
    // lista de locais usados por toda a app
    // code: o valor salvo/transferido
    // name: rótulo (pode ser traduzido se quiser)
    'locales' => [
        ['code' => 'pt',    'name' => 'Português (PT)'],
        ['code' => 'pt_MZ', 'name' => 'Português (Moçambique)'],
        ['code' => 'en',    'name' => 'English'],
        ['code' => 'en_US', 'name' => 'English (US)'],
    ],

    // locale por omissão
    'default' => env('APP_LOCALE', 'en'),
];