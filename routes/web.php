<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatGPTController;

/*
|--------------------------------------------------------------------------
| ChatGPT Route
|--------------------------------------------------------------------------
*/

Route::get('/chat-gpt', [ChatGPTController::class, 'index'])
     ->name('chat-gpt.index');
