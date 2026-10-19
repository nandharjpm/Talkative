<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatRequestController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $type = $request->input('type', 'incoming');

        $incomingCount = DB::table('chat_requests')
            ->where('receiver_id', $userId)
            ->where('status', config('constants.PENDING'))
            ->count();

        $sentCount = DB::table('chat_requests')
            ->where('sender_id', $userId)
            ->where('status', config('constants.PENDING'))
            ->count();

        $query = DB::table('chat_requests as cr')
            ->join('users as u', function ($join) use ($type) {
                if ($type === 'sent') {
                    $join->on('u.id', '=', 'cr.receiver_id');
                } else {
                    $join->on('u.id', '=', 'cr.sender_id');
                }
            });

        if ($type === 'sent') {
            $query->where('cr.sender_id', $userId);
        } else {
            $query->where('cr.receiver_id', $userId);
        }

        $requests = $query
            ->where('cr.status', config('constants.PENDING'))
            ->select(
                'cr.id',
                'cr.sender_id',
                'cr.receiver_id',
                'cr.status',
                'cr.created_at',
                'u.name',
                'u.username'
            )
            ->orderByDesc('cr.created_at')
            ->get();

        return response()->json([
            'requests' => $requests,
            'incoming_count' => $incomingCount,
            'sent_count' => $sentCount,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiverId' => ['required', 'integer'],
        ]);

        $senderId = Auth::id();
        $receiverId = $validated['receiverId'];

        if ($senderId == $receiverId) {
            return response()->json([
                'message' => "Can't Request for Yourself",
            ], 422);
        }

        $conversationExist = DB::table('conversations')->where(function ($query) use ($senderId, $receiverId) {
            $query->where('user_one_id', $senderId)
                ->where('user_two_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('user_one_id', $receiverId)
                ->where('user_two_id', $senderId);
        })->exists();

        if ($conversationExist) {
            return response()->json([
                'message' => 'You already have a conversation with this user.',
            ], 422);
        }

        $chatRequest = DB::table('chat_requests')
            ->where('status', config('constants.PENDING'))
            ->where(function ($query) use ($senderId, $receiverId) {
                $query->where(function ($query) use ($senderId, $receiverId) {
                    $query->where('sender_id', $senderId)
                        ->where('receiver_id', $receiverId);
                })
                    ->orWhere(function ($query) use ($senderId, $receiverId) {
                        $query->where('sender_id', $receiverId)
                            ->where('receiver_id', $senderId);
                    });
            })
            ->exists();

        if ($chatRequest) {
            return response()->json([
                'message' => 'This request is already pending',
            ], 422);
        }

        DB::table('chat_requests')->insert([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'status' => config('constants.PENDING'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Request Sent',
        ], 201);
    }

    public function accept($chatRequest)
    {
        $friendRequest = DB::table('chat_requests')
            ->where('id', $chatRequest)
            ->where('receiver_id', Auth::id())
            ->where('status', config('constants.PENDING'))
            ->lockForUpdate()
            ->first();

        if (!$friendRequest) {
            return response()->json([
                'message' => 'No Friend Request Found',
            ], 404);
        }

        $userOne = min($friendRequest->sender_id, $friendRequest->receiver_id);
        $userTwo = max($friendRequest->sender_id, $friendRequest->receiver_id);

        $conversation = DB::table('conversations')->where('user_one_id', $userOne)->where('user_two_id', $userTwo)->first();

        // dd($friendRequest, $conversation);

        if (!$conversation) {
            $conversationId = DB::table('conversations')->insertGetId([
                'user_one_id' => $userOne,
                'user_two_id' => $userTwo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $conversationId = $conversation->id;
        }

        DB::table('chat_requests')->where('id', $chatRequest)->update([
            'status' => config('constants.ACCEPTED'),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Request Accepted',
            'conversation_id' => $conversationId,
        ]);
    }

    public function reject($chatRequest)
    {
        $rejectRequest = DB::table('chat_requests')
            ->where('id', $chatRequest)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->update([
                'status' => 'rejected',
                'updated_at' => now(),
            ]);

        if (! $rejectRequest) {
            return response()->json([
                'message' => 'Request Not Found',
            ], 404);
        }

        return response()->json([
            'message' => 'Request Rejected',
        ]);
    }
}
