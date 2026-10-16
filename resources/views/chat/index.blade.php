@extends('layouts.app')

@section('title', 'Chats · TalkSpace')

@section('content')
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="brand-row">
            <div class="brand-mark">T</div>
            <div class="brand-name">TalkSpace<span>Private conversations</span></div>
            <button class="icon-button mobile-close" id="closeSidebar" aria-label="Close sidebar">×</button>
        </div>

        <div class="profile-card">
            <div class="avatar avatar-self">ME</div>
            <div class="profile-copy">
                <strong>{{ auth()->user()->name ?? 'Your account' }}</strong>
                <span>{{ '@' . (auth()->user()->username ?? 'yourusername') }}</span>
            </div>
            <span class="status-dot" title="Online"></span>
        </div>

        <label class="search-label" for="userSearch">Find people</label>
        <div class="search-box">
            <span class="search-icon">⌕</span>
            <input id="userSearch" type="search" placeholder="Search username..." autocomplete="off">
        </div>
        <div id="searchResults" class="search-results" aria-live="polite"></div>

        <nav class="side-nav" aria-label="Chat navigation">
            <button class="nav-item active" data-view="chats"><span>▤</span> Chats <span class="nav-count" id="chatCount">2</span></button>
            <button class="nav-item" data-view="requests"><span>♧</span> Chat requests <span class="nav-count request-count">2</span></button>
        </nav>

        <div class="section-heading"><span id="listHeading">MESSAGES</span><span class="muted">•••</span></div>
        <div class="conversation-list" id="conversationList">
            <button class="conversation-item selected" data-chat="arun" data-name="Arun Kumar" data-handle="@arunk">
                <div class="avatar avatar-blue">AK<span class="presence"></span></div>
                <div class="conversation-copy">
                    <div class="conversation-top"><strong>Arun Kumar</strong><time>10:42</time></div>
                    <p>Sent you a voice note</p>
                </div>
                <span class="unread-dot"></span>
            </button>
            <button class="conversation-item" data-chat="priya" data-name="Priya S" data-handle="@priyas">
                <div class="avatar avatar-pink">PS</div>
                <div class="conversation-copy">
                    <div class="conversation-top"><strong>Priya S</strong><time>Yesterday</time></div>
                    <p>Photo looks great!</p>
                </div>
            </button>
        </div>

        <div class="sidebar-bottom">
            <div class="secure-note"><span>◇</span>
                <div><strong>Your space, your privacy</strong><small>One-to-one conversations only</small></div>
            </div>
            <form method="POST" action="">
                @csrf
                <button type="button" class="logout-button"
                    onclick="alert('Authentication is not configured yet.')">
                    ↪ <span>Log out</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="main-panel">
        <header class="mobile-header">
            <button class="icon-button" id="openSidebar" aria-label="Open sidebar">☰</button>
            <strong>TalkSpace</strong>
            <button class="icon-button" aria-label="More options">•••</button>
        </header>

        <section class="chat-view" id="chatView">
            <header class="chat-header">
                <div class="chat-person">
                    <div class="avatar avatar-blue" id="headerAvatar">AK<span class="presence"></span></div>
                    <div>
                        <h1 id="headerName">Arun Kumar</h1>
                        <p><span class="online-label"></span><span id="headerStatus">Online now</span></p>
                    </div>
                </div>
                <div class="header-actions">
                    <button class="call-button" data-call="audio" title="Start audio call"><span>☎</span><span class="button-label">Audio</span></button>
                    <button class="call-button primary-call" data-call="video" title="Start video call"><span>▣</span><span class="button-label">Video</span></button>
                    <button class="icon-button" aria-label="More chat options">•••</button>
                </div>
            </header>

            <div class="encryption-note"><span>◇</span> This is a private conversation between you and <strong id="noticeName">Arun Kumar</strong>.</div>

            <div class="message-area" id="messageArea" aria-live="polite">
                <div class="date-divider"><span>Today</span></div>
                <div class="message-row received">
                    <div class="avatar avatar-blue tiny-avatar">AK</div>
                    <div class="message-content">
                        <div class="message-bubble">Hey! Are you free to talk for a minute?</div>
                        <div class="message-meta">10:38 AM</div>
                    </div>
                </div>
                <div class="message-row sent">
                    <div class="message-content">
                        <div class="message-bubble">Yes, sure. What's up?</div>
                        <div class="message-meta">10:39 AM <span class="read-tick">✓✓</span></div>
                    </div>
                </div>
                <div class="message-row received">
                    <div class="avatar avatar-blue tiny-avatar">AK</div>
                    <div class="message-content">
                        <div class="message-bubble">I wanted to show you the latest design.</div>
                        <div class="message-meta">10:40 AM</div>
                    </div>
                </div>
                <div class="message-row received">
                    <div class="avatar avatar-blue tiny-avatar">AK</div>
                    <div class="message-content">
                        <div class="message-bubble voice-bubble"><button class="play-button" aria-label="Play voice note">▶</button>
                            <div class="voice-wave"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div><small>0:12</small>
                        </div>
                        <div class="message-meta">10:42 AM</div>
                    </div>
                </div>
            </div>

            <div class="attachment-preview hidden" id="attachmentPreview"></div>
            <div class="recording-bar hidden" id="recordingBar"><span class="recording-pulse"></span><span id="recordingText">Recording voice note…</span><button id="cancelRecording" class="text-button">Cancel</button><button id="stopRecording" class="small-primary">Stop</button></div>
            <form class="composer" id="messageForm">
                <div class="composer-tools">
                    <label class="tool-button" for="fileInput" title="Attach a file">＋</label>
                    <input type="file" id="fileInput" class="visually-hidden" accept="image/*,.pdf,.doc,.docx,.txt,.zip">
                    <button type="button" class="tool-button" id="emojiButton" title="Emoji">☺</button>
                </div>
                <textarea id="messageInput" rows="1" placeholder="Write a message..." aria-label="Message text"></textarea>
                <div class="composer-actions">
                    <button type="button" class="tool-button mic-button" id="recordButton" title="Record voice note">♩</button>
                    <button type="submit" class="send-button" id="sendButton" aria-label="Send message">➤</button>
                </div>
            </form>
            <div class="composer-hint">Enter to send <span>·</span> Shift + Enter for a new line</div>
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
            <div class="request-tabs"><button class="request-tab active" data-request-tab="incoming">Incoming <span>2</span></button><button class="request-tab" data-request-tab="sent">Sent</button></div>
            <div class="request-list" id="requestList">
                <article class="request-card" data-request-id="1">
                    <div class="avatar avatar-green">VK</div>
                    <div class="request-details"><strong>Vijay Kumar</strong><span>@vijayk</span>
                        <p>Wants to start a conversation with you.</p><small>Received 15 minutes ago</small>
                    </div>
                    <div class="request-actions"><button class="accept-button">Accept</button><button class="decline-button">Decline</button></div>
                </article>
                <article class="request-card" data-request-id="2">
                    <div class="avatar avatar-orange">RM</div>
                    <div class="request-details"><strong>Ramesh M</strong><span>@rameshm</span>
                        <p>Wants to connect and chat.</p><small>Received yesterday</small>
                    </div>
                    <div class="request-actions"><button class="accept-button">Accept</button><button class="decline-button">Decline</button></div>
                </article>
            </div>
        </section>

        <section class="empty-view hidden" id="emptyView">
            <div class="empty-illustration">✉</div>
            <h2>Your conversations, all in one place</h2>
            <p>Choose a chat or search for someone by username to get started.</p>
            <button class="small-primary" id="emptyFindPeople">Find people</button>
        </section>
    </main>
</div>

<div class="toast" id="toast" role="status" aria-live="polite"></div>
<div class="call-modal hidden" id="callModal" role="dialog" aria-modal="true" aria-labelledby="callTitle">
    <div class="call-modal-card">
        <button class="modal-close" id="closeCallModal" aria-label="Close">×</button>
        <div class="call-avatar avatar avatar-blue" id="callAvatar">AK</div>
        <p class="eyebrow" id="callTypeLabel">OUTGOING AUDIO CALL</p>
        <h2 id="callTitle">Calling Arun Kumar</h2>
        <p id="callDescription">Waiting for them to answer…</p>
        <div class="call-controls"><button class="call-control" id="muteCall">♩<span>Mute</span></button><button class="call-control end-call" id="endCall">☎<span>End call</span></button><button class="call-control" id="toggleCamera">▣<span>Camera</span></button></div>
        <p class="call-disclaimer">UI preview only — connect a WebRTC signaling service to place real calls.</p>
    </div>
</div>
@endsection