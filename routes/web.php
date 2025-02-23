<?php

use App\Http\Middleware\Admin;
use Illuminate\Support\Facades\Route;

// if (! auth()->check()) {
//     auth()->loginUsingId(\App\Models\User::first()->id);
// }

Route::get('/', \App\Livewire\HomePage::class)->name('home');
Route::get('/college-wide', \App\Livewire\CollegeWide::class)->name('college-wide');
Route::get('/exporter', \App\Livewire\Exporter::class)->name('exporter');
Route::get('/users', \App\Livewire\UserList::class)->name('users');
Route::get('/settings', \App\Livewire\Settings::class)->name('settings')->middleware(Admin::class);
Route::get('/help', \App\Livewire\Help::class)->name('help');

Route::get('/signed-off/{user}', function (\App\Models\User $user) {
    if (! request()->hasValidSignature()) {
        abort(401, 'Invalid link signature - it may have expired.  Please visit the <a href="'.route('home').'">home page</a> to log in instead.');
    }
    $user->signOffLastYearsSoftware();
    return view('signed_off', ['user' => $user]);
})->name('signed-off');
