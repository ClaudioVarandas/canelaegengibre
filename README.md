# Canela e Gengibre

A multilingual food & lifestyle journal rebuilt from scratch — Laravel 13 backend, Filament admin, editorial Blade/Alpine/Tailwind frontend, and a one-shot import of 145 articles from the legacy Sentinel-era database, then machine-translated into three locales via the Claude API.

---

## Summary

- **What:** A Portuguese food/nutrition journal that has existed since 2015. The site is being rebuilt on a modern stack while preserving all content, images, authors, categories, and tags from the legacy MySQL dump.
- **Why:** Replace the Cartalyst Sentinel / Laravel 5-era codebase with a stack that is easier to maintain, adds a proper admin UI, and scales to three locales (pt-PT as source, plus pt-BR and en).
- **How:** Test-driven rebuild in 14 steps — scaffold, schema, admin, import, translate, route, theme, SEO, perf. 132 tests (unit, feature, architecture, browser smoke) backing the whole thing.

---

## Tech stack

| Layer | Choice |
|---|---|
| Language / runtime | PHP 8.4 |
| Framework | Laravel 13 |
| Admin panel | Filament 4 + `filament/spatie-laravel-media-library-plugin` |
| Auth / roles | Built-in Laravel auth + `spatie/laravel-permission` |
| Media | `spatie/laravel-medialibrary` (cover + content collections per article, avatar per user) |
| Frontend | Blade + Alpine.js 3 (light usage) |
| Styling | Tailwind CSS 4 + DaisyUI 5 (custom `canela` theme in oklch) |
| Typography | Fraunces (headings) + Inter (body), bundled via `@fontsource/*` |
| Build | Vite 8 via `laravel-vite-plugin` |
| AI translation | Anthropic PHP SDK (`anthropic-ai/sdk`), Claude Opus 4.7 default, prompt caching on the system prompt |
| Tests | Pest 4 (Unit + Feature + Browser via Playwright + Architecture) |
| Code style | Laravel Pint |
| Static analysis / refactor | Rector |
| Package discovery | Laravel Boost MCP |

---

## Requirements

- **PHP 8.4** with extensions: `intl`, `bcmath`, `mbstring`, `gd`, `exif`, `fileinfo`, `zip`, `curl`, `pdo_mysql`
    - On Fedora: `sudo dnf install -y php-intl php-bcmath php-mbstring php-gd php-exif php-zip`
- **Composer 2**
- **Node.js 20+** and npm
- **MySQL 8+** (tested against MySQL 9.5)
- **Playwright** (auto-installed by the browser test plugin; `npx playwright install chromium` the first time)
- **Anthropic API key** (only if you want to run `canela:translate`)

---

## Setup

```bash
# 1. Clone & install
composer install
npm install

# 2. Env
cp .env.example .env
php artisan key:generate
# Edit .env: DB_HOST / DB_PORT / DB_DATABASE / DB_USERNAME / DB_PASSWORD
# Plus: ANTHROPIC_API_KEY=... if you want translations
#       DB_LEGACY_DATABASE=canela_legacy (default)

# 3. Build assets
npm run build

# 4. Create the new app database + the legacy staging database
mysql -u root -p -e "CREATE DATABASE canelaegengibre;"
mysql -u root -p -e "CREATE DATABASE canela_legacy CHARACTER SET utf8 COLLATE utf8_unicode_ci;"

# 5. Migrate the new schema + seed roles
php artisan migrate --seed

# 6. Link storage/app/public → public/storage so uploaded media is web-served
php artisan storage:link

# 7. Load the legacy dump into canela_legacy
mysql -u root -p canela_legacy < database/dumps/canela-2025-12-22.sql

# 8. Import legacy content (users, categories, tags, articles, media)
php artisan canela:import --images=/path/to/legacy/images

# 9. (Optional) Translate pt_PT → pt_BR + en
php artisan canela:translate --entity=articles    --locales=pt_BR,en
php artisan canela:translate --entity=categories  --locales=pt_BR,en
php artisan canela:translate --entity=tags        --locales=pt_BR,en

# 10. Promote the owner account to super-admin (so you can log into /admin)
php artisan tinker --execute 'App\Models\User::where("email","cvarandas@gmail.com")->first()->assignRole("super-admin");'
```

> **Media storage.** All uploaded files — article covers, inline content images, user avatars — land under `storage/app/public/images/{media-id}/{filename}` and are served at `/storage/images/…`. The `images/` prefix is controlled by `MEDIA_PREFIX=images` in `.env` (set by default). If you change it, existing files need to be moved or re-imported.

