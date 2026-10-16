(() => {
    const $ = (selector, root = document) => root.querySelector(selector);
    const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];
    const toast = $('#toast');
    let toastTimer;
    let selectedChat = 'arun';
    let selectedName = 'Arun Kumar';
    let selectedHandle = '@arunk';
    let selectedAvatar = 'AK';
    let pendingFile = null;
    let mediaRecorder = null;
    let recordedChunks = [];
    let recordingStream = null;
    let recordingStartedAt = 0;
    let activeCallType = 'audio';
    let muted = false;
    let cameraOn = true;

    function notify(message) {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 2800);
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, ch => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[ch]));
    }

    function setView(view) {
        const chatView = $('#chatView');
        const requestsView = $('#requestsView');
        const emptyView = $('#emptyView');
        if (!chatView || !requestsView || !emptyView) return;
        chatView.classList.toggle('hidden', view !== 'chats');
        requestsView.classList.toggle('hidden', view !== 'requests');
        emptyView.classList.add('hidden');
        $$('.nav-item').forEach(item => item.classList.toggle('active', item.dataset.view === view));
        $('#listHeading').textContent = view === 'requests' ? 'YOUR REQUESTS' : 'MESSAGES';
        if (view === 'requests') $('#requestList')?.scrollTo({ top: 0 });
        closeSidebar();
    }

    function openChat({ id, name, handle, initials }) {
        selectedChat = id;
        selectedName = name;
        selectedHandle = handle || '';
        selectedAvatar = initials || name.split(/\s+/).map(part => part[0]).join('').slice(0, 2).toUpperCase();
        $('#headerName').textContent = name;
        $('#noticeName').textContent = name;
        $('#headerAvatar').childNodes[0].textContent = selectedAvatar;
        $('#headerStatus').textContent = 'Conversation';
        $('#messageArea').innerHTML = '<div class="date-divider"><span>Conversation</span></div><div class="empty-chat-note">Your messages with ' + escapeHtml(name) + ' will appear here.</div>';
        $$('.conversation-item').forEach(item => item.classList.toggle('selected', item.dataset.chat === id));
        setView('chats');
        $('#messageInput')?.focus();
    }

    const searchInput = $('#userSearch');
    let searchDebounce;
    searchInput?.addEventListener('input', () => {
        clearTimeout(searchDebounce);
        const term = searchInput.value.trim();
        const results = $('#searchResults');
        if (!results) return;
        if (term.length < 2) { results.innerHTML = ''; return; }
        searchDebounce = setTimeout(async () => {
            // Connect this request to your Laravel UserSearchController.
            // Expected JSON: [{ id, name, username, avatar }]
            try {
                const response = await fetch('/users/search?q=' + encodeURIComponent(term), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) throw new Error('Search endpoint is not ready');
                const users = await response.json();
                if (!Array.isArray(users) || users.length === 0) {
                    results.innerHTML = '<div class="no-results">No users found.</div>';
                    return;
                }
                results.innerHTML = users.map(user => {
                    const initials = (user.name || user.username || 'U').split(/\s+/).map(s => s[0]).join('').slice(0, 2).toUpperCase();
                    return `<div class="search-result"><div class="avatar avatar-blue">${escapeHtml(initials)}</div><div class="result-copy"><strong>${escapeHtml(user.name || user.username)}</strong><small>@${escapeHtml(user.username || '')}</small></div><button class="request-user-button" data-user-id="${Number(user.id)}">Request</button></div>`;
                }).join('');
            } catch (error) {
                results.innerHTML = '<div class="no-results">Search UI is ready. Connect /users/search to your controller.</div>';
            }
        }, 250);
    });

    $('#searchResults')?.addEventListener('click', async event => {
        const button = event.target.closest('[data-user-id]');
        if (!button) return;
        try {
            const response = await fetch('/chat-requests', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ receiver_id: Number(button.dataset.userId) })
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Could not send request.');
            notify(data.message || 'Chat request sent.');
            button.textContent = 'Sent';
            button.disabled = true;
        } catch (error) {
            notify(error.message || 'Connect the request endpoint to enable this action.');
        }
    });

    $$('.conversation-item').forEach(item => item.addEventListener('click', () => {
        openChat({
            id: item.dataset.chat,
            name: item.dataset.name,
            handle: item.dataset.handle,
            initials: item.querySelector('.avatar')?.textContent.trim().slice(0, 2)
        });
    }));

    $$('.nav-item').forEach(item => item.addEventListener('click', () => setView(item.dataset.view)));

    $('#requestList')?.addEventListener('click', async event => {
        const card = event.target.closest('.request-card');
        if (!card) return;
        const accept = event.target.closest('.accept-button');
        const decline = event.target.closest('.decline-button');
        if (!accept && !decline) return;
        const requestId = card.dataset.requestId;
        const action = accept ? 'accept' : 'reject';
        try {
            const response = await fetch(`/chat-requests/${requestId}/${action}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]')?.content || ''
                }
            });
            if (!response.ok) throw new Error('Connect this button to the chat request controller.');
            card.remove();
            notify(action === 'accept' ? 'Request accepted.' : 'Request declined.');
        } catch (error) {
            notify(error.message);
        }
    });

    const messageForm = $('#messageForm');
    const messageInput = $('#messageInput');
    function appendMessage(text, type = 'text', file = null) {
        const area = $('#messageArea');
        if (!area) return;
        const row = document.createElement('div');
        row.className = 'message-row sent';
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        let bubble = `<div class="message-bubble">${escapeHtml(text)}</div>`;
        if (type === 'file' && file) {
            if (file.type.startsWith('image/')) {
                const url = URL.createObjectURL(file);
                bubble = `<div class="message-bubble"><img src="${url}" alt="${escapeHtml(file.name)}" style="display:block;max-width:220px;max-height:190px;border-radius:8px"><small>${escapeHtml(file.name)}</small></div>`;
            } else {
                bubble = `<div class="message-bubble">📎 ${escapeHtml(file.name)}<br><small>${Math.ceil(file.size / 1024)} KB · preview only</small></div>`;
            }
        }
        if (type === 'voice' && file) {
            const url = URL.createObjectURL(file);
            bubble = `<div class="message-bubble"><audio controls src="${url}" style="max-width:220px"></audio></div>`;
        }
        row.innerHTML = `<div class="message-content">${bubble}<div class="message-meta">${time} <span class="read-tick">✓</span></div></div>`;
        area.appendChild(row);
        area.scrollTop = area.scrollHeight;
    }

    messageForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const text = messageInput.value.trim();
        if (!text && !pendingFile) return;
        if (pendingFile) {
            appendMessage('', 'file', pendingFile);
            notify('Attachment preview shown. Connect the upload endpoint to send it.');
            pendingFile = null;
            $('#attachmentPreview').classList.add('hidden');
            $('#attachmentPreview').innerHTML = '';
        }
        if (text) {
            // UI preview: replace with POST /conversations/{id}/messages when conversation IDs are wired in.
            appendMessage(text);
            messageInput.value = '';
            resizeInput();
            notify('Message added to preview. Connect MessageController to save it.');
        }
    });

    function resizeInput() {
        if (!messageInput) return;
        messageInput.style.height = 'auto';
        messageInput.style.height = Math.min(messageInput.scrollHeight, 110) + 'px';
    }
    messageInput?.addEventListener('input', resizeInput);
    messageInput?.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            messageForm.requestSubmit();
        }
    });

    $('#fileInput')?.addEventListener('change', event => {
        const file = event.target.files?.[0];
        if (!file) return;
        if (file.size > 15 * 1024 * 1024) {
            notify('Please choose a file smaller than 15 MB.');
            event.target.value = '';
            return;
        }
        pendingFile = file;
        const preview = $('#attachmentPreview');
        const visual = file.type.startsWith('image/') ? `<img src="${URL.createObjectURL(file)}" alt="">` : '<span style="font-size:22px">📎</span>';
        preview.innerHTML = `${visual}<span class="attachment-name">${escapeHtml(file.name)} · ${Math.max(1, Math.ceil(file.size / 1024))} KB</span><button type="button" id="removeAttachment" aria-label="Remove attachment">×</button>`;
        preview.classList.remove('hidden');
        event.target.value = '';
    });
    $('#attachmentPreview')?.addEventListener('click', event => {
        if (event.target.closest('#removeAttachment')) {
            pendingFile = null;
            $('#attachmentPreview').innerHTML = '';
            $('#attachmentPreview').classList.add('hidden');
        }
    });
    $('#emojiButton')?.addEventListener('click', () => {
        if (messageInput) {
            messageInput.value += ' 😊';
            messageInput.focus();
            resizeInput();
        }
    });

    const recordButton = $('#recordButton');
    recordButton?.addEventListener('click', async () => {
        if (mediaRecorder && mediaRecorder.state === 'recording') {
            mediaRecorder.stop();
            return;
        }
        if (!navigator.mediaDevices?.getUserMedia || !window.MediaRecorder) {
            notify('Voice recording is not supported by this browser.');
            return;
        }
        try {
            recordingStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            recordedChunks = [];
            mediaRecorder = new MediaRecorder(recordingStream);
            mediaRecorder.ondataavailable = event => { if (event.data.size) recordedChunks.push(event.data); };
            mediaRecorder.onstop = () => {
                const blob = new Blob(recordedChunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                const file = new File([blob], `voice-note-${Date.now()}.webm`, { type: blob.type });
                recordingStream?.getTracks().forEach(track => track.stop());
                recordingStream = null;
                $('#recordingBar').classList.add('hidden');
                pendingFile = file;
                $('#attachmentPreview').innerHTML = `<span style="font-size:22px">🎙</span><span class="attachment-name">Voice note · ${Math.max(1, Math.round((Date.now() - recordingStartedAt) / 1000))} sec</span><button type="button" id="removeAttachment" aria-label="Remove voice note">×</button>`;
                $('#attachmentPreview').classList.remove('hidden');
                notify('Voice note recorded. Press send to add it to the preview.');
            };
            recordingStartedAt = Date.now();
            mediaRecorder.start();
            $('#recordingBar').classList.remove('hidden');
            recordButton.textContent = '■';
        } catch (error) {
            notify('Microphone permission was denied or unavailable.');
        }
    });
    $('#stopRecording')?.addEventListener('click', () => {
        if (mediaRecorder?.state === 'recording') mediaRecorder.stop();
        if (recordButton) recordButton.textContent = '♩';
    });
    $('#cancelRecording')?.addEventListener('click', () => {
        if (mediaRecorder?.state === 'recording') {
            mediaRecorder.onstop = () => recordingStream?.getTracks().forEach(track => track.stop());
            mediaRecorder.stop();
        }
        $('#recordingBar')?.classList.add('hidden');
        recordingStream?.getTracks().forEach(track => track.stop());
        recordingStream = null;
        if (recordButton) recordButton.textContent = '♩';
    });

    const callModal = $('#callModal');
    $$('[data-call]').forEach(button => button.addEventListener('click', () => {
        activeCallType = button.dataset.call;
        $('#callTypeLabel').textContent = `OUTGOING ${activeCallType.toUpperCase()} CALL`;
        $('#callTitle').textContent = `Calling ${selectedName}`;
        $('#callDescription').textContent = 'UI preview — signaling is not connected.';
        $('#callAvatar').childNodes[0].textContent = selectedAvatar;
        callModal?.classList.remove('hidden');
    }));
    $('#closeCallModal')?.addEventListener('click', () => callModal?.classList.add('hidden'));
    $('#endCall')?.addEventListener('click', () => {
        callModal?.classList.add('hidden');
        notify('Call preview ended.');
    });
    $('#muteCall')?.addEventListener('click', event => {
        muted = !muted;
        event.currentTarget.querySelector('span').textContent = muted ? 'Unmute' : 'Mute';
        notify(muted ? 'Mute UI toggled.' : 'Mute UI toggled off.');
    });
    $('#toggleCamera')?.addEventListener('click', event => {
        cameraOn = !cameraOn;
        event.currentTarget.querySelector('span').textContent = cameraOn ? 'Camera' : 'Camera off';
        notify(cameraOn ? 'Camera UI toggled on.' : 'Camera UI toggled off.');
    });

    const sidebar = $('#sidebar');
    function closeSidebar() { sidebar?.classList.remove('open'); }
    $('#openSidebar')?.addEventListener('click', () => sidebar?.classList.add('open'));
    $('#closeSidebar')?.addEventListener('click', closeSidebar);
    $('#emptyFindPeople')?.addEventListener('click', () => {
        closeSidebar();
        searchInput?.focus();
        notify('Type a username in Find people.');
    });

    // The page is a frontend starter. Demo controls do not persist data unless connected to Laravel endpoints.
})();
