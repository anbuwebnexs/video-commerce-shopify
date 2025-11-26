/**
 * WebRTC Video Streaming Module
 * Handles peer-to-peer video connections for live commerce
 */

class VideoStream {
    constructor(socket, config) {
        this.socket = socket;
        this.config = config || {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };
        this.localStream = null;
        this.peerConnection = null;
        this.remoteVideo = document.getElementById('remoteVideo');
        this.localVideo = document.getElementById('localVideo');
        
        this.initializeSocketEvents();
        this.initializeUIControls();
    }

    // Initialize socket event listeners for signaling
    initializeSocketEvents() {
        this.socket.on('offer', async (data) => {
            await this.handleOffer(data.offer, data.from);
        });

        this.socket.on('answer', async (data) => {
            await this.handleAnswer(data.answer);
        });

        this.socket.on('ice-candidate', async (data) => {
            await this.handleIceCandidate(data.candidate);
        });

        this.socket.on('user-joined', (userId) => {
            console.log('User joined:', userId);
            this.showNotification('A viewer has joined the stream');
        });

        this.socket.on('user-left', (userId) => {
            console.log('User left:', userId);
            this.showNotification('A viewer has left');
        });
    }

    // Initialize UI control buttons
    initializeUIControls() {
        document.getElementById('startStream')?.addEventListener('click', () => this.startStreaming());
        document.getElementById('joinStream')?.addEventListener('click', () => this.joinStream());
        document.getElementById('toggleVideo')?.addEventListener('click', () => this.toggleVideo());
        document.getElementById('toggleAudio')?.addEventListener('click', () => this.toggleAudio());
        document.getElementById('shareScreen')?.addEventListener('click', () => this.shareScreen());
        document.getElementById('endCall')?.addEventListener('click', () => this.endCall());
    }

    // Start streaming (broadcaster)
    async startStreaming() {
        try {
            this.localStream = await navigator.mediaDevices.getUserMedia({
                video: { width: 1280, height: 720 },
                audio: true
            });
            this.localVideo.srcObject = this.localStream;
            
            this.createPeerConnection();
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
            });

            const offer = await this.peerConnection.createOffer();
            await this.peerConnection.setLocalDescription(offer);
            
            const roomId = document.getElementById('roomInput').value;
            this.socket.emit('offer', { offer, room: roomId });
            
            this.showNotification('Streaming started!');
        } catch (error) {
            console.error('Error starting stream:', error);
            this.showNotification('Failed to start stream: ' + error.message, 'error');
        }
    }

    // Join existing stream (viewer)
    async joinStream() {
        try {
            this.createPeerConnection();
            const roomId = document.getElementById('roomInput').value;
            this.socket.emit('join-stream', { room: roomId });
            this.showNotification('Joining stream...');
        } catch (error) {
            console.error('Error joining stream:', error);
        }
    }

    // Create RTCPeerConnection
    createPeerConnection() {
        this.peerConnection = new RTCPeerConnection(this.config);

        this.peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                const roomId = document.getElementById('roomInput').value;
                this.socket.emit('ice-candidate', { 
                    candidate: event.candidate, 
                    room: roomId 
                });
            }
        };

        this.peerConnection.ontrack = (event) => {
            this.remoteVideo.srcObject = event.streams[0];
        };

        this.peerConnection.onconnectionstatechange = () => {
            console.log('Connection state:', this.peerConnection.connectionState);
        };
    }

    // Handle incoming offer
    async handleOffer(offer, from) {
        this.createPeerConnection();
        await this.peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
        
        const answer = await this.peerConnection.createAnswer();
        await this.peerConnection.setLocalDescription(answer);
        
        this.socket.emit('answer', { answer, to: from });
    }

    // Handle incoming answer
    async handleAnswer(answer) {
        await this.peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
    }

    // Handle ICE candidate
    async handleIceCandidate(candidate) {
        if (this.peerConnection) {
            await this.peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
        }
    }

    // Toggle video on/off
    toggleVideo() {
        if (this.localStream) {
            const videoTrack = this.localStream.getVideoTracks()[0];
            videoTrack.enabled = !videoTrack.enabled;
            const btn = document.getElementById('toggleVideo');
            btn.innerHTML = videoTrack.enabled ? 
                '<i class="bi bi-camera-video-fill"></i>' : 
                '<i class="bi bi-camera-video-off-fill"></i>';
        }
    }

    // Toggle audio on/off
    toggleAudio() {
        if (this.localStream) {
            const audioTrack = this.localStream.getAudioTracks()[0];
            audioTrack.enabled = !audioTrack.enabled;
            const btn = document.getElementById('toggleAudio');
            btn.innerHTML = audioTrack.enabled ? 
                '<i class="bi bi-mic-fill"></i>' : 
                '<i class="bi bi-mic-mute-fill"></i>';
        }
    }

    // Share screen
    async shareScreen() {
        try {
            const screenStream = await navigator.mediaDevices.getDisplayMedia({
                video: true
            });
            const videoTrack = screenStream.getVideoTracks()[0];
            
            if (this.peerConnection) {
                const sender = this.peerConnection.getSenders().find(s => 
                    s.track.kind === 'video'
                );
                sender.replaceTrack(videoTrack);
            }
            
            this.localVideo.srcObject = screenStream;
            
            videoTrack.onended = () => {
                this.stopScreenShare();
            };
        } catch (error) {
            console.error('Screen share error:', error);
        }
    }

    // Stop screen share and return to camera
    async stopScreenShare() {
        const cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
        const videoTrack = cameraStream.getVideoTracks()[0];
        
        if (this.peerConnection) {
            const sender = this.peerConnection.getSenders().find(s => 
                s.track.kind === 'video'
            );
            sender.replaceTrack(videoTrack);
        }
        this.localVideo.srcObject = cameraStream;
    }

    // End call
    endCall() {
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
        }
        if (this.peerConnection) {
            this.peerConnection.close();
        }
        this.localVideo.srcObject = null;
        this.remoteVideo.srcObject = null;
        this.showNotification('Stream ended');
    }

    // Show notification
    showNotification(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : 'info'} position-fixed`;
        toast.style.cssText = 'top: 80px; right: 20px; z-index: 9999;';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (typeof socket !== 'undefined') {
        window.videoStream = new VideoStream(socket);
    }
});
