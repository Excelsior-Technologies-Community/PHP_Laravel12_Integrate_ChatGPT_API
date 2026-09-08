<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatGPTController;
use Illuminate\Support\Facades\Http;


/*
|--------------------------------------------------------------------------
| AI Domain Generator
|--------------------------------------------------------------------------
*/

Route::get(
    '/chat-gpt',
    [ChatGPTController::class, 'index']
)->name('chat-gpt.index');


/*
|--------------------------------------------------------------------------
| Regenerate
|--------------------------------------------------------------------------
*/

Route::post(
    '/chat-gpt/regenerate',
    [ChatGPTController::class, 'regenerate']
)->name('chat-gpt.regenerate');


/*
|--------------------------------------------------------------------------
| Favorites
|--------------------------------------------------------------------------
*/

Route::post(
    '/chat-gpt/favorite',
    [ChatGPTController::class, 'favorite']
)->name('chat-gpt.favorite');


Route::delete(
    '/chat-gpt/favorite/{id}',
    [ChatGPTController::class, 'removeFavorite']
)->name('chat-gpt.favorite.delete');


/*
|--------------------------------------------------------------------------
| Clear All Favorites
|--------------------------------------------------------------------------
*/

Route::delete(
    '/chat-gpt/favorites/clear',
    [ChatGPTController::class, 'clearFavorites']
)->name('chat-gpt.favorites.clear');


/*
|--------------------------------------------------------------------------
| History
|--------------------------------------------------------------------------
*/

Route::delete(
    '/chat-gpt/history/{id}',
    [ChatGPTController::class, 'deleteHistory']
)->name('chat-gpt.history.delete');


/*
|--------------------------------------------------------------------------
| Clear All History
|--------------------------------------------------------------------------
*/

Route::delete(
    '/chat-gpt/history/clear',
    [ChatGPTController::class, 'clearHistory']
)->name('chat-gpt.history.clear');


/*
|--------------------------------------------------------------------------
| Export History
|--------------------------------------------------------------------------
*/

Route::get(
    '/chat-gpt/history/export',
    [ChatGPTController::class, 'exportHistory']
)->name('chat-gpt.history.export');


/*
|--------------------------------------------------------------------------
| Test Gemini
|--------------------------------------------------------------------------
*/

Route::get('/test-gemini', function () {

    $response = Http::withHeaders([
        'x-goog-api-key' => env('GEMINI_API_KEY'),
        'Content-Type' => 'application/json',
    ])->post(
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.7-flash:generateContent',
        [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' =>
                            'Give me 5 creative domain names for a technology startup.'
                        ]
                    ]
                ]
            ]
        ]
    );

    return response()->json([
        'status' => $response->status(),
        'response' => $response->json(),
    ]);
});
