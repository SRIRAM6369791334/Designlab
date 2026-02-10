<?php

use App\Models\Design;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('/designs/{design}/editor', function (Design $design) {
        Gate::authorize('view', $design);

        return view('design.editor', ['design' => $design]);
    })->name('design.editor');

    Route::get('/admin', fn () => view('admin.dashboard'))
        ->middleware('role:admin')
        ->name('admin.dashboard');
});
