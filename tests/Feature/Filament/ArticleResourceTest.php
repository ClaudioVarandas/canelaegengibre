<?php

declare(strict_types=1);

use App\Enums\ArticleStatus;
use App\Filament\Admin\Resources\Articles\Pages\CreateArticle;
use App\Filament\Admin\Resources\Articles\Pages\EditArticle;
use App\Filament\Admin\Resources\Articles\Pages\ListArticles;
use App\Models\Article;
use App\Models\ArticleTranslation;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
});

function makeAdmin(): User
{
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    return $user;
}

function makeAuthor(): User
{
    $user = User::factory()->create();
    $user->assignRole('author');

    return $user;
}

function makeCategory(): Category
{
    $category = Category::factory()->create();
    CategoryTranslation::factory()->create([
        'category_id' => $category->id,
        'locale' => 'pt_PT',
        'name' => 'Alimentação',
        'slug' => 'alimentacao',
    ]);

    return $category;
}

it('shows every article to a super-admin', function (): void {
    $admin = makeAdmin();
    $articles = Article::factory()->count(3)->create();

    $this->actingAs($admin);
    Livewire::test(ListArticles::class)->assertCanSeeTableRecords($articles);
});

it('limits the list to the author\'s own articles', function (): void {
    $author = makeAuthor();
    $otherAuthor = makeAuthor();
    $own = Article::factory()->create(['user_id' => $author->id]);
    $foreign = Article::factory()->create(['user_id' => $otherAuthor->id]);

    $this->actingAs($author);
    Livewire::test(ListArticles::class)
        ->assertCanSeeTableRecords([$own])
        ->assertCanNotSeeTableRecords([$foreign]);
});

it('forbids an author from editing another author\'s article', function (): void {
    $me = makeAuthor();
    $other = makeAuthor();
    $article = Article::factory()->create(['user_id' => $other->id]);

    $this->actingAs($me);
    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])->assertForbidden();
});

it('allows an author to edit their own article', function (): void {
    $author = makeAuthor();
    $article = Article::factory()->create(['user_id' => $author->id]);

    $this->actingAs($author);
    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])->assertOk();
});

it('creates an article and persists its pt_PT translation', function (): void {
    $admin = makeAdmin();
    $category = makeCategory();

    $this->actingAs($admin);
    Livewire::test(CreateArticle::class)
        ->fillForm([
            'status' => ArticleStatus::Draft->value,
            'user_id' => $admin->id,
            'category_id' => $category->id,
            'translations' => [
                'pt_PT' => [
                    'title' => 'Bolo de chocolate',
                    'slug' => 'bolo-de-chocolate',
                    'excerpt' => 'Receita simples',
                    'content' => 'Misture tudo...',
                ],
                'pt_BR' => [],
                'en' => [],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $translation = ArticleTranslation::where('slug', 'bolo-de-chocolate')->first();
    expect($translation)->not->toBeNull();
    expect($translation->title)->toBe('Bolo de chocolate');
    expect($translation->locale)->toBe('pt_PT');
});

it('loads existing translations into the edit form', function (): void {
    $admin = makeAdmin();
    $article = Article::factory()->create(['user_id' => $admin->id]);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'pt_PT',
        'title' => 'Título PT',
        'slug' => 'titulo-pt',
    ]);
    ArticleTranslation::factory()->create([
        'article_id' => $article->id,
        'locale' => 'en',
        'title' => 'English title',
        'slug' => 'english-title',
    ]);

    $this->actingAs($admin);
    Livewire::test(EditArticle::class, ['record' => $article->getRouteKey()])
        ->assertFormSet([
            'translations.pt_PT.title' => 'Título PT',
            'translations.en.title' => 'English title',
        ]);
});
