<?php

namespace App\Http\Controllers;

use App\Models\FavoriteDomain;
use App\Models\GenerationHistory;
use Illuminate\Http\Request;
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

        $search = $request->get('search', '');

        $historyQuery = GenerationHistory::query();

        if ($search !== '') {
            $historyQuery->where('topic', 'like', '%' . $search . '%');
        }

        $history = $historyQuery->latest()->paginate(10, ['*'], 'history_page');
        $favorites = FavoriteDomain::latest()->paginate(10, ['*'], 'favorites_page');

        return view('chatGPT', compact(
            'result',
            'topic',
            'provider',
            'search',
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
                'search' => request('search', ''),
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
                'search' => request('search', ''),
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
     * Clear all generation history.
     */
    public function clearHistory(Request $request)
    {
        GenerationHistory::query()->delete();

        return back()->with(
            'history_success',
            'All generation history cleared!'
        );
    }

    /**
     * Clear all favorite domains.
     */
    public function clearFavorites(Request $request)
    {
        FavoriteDomain::query()->delete();

        return back()->with(
            'favorite_success',
            'All favorites cleared!'
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

            try {

                $response = OpenAI::chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => $messages,
                ]);

                $data = $response->toArray();

                if (!isset($data['choices'][0]['message']['content'])) {
                    return 'Error: Invalid response from OpenAI. Please try again.';
                }

                return $data['choices'][0]['message']['content'];

            } catch (\Exception $e) {

                return 'OpenAI Error: ' . $e->getMessage();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Gemini
        |--------------------------------------------------------------------------
        */
        if ($provider === 'gemini') {

            try {

                $response = Gemini::generativeModel(
                    model: config('gemini.model', 'gemini-2.5-flash')
                )->generateContent($prompt);

                $text = $response->text();

                if (empty($text)) {
                    return 'Error: Empty response from Gemini. Please try again.';
                }

                return $text;

            } catch (\Exception $e) {

                return 'Gemini Error: ' . $e->getMessage();
            }
        }

        return '';
    }
}