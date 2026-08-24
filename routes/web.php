<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/openapi.json', function () {
    $path = public_path('openapi.json');
    if (!file_exists($path)) {
        abort(404, 'openapi.json not found');
    }
    $content = json_decode(file_get_contents($path), true);
    $content['servers'] = [
        ['url' => url('/'), 'description' => 'Current Server']
    ];
    return response()->json($content);
});
