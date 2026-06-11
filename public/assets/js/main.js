/**
 * Main JavaScript File
 * PHP 5.6 Compatible Streaming Platform
 */

(function($) {
    'use strict';

    // Initialize when DOM is ready
    $(document).ready(function() {
        initTooltips();
        initSmoothScroll();
        initLazyLoad();
    });

    /**
     * Initialize Bootstrap tooltips
     */
    function initTooltips() {
        $('[data-toggle="tooltip"]').tooltip();
    }

    /**
     * Smooth scroll for anchor links
     */
    function initSmoothScroll() {
        $('a[href^="#"]').on('click', function(e) {
            var target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 70
                }, 1000);
            }
        });
    }

    /**
     * Lazy load images
     */
    function initLazyLoad() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(function(img) {
                imageObserver.observe(img);
            });
        }
    }

    /**
     * Save watch progress to server
     * @param {number} showId - Show ID
     * @param {string} episodeId - Episode ID
     * @param {number} progress - Watch progress in seconds
     */
    function saveWatchProgress(showId, episodeId, progress) {
        $.ajax({
            url: BASE_URL + '/api/watch/progress',
            method: 'POST',
            data: {
                show_id: showId,
                episode_id: episodeId,
                progress: progress
            },
            success: function(response) {
                console.log('Watch progress saved');
            },
            error: function(xhr, status, error) {
                console.error('Failed to save watch progress:', error);
            }
        });
    }

    /**
     * Auto-save watch progress every 30 seconds
     * @param {number} showId - Show ID
     * @param {string} episodeId - Episode ID
     * @param {Object} player - Video player instance
     */
    function autoSaveProgress(showId, episodeId, player) {
        setInterval(function() {
            if (player && typeof player.currentTime === 'function') {
                saveWatchProgress(showId, episodeId, Math.floor(player.currentTime()));
            }
        }, 30000);
    }

    /**
     * Format time from seconds to HH:MM:SS
     * @param {number} seconds
     * @return {string}
     */
    function formatTime(seconds) {
        var hrs = Math.floor(seconds / 3600);
        var mins = Math.floor((seconds % 3600) / 60);
        var secs = Math.floor(seconds % 60);

        var result = '';
        if (hrs > 0) {
            result += (hrs < 10 ? '0' : '') + hrs + ':';
        }
        result += (mins < 10 ? '0' : '') + mins + ':';
        result += (secs < 10 ? '0' : '') + secs;

        return result;
    }

    /**
     * Show loading spinner
     * @param {string} container - jQuery selector for container
     */
    function showLoading(container) {
        $(container).html('<div class="text-center py-5"><div class="loading-spinner"></div></div>');
    }

    /**
     * Hide loading spinner
     * @param {string} container - jQuery selector for container
     */
    function hideLoading(container) {
        $(container).find('.loading-spinner').remove();
    }

    // Export functions for global use
    window.DramaStream = {
        saveWatchProgress: saveWatchProgress,
        autoSaveProgress: autoSaveProgress,
        formatTime: formatTime,
        showLoading: showLoading,
        hideLoading: hideLoading
    };

})(window.jQuery);
