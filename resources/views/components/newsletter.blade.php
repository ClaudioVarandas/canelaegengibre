<section id="newsletter" class="mx-auto my-24 max-w-6xl">
    <div class="rounded-3xl bg-gradient-to-br from-moss to-ink px-8 py-14 text-oat shadow-2xl sm:px-16">
        <div class="grid gap-10 md:grid-cols-2 md:items-center">
            <div>
                <h2 class="font-serif text-4xl leading-tight">
                    {{ __('Receive the next issue') }}
                </h2>
                <p class="mt-4 text-base text-oat/80">
                    {{ __('A short, curated email — new recipes, gentle reflections, seasonal tables.') }}
                </p>
            </div>
            <form class="flex flex-col gap-3 sm:flex-row">
                <label class="input input-bordered flex flex-1 items-center gap-2 bg-oat/95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-60" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2">
                        <rect width="20" height="16" x="2" y="4" rx="2" />
                        <path d="m22 7-10 5L2 7" />
                    </svg>
                    <input type="email" required placeholder="{{ __('you@example.com') }}" class="grow bg-transparent">
                </label>
                <button type="submit" class="btn btn-primary">
                    {{ __('Subscribe') }}
                </button>
            </form>
        </div>
    </div>
</section>
