class GameCarousel {
    constructor(carouselSelector = '#carouselExampleIndicators') {
        this.carouselElement = document.querySelector(carouselSelector);
        if (!this.carouselElement) return;

        this.carousel = null;
        this.slideTimer = null;
        this.videoTimer = null;

        this.pendingExpandedAfterFullscreenExit = false;
        this.activeToken = 0;

        this.AUTO_PLAY_INTERVAL = 10000;
        this.VIDEO_RESUME_DELAY = 5000;
        this.VIDEO_FALLBACK_DELAY = 5000;

        this.init();
    }

    init() {
        this.carousel = new bootstrap.Carousel(this.carouselElement, {
            interval: false,
            wrap: true,
            pause: false
        });

        this.bindEvents();
        this.setupMediaInteractions();
        this.setupButtons();
        this.updateIcons();
        this.syncActiveSlide();
    }

    isEnlarged() {
        const panel = this.getPanel();
        if (!panel) return false;
        return document.fullscreenElement === panel || panel.classList.contains('carousel-expanded');
    }

    handleStateChange() {
        this.updateIcons();

        const panel = this.getPanel();
        if (panel) {
            panel.querySelectorAll('.game-image').forEach(img => {
                img.style.cursor = this.isEnlarged() ? 'default' : 'zoom-in';
            });
        }

        if (this.isEnlarged()) {
            this.clearTimers();
        } else {
            this.resumeAutoplay();
        }
    }

    resumeAutoplay() {
        if (this.isEnlarged()) return;

        const activeItem = this.carouselElement.querySelector('.carousel-item.active');
        if (!activeItem) return;

        const video = activeItem.querySelector('video');
        if (video) {
            if (video.ended) {
                this.carousel.next();
            }
        } else {
            this.startImageTimer(this.activeToken);
        }
    }

    updateIcons() {
        const panel = this.getPanel();
        if (!panel) return;

        const expandBtn = panel.querySelector('#btnCarouselExpand');
        const fullscreenBtn = panel.querySelector('#btnCarouselFullscreen');

        if (expandBtn) {
            const icon = expandBtn.querySelector('.material-symbols-outlined');
            const isExpanded = panel.classList.contains('carousel-expanded');
            
            icon.textContent = isExpanded ? 'close_fullscreen' : 'open_in_full';
            expandBtn.title = isExpanded ? 'Minimizar' : 'Agrandar';
        }

        if (fullscreenBtn) {
            const icon = fullscreenBtn.querySelector('.material-symbols-outlined');
            const isFullscreen = document.fullscreenElement === panel;
            
            icon.textContent = isFullscreen ? 'fullscreen_exit' : 'fullscreen';
            fullscreenBtn.title = isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa';
        }
    }

    bindEvents() {
        this.carouselElement.addEventListener('slide.bs.carousel', () => {
            this.clearTimers();
            this.pauseAllVideos();
        });

        this.carouselElement.addEventListener('slid.bs.carousel', () => {
            this.activeToken++;
            this.syncActiveSlide();
        });

        document.addEventListener('keydown', (e) => this.onKeyDown(e));

        document.addEventListener('fullscreenchange', () => {
            const panel = this.getPanel();
            if (!panel) return;

            if (!document.fullscreenElement) {
                if (this.pendingExpandedAfterFullscreenExit) {
                    panel.classList.add('carousel-expanded');
                    this.pendingExpandedAfterFullscreenExit = false;
                } else {
                    panel.classList.remove('carousel-expanded');
                }
            }
            this.handleStateChange(); 
        });

        document.addEventListener('click', (e) => {
            const panel = this.getPanel();
            if (!panel) return;

            if (panel.classList.contains('carousel-expanded') && !panel.contains(e.target)) {
                panel.classList.remove('carousel-expanded');
                this.handleStateChange();
            }
        });
    }

    getPanel() {
        return this.carouselElement?.closest('.game-media-panel') || null;
    }

    onKeyDown(e) {
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            this.carousel?.prev();
            return;
        }

