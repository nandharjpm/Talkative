<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function index(Request $request){
        $userId = Auth::id();

        $conversations = DB::table('conversations')
            ->join('users', function($join) use ($userId){
            $join->on('users.id', '=', DB::raw("case when conversations.user_one_id={$userId}
            then conversations.user_two_id else conversations.user_one_id end"));
        })
        ->where(function($query) use ($userId){
            $query->where('conversations.user_one_id', $userId)
                ->orwhere('conversations.user_two_id', $userId);
        })
        ->select(
            'conversations.id',
            'conversations.user_one_id',
            'conversations.user_two_id',
            'users.name as others_name',
            'users.username as others_user_name',
        )->orderByDesc('conversations.updated_at')
        ->get();

        return view('chat.index', compact('conversations'));
    }
}
