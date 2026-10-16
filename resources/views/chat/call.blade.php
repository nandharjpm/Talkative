@extends('layouts.app')

@section('title', 'Call · TalkSpace')

@section('content')
<div class="standalone-call">
    <a href="{{ url('/chat') }}" class="back-link">← Back to chats</a>
    <div class="standalone-call-card">
        <div class="avatar avatar-blue call-avatar-large">TS</div>
        <p class="eyebrow">CALL SCREEN</p>
        <h1>Audio / video call</h1>
        <p>This screen is a UI shell. Integrate WebRTC signaling and call authorization to connect users.</p>
        <div class="standalone-call-actions"><button class="call-button">♩ Mute</button><button class="call-button primary-call">▣ Camera</button><a href="{{ url('/chat') }}" class="hangup-link">☎ End call</a></div>
    </div>
</div>
@endsection
