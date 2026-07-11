<?php

namespace App\Http\Controllers;

use Dotenv\Dotenv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AiController extends Controller
{
    public function ask(Request $request)
    {
        $validated = $request->validate([
            'question' => 'nullable|string',
            'images.*' => 'nullable|image|max:5120'
        ]);

        $question = $validated['question'] ?? '';
        $imageUrls = [];

        // 1. Process images if the user uploaded them
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('ai_images', 'public');
                $imageData = 'data:' . $img->getMimeType() . ';base64,' . base64_encode(file_get_contents($img->getRealPath()));
                $imageUrls[] = [
                    'path' => $path,
                    'mime' => $img->getMimeType(),
                    'data' => $imageData,
                ];
            }
        }

        if (file_exists(base_path('.env'))) {
            Dotenv::createMutable(base_path(), '.env')->safeLoad();
        }

        // Fetch OpenRouter configurations safely. Preserve text-only settings.
        $apiKey = env('OPENAI_API_KEY');
        $endpoint = rtrim(env('OPENAI_API_BASE_URI', 'https://openrouter.ai/api/v1'), '/') . '/chat/completions';
        $model = env('OPENAI_MODEL', 'openrouter/free');

        if (! empty($imageUrls)) {
            $apiKey = env('IMAGE_AI_API_KEY', $apiKey);
            $endpoint = rtrim(env('IMAGE_AI_ENDPOINT', env('OPENAI_API_BASE_URI', 'https://openrouter.ai/api/v1')), '/') . '/chat/completions';
            $model = env('IMAGE_AI_MODEL', 'google/gemini-3.1-flash-lite-image');
        }

        if (empty($apiKey)) {
            return response()->json(['error' => 'AI request API key not configured. Please check your OPENAI_API_KEY in .env.'], 500);
        }

        $system = "You are a helpful gardening assistant. When given images, analyze plant health, identify likely issues, suggest immediate care steps, recommended products, and next diagnostics. Keep answers concise and use bullets where appropriate.";

        // 2. Build the message history
        $messages = [
            ['role' => 'system', 'content' => $system]
        ];

        // Format payload based on whether images exist or if it's text-only
        if (! empty($imageUrls)) {
            $userContent = [
                ['type' => 'text', 'text' => $question ?: 'Analyze these images based on your instructions.']
            ];

            foreach ($imageUrls as $image) {
                $userContent[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => $image['data'], 'detail' => 'auto']
                ];
            }

            $messages[] = ['role' => 'user', 'content' => $userContent];
        } else {
            if (empty($question)) {
                return response()->json(['error' => 'Please provide a text question or upload an image.'], 400);
            }
            
            $messages[] = ['role' => 'user', 'content' => $question];
        }

        $payload = [
            'messages' => $messages,
            'max_tokens' => 800,
            'model' => $model
        ];

        try {
            $response = Http::withToken($apiKey)
                ->accept('application/json')
                ->withoutVerifying()
                ->withHeaders([
                    'HTTP-Referer' => env('APP_URL', 'http://localhost'),
                    'X-OpenRouter-Title' => 'VerdantOps Application',
                ])
                ->timeout(35)
                ->retry(2, 150)
                ->post($endpoint, $payload);
        } catch (\Throwable $e) {
            Log::error('AI request exception occurred', ['message' => $e->getMessage(), 'api_endpoint' => $endpoint, 'model' => $model]);
            return response()->json(['error' => 'Image upload failed. Unable to process the request. Please try again.'], 500);
        }

        if ($response->failed()) {
            Log::error('AI API execution error diagnostic', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return response()->json(['error' => 'AI processing failed. Check log analytics for details.'], 500);
        }

        $body = $response->json();
        $content = $body['choices'][0]['message']['content'] ?? json_encode($body);

        return response()->json(['result' => $content]);
    }
}