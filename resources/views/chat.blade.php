<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Application</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/app.js')
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Chat Application</h1>
    <div class="flex">
        <div class="w-1/4 pr-4">
            <h2 class="text-xl font-semibold mb-2">Channels</h2>
            <ul id="channelList" class="space-y-2"></ul>
            <button id="newChannelBtn" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">New Channel</button>
            <button id="newPrivateChannelBtn" class="mt-2 bg-green-500 text-white px-4 py-2 rounded">New Private Chat</button>
        </div>
        <div class="w-3/4">
            <div id="chatWindow" class="bg-white p-4 rounded-lg shadow">
                <h2 id="currentChannel" class="text-xl font-semibold mb-2">Select a channel</h2>
                <div id="messages" class="h-64 overflow-y-auto mb-4"></div>
                <form id="messageForm" class="flex">
                    <input type="text" id="messageInput" class="flex-grow border rounded-l px-4 py-2" placeholder="Type your message...">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentChannelId = null;

        function loadChannels() {
            fetch('/api/channels')
                .then(response => response.json())
                .then(channels => {
                    const channelList = document.getElementById('channelList');
                    channelList.innerHTML = '';
                    channels.forEach(channel => {
                        const li = document.createElement('li');
                        li.textContent = channel.name;
                        li.className = 'cursor-pointer hover:bg-gray-200 p-2 rounded';
                        li.onclick = () => selectChannel(channel.id);
                        channelList.appendChild(li);
                    });
                });
        }

        function selectChannel(channelId) {
            currentChannelId = channelId;
            fetch(`/api/channels/${channelId}`)
                .then(response => response.json())
                .then(channel => {
                    document.getElementById('currentChannel').textContent = channel.name;
                    loadMessages(channelId);
                    subscribeToChannel(channelId);
                });
        }

        function subscribeToChannel(channelId) {
            if (window.Echo) {
                console.log(`Subscribing to channel: channel.${channelId}`);
                if (currentChannelId) {
                    console.log(`Leaving channel: channel.${currentChannelId}`);
                    window.Echo.leave(`channel.${currentChannelId}`);
                }
                window.Echo.private(`channel.${channelId}`)
                    .listen('NewMessage', (e) => {
                        console.log('Received new message:', e);
                        appendMessage(e.message);
                    });
                currentChannelId = channelId;
            } else {
                console.error('Echo is not initialized');
            }
        }

        function loadMessages(channelId) {
            fetch(`/api/channels/${channelId}/messages`)
                .then(response => response.json())
                .then(messages => {
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = '';
                    messages.forEach(appendMessage);
                });
        }

        function appendMessage(message) {
            const messagesDiv = document.getElementById('messages');
            if (!document.getElementById(`message-${message.id}`)) {
                const p = document.createElement('p');
                p.id = `message-${message.id}`;
                p.innerHTML = `<strong>${message.user.name}:</strong> ${message.content}`;
                messagesDiv.appendChild(p);
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
        }

        document.getElementById('messageForm').onsubmit = function(e) {
            e.preventDefault();
            const content = document.getElementById('messageInput').value;
            if (content && currentChannelId) {
                fetch('/api/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        content: content,
                        channel_id: currentChannelId
                    })
                })
                    .then(response => response.json())
                    .then(message => {
                        document.getElementById('messageInput').value = '';
                        appendMessage(message);
                    });
            }
        };

        document.getElementById('newChannelBtn').onclick = function() {
            const name = prompt('Enter channel name:');
            if (name) {
                fetch('/api/channels', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name: name,
                        is_private: false,
                        user_ids: [{{ auth()->id() }}]
                    })
                })
                    .then(response => response.json())
                    .then(() => {
                        loadChannels();
                    });
            }
        };

        document.getElementById('newPrivateChannelBtn').onclick = function() {
            const userId = prompt('Enter user ID to start private chat:');
            if (userId) {
                fetch('/api/channels/private', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        loadChannels();
                        selectChannel(data.channel.id);
                    });
            }
        };

        loadChannels();
    });
</script>
</body>
</html>
