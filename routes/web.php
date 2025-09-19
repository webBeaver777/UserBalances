<?php

use Illuminate\Support\Facades\Route;

// SPA: все маршруты, кроме /api, отдают app.blade.php
Route::get('/{any}', fn (): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory => view('app'))->where('any', '.*');
