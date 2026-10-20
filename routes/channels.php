<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

Broadcast::channel('conversation.{conversationId}', function($user, $conversationId){
    return DB::table('conversations')->where('id', $conversationId)
            ->where(function ($query) use ($user){
                $query->where('user_one_id', $user->id)
                    ->orWhere('user_two_id', $user->id);
            })->exists();
});


