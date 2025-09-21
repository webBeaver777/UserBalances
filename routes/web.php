<?php

use Illuminate\Support\Facades\Route;

// SPA: все маршруты отдают app.blade.php для Vue роутера
Route::get('/{any}', fn (): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory => view('app'))->where('any', '.*');
