<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laravel 12 - AI Domain Name Generator</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

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

    </style>

</head>

<body>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-10">

            {{-- Main Generator Card --}}
            <div class="card main-card shadow-sm">

                {{-- Header --}}
                <div class="card-header card-header-custom p-4">

                    <h3 class="mb-1">
                        🤖 AI Domain Name Generator
                    </h3>

                    <p class="mb-0 text-white-50">
                        Generate creative domain names using OpenAI or Gemini
                    </p>

                </div>

                <div class="card-body p-4">

                    {{-- Success Messages --}}

                    @if(session('success'))

                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>

                    @endif


                    @if(session('favorite_success'))

                        <div class="alert alert-warning">
                            {{ session('favorite_success') }}
                        </div>

                    @endif


                    @if(session('history_success'))

                        <div class="alert alert-info">
                            {{ session('history_success') }}
                        </div>

                    @endif


                    {{-- Validation Errors --}}

                    @if($errors->any())

                        <div class="alert alert-danger">

                            <ul class="mb-0">

                                @foreach($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    @endif


                    {{-- ===================================================== --}}
                    {{-- Generator Form --}}
                    {{-- ===================================================== --}}

                    <form
                        method="GET"
                        action="{{ route('chat-gpt.index') }}"
                    >

                        {{-- AI Provider --}}

                        <div class="mb-3">

                            <label class="form-label fw-bold">
                                Select AI Provider
                            </label>

                            <select
                                name="provider"
                                class="form-select form-select-lg"
                            >

                                <option
                                    value="openai"
                                    {{ $provider === 'openai' ? 'selected' : '' }}
                                >
                                    🤖 OpenAI
                                </option>

                                <option
                                    value="gemini"
                                    {{ $provider === 'gemini' ? 'selected' : '' }}
                                >
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
                                required
                            >

                        </div>


                        {{-- Explicit generation flag --}}
                        <input
                            type="hidden"
                            name="generate"
                            value="1"
                        >


                        {{-- Generate Button --}}

                        <button
                            type="submit"
                            class="btn btn-success btn-lg"
                        >
                            ✨ Generate Domains
                        </button>

                    </form>


                    {{-- ===================================================== --}}
                    {{-- Generated Result --}}
                    {{-- ===================================================== --}}

                    @if(!empty($result))

                        <hr class="my-4">


                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <h4 class="section-title mb-0">
                                🌐 Generated Domain Names
                            </h4>


                            {{-- Regenerate --}}

                            <form
                                method="POST"
                                action="{{ route('chat-gpt.regenerate') }}"
                            >

                                @csrf

                                <input
                                    type="hidden"
                                    name="title"
                                    value="{{ $topic }}"
                                >

                                <input
                                    type="hidden"
                                    name="provider"
                                    value="{{ $provider }}"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    🔄 Regenerate
                                </button>

                            </form>

                        </div>


                        {{-- Result Box --}}

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
                                            '/^\s*\*?\s*\d+[\.\)\-\:]\s*/',
                                            '',
                                            $domain
                                        )
                                    );

                                @endphp


                                @if(!empty($cleanDomain))

                                    <div class="domain-card">

                                        <div class="row align-items-center">

                                            {{-- Domain Name --}}

                                            <div class="col-md-8">

                                                <span class="domain-text">
                                                    {{ $cleanDomain }}
                                                </span>

                                            </div>


                                            {{-- Favorite Button --}}

                                            <div class="col-md-4 text-md-end mt-2 mt-md-0">

                                                <form
                                                    method="POST"
                                                    action="{{ route('chat-gpt.favorite') }}"
                                                    class="d-inline"
                                                >

                                                    @csrf


                                                    <input
                                                        type="hidden"
                                                        name="topic"
                                                        value="{{ $topic }}"
                                                    >


                                                    <input
                                                        type="hidden"
                                                        name="domain"
                                                        value="{{ $cleanDomain }}"
                                                    >


                                                    {{-- Preserve selected provider --}}

                                                    <input
                                                        type="hidden"
                                                        name="provider"
                                                        value="{{ $provider }}"
                                                    >


                                                    <button
                                                        type="submit"
                                                        class="btn btn-outline-warning btn-sm"
                                                    >
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
                    {{-- Favorite Domains --}}
                    {{-- ===================================================== --}}

                    @if($favorites->count() > 0)

                        <hr class="my-5">


                        <h4 class="section-title mb-3">
                            ⭐ Favorite Domains
                        </h4>


                        <div class="row">

                            @foreach($favorites as $favorite)

                                <div class="col-md-6 mb-3">

                                    <div class="domain-card favorite-card">

                                        <div class="d-flex justify-content-between align-items-center">

                                            <div>

                                                <div class="domain-text">
                                                    {{ $favorite->domain }}
                                                </div>

                                                <small class="text-muted">
                                                    Topic: {{ $favorite->topic }}
                                                </small>

                                            </div>


                                            {{-- Delete Favorite --}}

                                            <form
                                                method="POST"
                                                action="{{ route('chat-gpt.favorite.delete', $favorite->id) }}"
                                            >

                                                @csrf

                                                @method('DELETE')


                                                <button
                                                    type="submit"
                                                    class="btn btn-outline-danger btn-sm"
                                                >
                                                    🗑️
                                                </button>

                                            </form>

                                        </div>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @endif


                    {{-- ===================================================== --}}
                    {{-- Generation History --}}
                    {{-- ===================================================== --}}

                    @if($history->count() > 0)

                        <hr class="my-5">


                        <h4 class="section-title mb-3">
                            📜 Generation History
                        </h4>


                        @foreach($history as $item)

                            <div class="card history-card mb-3">

                                <div class="card-body">

                                    <div class="d-flex justify-content-between align-items-start">

                                        {{-- History Information --}}

                                        <div>

                                            <h5 class="mb-1">
                                                {{ $item->topic }}
                                            </h5>

                                            <small class="text-muted">
                                                {{ $item->created_at->format('d M Y, h:i A') }}
                                            </small>

                                        </div>


                                        {{-- Delete History --}}

                                        <form
                                            method="POST"
                                            action="{{ route('chat-gpt.history.delete', $item->id) }}"
                                        >

                                            @csrf

                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="btn btn-outline-danger btn-sm"
                                            >
                                                🗑️ Delete
                                            </button>

                                        </form>

                                    </div>


                                    {{-- Generated Result --}}

                                    <div class="mt-3">

                                        {!! nl2br(e($item->result)) !!}

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

</body>

</html>