        if (e.key === 'ArrowRight') {
            e.preventDefault();
            this.carousel?.next();
            return;
        }
    }

    syncActiveSlide() {
        const token = this.activeToken;
        const activeItem = this.carouselElement.querySelector('.carousel-item.active');
        if (!activeItem) return;

        const video = activeItem.querySelector('video');
        if (video) {
            this.playVideo(video, activeItem, token);
        } else {
            this.startImageTimer(token);
        }
    }

    startImageTimer(token = this.activeToken) {
        this.clearTimers();
        if (this.isEnlarged()) return; 

        this.slideTimer = setTimeout(() => {
            if (token !== this.activeToken) return;
            if (this.carousel) this.carousel.next();
        }, this.AUTO_PLAY_INTERVAL);
    }

    playVideo(video, activeItem, token = this.activeToken) {
        this.clearTimers();

        video.onended = null;
        video.muted = true;
        video.playsInline = true;
        video.setAttribute('playsinline', '');

        try {
            video.currentTime = 0;
        } catch (_) {}

        const resumeNext = () => {
            if (this.isEnlarged()) return; 
            
            this.videoTimer = setTimeout(() => {
                if (token !== this.activeToken) return;
                if (!this.carousel) return;

                const currentActive = this.carouselElement.querySelector('.carousel-item.active');
                if (currentActive === activeItem) {
                    this.carousel.next();
                }
            }, this.VIDEO_RESUME_DELAY);
        };

        const playPromise = video.play();

        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise
                .then(() => {
                    if (token !== this.activeToken) return;
                })
                .catch(err => {
                    console.log('No se pudo iniciar el vídeo automáticamente:', err);

                    this.videoTimer = setTimeout(() => {
                        if (token !== this.activeToken) return;
                        if (!this.carousel) return;
                        if (this.isEnlarged()) return;

                        const currentActive = this.carouselElement.querySelector('.carousel-item.active');
                        if (currentActive === activeItem) {
                            this.carousel.next();
                        }
                    }, this.VIDEO_FALLBACK_DELAY);
                });
        }

        video.onended = () => {
            if (token !== this.activeToken) return;
            if (!this.isEnlarged()) {
                resumeNext();
            }
        };
    }

    pauseAllVideos() {
        this.carouselElement.querySelectorAll('video').forEach(video => {
            video.onended = null;
            if (!video.paused) {
                video.pause();
            }
        });
    }

    clearTimers() {
        if (this.slideTimer) {
            clearTimeout(this.slideTimer);
            this.slideTimer = null;
        }

        if (this.videoTimer) {
            clearTimeout(this.videoTimer);
            this.videoTimer = null;
        }
    }

    setupMediaInteractions() {
        const panel = this.getPanel();
        if (!panel) return;

        this.carouselElement.querySelectorAll('.carousel-item').forEach(item => {
            const video = item.querySelector('video');
            const img = item.querySelector('.game-image');

            if (video) {
                video.style.cursor = 'pointer';

                video.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    if (video.paused) {
                        video.play().catch(() => {});
                    } else {
                        video.pause();
                    }
                });
            }

            if (img) {
                img.style.cursor = 'zoom-in';

                img.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    if (this.isEnlarged()) return;

                    panel.classList.add('carousel-expanded');
                    this.handleStateChange();
                });
            }
        });
    }

    setupButtons() {
        const panel = this.getPanel();
        if (!panel) return;

        const controls = document.createElement('div');
        controls.className = 'carousel-actions';
        controls.innerHTML = `
            <button type="button" class="carousel-action-btn" id="btnCarouselExpand" title="Agrandar">
                <span class="material-symbols-outlined">open_in_full</span>
            </button>
            <button type="button" class="carousel-action-btn" id="btnCarouselFullscreen" title="Pantalla completa">
                <span class="material-symbols-outlined">fullscreen</span>
            </button>
        `;
        panel.appendChild(controls);

        const expandBtn = controls.querySelector('#btnCarouselExpand');
        const fullscreenBtn = controls.querySelector('#btnCarouselFullscreen');

        expandBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            const isFullscreen = document.fullscreenElement === panel;
            const isExpanded = panel.classList.contains('carousel-expanded');

            if (isFullscreen) {
                this.pendingExpandedAfterFullscreenExit = true;
                try {
                    await document.exitFullscreen();
                } catch (err) {
                    console.log(err);
                }
                return;
            }

            panel.classList.toggle('carousel-expanded', !isExpanded);
            this.handleStateChange();
        });

        fullscreenBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            this.pendingExpandedAfterFullscreenExit = false;
            panel.classList.remove('carousel-expanded');

            try {
                if (document.fullscreenElement === panel) {
                    await document.exitFullscreen();
                } else {
                    await panel.requestFullscreen();
                }
            } catch (err) {
                console.log(err);
            }
        });
    }

    destroy() {
        this.clearTimers();
        this.pauseAllVideos();

        if (this.carousel) {
            this.carousel.dispose();
            this.carousel = null;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.gameCarousel = new GameCarousel('#carouselExampleIndicators');
});