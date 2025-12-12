<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenAI\Laravel\Facades\OpenAI;

class ChatGPTController extends Controller
{
    /**
     * Handle ChatGPT request and return domain name suggestions.
     */
    public function index(Request $request)
    {
        $result = '';

        // Check if user submitted a topic
        if ($request->filled('title')) {

            // Prepare message for ChatGPT
            $messages = [
                [
                    'role'    => 'user',
                    'content' => 'Suggest me 5 domain names from topic "' . $request->title . '". 
                                 Give only list: 1. 2. 3. 4. 5.'
                ],
            ];

            // Call ChatGPT API using OpenAI facade
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini', // Recommended latest model
                'messages' => $messages,
            ]);

            // Extract and store the AI response
            $result = Arr::get($response, 'choices.0.message')['content'] ?? '';
        }

        // Return result to Blade view
        return view('chatGPT', compact('result'));
    }
}
