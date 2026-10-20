<?php

namespace App\Http\Controllers;

use App\Models\TalkiMessage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TalkiController extends Controller
{
    public function history(Request $request)
    {
        $messages = TalkiMessage::where('user_id', Auth::user()->id)->latest('id')
            ->take(30)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'created_at' => $message->created_at,
                ];
            });
        return response()->json([
            'messages' => $messages
        ]);
    }


    public function send(Request $request)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $userMessage = trim($validated['message']);

        if ($userMessage === '') {
            return response()->json([
                'message' => 'Please enter a message.',
            ], 422);
        }

        $history = TalkiMessage::where('user_id', $user->id)
            ->latest('id')
            ->take(12)
            ->get()
            ->reverse()
            ->values();

        $messages = [
            [
                'role' => 'system',
                'content' => implode("\n", [
                    'You are Talki, a friendly AI companion inside Talkative.',
                    'Reply in the same language the user uses.',
                    'Understand natural language, slang, and mixed languages such as Tanglish.',
                    'Adapt your tone to the user’s apparent mood without assuming you know their feelings.',
                    'If they seem sad, respond gently. If excited, share their enthusiasm.',
                    'If angry, remain calm and listen without judging.',
                    'Be warm, natural, and conversational rather than robotic.',
                    'Ask thoughtful follow-up questions when appropriate.',
                    'Keep replies concise unless the user asks for detail.',
                    'Never claim to be human.',
                ]),
            ],
        ];

        foreach ($history as $item) {
            $messages[] = [
                'role' => $item->role,
                'content' => $item->content,
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        try {
            $response = Http::withToken(config('services.groq.key'))
                ->acceptJson()
                ->timeout(45)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model', 'openai/gpt-oss-20b'),
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_completion_tokens' => 600,
                ]);

            if (!$response->successful()) {
                Log::warning('Talki API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'user_id' => $user->id,
                ]);

                return response()->json([
                    'message' => 'Talki is unavailable right now. Please try again shortly.',
                ], 502);
            }

            $reply = trim(
                (string) $response->json('choices.0.message.content')
            );

            if ($reply === '') {
                Log::warning('Talki returned an empty reply', [
                    'user_id' => $user->id,
                ]);

                return response()->json([
                    'message' => 'Talki could not generate a reply. Please try again.',
                ], 502);
            }

            TalkiMessage::create([
                'user_id' => $user->id,
                'role' => 'user',
                'content' => $userMessage,
            ]);

            $savedReply = TalkiMessage::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'content' => $reply,
            ]);

            return response()->json([
                'message' => [
                    'id' => $savedReply->id,
                    'role' => 'assistant',
                    'content' => $reply,
                    'created_at' => $savedReply->created_at,
                ],
            ]);
        } catch (\Exception $ex) {
            Log::error('Talki request exception', [
                'user_id' => $user->id,
                'error' => $ex->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not connect to Talki. Please try again.',
            ], 502);
        }
    }
}
