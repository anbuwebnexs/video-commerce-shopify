/**
 * Socket.io Server for Video Commerce
 * Handles WebRTC signaling and real-time chat
 */

const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const cors = require('cors');

const app = express();
const server = http.createServer(app);

// Configure Socket.io with CORS
const io = new Server(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

app.use(cors());
app.use(express.json());

// Store active rooms and users
const rooms = new Map();
const users = new Map();

// Socket.io connection handling
io.on('connection', (socket) => {
    console.log('User connected:', socket.id);

    // Join a room
    socket.on('join-room', (roomId) => {
        socket.join(roomId);
        
        if (!rooms.has(roomId)) {
            rooms.set(roomId, new Set());
        }
        rooms.get(roomId).add(socket.id);
        
        // Notify others in the room
        socket.to(roomId).emit('user-joined', socket.id);
        
        console.log(`User ${socket.id} joined room ${roomId}`);
    });

    // Handle WebRTC offer
    socket.on('offer', (data) => {
        socket.to(data.room).emit('offer', {
            offer: data.offer,
            from: socket.id
        });
    });

    // Handle WebRTC answer
    socket.on('answer', (data) => {
        socket.to(data.to).emit('answer', {
            answer: data.answer,
            from: socket.id
        });
    });

    // Handle ICE candidates
    socket.on('ice-candidate', (data) => {
        socket.to(data.room).emit('ice-candidate', {
            candidate: data.candidate,
            from: socket.id
        });
    });

    // Handle chat messages
    socket.on('chat-message', (data) => {
        const message = {
            id: Date.now(),
            userId: socket.id,
            username: data.username || 'Anonymous',
            message: data.message,
            timestamp: new Date().toISOString()
        };
        
        io.to(data.room).emit('chat-message', message);
    });

    // Handle typing indicator
    socket.on('typing', (data) => {
        socket.to(data.room).emit('user-typing', {
            userId: socket.id,
            username: data.username
        });
    });

    // Handle product highlight during stream
    socket.on('highlight-product', (data) => {
        socket.to(data.room).emit('product-highlighted', {
            productId: data.productId,
            productData: data.productData
        });
    });

    // Handle disconnection
    socket.on('disconnect', () => {
        console.log('User disconnected:', socket.id);
        
        // Remove from all rooms
        rooms.forEach((members, roomId) => {
            if (members.has(socket.id)) {
                members.delete(socket.id);
                socket.to(roomId).emit('user-left', socket.id);
            }
        });
    });
});

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ status: 'ok', timestamp: new Date().toISOString() });
});

// Get active rooms
app.get('/rooms', (req, res) => {
    const activeRooms = [];
    rooms.forEach((members, roomId) => {
        if (members.size > 0) {
            activeRooms.push({
                roomId,
                viewers: members.size
            });
        }
    });
    res.json(activeRooms);
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
    console.log(`Socket.io server running on port ${PORT}`);
});
