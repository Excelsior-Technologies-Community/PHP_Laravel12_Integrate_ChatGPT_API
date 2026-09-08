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

<<<<<<< HEAD
        /*
        |--------------------------------------------------------------------------
        | Favorites
        |--------------------------------------------------------------------------
        */
        $favorites = FavoriteDomain::latest()->get();
=======
        $search = $request->get('search', '');

        $historyQuery = GenerationHistory::query();

        if ($search !== '') {
            $historyQuery->where('topic', 'like', '%' . $search . '%');
        }

        $history = $historyQuery->latest()->paginate(10, ['*'], 'history_page');
        $favorites = FavoriteDomain::latest()->paginate(10, ['*'], 'favorites_page');
>>>>>>> 0b3e60cef0697046178ba7fd98fa8b4c9e69de3e

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
            'search',
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
                'search' => request('search', ''),
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

<<<<<<< HEAD
        return back()->with(
            'favorite_success',
            'Domain saved to favorites successfully!'
        );
=======
        return redirect()
            ->route('chat-gpt.index', [
                'title' => $request->topic,
                'provider' => $request->provider ?? 'openai',
                'search' => request('search', ''),
            ])
            ->with('favorite_success', 'Domain saved to favorites!');
>>>>>>> 0b3e60cef0697046178ba7fd98fa8b4c9e69de3e
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
<<<<<<< HEAD
    public function clearHistory()
=======
    public function clearHistory(Request $request)
>>>>>>> 0b3e60cef0697046178ba7fd98fa8b4c9e69de3e
    {
        GenerationHistory::query()->delete();

        return back()->with(
            'history_success',
<<<<<<< HEAD
            'All generation history has been deleted successfully!'
=======
            'All generation history cleared!'
>>>>>>> 0b3e60cef0697046178ba7fd98fa8b4c9e69de3e
        );
    }

    /**
<<<<<<< HEAD
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
=======
     * Clear all favorite domains.
     */
    public function clearFavorites(Request $request)
    {
        FavoriteDomain::query()->delete();

        return back()->with(
            'favorite_success',
            'All favorites cleared!'
>>>>>>> 0b3e60cef0697046178ba7fd98fa8b4c9e69de3e
        );
    }

    /**
<<<<<<< HEAD
     * Generate domain names using selected AI provider.
=======
     * Generate domain names using the selected AI provider.
>>>>>>> 0b3e60cef0697046178ba7fd98fa8b4c9e69de3e
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

<<<<<<< HEAD
            $response = Gemini::generativeModel(
                model: config(
                    'gemini.model',
                    'gemini-2.5-flash'
                )
            )->generateContent($prompt);
=======
            try {
>>>>>>> 0b3e60cef0697046178ba7fd98fa8b4c9e69de3e

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

