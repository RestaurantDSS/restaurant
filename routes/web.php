<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

Route::get('/', function () {
    return redirect('/admin/login');
});

Livewire::setUpdateRoute(function($handle){
    return Route::post('/restaurant/public/livewire/update',$handle);
});
