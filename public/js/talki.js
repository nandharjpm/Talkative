
$(function () {
    'use strict';

    const $talkiView = $('#talkiView');
    const $talkiMessages = $('#talkiMessages');
    const $talkiInput = $('#talkiInput');
    const $talkiForm = $('#talkiForm');
    const $talkiSendButton = $('#talkiSendButton');
    const historyUrl = $talkiView.data('history-url');
    const messageUrl = $talkiView.data('message-url');

    let talkiLoaded = false;
    let talkiSending = false;

    function appendTalkiMessage(message, role) {
        const $bubble = $('<div>')
            .addClass('talki-message')
            .addClass(role === 'user' ? 'talki-user' : 'talki-assistant')
            .text(message);

        $talkiMessages.append($bubble);
        $talkiMessages.scrollTop($talkiMessages[0].scrollHeight);
    }

    function showTalki() {
        $('#chatView, #requestsView, #emptyView').addClass('hidden');
        $talkiView.removeClass('hidden');

        $('.nav-item').removeClass('active');
        $('#talkiNavButton').addClass('active');

        if (!talkiLoaded) {
            talkiLoaded = true;

            $.get(historyUrl)
                .done(function (response) {
                    $talkiMessages.empty();

                    if (!response.messages || !response.messages.length) {
                        $talkiMessages.append(
                            $('<div>')
                                .addClass('talki-welcome')
                                .text("Hey, I'm Talki! 👋 What's on your mind?")
                        );
                        return;
                    }

                    response.messages.forEach(function (message) {
                        appendTalkiMessage(message.content, message.role);
                    });
                })
                .fail(function () {
                    $talkiMessages.empty();
                    appendTalkiMessage(
                        'I could not load our previous chat. You can still send me a message!',
                        'assistant'
                    );
                });
        }

        $talkiInput.trigger('focus');
    }

    $('#talkiNavButton').on('click', showTalki);

    $('#navChats').on('click', function () {
        $talkiView.addClass('hidden');
    });

    $('.nav-item[data-view="requests"]').on('click', function () {
        $talkiView.addClass('hidden');
    });

    $talkiForm.on('submit', function (event) {
        event.preventDefault();

        if (talkiSending) {
            return;
        }

        const message = $talkiInput.val().trim();

        if (!message) {
            return;
        }

        appendTalkiMessage(message, 'user');
        $talkiInput.val('').trigger('focus');

        talkiSending = true;
        $talkiSendButton.prop('disabled', true);

        const $typing = $('<div>')
            .addClass('talki-message talki-assistant talki-typing')
            .text('Talki is typing…');

        $talkiMessages.append($typing);
        $talkiMessages.scrollTop($talkiMessages[0].scrollHeight);

        $.ajax({
            url: messageUrl,
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: JSON.stringify({
                message: message
            })
        })
            .done(function (response) {
                if (response.message && response.message.content) {
                    appendTalkiMessage(response.message.content, 'assistant');
                } else {
                    appendTalkiMessage('I could not generate a reply. Please try again.', 'assistant');
                }
            })
            .fail(function (xhr) {
                const errorMessage = xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Sorry, I could not connect right now. Please try again.';

                appendTalkiMessage(errorMessage, 'assistant');
            })
            .always(function () {
                $typing.remove();
                talkiSending = false;
                $talkiSendButton.prop('disabled', false);
                $talkiInput.trigger('focus');
            });
    });
});