### Running the app

- `composer run dev` runs the full dev stack (Laravel + queue worker + pail + Vite) via `concurrently`.
- Or individually: `php artisan serve`, `npm run dev`, `php artisan queue:listen`, `php artisan pail`.

---

## What's running

### URLs

| Path | Purpose |
|---|---|
| `/` | Homepage (pt-PT — default locale, unprefixed) |
| `/artigos` · `/artigos/{slug}` | Articles listing + single article (pt-PT) |
| `/categorias/{slug}` | Category listing (pt-PT) |
| `/sobre` | About page (pt-PT) |
| `/pt-br/…` | Same five routes in Brazilian Portuguese (`/pt-br/artigos/…`, `/pt-br/categorias/…`, `/pt-br/sobre`) |
| `/en/…` | English (`/en/articles/…`, `/en/category/…`, `/en/about`) |
| `/admin` | Filament admin panel (login required) |
| `/sitemap.xml` | Per-locale URL sitemap (1-hour cache) |
| `/robots.txt` | Allow-all + sitemap pointer, `/admin` disallowed |
| `/up` | Laravel health endpoint |

### Console commands

| Command | What it does |
|---|---|
| `canela:import --images={path} [--fresh] [--limit-articles=N]` | Imports users, categories, tags (with dedupe), articles, translations (pt-PT), cover + inline content media from the legacy `legacy` DB connection. Idempotent with `--fresh`. |
| `canela:translate --entity={articles\|categories\|tags} [--locales=pt_BR,en] [--article=ID] [--limit=N] [--dry-run]` | Batch-translates pt-PT translations into missing target locales using Claude. Skips records that already have all target locales. |
| `canela:publish-scheduled` | Promotes Scheduled articles whose `published_at` has arrived to Published. Wired to run every minute in `routes/console.php`. |

### Admin (Filament v4)

- **ArticleResource** — per-locale tabs (title, slug, excerpt, content, meta_title, meta_description), status enum (Draft / Pending / Scheduled / Published), featured toggle, author + category selects, tag multi-select, cover upload (media-library), separate OG image override. Authors see only their own articles; super-admins see everything.
- **CategoryResource** — 2-level tree (parent select), per-locale name/slug/description tabs, super-admin only.
- **TagResource** — per-locale name/slug tabs, authors can create/edit (so they can tag new articles), no delete.
- **UserResource** — profile + role multi-select (`spatie/permission`), password optional on edit (hash preserved), avatar via media-library.
- **RedirectResource** — from_path → to_path with HTTP code (301 / 302 / 307 / 308), super-admin only, unique from_path.

### Frontend theme

Custom DaisyUI theme `canela` in `resources/css/app.css`, mapped to the palette in `docs/ideas/template.md`:

| Token | Value |
|---|---|
| cinnamon (primary) | `#A85B32` |
| ginger (accent) | `#D98C3B` |
| oat (base bg) | `#FDF7EF` |
| moss (secondary) | `#54624E` |
| ink (text) | `#1F2937` |

Typography: Fraunces for headings, Inter for body. Both bundled, no external CDN.

Homepage composition (per `docs/ideas/template.md`):

1. Sticky translucent header (logo · nav · search · subscribe · locale switcher)
2. **Hero** — 2-column, equal height, `gap-2` (featured article with full-bleed cover + dark overlay on the left, 4 stacked compact cards on the right)
3. Category pills with Alpine panel swap — preloaded, 4 articles per category
4. Latest carousel (DaisyUI `carousel-center`)
5. Featured editorial — big gradient block + 2 supporting cards
6. About + search + tag cloud
7. Newsletter on moss→ink gradient
8. Footer

---

## Localization & URL strategy

pt-PT lives at the root (so legacy URLs like `canelaegengibre.pt/artigos/{slug}` keep resolving with zero redirect). pt-BR and en are prefixed.

| Locale | Home | Articles | Categories | About |
|---|---|---|---|---|
| pt-PT (default) | `/` | `/artigos/{slug}` | `/categorias/{slug}` | `/sobre` |
| pt-BR | `/pt-br/` | `/pt-br/artigos/{slug}` | `/pt-br/categorias/{slug}` | `/pt-br/sobre` |
| en | `/en/` | `/en/articles/{slug}` | `/en/category/{slug}` | `/en/about` |

