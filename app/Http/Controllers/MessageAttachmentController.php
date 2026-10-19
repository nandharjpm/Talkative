<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MessageAttachmentController extends Controller
{
    public function store(Request $request, $conversation)
    {
        $conversationData = DB::table('conversations')
            ->where('id', $conversation)
            ->where(function ($query) {
                $query->where('user_one_id', Auth::id())
                    ->orWhere('user_two_id', Auth::id());
            })
            ->first();

        if (! $conversationData) {
            abort(404);
        }

        $validated = $request->validate([
            'attachment' => [
                'required',
                'file',
                'max:15360',
                'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,txt,zip,webm,mp3,wav,ogg',
            ],
            'body' => ['nullable', 'string', 'max:10000'],
        ]);

        $file = $request->file('attachment');

        $filePath = $file->store('chat-attachments', 'public');

        $mimeType = $file->getMimeType();

        $type = str_starts_with($mimeType, 'image/') ? 'image' :
             (str_starts_with($mimeType, 'audio/') || $mimeType === 'video/webm' ? 'voice' : 'file');

        try{
            $messageId = DB::table('messages')->insertGetId([
                'conversation_id' => $conversation,
                'sender_id' => Auth::id(),
                'type' => $type,
                'body' => $validated['body'] ?? '',
                'cretated_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('message_attachments')->insert([
                'message_id' => $messageId,
                'file_path' => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'mimie_type' => $mimeType,
                'file_size' => $file->getSize(),
                'duration_seconds' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('conversations')->where('id', $conversation)->update(['updated_at' => now()]);

        }catch(Exception $ex){
            Storage::disk('public')->delete($filePath);
            Log::error('Message Attachment Error :', $ex);
        }

        return response()->json([
            'message' => 'Attachment uploaded successfully.',
            'data' => DB::table('messages')
                ->where('id', $messageId)
                ->first(),
        ], 201);
    }
}
