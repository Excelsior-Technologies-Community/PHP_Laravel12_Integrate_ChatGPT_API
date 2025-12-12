# PHP_Laravel12_Integrate_ChatGPT_API

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel">
  <img src="https://img.shields.io/badge/OpenAI-API-blue?style=for-the-badge&logo=openai">
  <img src="https://img.shields.io/badge/gpt-4o--mini-success?style=for-the-badge">
</p>

---

##  Overview  
This guide explains how to integrate **ChatGPT (OpenAI API)** into Laravel 12 using the official package:

```
openai-php/laravel
```

The user enters a topic → ChatGPT returns **5 domain name suggestions**.

---

##  Features  

###  AI Features  
- Uses official **gpt-4o-mini** model  
- High-quality, fast AI responses  
- Any prompt, any use case  
- Clean extraction using Laravel `Arr` helper  

###  Technical Features  
- Official Laravel SDK for OpenAI  
- No manual curl  
- API key stored in `.env`  
- Fully MVC structured  

###  UI Features  
- Bootstrap design  
- Clean UX  
- Instant result display  

---

##  Folder Structure  

```
app/
├── Http/
│   └── Controllers/
│       └── ChatGPTController.php

resources/
└── views/
    └── chatGPT.blade.php

routes/
└── web.php

config/
└── openai.php
```

---

#  Step 1 — Install Laravel  

```bash
composer create-project laravel/laravel example-app
cd example-app
```

---

#  Step 2 — Install OpenAI Laravel SDK  

```bash
composer require openai-php/laravel
```

Publish config:

```bash
php artisan vendor:publish --provider="OpenAI\Laravel\ServiceProvider"
```

This creates:

```
config/openai.php
```

---

#  Step 3 — Setup OPENAI_API_KEY

To connect Laravel with ChatGPT, you must add your API key.

---

### **1️⃣ Visit OpenAI Dashboard**

🔗 https://platform.openai.com  

Login with your account.

---

### **2️⃣ Create API Key**

Go to:  
🔗 https://platform.openai.com/account/api-keys  

Click: **Create new secret key**

<img width="1919" height="720" alt="1" src="https://github.com/user-attachments/assets/f0a7c1b2-11c4-44f1-9b72-6aa6dcf77a26" />

Create  secret key:-

> <img width="608" height="637" alt="Screenshot 2025-12-12 125148" src="https://github.com/user-attachments/assets/61795339-4faa-460e-8620-55d273f6f8ca" />


Copy the generated key:-

<img width="698" height="503" alt="Screenshot 2025-12-12 125206" src="https://github.com/user-attachments/assets/f66900f7-1ced-44d1-a45c-4b4f43df36c6" />

---

### **3️⃣ Add API Key to `.env`**

```
OPENAI_API_KEY=sk-your-api-key-here
```

###  Important Rules  
- No quotes (  `OPENAI_API_KEY="sk-xxx"` )  
- No extra space  
- Correct format:  
  ```
  OPENAI_API_KEY=sk-123456789abcdef
  ```

---

### **4️ Clear Configuration Cache**

```
php artisan config:clear
php artisan cache:clear
```

---

#  Step 4 — Create Route  

 `routes/web.php`

```php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatGPTController;

Route::get('/chat-gpt', [ChatGPTController::class, 'index'])
     ->name('chat-gpt.index');
```

---

#  Step 5 — Create Controller  

 `app/Http/Controllers/ChatGPTController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenAI\Laravel\Facades\OpenAI;

class ChatGPTController extends Controller
{
    public function index(Request $request)
    {
        $result = '';

        if ($request->filled('title')) {

            $messages = [
                [
                    'role' => 'user',
                    'content' => 'Suggest me 5 domain names based on topic "'
                                 . $request->title .
                                 '". Give clean list: 1. 2. 3. 4. 5.'
                ],
            ];

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => $messages,
            ]);

            $result = Arr::get($response, 'choices.0.message')['content'] ?? '';
        }

        return view('chatGPT', compact('result'));
    }
}
```

---

#  Step 6 — Create Blade View  

 `resources/views/chatGPT.blade.php`

```html
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

            <form method="GET" action="{{ route('chat-gpt.index') }}">
                <div class="form-group">
                    <label><strong>Enter Topic Title:</strong></label>
                    <input type="text" name="title" class="form-control" placeholder="eg: travel, tech" required>
                </div>

                <button type="submit" class="btn btn-success mt-3">Submit</button>
            </form>

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
```

---

#  Step 7 — Run the Application  

```bash
php artisan serve
```

Open browser:

```
http://localhost:8000/chat-gpt
```

---

#  Output Example  

<img width="1630" height="539" alt="Screenshot 2025-12-12 133204" src="https://github.com/user-attachments/assets/1b4b6f7b-aad6-4867-a4ed-4e8bccd494ee" />


<img width="1649" height="537" alt="Screenshot 2025-12-12 133238" src="https://github.com/user-attachments/assets/bdb83097-e7d4-4ef6-a10a-8c5f76f1ab1c" />

