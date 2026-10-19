<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CallController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'integer', 'exists:conversations,id'],
            'type' => ['required', 'in:audio,video'],
        ]);

        $conversation = DB::table('conversations')
            ->where('id', $validated['conversation_id'])
            ->where(function ($query) {
                $query->where('user_one_id', Auth::id())
                    ->orWhere('user_two_id', Auth::id());
            })
            ->first();

        if (!$conversation) {
            abort(404);
        }

        $receiverId = (int) $conversation->user_one_id === (int) Auth::id()
            ? $conversation->user_two_id
            : $conversation->user_one_id;

        $callId = DB::table('calls')->insertGetId([
            'conversation_id' => $conversation->id,
            'caller_id' => Auth::id(),
            'receiver_id' => $receiverId,
            'type' => $validated['type'],
            'status' => 'initiated',
            'started_at' => now(),
            'ended_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Call record created.',
            'call_id' => $callId,
        ], 201);
    }
}