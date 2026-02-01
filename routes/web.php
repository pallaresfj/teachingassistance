<?php

use App\Livewire\ProfileEdit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes redirect to Filament login
Route::get('/login', function () {
    return redirect('/app/login');
})->name('login');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
})->name('logout');

// Dashboard redirect based on role
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->isSoporte()) {
            return redirect('/app');
        }

        if ($user->isDirectivo()) {
            return redirect('/app/directivo-dashboard');
        }

        return redirect('/app/docente-dashboard');
    })->name('dashboard');

    // Profile route for docente and directivo
    Route::get('/profile', ProfileEdit::class)->name('profile.edit');
});

// Docente routes - Redirect to Filament panel
Route::middleware(['auth', 'role:docente,directivo'])->prefix('docente')->name('docente.')->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/app/docente-dashboard');
    })->name('dashboard');
    Route::get('/scanner', function () {
        return view('pages.scanner');
    })->name('scanner');
});

// Directivo routes - Redirect to Filament panel
Route::middleware(['auth', 'role:directivo'])->prefix('directivo')->name('directivo.')->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/app/directivo-dashboard');
    })->name('dashboard');
    Route::get('/reports', function () {
        return view('pages.reports');
    })->name('reports');
});

// API-like routes for attendance
Route::middleware(['auth', 'role:docente,directivo'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::post('/register', function () {
        // Handled by Livewire component
        return response()->json(['message' => 'Use Livewire component']);
    })->name('register');
});
