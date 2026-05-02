@extends('layouts.app')

@section('title', __('About') . ' · Canela e Gengibre')

@section('content')
    <x-header :root-categories="$rootCategories ?? collect()" />

    <main class="mx-auto max-w-3xl px-6 py-20" data-locale="{{ app()->getLocale() }}">
        <header class="mb-10 text-center">
            <p class="text-sm uppercase tracking-widest text-cinnamon">{{ __('Our story') }}</p>
            <h1 class="mt-2 font-display text-5xl text-ink">{{ __('About') }}</h1>
        </header>

        <figure class="mx-auto mb-12 w-48">
            <img src="{{ asset('images/carla.png') }}" alt="Carla Cerqueira"
                 class="h-48 w-48 rounded-full object-cover shadow-soft ring-1 ring-ink/10">
        </figure>

        <div class="space-y-8 text-xl leading-relaxed text-ink/80">
            <p>Sou a Carla Cerqueira, licenciada em Comunicação Social, apaixonada pela escrita e por tudo o que nos aproxima de uma vida mais equilibrada, consciente e feliz. Durante muito tempo, a alimentação foi apenas uma rotina. Até ao dia em que decidi mudar. Através de uma reeducação alimentar, perdi 40&nbsp;kg e ganhei muito mais do que um novo corpo: ganhei uma nova forma de olhar para mim, para a comida e para o bem-estar.</p>

            <p>Foi dessa transformação que nasceu o <em>Canela e Gengibre</em>, um espaço onde junto duas paixões que fazem parte de mim: a escrita e a alimentação saudável. Aqui partilho receitas, sugestões, experiências e pequenas inspirações para quem procura cuidar de si sem complicações, sem extremismos e sem perder o prazer de comer. Porque acredito que uma vida saudável também pode ser saborosa, simples e cheia de cor.</p>

            <p>Mais do que um site de receitas, o <em>Canela e Gengibre</em> é o reflexo de um caminho pessoal feito de escolhas, aprendizagens e recomeços. O meu lema de vida continua a acompanhar-me em cada passo: <em>“Para ver o arco-íris, é preciso não temer a chuva.”</em> E é também isso que quero partilhar por aqui: a coragem de mudar, a leveza de experimentar e a vontade de viver de forma naturalmente diferente.</p>
        </div>
    </main>

    <x-footer :root-categories="$rootCategories ?? collect()" />
@endsection
