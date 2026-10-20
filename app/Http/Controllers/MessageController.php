<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    private function checkConversation($conversationId)
    {
        return DB::table('conversations')->where('id', $conversationId)->where(function ($query) {
            $query->where('user_one_id', Auth::id())
                ->orWhere('user_two_id', Auth::id());
        })->exists();
    }

    public function index($conversation)
    {
        if (! $this->checkConversation($conversation)) {
            abort(404);
        }

        $message = DB::table('messages')
            ->join('users', 'users.id', '=', 'messages.sender_id')
            ->where('messages.conversation_id', $conversation)
            ->select(
                'messages.id',
                'messages.conversation_id',
                'messages.sender_id',
                'messages.type',
                'messages.body',
                'messages.created_at',
                'users.name as sender_name',
                'users.username as sender_username',
            )
            ->orderBy('messages.id')
            ->get();

        $message->transform(function ($item) {
            $item->sender_initials = strtoupper(
                substr($item->sender_name ?? 'U', 0, 2)
            );

            return $item;
        });

        return response()->json([
            'message' => $message,
        ]);
    }

    public function store(Request $request, $conversation)
    {

        $startedAt = microtime(true);

        Log::info('Chat message timing: store started', [
            'time' => $startedAt,
        ]);


        if (! $this->checkConversation($conversation)) {
            abort(404);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $messageId = DB::table('messages')->insertGetId([
            'conversation_id' => $conversation,
            'sender_id' => Auth::id(),
            'type' => 'text',
            'body' => $validated['body'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('conversations')->where('id', $conversation)
            ->update([
                'updated_at' => now(),
            ]);

        $message = DB::table('messages')
            ->join('users', 'users.id', '=', 'messages.sender_id')
            ->where('messages.id', $messageId)
            ->select(
                'messages.id',
                'messages.conversation_id',
                'messages.sender_id',
                'messages.type',
                'messages.body',
                'messages.created_at',
                'users.name as sender_name',
                'users.username as sender_username'
            )
            ->first();

        $message->sender_initials = strtoupper(
            substr($message->sender_name ?? 'U', 0, 2)
        );

        Log::info('Chat message timing: before broadcast', [
            'elapsed_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ]);

        MessageSent::dispatch((array) $message);

        Log::info('Chat message timing: after broadcast', [
            'elapsed_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ]);

        Log::info('Chat message timing: response ready', [
            'elapsed_ms' => round((microtime(true) - $startedAt) * 1000, 2),
        ]);

        return response()->json([
            'message' => $message,
        ], 201);
    }
}
