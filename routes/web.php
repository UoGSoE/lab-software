<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\HomePage::class)->name('home');
Route::get('/college-wide', \App\Livewire\CollegeWide::class)->name('college-wide');
Route::get('/exporter', \App\Livewire\Exporter::class)->name('exporter');
Route::get('/users', \App\Livewire\UserList::class)->name('users');
Route::get('/settings', \App\Livewire\Settings::class)->name('settings');
Route::get('/help', \App\Livewire\Help::class)->name('help');
