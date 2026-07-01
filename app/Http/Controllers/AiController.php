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

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('ai_images', 'public');
                $imageUrls[] = asset('storage/' . $path);
            }
        }

        if (file_exists(base_path('.env'))) {
            Dotenv::createMutable(base_path(), '.env')->safeLoad();
        }

        $openAiApiKey = config('services.openai.key') ?? env('OPENAI_API_KEY');
        $deepseekApiKey = config('services.deepseek.key') ?? env('DEEPSEEK_API_KEY');

        if (!empty($openAiApiKey)) {
            $apiKey = $openAiApiKey;
            $endpoint = rtrim(config('services.openai.base_uri', env('OPENAI_API_BASE_URI', 'https://api.openai.com/v1')), '/') . '/chat/completions';
            $model = env('OPENAI_MODEL', 'gpt-4o-mini');
        } elseif (!empty($deepseekApiKey)) {
            $apiKey = $deepseekApiKey;
            $endpoint = rtrim(config('services.deepseek.base_uri', env('DEEPSEEK_API_BASE_URI', 'https://api.deepseek.ai/v1')), '/') . '/chat/completions';
            $model = env('DEEPSEEK_MODEL', 'gpt-4o-mini');
        } else {
            return response()->json(['error' => 'AI request API key not configured. Set OPENAI_API_KEY or DEEPSEEK_API_KEY in .env'], 500);
        }

        $system = "You are a helpful gardening assistant. When given images, analyze plant health, identify likely issues, suggest immediate care steps, recommended products, and next diagnostics. Keep answers concise and use bullets where appropriate.";

        $userPrompt = $question;
        if (!empty($imageUrls)) {
            $userPrompt .= "\n\nImages:\n" . implode("\n", $imageUrls);
        }

        $payload = [
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'max_tokens' => 800,
        ];

        if (!empty($model)) {
            $payload['model'] = $model;
        }

        try {
            $response = Http::withToken($apiKey)
                ->accept('application/json')
                ->timeout(15)
                ->retry(2, 100)
                ->post($endpoint, $payload);
        } catch (\Throwable $e) {
            Log::error('AI request exception', [
                'message' => $e->getMessage(),
                'endpoint' => $endpoint,
            ]);

            return response()->json(['error' => 'AI request failed. Please try again later.'], 500);
        }

        if ($response->failed()) {
            $status = $response->status();
            $bodyText = $response->body();
            Log::error('AI response failed', [
                'status' => $status,
                'body' => $bodyText,
                'endpoint' => $endpoint,
            ]);

            if ($status === 401) {
                return response()->json(['error' => 'AI authentication failed. Check API key configuration.'], 500);
            }

            return response()->json(['error' => 'AI request failed. Please try again later.'], 500);
        }

        $body = $response->json();

        // Try multiple possible response locations
        $content = null;
        if (isset($body['choices'][0]['message']['content'])) {
            $content = $body['choices'][0]['message']['content'];
        } elseif (isset($body['choices'][0]['text'])) {
            $content = $body['choices'][0]['text'];
        } elseif (isset($body['result'])) {
            $content = $body['result'];
        } else {
            $content = json_encode($body);
        }

        return response()->json(['result' => $content]);
    }
}

