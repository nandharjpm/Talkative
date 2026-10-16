@extends('layouts.app')

@section('title', 'Chat requests · TalkSpace')

@section('content')
<div class="standalone-call">
    <div class="standalone-call-card" style="max-width:720px;text-align:left">
        <p class="eyebrow">YOUR CONNECTIONS</p>
        <h1>Chat requests</h1>
        <p>Review incoming requests and choose who can start a private conversation with you.</p>
        <div class="request-list" id="requestList">
            @forelse(($requests ?? collect()) as $request)
                <article class="request-card" data-request-id="{{ $request->id }}">
                    <div class="avatar avatar-blue">{{ strtoupper(substr($request->name ?? $request->username ?? 'U', 0, 2)) }}</div>
                    <div class="request-details">
                        <strong>{{ $request->name ?? $request->username }}</strong>
                        <span>{{ '@' . ($request->username ?? '') }}</span>
                        <p>Wants to start a conversation with you.</p>
                    </div>
                    <div class="request-actions">
                        <form method="POST" action="{{ url('/chat-requests/' . $request->id . '/accept') }}">@csrf<button class="accept-button" type="submit">Accept</button></form>
                        <form method="POST" action="{{ url('/chat-requests/' . $request->id . '/reject') }}">@csrf<button class="decline-button" type="submit">Decline</button></form>
                    </div>
                </article>
            @empty
                <p>No pending chat requests.</p>
            @endforelse
        </div>
        <p><a class="back-link" style="position:static" href="{{ url('/chat') }}">← Back to chats</a></p>
    </div>
</div>
@endsection
