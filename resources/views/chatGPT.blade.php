<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Laravel 12 - AI Domain Name Generator</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <style>

        body {
            background: #f5f7fb;
        }

        .main-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }

        .card-header-custom {
            background: #212529;
            color: white;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            background: white;
            padding: 20px;
            height: 100%;
        }

        .stat-number {
            font-size: 28px;
            font-weight: 700;
        }

        .domain-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            background: white;
        }

        .favorite-card {
            border-left: 4px solid #ffc107;
        }

        .history-card {
            border-left: 4px solid #0d6efd;
        }

        .search-card {
            border-left: 4px solid #198754;
        }

        .domain-text {
            font-weight: 600;
            font-size: 16px;
        }

        .section-title {
            font-weight: 700;
        }

        .result-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }

        .copy-btn {
            min-width: 75px;
        }

        .empty-box {
            padding: 25px;
            text-align: center;
            color: #6c757d;
        }

    </style>

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-11">

            <div class="card main-card shadow-sm">

                {{-- ===================================================== --}}
                {{-- HEADER --}}
                {{-- ===================================================== --}}

                <div class="card-header card-header-custom p-4">

                    <h3 class="mb-1">
                        🤖 AI Domain Name Generator
                    </h3>

                    <p class="mb-0 text-white-50">
                        Generate creative domain names using OpenAI or Gemini
                    </p>

                </div>

                <div class="card-body p-4">

                    {{-- ===================================================== --}}
                    {{-- SUCCESS / ERROR MESSAGES --}}
                    {{-- ===================================================== --}}

                    <div
                        id="copySuccessAlert"
                        class="alert alert-success alert-dismissible fade show d-none"
                        role="alert">

                        <strong id="copyAlertTitle">
                            ✅ Success!
                        </strong>

                        <span id="copySuccessMessage"></span>

                        <button
                            type="button"
                            class="btn-close"
                            onclick="hideCopyAlert()">
                        </button>

                    </div>


                    @if(session('success'))

                        <div
                            class="alert alert-success alert-dismissible fade show"
                            role="alert">

                            <strong>
                                ✅ Success!
                            </strong>

                            {{ session('success') }}

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                            </button>

                        </div>

                    @endif


                    @if(session('favorite_success'))

                        <div
                            class="alert alert-warning alert-dismissible fade show"
                            role="alert">

                            <strong>
                                ⭐ Favorite!
                            </strong>

                            {{ session('favorite_success') }}

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                            </button>

                        </div>

                    @endif


                    @if(session('history_success'))

                        <div
                            class="alert alert-info alert-dismissible fade show"
                            role="alert">

                            <strong>
                                📜 History!
                            </strong>

                            {{ session('history_success') }}

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                            </button>

                        </div>

                    @endif


                    @if($errors->any())

                        <div
                            class="alert alert-danger alert-dismissible fade show"
                            role="alert">

                            <strong>
                                ❌ Error!
                            </strong>

                            <ul class="mb-0">

                                @foreach($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert">
                            </button>

                        </div>

                    @endif


                    {{-- ===================================================== --}}
                    {{-- STATISTICS --}}
                    {{-- ===================================================== --}}

                    <div class="row g-3 mb-4">

                        <div class="col-md-4">

                            <div class="stat-card shadow-sm">

                                <div class="text-muted">
                                    🤖 Total Generations
                                </div>

                                <div class="stat-number">
                                    {{ $totalGenerations }}
                                </div>

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="stat-card shadow-sm">

                                <div class="text-muted">
                                    ⭐ Favorite Domains
                                </div>

                                <div class="stat-number">
                                    {{ $totalFavorites }}
                                </div>

                            </div>

                        </div>


                        <div class="col-md-4">

                            <div class="stat-card shadow-sm">

                                <div class="text-muted">
                                    🌐 Domains Generated
                                </div>

                                <div class="stat-number">
                                    {{ $totalDomainsGenerated }}
                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- GENERATOR FORM --}}
                    {{-- ===================================================== --}}

                    <form
                        method="GET"
                        action="{{ route('chat-gpt.index') }}">

                        {{-- Provider --}}

                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Select AI Provider
                            </label>

                            <select
                                name="provider"
                                class="form-select form-select-lg">

                                <option
                                    value="openai"
                                    {{ $provider == 'openai' ? 'selected' : '' }}>

                                    🤖 OpenAI

                                </option>

                                <option
                                    value="gemini"
                                    {{ $provider == 'gemini' ? 'selected' : '' }}>

                                    ✨ Gemini

                                </option>

                            </select>

                        </div>


                        {{-- Topic --}}

                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Enter Topic
                            </label>

                            <input
                                type="text"
                                name="title"
                                value="{{ $topic }}"
                                class="form-control form-control-lg"
                                placeholder="Example: technology, travel, fitness"
                                required>

                        </div>


                        <input
                            type="hidden"
                            name="generate"
                            value="1">


                        <button
                            type="submit"
                            class="btn btn-success btn-lg">

                            ✨ Generate Domains

                        </button>

                    </form>


                    {{-- ===================================================== --}}
                    {{-- GENERATED RESULT --}}
                    {{-- ===================================================== --}}

                    @if(!empty($result))

                        <hr class="my-4">


                        <div
                            class="d-flex justify-content-between align-items-center mb-3">

                            <h4 class="section-title mb-0">
                                🌐 Generated Domain Names
                            </h4>


                            <form
                                method="POST"
                                action="{{ route('chat-gpt.regenerate') }}">

                                @csrf

                                <input
                                    type="hidden"
                                    name="title"
                                    value="{{ $topic }}">

                                <input
                                    type="hidden"
                                    name="provider"
                                    value="{{ $provider }}">


                                <button
                                    type="submit"
                                    class="btn btn-primary">

                                    🔄 Regenerate

                                </button>

                            </form>

                        </div>


                        {{-- Search current generated domains --}}

                        <div class="mb-3">

                            <input
                                type="text"
                                id="domainSearch"
                                class="form-control"
                                placeholder="🔎 Search generated domains..."
                                onkeyup="searchDomains()">

                        </div>


                        <div class="result-box">

                            @php

                                $domains = preg_split(
                                    '/\r\n|\r|\n/',
                                    trim($result)
                                );

                            @endphp


                            @foreach($domains as $domain)

                                @php

                                    $cleanDomain = trim(
                                        preg_replace(
                                            '/^\s*\**\s*\d+[\.\)\-\:]\s*\**/',
                                            '',
                                            $domain
                                        )
                                    );

                                @endphp


                                @if(!empty($cleanDomain))

                                    <div
                                        class="domain-card generated-domain"
                                        data-domain="{{ strtolower($cleanDomain) }}">

                                        <div class="row align-items-center">

                                            <div class="col-md-7">

                                                <span class="domain-text">
                                                    {{ $cleanDomain }}
                                                </span>

                                            </div>


                                            <div
                                                class="col-md-5 text-md-end mt-2 mt-md-0">

                                                {{-- COPY --}}

                                                <button
                                                    type="button"
                                                    class="btn btn-outline-secondary btn-sm copy-btn"
                                                    onclick="copyDomain(@js($cleanDomain), this)">

                                                    📋 Copy

                                                </button>


                                                {{-- FAVORITE --}}

                                                <form
                                                    method="POST"
                                                    action="{{ route('chat-gpt.favorite') }}"
                                                    class="d-inline">

                                                    @csrf

                                                    <input
                                                        type="hidden"
                                                        name="topic"
                                                        value="{{ $topic }}">

                                                    <input
                                                        type="hidden"
                                                        name="domain"
                                                        value="{{ $cleanDomain }}">

                                                    <input
                                                        type="hidden"
                                                        name="provider"
                                                        value="{{ $provider }}">


                                                    <button
                                                        type="submit"
                                                        class="btn btn-outline-warning btn-sm">

                                                        ⭐ Favorite

                                                    </button>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                @endif

                            @endforeach

                        </div>

                    @endif


                    {{-- ===================================================== --}}
                    {{-- SEARCH ALL GENERATED DOMAINS --}}
                    {{-- ===================================================== --}}

                    <hr class="my-5">


                    <h4 class="section-title mb-3">
                        🔎 Search All Generated Domains
                    </h4>


                    <form
                        method="GET"
                        action="{{ route('chat-gpt.index') }}"
                        class="row g-2 mb-3">

                        <div class="col-md-8">

                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                class="form-control"
                                placeholder="Search domain name...">

                        </div>


                        <div class="col-md-4">

                            <button
                                type="submit"
                                class="btn btn-success">

                                🔎 Search

                            </button>


                            <a
                                href="{{ route('chat-gpt.index') }}"
                                class="btn btn-secondary">

                                Clear

                            </a>

                        </div>

                    </form>


                    @if($search !== '')

                        <div class="result-box">

                            @if(count($searchResults) > 0)

                                <div class="mb-3">

                                    <strong>
                                        {{ count($searchResults) }}
                                    </strong>

                                    matching domain(s) found.

                                </div>


                                @foreach($searchResults as $item)

                                    <div class="domain-card search-card">

                                        <div
                                            class="d-flex justify-content-between align-items-center">

                                            <div>

                                                <div class="domain-text">
                                                    {{ $item['domain'] }}
                                                </div>

                                                <small class="text-muted">

                                                    Topic:
                                                    {{ $item['topic'] }}

                                                </small>

                                            </div>


                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary btn-sm"
                                                onclick="copyDomain(@js($item['domain']), this)">

                                                📋 Copy

                                            </button>

                                        </div>

                                    </div>

                                @endforeach

                            @else

                                <div class="empty-box">

                                    No matching domains found.

                                </div>

                            @endif

                        </div>

                    @endif


                    {{-- ===================================================== --}}
                    {{-- FAVORITES --}}
                    {{-- ===================================================== --}}

                    @if($favorites->count() > 0)

                        <hr class="my-5">


                        <div
                            class="d-flex justify-content-between align-items-center mb-3">

                            <h4 class="section-title mb-0">
                                ⭐ Favorite Domains
                            </h4>


                            <form
                                method="POST"
                                action="{{ route('chat-gpt.favorites.clear') }}"
                                onsubmit="return confirm('Delete all favorite domains?')">

                                @csrf

                                @method('DELETE')


                                <button
                                    type="submit"
                                    class="btn btn-danger btn-sm">

                                    🗑️ Clear All

                                </button>

                            </form>

                        </div>


                        <div class="row">

                            @foreach($favorites as $favorite)

                                <div class="col-md-6 mb-3">

                                    <div class="domain-card favorite-card">

                                        <div
                                            class="d-flex justify-content-between align-items-center">

                                            <div>

                                                <div class="domain-text">
                                                    {{ $favorite->domain }}
                                                </div>

                                                <small class="text-muted">

                                                    Topic:
                                                    {{ $favorite->topic }}

                                                </small>

                                            </div>


                                            <div>

                                                {{-- COPY --}}

                                                <button
                                                    type="button"
                                                    class="btn btn-outline-secondary btn-sm"
                                                    onclick="copyDomain(@js($favorite->domain), this)">

                                                    📋

                                                </button>


                                                {{-- DELETE --}}

                                                <form
                                                    method="POST"
                                                    action="{{ route('chat-gpt.favorite.delete', $favorite->id) }}"
                                                    class="d-inline">

                                                    @csrf

                                                    @method('DELETE')


                                                    <button
                                                        type="submit"
                                                        class="btn btn-outline-danger btn-sm">

                                                        🗑️

                                                    </button>

                                                </form>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif


                    {{-- ===================================================== --}}
                    {{-- GENERATION HISTORY --}}
                    {{-- ===================================================== --}}

                    @if($history->count() > 0)

                        <hr class="my-5">


                        <div
                            class="d-flex justify-content-between align-items-center mb-3">

                            <h4 class="section-title mb-0">
                                📜 Generation History
                            </h4>


                            <div>

                                {{-- CSV --}}

                                <a
                                    href="{{ route('chat-gpt.history.export') }}"
                                    class="btn btn-success btn-sm">

                                    📥 Export CSV

                                </a>


                                {{-- CLEAR HISTORY --}}

                                <form
                                    method="POST"
                                    action="{{ route('chat-gpt.history.clear') }}"
                                    class="d-inline"
                                    onsubmit="return confirm('Delete all generation history?')">

                                    @csrf

                                    @method('DELETE')


                                    <button
                                        type="submit"
                                        class="btn btn-danger btn-sm">

                                        🗑️ Clear All

                                    </button>

                                </form>

                            </div>

                        </div>


                        {{-- SORTING --}}

                        <form
                            method="GET"
                            action="{{ route('chat-gpt.index') }}"
                            class="mb-3">

                            <input
                                type="hidden"
                                name="title"
                                value="{{ $topic }}">

                            <input
                                type="hidden"
                                name="provider"
                                value="{{ $provider }}">


                            <label class="form-label fw-bold">
                                Sort History
                            </label>


                            <select
                                name="sort"
                                class="form-select"
                                onchange="this.form.submit()">

                                <option
                                    value="newest"
                                    {{ $sort == 'newest' ? 'selected' : '' }}>

                                    Newest First

                                </option>


                                <option
                                    value="oldest"
                                    {{ $sort == 'oldest' ? 'selected' : '' }}>

                                    Oldest First

                                </option>

                            </select>

                        </form>


                        {{-- HISTORY ITEMS --}}

                        @foreach($history as $item)

                            <div class="card history-card mb-3">

                                <div class="card-body">

                                    <div
                                        class="d-flex justify-content-between align-items-start">

                                        <div>

                                            <h5 class="mb-1">
                                                {{ $item->topic }}
                                            </h5>


                                            <small class="text-muted">

                                                {{ $item->created_at->format('d M Y, h:i A') }}

                                            </small>

                                        </div>


                                        {{-- DELETE ONE HISTORY --}}

                                        <form
                                            method="POST"
                                            action="{{ route('chat-gpt.history.delete', $item->id) }}"
                                            onsubmit="return confirm('Delete this history record?')">

                                            @csrf

                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="btn btn-outline-danger btn-sm">

                                                🗑️ Delete

                                            </button>

                                        </form>

                                    </div>


                                    <div class="mt-3">

                                        @php

                                            $historyDomains = preg_split(
                                                '/\r\n|\r|\n/',
                                                trim($item->result)
                                            );

                                        @endphp


                                        @foreach($historyDomains as $historyDomain)

                                            @php

                                                $cleanHistoryDomain = trim(
                                                    preg_replace(
                                                        '/^\s*\**\s*\d+[\.\)\-\:]\s*\**/',
                                                        '',
                                                        $historyDomain
                                                    )
                                                );

                                            @endphp


                                            @if(!empty($cleanHistoryDomain))

                                                <div class="domain-card">

                                                    <div
                                                        class="d-flex justify-content-between align-items-center">

                                                        <span class="domain-text">

                                                            {{ $cleanHistoryDomain }}

                                                        </span>


                                                        <button
                                                            type="button"
                                                            class="btn btn-outline-secondary btn-sm"
                                                            onclick="copyDomain(@js($cleanHistoryDomain), this)">

                                                            📋 Copy

                                                        </button>

                                                    </div>

                                                </div>

                                            @endif

                                        @endforeach

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    @endif

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>

    /*
    |--------------------------------------------------------------------------
    | Search current generated domains
    |--------------------------------------------------------------------------
    */

    function searchDomains() {

        const input =
            document.getElementById('domainSearch');

        if (!input) {
            return;
        }

        const search =
            input.value.toLowerCase().trim();

        const domains =
            document.querySelectorAll('.generated-domain');

        domains.forEach(function(domain) {

            const text =
                domain.getAttribute('data-domain') || '';

            if (text.includes(search)) {

                domain.style.display = '';

            } else {

                domain.style.display = 'none';

            }

        });
    }


    /*
    |--------------------------------------------------------------------------
    | Copy domain
    |--------------------------------------------------------------------------
    */

    function copyDomain(domain, button) {

        if (!navigator.clipboard) {

            showCopyError(
                'Clipboard is not supported by this browser.'
            );

            return;
        }


        navigator.clipboard
            .writeText(domain)

            .then(function() {

                const oldText =
                    button.innerHTML;

                button.innerHTML =
                    '✅ Copied';


                showCopySuccess(
                    'Domain "' +
                    domain +
                    '" copied successfully!'
                );


                setTimeout(function() {

                    button.innerHTML =
                        oldText;

                }, 1500);

            })

            .catch(function() {

                showCopyError(
                    'Unable to copy domain.'
                );

            });
    }


    /*
    |--------------------------------------------------------------------------
    | Show copy success
    |--------------------------------------------------------------------------
    */

    function showCopySuccess(message) {

        const alert =
            document.getElementById(
                'copySuccessAlert'
            );

        const title =
            document.getElementById(
                'copyAlertTitle'
            );

        const messageElement =
            document.getElementById(
                'copySuccessMessage'
            );


        alert.classList.remove(
            'd-none',
            'alert-danger'
        );

        alert.classList.add(
            'alert-success'
        );


        title.innerHTML =
            '✅ Success!';


        messageElement.innerText =
            ' ' + message;


        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });


        setTimeout(function() {

            hideCopyAlert();

        }, 3000);
    }


    /*
    |--------------------------------------------------------------------------
    | Show copy error
    |--------------------------------------------------------------------------
    */

    function showCopyError(message) {

        const alert =
            document.getElementById(
                'copySuccessAlert'
            );

        const title =
            document.getElementById(
                'copyAlertTitle'
            );

        const messageElement =
            document.getElementById(
                'copySuccessMessage'
            );


        alert.classList.remove(
            'd-none',
            'alert-success'
        );

        alert.classList.add(
            'alert-danger'
        );


        title.innerHTML =
            '❌ Error!';


        messageElement.innerText =
            ' ' + message;


        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });


        setTimeout(function() {

            hideCopyAlert();

        }, 3000);
    }


    /*
    |--------------------------------------------------------------------------
    | Hide copy alert
    |--------------------------------------------------------------------------
    */

    function hideCopyAlert() {

        const alert =
            document.getElementById(
                'copySuccessAlert'
            );

        alert.classList.add(
            'd-none'
        );

    }

</script>

</body>

</html>

