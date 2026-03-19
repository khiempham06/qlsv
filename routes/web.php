<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ChallengeController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('users', UserController::class);
    Route::resource('messages', MessageController::class)->only(['store', 'update', 'destroy']);
    Route::get('messages/fetch/{id}', [MessageController::class, 'fetchMessages'])->name('messages.fetch');

    Route::resource('assignments', AssignmentController::class);
    Route::post('assignments/{id}/submit', [AssignmentController::class, 'submit'])->name('assignments.submit');
    Route::get('submissions/{id}/download', [AssignmentController::class, 'downloadSubmission'])->name('submissions.download');

    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');
    Route::post('/challenges', [ChallengeController::class, 'store'])->name('challenges.store');
    Route::post('/challenges/{id}/answer', [ChallengeController::class, 'submitAnswer'])->name('challenges.answer');
});
