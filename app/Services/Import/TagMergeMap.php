<?php

declare(strict_types=1);

namespace App\Services\Import;

use Illuminate\Support\Str;

class TagMergeMap
{
    /**
     * Map of legacy slug -> canonical (new) slug. Any legacy slug not listed
     * is kept as-is (but normalized via Str::slug).
     *
     * @var array<string, string>
     */
    private const MERGES = [
        'receita' => 'receitas',
        'receitasrapidas' => 'receitas',
        'pao-sem-gluten' => 'pao',
        'paodealfarroba' => 'pao',
        'panqueca' => 'panquecas',
        'maca' => 'maca',
        'semacucar' => 'sem-acucar',
        'semaçúcar' => 'sem-acucar',
        'refeicao' => 'refeicoes',
        'refeições' => 'refeicoes',
        'brocolos' => 'brocolos',
        'brócolos' => 'brocolos',
        'organizacao' => 'organizacao',
        'organização' => 'organizacao',
        'hidratacao' => 'hidratacao',
        'hidratação' => 'hidratacao',
        'motivacao' => 'motivacao',
        'motivação' => 'motivacao',
        'mudanca' => 'mudanca',
        'mudança' => 'mudanca',
        'criancas' => 'criancas',
        'crianças' => 'criancas',
        'postreino' => 'pos-treino',
        'pos-treino' => 'pos-treino',
        'pre-treino' => 'pre-treino',
        'pretreino' => 'pre-treino',
    ];

    public function canonical(string $legacySlug): string
    {
        $normalized = Str::slug($legacySlug);

        return self::MERGES[$legacySlug] ?? self::MERGES[$normalized] ?? $normalized;
    }
}
