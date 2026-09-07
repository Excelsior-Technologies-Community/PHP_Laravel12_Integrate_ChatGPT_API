<?php

namespace App\Http\Controllers;

use App\Models\FavoriteDomain;
use App\Models\GenerationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenAI\Laravel\Facades\OpenAI;
use Gemini\Laravel\Facades\Gemini;

class ChatGPTController extends Controller
{
    /**
     * Display the AI domain generator.
     */
    public function index(Request $request)
    {
        $result = '';
        $topic = $request->get('title', '');
        $provider = $request->get('provider', 'openai');

        /*
        |--------------------------------------------------------------------------
        | Generate only when explicitly requested
        |--------------------------------------------------------------------------
        */
        if (
            $request->filled('title') &&
            $request->get('generate') === '1'
        ) {
            $result = $this->generateDomains($topic, $provider);

            GenerationHistory::create([
                'topic' => $topic,
                'result' => $result,
            ]);
        }

        $history = GenerationHistory::latest()->get();
        $favorites = FavoriteDomain::latest()->get();

        return view('chatGPT', compact(
            'result',
            'topic',
            'provider',
            'history',
            'favorites'
        ));
    }

    /**
     * Generate a fresh set of domain names.
     */
    public function regenerate(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'provider' => 'required|in:openai,gemini',
        ]);

        $topic = $request->title;
        $provider = $request->provider;

        $result = $this->generateDomains($topic, $provider);

        GenerationHistory::create([
            'topic' => $topic,
            'result' => $result,
        ]);

        return redirect()
            ->route('chat-gpt.index', [
                'title' => $topic,
                'provider' => $provider,
                'generate' => '1',
            ])
            ->with('success', 'New domain names generated successfully!');
    }

    /**
     * Save a domain name to favorites.
     */
    public function favorite(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'provider' => 'nullable|in:openai,gemini',
        ]);

        FavoriteDomain::firstOrCreate([
            'topic' => $request->topic,
            'domain' => $request->domain,
        ]);

        return redirect()
            ->route('chat-gpt.index', [
                'title' => $request->topic,
                'provider' => $request->provider ?? 'openai',
            ])
            ->with('favorite_success', 'Domain saved to favorites!');
    }

    /**
     * Delete a favorite domain.
     */
    public function removeFavorite($id)
    {
        FavoriteDomain::findOrFail($id)->delete();

        return back()->with(
            'favorite_success',
            'Domain removed from favorites!'
        );
    }

    /**
     * Delete generation history.
     */
    public function deleteHistory($id)
    {
        GenerationHistory::findOrFail($id)->delete();

        return back()->with(
            'history_success',
            'Generation history deleted!'
        );
    }

    /**
     * Generate domain names using the selected AI provider.
     */
    private function generateDomains(
        string $topic,
        string $provider = 'openai'
    ): string {

        $prompt = 'Suggest exactly 5 creative and brandable domain names '
            . 'based on the topic "' . $topic . '". '
            . 'Return only a numbered list from 1 to 5. '
            . 'Do not provide explanations.';

        /*
        |--------------------------------------------------------------------------
        | OpenAI
        |--------------------------------------------------------------------------
        */
        if ($provider === 'openai') {

            $messages = [
                [
                    'role' => 'system',
                    'content' => 'You are a professional domain name generator.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ],
            ];

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
            ]);

            return Arr::get(
                $response->toArray(),
                'choices.0.message.content',
                ''
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Gemini
        |--------------------------------------------------------------------------
        */
        if ($provider === 'gemini') {

            $response = Gemini::generativeModel(
                model: config('gemini.model', 'gemini-2.5-flash')
            )->generateContent($prompt);

            return $response->text();
        }

        return '';
    }
}