- Slugs are **unique per locale** (not globally) — the translation tables enforce `UNIQUE(locale, slug)`.
- Missing translations return 404 rather than rendering a half-translated fallback.
- Legacy 404s are caught by a render hook (`bootstrap/app.php`) that checks the `redirects` table and emits a 301/302/307/308 if there's a match.
- UI strings translated via `lang/pt_PT.json` and `lang/pt_BR.json`; keys fall back to English (source) when missing.

---

## Data migration

Legacy dump: `database/dumps/canela-2025-12-22.sql` (145 articles, 9 categories, 185 tags, 3 users). Loaded once into a `canela_legacy` MySQL database. The `canela:import` command reads that via a dedicated `legacy` connection in `config/database.php` and writes to the primary connection.

Schema mapping (legacy → new):

| Legacy | New | Notes |
|---|---|---|
| `users.first_name + last_name` | `users.name` | concatenated on import |
| `users.info` | `users.bio` | renamed |
| `users.permissions/active/confirmed/last_login` | — | dropped (Sentinel cruft) |
| `roles` + `role_users` | spatie/permission | |
| `categories.lft/rgt/depth/icon/menu` | — | dropped, only `parent_id` + `sort` kept |
| `article_types` | — | filtered to type_id=1 (Post) |
| `articles.state` (tinyint) | `articles.status` (enum) | Lavalite convention: `state ≥ 1` → `Published`, otherwise `Draft` (confirmed against real data: 140 rows use `state=1`, a couple of edge cases use 0/-1) |
| `articles.media_featured` | Spatie media `cover` collection | file copied from `--images` dir to `storage/app/public/images/…` |
| Inline `<img src="/uploads/media/images/…">` | Spatie media `content` collection | auto-copied to the same prefixed folder |
| `tags.namespace/slug/name/count` | `tags` + `tag_translations` | namespace + count dropped; near-duplicates merged via `TagMergeMap` |
| `tagged.taggable_type/id/tag_id` | `article_tag` pivot | polymorphism dropped (only articles existed) |
| — | `redirects` | new table for legacy-path preservation |

Import run against the real dump:

- 3 users (legacy bcrypt hashes preserved via raw `DB::table()` insert)
- 9 categories → 9 pt-PT translations
- 177 canonical tags (from 184 legacy; 7 near-duplicate slugs merged: `receitas`/`receitasrapidas`, `pao`/`paodealfarroba`, `panqueca`/`panquecas`, `sem-acucar`, `refeicoes`, `pos-treino`, …)
- 145 articles → 145 pt-PT translations
- 747 article-tag pivot rows
- 145 cover images + 66 inline content images moved into spatie media
- End-to-end ~4s

---

## Translation

Single entry point: `php artisan canela:translate --entity=… --locales=…`.

- The `TranslatorContract` interface is bound to `ClaudeTranslator` in `AppServiceProvider` (container-swappable in tests, see `tests/Support/FakeTranslator.php`).
- `ClaudeTranslator` builds one request per record with `source_locale`, `target_locales`, and a `fields` object; the system prompt (cached via `cache_control: ephemeral`) specifies the preservation rules — keep every HTML tag/attribute intact, translate text nodes only, produce natural pt-BR vocabulary (`café da manhã`, `xícara`, …), editorial English, and per-locale slugs.
- Model default: `claude-opus-4-7`. Swap via `ANTHROPIC_MODEL=claude-sonnet-4-6` in `.env` for a cheaper pass.
- The command is idempotent (skips records already having all target locales), supports `--dry-run`, `--limit`, `--article`, and `--entity=articles|categories|tags`.

Rough cost for 145 articles × 2 locales on Opus 4.7 (input ~2K + output ~4K per call): **~$18** input+output. Sonnet 4.6 is ~1/5 that.

---

## Testing

```bash
# Everything (unit + feature + browser + arch)
./vendor/bin/pest --compact

# One suite
./vendor/bin/pest --testsuite=Unit
./vendor/bin/pest --testsuite=Feature
./vendor/bin/pest --testsuite=Browser

# Filter
./vendor/bin/pest --filter=ArticleResourceTest --compact

# Lint / format
./vendor/bin/pint --dirty --format agent
```

Coverage snapshot:

