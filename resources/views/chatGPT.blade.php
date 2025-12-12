<!DOCTYPE html>
<html>
<head>
    <title>Laravel 12 - Integrate ChatGPT API Example</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container">
    <div class="card mt-5">

        <h3 class="card-header p-3">Laravel 12 - ChatGPT Domain Name Generator</h3>

        <div class="card-body">

            <!-- Input form for user topic -->
            <form method="GET" action="{{ route('chat-gpt.index') }}">
                <div class="form-group">
                    <label><strong>Enter Topic Title:</strong></label>
                    <input type="text" name="title" class="form-control" placeholder="eg: technology, travel">
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>

            <!-- Show ChatGPT result -->
            @if(!empty($result))
                <div class="mt-4">
                    <strong>Generated Domain Names:</strong><br>
                    {!! nl2br($result) !!}
                </div>
            @endif

        </div>
    </div>
</div>
</body>
</html>
