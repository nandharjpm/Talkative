
$(function () {
    'use strict';

    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    let activeConversationId = null;
    let pendingFile = null;
    let searchTimer = null;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    });

    // Display a notification.
    function notify(message) {
        const $toast = $('#toast');

        $toast.text(message).addClass('show');

        setTimeout(function () {
            $toast.removeClass('show');
        }, 2500);
    }

    // Escape user-provided text before inserting HTML.
    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    // Format message time.
    function formatTime(value) {
        if (!value) {
            return '';
        }

        const date = new Date(value);

        if (isNaN(date.getTime())) {
            return '';
        }

        return date.toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Switch between chat and request screens.
    function setView(view) {
        $('#chatView').toggleClass('hidden', view !== 'chats');
        $('#requestsView').toggleClass('hidden', view !== 'requests');
        $('#emptyView').addClass('hidden');

        $('.nav-item').removeClass('active');
        $('.nav-item[data-view="' + view + '"]').addClass('active');

        $('#listHeading').text(
            view === 'requests' ? 'YOUR REQUESTS' : 'MESSAGES'
        );

        $('#sidebar').removeClass('open');
    }


    // Load chat requests.
    function loadChatRequests(type = 'incoming') {
        const $list = $('#requestList');

        $list.empty().append(
            $('<p>').addClass('no-results').text('Loading requests...')
        );

        $.ajax({
            url: $('#chatPage').data('requests-url'),
            method: 'GET',
            data: { type: type },
            dataType: 'json'
        })
            .done(function (response) {
                $list.empty();

                $('#incomingCount').text(response.incoming_count ?? 0);
                $('#sentCount').text(response.sent_count ?? 0);
                $('.request-count').text(response.incoming_count ?? 0);

                const requests = response.requests || [];

                if (!requests.length) {
                    $('<p>')
                        .addClass('no-results')
                        .text(
                            type === 'sent'
                                ? 'No pending sent requests.'
                                : 'No pending incoming requests.'
                        )
                        .appendTo($list);

                    return;
                }

                $.each(requests, function (_, request) {
                    const $card = $('<article>')
                        .addClass('request-card')
                        .attr('data-request-id', request.id);

                    const $details = $('<div>').addClass('request-details');

                    $('<strong>').text(request.name).appendTo($details);

                    $('<span>')
                        .text('@' + (request.username || ''))
                        .appendTo($details);

                    $('<p>')
                        .text(
                            type === 'sent'
                                ? 'You sent a request to this user.'
                                : 'Wants to start a conversation with you.'
                        )
                        .appendTo($details);

                    const $actions = $('<div>').addClass('request-actions');

                    if (type === 'incoming') {
                        $('<button>')
                            .attr('type', 'button')
                            .addClass('accept-button')
                            .text('Accept')
                            .appendTo($actions);

                        $('<button>')
                            .attr('type', 'button')
                            .addClass('decline-button')
                            .text('Decline')
                            .appendTo($actions);
                    } else {
                        $('<span>')
                            .text('Pending')
                            .appendTo($actions);
                    }

                    $card.append($details, $actions);
                    $list.append($card);
                });
            })
            .fail(function (xhr) {
                console.error('Chat requests error:', xhr.status, xhr.responseText);
                $list.empty().append(
                    $('<p>').addClass('no-results')
                        .text('Unable to load chat requests.')
                );
            });
    }

    // Load request counts when the page opens.
    function loadRequestCounts() {
        $.ajax({
            url: $('#chatPage').data('requests-url'),
            method: 'GET',
            data: { type: 'incoming' },
            dataType: 'json'
        })
            .done(function (response) {
                $('#incomingCount').text(response.incoming_count ?? 0);
                $('#sentCount').text(response.sent_count ?? 0);
                $('.request-count').text(response.incoming_count ?? 0);
            })
            .fail(function (xhr) {
                console.error('Request count error:', xhr.status, xhr.responseText);
            });
    }

    loadRequestCounts();

    // Add a message to the current conversation.
    function renderMessage(message) {
        const isMine = Number(message.sender_id) === Number(
            $('#chatPage').data('current-user-id')
        );

        const text = message.body || message.message || '';
        const time = formatTime(message.created_at);

        const $row = $('<div>')
            .addClass('message-row')
            .addClass(isMine ? 'sent' : 'received');

        const $content = $('<div>').addClass('message-content');

        const $bubble = $('<div>')
            .addClass('message-bubble')
            .text(text);

        const $meta = $('<div>')
            .addClass('message-meta')
            .text(time);

        $content.append($bubble, $meta);

        if (!isMine) {
            const initials = message.sender_initials ||
                (message.sender_name || 'U').substring(0, 2).toUpperCase();

            const $avatar = $('<div>')
                .addClass('avatar avatar-blue tiny-avatar')
                .text(initials);

            $row.append($avatar);
        }

        $row.append($content);

        $('#messageArea').append($row);
    }

    // Load messages from Laravel.
    function loadMessages(conversationId) {
        $.ajax({
            url: $('#chatPage').data('message-url') + '/' + conversationId + '/messages',
            method: 'GET'
        })
            .done(function (response) {
                const messages = Array.isArray(response)
                    ? response
                    : (response.message || []);

                $('#messageArea').empty();

                if (!messages.length) {
                    $('#messageArea').append(
                        $('<div>')
                            .addClass('empty-chat-note')
                            .text('No messages yet. Start the conversation!')
                    );

                    return;
                }

                $.each(messages, function (_, message) {
                    renderMessage(message);
                });

                const $area = $('#messageArea');
                $area.scrollTop($area[0].scrollHeight);
            })
            .fail(function (xhr) {
                notify(
                    xhr.responseJSON?.message ||
                    'Unable to load messages.'
                );
            });
    }

    // Open a conversation.
    $(document).on('click', '.conversation-item', function () {
        const $item = $(this);
        const conversationId = $item.attr('data-conversation-id');

        if (!conversationId) {
            notify('This conversation is not connected to Laravel yet.');
            return;
        }

        activeConversationId = conversationId;

        $('.conversation-item').removeClass('selected');
        $item.addClass('selected');

        const name = $item.attr('data-name') || 'Conversation';

        $('#headerName, #noticeName').text(name);
        $('#headerStatus').text('Conversation');

        setView('chats');
        loadMessages(activeConversationId);

        $('#messageInput').trigger('focus');
    });

    // Search users with a short debounce.
    $('#userSearch').on('input', function () {
        clearTimeout(searchTimer);

        const term = $.trim($(this).val());
        const $results = $('#searchResults');

        if (term.length < 2) {
            $results.empty();
            return;
        }

        searchTimer = setTimeout(function () {
            $.ajax({
                url: $('#chatPage').data('search-url'),
                method: 'GET',
                data: { q: term },
                dataType: 'json'
            })
                .done(function (users) {
                    $results.empty();

                    if (!users.length) {
                        $('<div>')
                            .addClass('no-results')
                            .text('No users found.')
                            .appendTo($results);

                        return;
                    }

                    $.each(users, function (_, user) {
                        const $result = $('<div>')
                            .addClass('search-result');

                        $('<div>')
                            .addClass('result-copy')
                            .append(
                                $('<strong>').text(user.name),
                                $('<small>').text('@' + (user.username || ''))
                            )
                            .appendTo($result);

                        $('<button>')
                            .attr('type', 'button')
                            .addClass('request-user-button')
                            .attr('data-user-id', user.id)
                            .text('Request')
                            .appendTo($result);

                        $results.append($result);
                    });
                })
                .fail(function (xhr) {
                    console.error('User search error:', xhr.status, xhr.responseText);
                    notify('No users found.');
                });
        }, 300);
    });

    // Send a chat request.
    $(document)
        .off('click.chatRequest', '.request-user-button')
        .on('click.chatRequest', '.request-user-button', function () {
            const $button = $(this);
            const receiverId = $button.data('user-id');

            if ($button.prop('disabled')) {
                return;
            }

            $button.prop('disabled', true).text('Sending...');

            $.ajax({
                url: $('#chatPage').data('request-store-url'),
                method: 'POST',
                data: {
                    receiverId: receiverId,
                    _token: csrfToken
                },
                dataType: 'json'
            })
                .done(function (response) {
                    $button.text('Requested');
                    notify(response.message || 'Chat request sent.');
                })
                .fail(function (xhr) {
                    console.error('Chat request error:', xhr.status, xhr.responseText);

                    $button.prop('disabled', false).text('Request');

                    notify(
                        xhr.responseJSON?.message ||
                        'Unable to send chat request.'
                    );
                });
        });

    // Accept or reject a request.
    $(document)
        .off('click.chatRequestAction', '.accept-button, .decline-button')
        .on('click.chatRequestAction', '.accept-button, .decline-button', function (event) {
            event.preventDefault();

            const $button = $(this);
            const $card = $button.closest('.request-card');
            const requestId = $card.attr('data-request-id');

            if ($button.prop('disabled')) {
                return;
            }

            const action = $button.hasClass('accept-button')
                ? 'accept'
                : 'reject';

            const requestsUrl = $('#chatPage').data('requests-url');

            $button.prop('disabled', true).text(
                action === 'accept' ? 'Accepting...' : 'Declining...'
            );

            $.ajax({
                url: requestsUrl.replace(/\/$/, '') + '/' + requestId + '/' + action,
                method: 'POST',
                dataType: 'json'
            })
                .done(function (response) {
                    $card.remove();

                    notify(response.message ||
                        (action === 'accept'
                            ? 'Request accepted.'
                            : 'Request rejected.')
                    );

                    if (action === 'accept' && response.conversation_id) {
                        notify('Conversation created successfully.');
                    }

                    loadChatRequests(
                        $('.request-tab.active').data('request-tab') || 'incoming'
                    );
                })
                .fail(function (xhr) {
                    console.error(
                        'Accept/reject error:',
                        xhr.status,
                        xhr.responseText
                    );

                    $button.prop('disabled', false).text(
                        action === 'accept' ? 'Accept' : 'Decline'
                    );

                    notify(
                        xhr.responseJSON?.message ||
                        'Unable to update chat request.'
                    );
                });
        });

    // Send a text message.
    $('#messageForm')
        .off('submit')
        .on('submit', function (event) {
            event.preventDefault();

            const $form = $(this);

            if ($form.data('sending')) {
                return;
            }

            const message = $.trim($('#messageInput').val());

            if (!activeConversationId) {
                notify('Select a conversation first.');
                return;
            }

            if (!message && !pendingFile) {
                return;
            }

            if (pendingFile) {
                if ($form.data('sending')) {
                    return;
                }

                $form.data('sending', true);
                $('#sendButton').prop('disabled', true);

                uploadAttachment(message);
                return;
            }

            $form.data('sending', true);
            $('#sendButton').prop('disabled', true);

            $.ajax({
                url: $('#chatPage').data('message-url') + '/' + activeConversationId + '/messages',
                method: 'POST',
                data: {
                    body: message
                }
            })
                .done(function (response) {
                    $('#messageInput').val('');

                    renderMessage(response.message || response);
                    notify('Message sent.');
                })
                .fail(function (xhr) {
                    console.error('Send message error:', xhr.status, xhr.responseText);

                    notify(
                        xhr.responseJSON?.message ||
                        'Unable to send message.'
                    );
                })
                .always(function () {
                    $('#messageForm').removeData('sending');
                    $('#sendButton').prop('disabled', false);
                });
        });

    // Upload an attachment, optionally with a message.
    function uploadAttachment(message) {
        const formData = new FormData();

        formData.append('attachment', pendingFile);

        if (message) {
            formData.append('body', message);
        }

        $.ajax({
            url: $('#chatPage').data('attachment-url') + '/' + activeConversationId + '/attachments',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false
        })
            .done(function (response) {
                $('#messageInput').val('');
                $('#fileInput').val('');
                $('#attachmentPreview').empty().addClass('hidden');

                pendingFile = null;

                loadMessages(activeConversationId);
                notify(response.message || 'Attachment sent.');
            })
            .fail(function (xhr) {
                notify(
                    xhr.responseJSON?.message ||
                    'Unable to upload attachment.'
                );
            });
    }

    // Attachment selection and preview.
    $('#fileInput').on('change', function () {
        const file = this.files[0];

        if (!file) {
            return;
        }

        if (file.size > 15 * 1024 * 1024) {
            notify('Maximum file size is 15 MB.');
            $(this).val('');
            return;
        }

        pendingFile = file;

        $('#attachmentPreview')
            .removeClass('hidden')
            .empty()
            .append(
                $('<span>')
                    .addClass('attachment-name')
                    .text(file.name + ' (' +
                        Math.ceil(file.size / 1024) + ' KB)')
            )
            .append(
                $('<button>')
                    .attr('type', 'button')
                    .attr('id', 'removeAttachment')
                    .text('×')
            );
    });

    $(document).on('click', '#removeAttachment', function () {
        pendingFile = null;
        $('#fileInput').val('');
        $('#attachmentPreview').empty().addClass('hidden');
    });

    // Emoji shortcut.
    $('#emojiButton').on('click', function () {
        const $input = $('#messageInput');

        $input.val($input.val() + ' 😊').trigger('focus');
    });

    // Enter sends; Shift + Enter adds a new line.
    $('#messageInput').on('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            $('#messageForm').trigger('submit');
        }
    });

    $(document).on('click', '.request-tab', function () {
        $('.request-tab').removeClass('active');
        $(this).addClass('active');

        loadChatRequests($(this).data('request-tab'));
    });


    // Navigation and mobile sidebar.
    $('.nav-item').on('click', function () {
        const view = $(this).attr('data-view');

        setView(view);

        if (view === 'requests') {
            loadChatRequests('incoming');
        }
    });

    $('#openSidebar').on('click', function () {
        $('#sidebar').addClass('open');
    });

    $('#closeSidebar').on('click', function () {
        $('#sidebar').removeClass('open');
    });

    $('#emptyFindPeople').on('click', function () {
        $('#sidebar').addClass('open');
        $('#userSearch').trigger('focus');
    });
});
