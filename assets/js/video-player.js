/**
 * Video Player Module with Social Media Support
 * Supports YouTube, Facebook, Instagram embeds and native video
 */

class VideoPlayer {
    constructor() {
        this.modal = document.getElementById('videoModal');
        this.container = document.getElementById('video-container');
        this.initializeEventListeners();
    }

    initializeEventListeners() {
        // Video play buttons
        document.querySelectorAll('.btn-play-video').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const videoUrl = e.currentTarget.dataset.video;
                const videoType = e.currentTarget.dataset.type || this.detectVideoType(videoUrl);
                this.playVideo(videoUrl, videoType);
            });
        });

        // Close modal cleanup
        if (this.modal) {
            this.modal.addEventListener('hidden.bs.modal', () => {
                this.stopVideo();
            });
        }
    }

    // Detect video type from URL
    detectVideoType(url) {
        if (url.includes('youtube.com') || url.includes('youtu.be')) return 'youtube';
        if (url.includes('facebook.com') || url.includes('fb.watch')) return 'facebook';
        if (url.includes('instagram.com')) return 'instagram';
        if (url.includes('tiktok.com')) return 'tiktok';
        if (url.includes('vimeo.com')) return 'vimeo';
        return 'native';
    }

    // Play video based on type
    playVideo(url, type) {
        let embedHtml = '';

        switch (type) {
            case 'youtube':
                embedHtml = this.getYouTubeEmbed(url);
                break;
            case 'facebook':
                embedHtml = this.getFacebookEmbed(url);
                break;
            case 'instagram':
                embedHtml = this.getInstagramEmbed(url);
                break;
            case 'vimeo':
                embedHtml = this.getVimeoEmbed(url);
                break;
            case 'tiktok':
                embedHtml = this.getTikTokEmbed(url);
                break;
            default:
                embedHtml = this.getNativeEmbed(url);
        }

        this.container.innerHTML = embedHtml;
        
        // Show modal
        const bsModal = new bootstrap.Modal(this.modal);
        bsModal.show();
    }

    // YouTube embed
    getYouTubeEmbed(url) {
        const videoId = this.extractYouTubeId(url);
        return `<iframe 
            src="https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
            allowfullscreen>
        </iframe>`;
    }

    // Extract YouTube video ID
    extractYouTubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    // Facebook embed
    getFacebookEmbed(url) {
        const encodedUrl = encodeURIComponent(url);
        return `<iframe 
            src="https://www.facebook.com/plugins/video.php?href=${encodedUrl}&show_text=0&autoplay=1" 
            frameborder="0" 
            allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share" 
            allowfullscreen>
        </iframe>`;
    }

    // Instagram embed
    getInstagramEmbed(url) {
        // Instagram requires their embed.js script
        return `<blockquote class="instagram-media" data-instgrm-captioned data-instgrm-permalink="${url}">
            <a href="${url}">View on Instagram</a>
        </blockquote>
        <script async src="https://www.instagram.com/embed.js"></script>`;
    }

    // Vimeo embed
    getVimeoEmbed(url) {
        const videoId = url.split('/').pop();
        return `<iframe 
            src="https://player.vimeo.com/video/${videoId}?autoplay=1" 
            frameborder="0" 
            allow="autoplay; fullscreen; picture-in-picture" 
            allowfullscreen>
        </iframe>`;
    }

    // TikTok embed
    getTikTokEmbed(url) {
        const videoId = url.split('/video/').pop()?.split('?')[0];
        return `<blockquote class="tiktok-embed" cite="${url}" data-video-id="${videoId}">
            <a href="${url}">View on TikTok</a>
        </blockquote>
        <script async src="https://www.tiktok.com/embed.js"></script>`;
    }

    // Native HTML5 video
    getNativeEmbed(url) {
        return `<video controls autoplay class="w-100">
            <source src="${url}" type="video/mp4">
            Your browser does not support video playback.
        </video>`;
    }

    // Stop video playback
    stopVideo() {
        this.container.innerHTML = '';
    }

    // Share video to social media
    shareVideo(platform, url, title) {
        const shareUrls = {
            facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
            twitter: `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`,
            linkedin: `https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(url)}&title=${encodeURIComponent(title)}`,
            whatsapp: `https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`,
            pinterest: `https://pinterest.com/pin/create/button/?url=${encodeURIComponent(url)}&description=${encodeURIComponent(title)}`
        };

        const shareUrl = shareUrls[platform];
        if (shareUrl) {
            window.open(shareUrl, '_blank', 'width=600,height=400');
        }
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    window.videoPlayer = new VideoPlayer();
});
