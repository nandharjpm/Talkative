
@extends('layouts.app')

@section('title', 'Chats · TalkSpace')

@section('content')
    <div class="app-shell"
        id="chatPage"
        data-current-user-id="{{ Auth::id() }}"
        data-search-url="{{ route('users.search') }}"
        data-requests-url="{{ route('chat-requests.index') }}"
        data-request-store-url="{{ route('chat-requests.store') }}"
        data-message-url="{{ url('/conversations') }}"
        data-attachment-url="{{ url('/conversations') }}"
        data-call-url="{{ route('calls.store') }}">

        <aside class="sidebar" id="sidebar">
            <div class="brand-row">
                <div class="brand-mark">T</div>

                <div class="brand-name">
                    TalkSpace
                    <span>Private conversations</span>
                </div>

                <button type="button" class="icon-button mobile-close"
                    id="closeSidebar" aria-label="Close sidebar">×</button>
            </div>

            <div class="profile-card">
                <div class="avatar avatar-self">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>

                <div class="profile-copy">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ '@' . (auth()->user()->username ?? '') }}</span>
                </div>

                <span class="status-dot" title="Your account"></span>
            </div>

            <label class="search-label" for="userSearch">Find people</label>

            <div class="search-box">
                <span class="search-icon">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>

                <input
                    id="userSearch"
                    type="search"
                    placeholder="Search username..."
                    autocomplete="off">
            </div>

            <div id="searchResults" class="search-results" aria-live="polite"></div>

            <nav class="side-nav" aria-label="Chat navigation">
                <button type="button" class="nav-item active" data-view="chats">
                    <span><i class="fa-regular fa-message"></i></span>
                    Chats
                    <span class="nav-count" id="chatCount">
                        {{ $conversations->count() }}
                    </span>
                </button>

                <button type="button" class="nav-item" data-view="requests">
                    <span><i class="fa-regular fa-hand"></i></span>
                    Chat requests
                    <span class="nav-count request-count">0</span>
                </button>
            </nav>

            <div class="section-heading">
                <span id="listHeading">MESSAGES</span>
                <span class="muted">...</span>
            </div>

            <div class="conversation-list" id="conversationList">
                @forelse ($conversations as $conversation)
                    <button
                        type="button"
                        class="conversation-item"
                        data-conversation-id="{{ $conversation->id }}"
                        data-name="{{ $conversation->others_user_name }}"
                        data-handle="{{ $conversation->others_name }}">

                        <div class="avatar avatar-blue">
                            {{ strtoupper(substr($conversation->others_user_name, 0, 2)) }}
                        </div>

                        <div class="conversation-copy">
                            <div class="conversation-top">
                                <strong>{{ $conversation->others_user_name }}</strong>
                                <time></time>
                            </div>

                            <p>
                                {{ '@' . ($conversation->others_name ?? '') }}
                            </p>
                        </div>
                    </button>
                @empty
                    <p class="no-results" id="noConversations">
                        No conversations yet. Find people to get started.
                    </p>
                @endforelse
            </div>

            <div class="sidebar-bottom">
                <div class="secure-note">
                    <span>◇</span>

                    <div>
                        <strong>Your space, your privacy</strong>
                        <small>One-to-one conversations only</small>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="logout-button">
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="main-panel">
            <header class="mobile-header">
                <button type="button" class="icon-button"
                    id="openSidebar" aria-label="Open sidebar">☰</button>

                <strong>TalkSpace</strong>

                <button type="button" class="icon-button"
                    aria-label="More options">...</button>
            </header>

            <section class="chat-view hidden" id="chatView">
                <header class="chat-header">
                    <div class="chat-person">
                        <div class="avatar avatar-blue" id="headerAvatar">
                            <span id="headerInitials">{{ strtoupper(substr($conversation->others_user_name, 0, 2)) }}</span>
                        </div>

                        <div>
                            <h1 id="headerName"></h1>
                            <p>
                                <span class="online-label"></span>
                                <span id="headerStatus">Conversation</span>
                            </p>
                        </div>
                    </div>

                    <div class="header-actions">
                        <button type="button" class="call-button"
                            data-call="audio" title="Start audio call">
                            <span>☎</span>
                            <span class="button-label">Audio</span>
                        </button>

                        <button type="button" class="call-button primary-call"
                            data-call="video" title="Start video call">
                            <span>▣</span>
                            <span class="button-label">Video</span>
                        </button>

                        <button type="button" class="icon-button"
                            aria-label="More chat options">...</button>
                    </div>
                </header>

                <div class="encryption-note">
                    <span>◇</span>
                    This is a private conversation between you and
                    <strong id="noticeName"></strong>.
                </div>

                <div class="message-area" id="messageArea" aria-live="polite">
                    <p class="no-results"></p>
                </div>

                <div class="attachment-preview hidden" id="attachmentPreview"></div>

                <div class="recording-bar hidden" id="recordingBar">
                    <span class="recording-pulse"></span>
                    <span id="recordingText">Recording voice note…</span>

                    <button type="button" id="cancelRecording" class="text-button">
                        Cancel
                    </button>

                    <button type="button" id="stopRecording" class="small-primary">
                        Stop
                    </button>
                </div>

                <form class="composer" id="messageForm">
                    <div class="composer-tools">
                        <label class="tool-button" for="fileInput"
                            title="Attach a file">＋</label>

                        <input
                            type="file"
                            id="fileInput"
                            class="visually-hidden"
                            accept="image/*,.pdf,.doc,.docx,.txt,.zip,.webm,.mp3,.wav,.ogg">

                        <button type="button" class="tool-button"
                            id="emojiButton" title="Emoji">☺</button>
                    </div>

                    <textarea
                        id="messageInput"
                        rows="1"
                        placeholder="Write a message..."
                        aria-label="Message text"></textarea>

                    <div class="composer-actions">
                        <button type="button" class="tool-button mic-button"
                            id="recordButton" title="Record voice note">♩</button>

                        <button type="submit" class="send-button"
                            id="sendButton" aria-label="Send message">➤</button>
                    </div>
                </form>

                <div class="composer-hint">
                    Enter to send <span>·</span> Shift + Enter for a new line
                </div>
            </section>

            <section class="requests-view hidden" id="requestsView">
                <div class="requests-heading">
                    <div>
                        <p class="eyebrow">YOUR CONNECTIONS</p>
                        <h1>Chat requests</h1>
                        <p>Choose who you want to start a private conversation with.</p>
                    </div>

                    <div class="requests-icon">♧</div>
                </div>

                <div class="request-tabs">
                    <button type="button" class="request-tab active"
                        data-request-tab="incoming">
                        Incoming <span id="incomingCount">0</span>
                    </button>

                    <button type="button" class="request-tab"
                        data-request-tab="sent">
                        Sent <span id="sentCount">0</span>
                    </button>
                </div>

                <div class="request-list" id="requestList">
                    <p class="no-results">Loading requests...</p>
                </div>
            </section>

            <section class="empty-view" id="emptyView">
                <div class="empty-illustration">✉</div>

                <h2>Your conversations, all in one place</h2>

                <p>
                    Choose an existing conversation or search for someone
                    by username to get started.
                </p>

                <button type="button" class="small-primary" id="emptyFindPeople">
                    Find people
                </button>
            </section>
        </main>
    </div>

    <div class="toast" id="toast" role="status" aria-live="polite"></div>

    <div class="call-modal hidden" id="callModal"
        role="dialog" aria-modal="true" aria-labelledby="callTitle">

        <div class="call-modal-card">
            <button type="button" class="modal-close"
                id="closeCallModal" aria-label="Close">×</button>

            <div class="call-avatar avatar avatar-blue" id="callAvatar">{{ strtoupper(substr($conversation->others_user_name, 0, 2)) }}</div>

            <p class="eyebrow" id="callTypeLabel"></p>

            <h2 id="callTitle"></h2>

            <p id="callDescription"></p>

            <div class="call-controls">
                <button type="button" class="call-control" id="muteCall">
                    ♩<span>Mute</span>
                </button>

                <button type="button" class="call-control end-call" id="endCall">
                    ☎<span>End call</span>
                </button>

                <button type="button" class="call-control" id="toggleCamera">
                    ▣<span>Camera</span>
                </button>
            </div>

            <p class="call-disclaimer">
                Calls require a connected WebRTC signaling service.
            </p>
        </div>
    </div>
@endsection
