<?php

declare(strict_types=1);

arch('no debug helpers sneak into app code')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('controllers live under App\\Http\\Controllers and are named *Controller')
    ->expect('App\Http\Controllers')
    ->toExtend('App\Http\Controllers\Controller')
    ->toHaveSuffix('Controller');

arch('models live under App\\Models and extend Eloquent')
    ->expect('App\Models')
    ->toExtend('Illuminate\Database\Eloquent\Model')
    ->ignoring(['App\Models\User', 'App\Models\Concerns']);

arch('enums are declared strict')
    ->expect('App\Enums')
    ->toBeEnums();

arch('services live under App\\Services')
    ->expect('App\Services')
    ->toOnlyBeUsedIn(['App', 'Database', 'Tests']);
