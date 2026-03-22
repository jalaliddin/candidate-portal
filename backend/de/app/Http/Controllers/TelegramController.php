<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    public function webhook(Request $request): JsonResponse
    {
        $update = $request->all();

        $message = $update['message'] ?? $update['edited_message'] ?? null;

        if (! $message) {
            return response()->json(['ok' => true]);
        }

        $chatType = $message['chat']['type'] ?? 'private';
        $chatId = $message['chat']['id'];
        $messageId = $message['message_id'];
        $text = $message['text'] ?? null;

        // Only respond in groups
        if (! in_array($chatType, ['group', 'supergroup'], true)) {
            return response()->json(['ok' => true]);
        }

        // Skip commands and empty messages
        if (! $text || str_starts_with($text, '/')) {
            return response()->json(['ok' => true]);
        }

        // Optional: restrict to a specific group
        $allowedGroupId = config('services.telegram.group_id');
        if ($allowedGroupId && (string) $chatId !== (string) $allowedGroupId) {
            return response()->json(['ok' => true]);
        }

        $faqs = Faq::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['question', 'answer', 'category']);

        if ($faqs->isEmpty()) {
            return response()->json(['ok' => true]);
        }

        $reply = $this->askOpenAI($text, $faqs);

        if ($reply) {
            $this->sendTelegramMessage($chatId, $reply, $messageId);
        }

        return response()->json(['ok' => true]);
    }

    private function askOpenAI(string $userMessage, $faqs): ?string
    {
        $faqList = $faqs
            ->map(fn ($faq) => ($faq->category ? "[{$faq->category}]\n" : '')."Q: {$faq->question}\nA: {$faq->answer}")
            ->join("\n\n");

        $systemPrompt = <<<PROMPT
You are a helpful assistant for a German healthcare recruitment company's candidate portal.
Answer questions based ONLY on the following FAQ list.
If the question is not covered by the FAQ, politely say you do not have that information and suggest contacting the support team directly.
Keep responses concise and friendly. Respond in the same language the user wrote in (German or English).

FAQ:
{$faqList}
PROMPT;

        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => config('services.openai.model', 'gpt-4o-mini'),
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'max_tokens'  => 500,
                    'temperature' => 0.3,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('OpenAI error', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Exception $e) {
            Log::error('OpenAI exception', ['message' => $e->getMessage()]);
        }

        return null;
    }

    private function sendTelegramMessage(int|string $chatId, string $text, int $replyToMessageId): void
    {
        try {
            Http::timeout(10)->post(
                'https://api.telegram.org/bot'.config('services.telegram.bot_token').'/sendMessage',
                [
                    'chat_id'              => $chatId,
                    'text'                 => $text,
                    'reply_to_message_id'  => $replyToMessageId,
                    'parse_mode'           => 'HTML',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Telegram send error', ['message' => $e->getMessage()]);
        }
    }
}
