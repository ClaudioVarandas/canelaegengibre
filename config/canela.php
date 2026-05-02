<?php

declare(strict_types=1);

return [
    'default_locale' => 'pt_PT',

    'locales' => [
        'pt_PT' => [
            'label' => 'Português (PT)',
            'segment' => '',
            'articles_path' => 'artigos',
            'categories_path' => 'categorias',
            'about_path' => 'sobre',
            'search_path' => 'pesquisa',
            'enabled' => true,
        ],
        'pt_BR' => [
            'label' => 'Português (BR)',
            'segment' => 'pt-br',
            'articles_path' => 'artigos',
            'categories_path' => 'categorias',
            'about_path' => 'sobre',
            'search_path' => 'busca',
            'enabled' => false,
        ],
        'en' => [
            'label' => 'English',
            'segment' => 'en',
            'articles_path' => 'articles',
            'categories_path' => 'category',
            'about_path' => 'about',
            'search_path' => 'search',
            'enabled' => false,
        ],
    ],
];