| Suite | Count | Examples |
|---|---|---|
| Unit | `ArticleStatus` enum, `ContentSanitizer`, `TagMergeMap` | 11 tests |
| Feature / Frontend | locale routing, homepage content, SEO (sitemap/robots/meta/JSON-LD/hreflang), cache headers | 24 tests |
| Feature / Filament | admin access gates + every resource (5) with per-locale form fills + policy authorization | 22 tests |
| Feature / Models | relationships, fallback translation chain, scopes, unique slug-per-locale | 13 tests |
| Feature / Console | `canela:publish-scheduled`, `canela:translate` (with a `FakeTranslator`) | 6 tests |
| Feature / Import | fails without `--images`; imports a single article end-to-end (skipped if legacy DB unreachable) | 2 tests |
| Architecture | no `dd`/`dump`, Controllers extend Controller + suffixed, Models extend Eloquent, Enums are enums, Services namespaced | 5 tests |
| Browser | 17 routes × `assertNoSmoke()` (no JS errors, no console logs), hero click nav, en locale landing | 3 tests |
| **Total** | **132 tests / 291 assertions** (7 skipped while pt-BR/en are disabled) | ~10s wall clock |

The browser suite uses `pestphp/pest-plugin-browser` + Playwright Chromium. `RefreshDatabase` + sqlite-in-memory keeps each test isolated.

---

## Project layout

```
app/
  Console/Commands/         # canela:import, canela:translate, canela:publish-scheduled
  Enums/                    # ArticleStatus
  Filament/Admin/
    Concerns/SyncsTranslations.php
    Resources/              # Article, Category, Tag, User, Redirect
  Http/
    Controllers/            # Home, Article, Category, About, Sitemap
    Middleware/             # SetLocale, CachePublicResponse
  Models/                   # Article, ArticleTranslation, Category, CategoryTranslation,
                            # Tag, TagTranslation, Redirect, User
  Policies/                 # ArticlePolicy (+ Category/Tag/User/Redirect)
  Providers/                # AppServiceProvider, Filament/AdminPanelProvider
  Services/
    Import/                 # ContentSanitizer, TagMergeMap
    Translation/            # TranslatorContract, ClaudeTranslator

config/
  canela.php                # supported locales + URL segment map
  media-library.php         # spatie/media-library config (prefix="images", disk="public")

database/
  dumps/                    # legacy SQL (gitignored)
  migrations/               # users profile fields, categories, category_translations,
                            # tags, tag_translations, articles, article_translations,
                            # article_tag, redirects, permission tables, media
  factories/
  seeders/RoleSeeder.php

lang/                       # pt_PT.json, pt_BR.json

resources/
  css/app.css               # Tailwind v4 + DaisyUI canela theme + fonts
  js/app.js                 # Alpine.js
  views/
    layouts/app.blade.php   # shell with meta/OG/hreflang
    components/             # header, footer, newsletter, article-card, locale-switcher
    home.blade.php
    articles/index, show
    categories/show
    about
    sitemap.blade.php

routes/
  web.php                   # per-locale route groups + legacy redirect fallback
  console.php               # scheduled canela:publish-scheduled

tests/
  Unit/ Feature/ Browser/ Support/
```

---

## Production checklist

Before pointing DNS at this build:

1. `.env` — set `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY=…` (run `php artisan key:generate`), `SESSION_ENCRYPT=true`, `ANTHROPIC_API_KEY=…` only if you'll re-run translations.
2. `php artisan migrate --force && php artisan db:seed --force` for the role seeder.
3. `php artisan storage:link`.
4. `npm ci && npm run build`.
5. `php artisan optimize` (config/route/event cache). Skip `view:cache` until after Filament discover runs once.
6. Ensure HTTPS is terminated upstream — `SecurityHeaders` middleware adds HSTS automatically when the request is HTTPS in `production`.
7. Run the legacy import once (`canela:import --images=…`); the command is idempotent under `--fresh`.
8. The Filament admin is throttled to 300 requests/minute per IP; `/pesquisa` is throttled to 60/minute.

## Known follow-ups

- **Inline content image URLs** — legacy content is sanitized (HTML entities decoded, absolute `canelaegengibre.pt` host stripped), and the actual image files are moved into the spatie `content` media collection (served under `/storage/images/…`). However, the HTML body of each article still references the legacy-style paths (`/uploads/media/images/…`), which now 404. A second pass to rewrite each inline `<img src>` to the new spatie media URL per article is TODO.
- **Comments** — intentionally not implemented (not in the legacy data, not requested).
- **pt-BR / en locales** — wired end-to-end but disabled in `config/canela.php` for v1; flip `enabled => true` per locale to ship them. The translation command and routes are already in place.
- **FULLTEXT search** — current search uses LIKE. Drop in a MySQL FULLTEXT index on `article_translations(title, excerpt)` once content volume justifies it.

---

## License

Personal project — no license specified.
