<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ArticleTranslation>
 */
class ArticleTranslationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'article_id' => Article::factory(),
            'locale' => 'pt_PT',
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(4),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
            'meta_title' => null,
            'meta_description' => null,
        ];
    }
}
