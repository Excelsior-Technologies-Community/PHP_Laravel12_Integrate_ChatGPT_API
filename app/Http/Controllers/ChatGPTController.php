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
     * Display AI domain generator.
     */
    public function index(Request $request)
    {
        $result = '';

        $topic = $request->get('title', '');

        $provider = $request->get('provider', 'openai');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        $search = $request->get('search', '');

        /*
        |--------------------------------------------------------------------------
        | History sorting
        |--------------------------------------------------------------------------
        */
        $sort = $request->get('sort', 'newest');

        /*
        |--------------------------------------------------------------------------
        | Generate domains
        |--------------------------------------------------------------------------
        */
        if (
            $request->filled('title') &&
            $request->get('generate') === '1'
        ) {
            $result = $this->generateDomains(
                $topic,
                $provider
            );

            if (!empty($result)) {
                GenerationHistory::create([
                    'topic' => $topic,
                    'result' => $result,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Favorites
        |--------------------------------------------------------------------------
        */
        $favorites = FavoriteDomain::latest()->get();

        /*
        |--------------------------------------------------------------------------
        | History sorting
        |--------------------------------------------------------------------------
        */
        $historyQuery = GenerationHistory::query();

        if ($sort === 'oldest') {
            $historyQuery->oldest();
        } else {
            $historyQuery->latest();
        }

        $history = $historyQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard statistics
        |--------------------------------------------------------------------------
        */
        $totalGenerations = GenerationHistory::count();

        $totalFavorites = FavoriteDomain::count();

        $totalDomainsGenerated = $totalGenerations * 5;

        /*
        |--------------------------------------------------------------------------
        | Search all generated domains
        |--------------------------------------------------------------------------
        */
        $searchResults = [];

        if ($search !== '') {

            foreach ($history as $item) {

                $domains = preg_split(
                    '/\r\n|\r|\n/',
                    trim($item->result)
                );

                foreach ($domains as $domain) {

                    $cleanDomain = trim(
                        preg_replace(
                            '/^\s*\**\s*\d+[\.\)\-\:]\s*\**/',
                            '',
                            $domain
                        )
                    );

                    if (
                        !empty($cleanDomain) &&
                        stripos($cleanDomain, $search) !== false
                    ) {
                        $searchResults[] = [
                            'domain' => $cleanDomain,
                            'topic' => $item->topic,
                        ];
                    }
                }
            }
        }

        return view('chatGPT', compact(
            'result',
            'topic',
            'provider',
            'history',
            'favorites',
            'search',
            'sort',
            'searchResults',
            'totalGenerations',
            'totalFavorites',
            'totalDomainsGenerated'
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

        $result = $this->generateDomains(
            $topic,
            $provider
        );

        if (!empty($result)) {
            GenerationHistory::create([
                'topic' => $topic,
                'result' => $result,
            ]);
        }

        return redirect()
            ->route('chat-gpt.index', [
                'title' => $topic,
                'provider' => $provider,
                'generate' => '1',
            ])
            ->with(
                'success',
                'New domain names generated successfully!'
            );
    }

    /**
     * Save a domain to favorites.
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

        return back()->with(
            'favorite_success',
            'Domain saved to favorites successfully!'
        );
    }

    /**
     * Delete one favorite.
     */
    public function removeFavorite($id)
    {
        FavoriteDomain::findOrFail($id)->delete();

        return back()->with(
            'favorite_success',
            'Domain removed from favorites successfully!'
        );
    }

    /**
     * Clear all favorites.
     */
    public function clearFavorites()
    {
        FavoriteDomain::query()->delete();

        return back()->with(
            'favorite_success',
            'All favorite domains have been deleted successfully!'
        );
    }

    /**
     * Delete one history record.
     */
    public function deleteHistory($id)
    {
        GenerationHistory::findOrFail($id)->delete();

        return back()->with(
            'history_success',
            'Generation history deleted successfully!'
        );
    }

    /**
     * Clear all generation history.
     */
    public function clearHistory()
    {
        GenerationHistory::query()->delete();

        return back()->with(
            'history_success',
            'All generation history has been deleted successfully!'
        );
    }

    /**
     * Export generation history as CSV.
     */
    public function exportHistory()
    {
        $history = GenerationHistory::latest()->get();

        $fileName =
            'generation-history-' .
            date('Y-m-d-H-i-s') .
            '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' =>
                'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($history) {

            $file = fopen('php://output', 'w');

            /*
            |--------------------------------------------------------------------------
            | CSV Header
            |--------------------------------------------------------------------------
            */
            fputcsv($file, [
                'ID',
                'Topic',
                'Generated Domains',
                'Created At',
            ]);

            /*
            |--------------------------------------------------------------------------
            | CSV Data
            |--------------------------------------------------------------------------
            */
            foreach ($history as $item) {

                fputcsv($file, [
                    $item->id,
                    $item->topic,
                    $item->result,
                    $item->created_at
                        ? $item->created_at->format(
                            'Y-m-d H:i:s'
                        )
                        : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream(
            $callback,
            200,
            $headers
        );
    }

    /**
     * Generate domain names using selected AI provider.
     */
    private function generateDomains(
        string $topic,
        string $provider = 'openai'
    ): string {

        $prompt =
            'Suggest exactly 5 creative and brandable domain names ' .
            'based on the topic "' . $topic . '". ' .
            'Return only a numbered list from 1 to 5. ' .
            'Do not provide explanations.';

        /*
        |--------------------------------------------------------------------------
        | OpenAI
        |--------------------------------------------------------------------------
        */
        if ($provider === 'openai') {

            $messages = [
                [
                    'role' => 'system',
                    'content' =>
                        'You are a professional domain name generator.',
                ],
                [
                    'role' => 'user',
                    'content' => $prompt,
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
                model: config(
                    'gemini.model',
                    'gemini-2.5-flash'
                )
            )->generateContent($prompt);

            return $response->text();
        }

        return '';
    }
}

