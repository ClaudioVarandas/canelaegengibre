# Project: Laravel 13 Lifestyle Blog — Planning & Implementation

## Context

I have a **fresh Laravel 13 project** with no starter kit and no frontend installed. I want to build a **lifestyle blog** (articles/recipes) from the ground up, migrating data from a legacy system.

## Stack (fixed — do not substitute)

- **Backend:** Laravel 13
- **Admin panel:** Filament (articles, taxonomies, media management)
- **Frontend:** Blade + Alpine.js
- **Styling:** Tailwind CSS + DaisyUI
- **Design system:** see `@docs/ideas/template.md`
- **Legacy DB dump:** `@database/dumps/canela-2025-12-22.sql`

Keep it **simple and easy to maintain**. No over-engineering, no unnecessary abstractions.

## Pages to Build (Frontend)

1. **Homepage** — per spec in `@docs/ideas/template.md`
2. **Articles list** (recipes)
3. **About page**

## Backoffice (Filament)

- Full CRUD for articles and supporting entities (categories, tags, authors, media — whatever the schema analysis reveals)
- Auth for admin users

---

## How I want you to work: Think → Ask → Plan → TDD → Implement

### Phase 1 — Think & Analyze (do this first, do not write code yet)

1. Read `@docs/ideas/template.md` carefully and summarize what you understand about the design, layout, and component requirements.
2. Analyze `@database/dumps/canela-2025-12-22.sql`:
    - List all tables, their columns, relationships, and row counts if inferable.
    - Identify what content types exist (articles, recipes, categories, tags, users, comments, media, etc.).
    - Flag anything **weird, legacy, redundant, or worth restructuring** (e.g., denormalized fields, unused columns, inconsistent naming, missing foreign keys).
3. Propose a **modernized schema** for the new Laravel project:
    - Show a mapping table: old table/column → new table/column
    - Justify any changes (merges, splits, renames, drops)
    - Call out anything ambiguous you need my input on

### Phase 2 — Ask

Before planning implementation, **ask me clarifying questions** about:
- Anything ambiguous in the legacy schema
- Anything underspecified in `template.md`
- Scope decisions (e.g., do we need comments? multi-author? drafts/scheduling? SEO fields? multilingual?)
- Migration strategy (one-shot seeder vs. artisan command vs. manual export)

**Wait for my answers before proceeding.**

### Phase 3 — Plan

Produce a written implementation plan covering:
- Directory/feature structure
- Migrations and models (with relationships)
- Filament resources and their fields/relations
- Frontend routes, controllers, Blade layouts, and reusable components
- Tailwind + DaisyUI setup and how `template.md` maps to components
- Data migration approach from the SQL dump
- Testing strategy (what we'll test with TDD and at what layer: unit, feature, browser)

**Wait for my approval before implementing.**

### Phase 4 — TDD Implementation

Implement feature by feature, **test first**:
1. Write a failing test (Pest or PHPUnit — your recommendation, justify briefly).
2. Write the minimum code to pass.
3. Refactor.
4. Move to the next feature.

Suggested feature order (but propose your own if better):
1. Project scaffolding (Tailwind, DaisyUI, Alpine, Filament install)
2. Core models + migrations (Article, Category, Tag, User, Media)
3. Legacy data migration command
4. Filament resources
5. Frontend: layout + homepage
6. Frontend: articles list + article detail
7. Frontend: about page
8. Polish, SEO, performance pass

Commit after each green test cycle with clear messages.

---

## Ground rules

- **Don't skip Phase 1 and 2.** I want to see your analysis and questions before any code.
- **Show your work** — when you make a non-obvious decision, tell me why.
- **Flag trade-offs** rather than silently picking one.
- **Ask when unsure**, especially about legacy data semantics.
- Prefer Laravel conventions over custom patterns unless there's a clear reason.

Start with **Phase 1** now.
