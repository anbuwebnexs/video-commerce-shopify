# Pure PHP Implementation - NO Node.js Required

This project has been refactored to use **100% PHP** instead of Node.js. All real-time functionality is now handled with pure PHP APIs.

## Architecture Changes

### BEFORE (with Node.js)
```
Browser -> Socket.io (JavaScript) -> Node.js Server (Express + Socket.io)
```

### AFTER (Pure PHP - AJAX Polling)
```
Browser -> AJAX -> PHP APIs -> File System / Database
```

## PHP APIs Implemented

### 1. Chat API (`api/chat.php`)
**Replaces**: Socket.io real-time chat

**Endpoints**:
- `POST /api/chat.php?action=send&room=1` - Send message
- `GET /api/chat.php?action=fetch&room=1` - Fetch all messages
- `GET /api/chat.php?action=get_last&room=1` - Get latest message

**Usage**:
```javascript
// Send message via AJAX
fetch('/api/chat.php?action=send&room=1', {
    method: 'POST',
    body: JSON.stringify({
        username: 'User',
        message: 'Hello!'
    })
});

// Poll for messages every 2 seconds
setInterval(() => {
    fetch('/api/chat.php?action=fetch&room=1')
        .then(r => r.json())
        .then(data => console.log(data.messages));
}, 2000);
```

### 2. WebRTC Signaling API (`api/webrtc-signal.php`)
**Replaces**: Socket.io WebRTC offer/answer exchange

**Endpoints**:
- `POST /api/webrtc-signal.php?action=send-offer` - Send SDP offer
- `GET /api/webrtc-signal.php?action=get-offer?room=X` - Get offer
- `POST /api/webrtc-signal.php?action=send-answer` - Send SDP answer
- `GET /api/webrtc-signal.php?action=get-answer?room=X` - Get answer
- `POST /api/webrtc-signal.php?action=send-ice` - Send ICE candidate
- `GET /api/webrtc-signal.php?action=get-ice?room=X` - Get ICE candidates

**Usage**:
```javascript
// Send WebRTC offer
fetch('/api/webrtc-signal.php?action=send-offer', {
    method: 'POST',
    body: JSON.stringify({
        room: 'room-123',
        signal: offerObject,
        peer: 'peer-id'
    })
});

// Poll for answer
fetch('/api/webrtc-signal.php?action=get-answer?room=room-123')
    .then(r => r.json())
    .then(data => {
        if (data.signal) {
            peerConnection.setRemoteDescription(data.signal);
        }
    });
```

## How It Works

### Chat Flow
1. User types message
2. Browser sends AJAX POST to `api/chat.php?action=send`
3. Message stored in database
4. Browser polls `api/chat.php?action=fetch` every 2 seconds
5. New messages displayed in real-time

### WebRTC Flow
1. Broadcaster creates offer with `RTCPeerConnection`
2. Sends offer to `api/webrtc-signal.php?action=send-offer` (stored in temp files)
3. Viewer polls `api/webrtc-signal.php?action=get-offer`
4. Viewer creates answer, sends to `api/webrtc-signal.php?action=send-answer`
5. Both peers poll for ICE candidates from `/action=get-ice`
6. Connection established peer-to-peer via WebRTC

## Advantages of This Approach

✅ **Single Language**: Pure PHP - no Node.js required  
✅ **Simpler Deployment**: No separate server/process management  
✅ **Lower Resource Usage**: No Node.js runtime  
✅ **Existing Skills**: Use standard PHP  
✅ **Reliable**: Uses proven polling mechanism  
✅ **Scalable**: File-based or database storage  

## Disadvantages

⚠️ **Polling Latency**: ~2-3 second delay (vs real-time)  
⚠️ **Higher Database Load**: Frequent polling queries  
⚠️ **Not WebSocket**: No true bi-directional streaming  

## Future Optimization

For production, consider:
- **PHP WebSocket Server** (Ratchet, ReactPHP)
- **Database Triggers** instead of file storage
- **Redis Cache** for signal storage
- **Nginx SSE** (Server-Sent Events) for one-way real-time

## Files Removed

- `node/server.js` - Socket.io server
- `package.json` - Node.js dependencies

## Files Added

- `api/chat.php` - AJAX-based chat API
- `api/webrtc-signal.php` - AJAX-based WebRTC signaling
- `assets/js/chat-ajax.js` - AJAX polling chat client
- `assets/js/webrtc-ajax.js` - AJAX WebRTC signaling client

## Migration Complete ✅

All functionality is now in **pure PHP**. Enjoy!
