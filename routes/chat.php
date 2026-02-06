<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('chat/{conversation?}', [ChatController::class, 'store'])->name('chat.store')->middleware('throttle:chat');
    Route::delete('chat/{conversation}', [ChatController::class, 'destroy'])->name('chat.destroy');
});
