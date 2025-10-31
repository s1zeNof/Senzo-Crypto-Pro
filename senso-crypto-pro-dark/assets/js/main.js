/**
 * Main JS - Animations & Utilities
 */

(function($) {
    'use strict';
    
    const scpMain = {
        
        init: function() {
            this.initGSAP();
            this.initScrollAnimations();
            this.initCryptoWidgets();
            this.initTooltips();
        },
        
        initGSAP: function() {
            if (typeof gsap === 'undefined') return;
            
            // Register plugins
            if (typeof ScrollTrigger !== 'undefined') {
                gsap.registerPlugin(ScrollTrigger);
            }
            
            // Initial page load animation
            gsap.from('body', {
                opacity: 0,
                duration: 0.5,
                ease: 'power2.out'
            });
        },
        
        initScrollAnimations: function() {
            if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
            
            // Fade in on scroll
            gsap.utils.toArray('.scp-fade-in').forEach(function(elem) {
                gsap.from(elem, {
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 85%',
                        toggleActions: 'play none none reverse',
                        markers: false
                    },
                    opacity: 0,
                    y: 50,
                    duration: 1,
                    ease: 'power2.out'
                });
            });
            
            // Scale in on scroll
            gsap.utils.toArray('.scp-scale-in').forEach(function(elem, index) {
                gsap.from(elem, {
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 85%',
                        toggleActions: 'play none none reverse'
                    },
                    scale: 0.8,
                    opacity: 0,
                    duration: 0.8,
                    delay: index * 0.1,
                    ease: 'back.out(1.7)'
                });
            });
            
            // Slide in from left
            gsap.utils.toArray('.scp-slide-left').forEach(function(elem) {
                gsap.from(elem, {
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 85%',
                        toggleActions: 'play none none reverse'
                    },
                    x: -100,
                    opacity: 0,
                    duration: 1,
                    ease: 'power3.out'
                });
            });
            
            // Slide in from right
            gsap.utils.toArray('.scp-slide-right').forEach(function(elem) {
                gsap.from(elem, {
                    scrollTrigger: {
                        trigger: elem,
                        start: 'top 85%',
                        toggleActions: 'play none none reverse'
                    },
                    x: 100,
                    opacity: 0,
                    duration: 1,
                    ease: 'power3.out'
                });
            });
            
            // Number counter animation
            gsap.utils.toArray('.scp-counter').forEach(function(elem) {
                const target = parseFloat(elem.getAttribute('data-target'));
                
                ScrollTrigger.create({
                    trigger: elem,
                    start: 'top 80%',
                    onEnter: function() {
                        gsap.to(elem, {
                            textContent: target,
                            duration: 2,
                            ease: 'power2.out',
                            snap: { textContent: 1 },
                            onUpdate: function() {
                                elem.textContent = Math.ceil(elem.textContent).toLocaleString();
                            }
                        });
                    }
                });
            });
        },
        
        initCryptoWidgets: function() {
            // Auto-update crypto prices
            $('.scp-crypto-price-widget').each(function() {
                const $widget = $(this);
                const symbol = $widget.data('symbol');
                
                scpMain.updateCryptoPrice($widget, symbol);
                
                // Update every 30 seconds
                setInterval(function() {
                    scpMain.updateCryptoPrice($widget, symbol);
                }, 30000);
            });
        },
        
        updateCryptoPrice: function($widget, symbol) {
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'scp_get_crypto_price',
                    nonce: scpData.nonce,
                    symbol: symbol
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const coinId = scpMain.getCoinGeckoId(symbol);
                        if (response.data[coinId]) {
                            const price = response.data[coinId].usd;
                            const change = response.data[coinId].usd_24h_change;
                            
                            const html = `
                                <div class="scp-crypto-widget">
                                    <div class="scp-crypto-symbol">${symbol}</div>
                                    <div class="scp-crypto-price">$${price.toLocaleString()}</div>
                                    <div class="scp-crypto-change ${change >= 0 ? 'positive' : 'negative'}">
                                        ${change >= 0 ? '▲' : '▼'} ${Math.abs(change).toFixed(2)}%
                                    </div>
                                </div>
                            `;
                            
                            $widget.html(html);
                            
                            // Animate
                            if (typeof gsap !== 'undefined') {
                                gsap.from($widget.find('.scp-crypto-widget'), {
                                    scale: 0.9,
                                    opacity: 0,
                                    duration: 0.5
                                });
                            }
                        }
                    }
                }
            });
        },
        
        getCoinGeckoId: function(symbol) {
            const map = {
                'BTC': 'bitcoin',
                'ETH': 'ethereum',
                'BNB': 'binancecoin',
                'XRP': 'ripple',
                'ADA': 'cardano',
                'DOGE': 'dogecoin',
                'SOL': 'solana'
            };
            return map[symbol.toUpperCase()] || 'bitcoin';
        },
        
        initTooltips: function() {
            // Simple tooltip implementation
            $('[data-tooltip]').hover(
                function() {
                    const text = $(this).data('tooltip');
                    const tooltip = $(`<div class="scp-tooltip">${text}</div>`);
                    
                    $('body').append(tooltip);
                    
                    const pos = $(this).offset();
                    tooltip.css({
                        top: pos.top - tooltip.outerHeight() - 10,
                        left: pos.left + ($(this).outerWidth() / 2) - (tooltip.outerWidth() / 2)
                    });
                    
                    gsap.from(tooltip, {
                        opacity: 0,
                        y: 10,
                        duration: 0.3
                    });
                },
                function() {
                    $('.scp-tooltip').remove();
                }
            );
        },
        
        // Utility functions
        formatNumber: function(num, decimals = 2) {
            if (num === null || num === undefined) return '0.00';
            return parseFloat(num).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        
        formatCurrency: function(num, currency = 'USD') {
            const symbols = {
                'USD': '$',
                'EUR': '€',
                'UAH': '₴'
            };
            
            return (symbols[currency] || '$') + this.formatNumber(num);
        },
        
        showNotification: function(message, type = 'success') {
            const notification = $(`
                <div class="scp-notification scp-notification-${type}">
                    <div class="scp-notification-icon">
                        ${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}
                    </div>
                    <div class="scp-notification-message">${message}</div>
                </div>
            `);
            
            $('body').append(notification);
            
            setTimeout(function() {
                notification.addClass('scp-show');
            }, 100);
            
            setTimeout(function() {
                notification.removeClass('scp-show');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 3000);
        },
        
        // Smooth scroll to element
        scrollTo: function(target, offset = 0) {
            if (typeof gsap === 'undefined') {
                $('html, body').animate({
                    scrollTop: $(target).offset().top - offset
                }, 500);
            } else {
                gsap.to(window, {
                    duration: 1,
                    scrollTo: {
                        y: target,
                        offsetY: offset
                    },
                    ease: 'power2.out'
                });
            }
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        scpMain.init();
    });
    
    // Make utilities global
    window.scpMain = scpMain;
    
    // Parallax effect for hero sections
    $(window).on('scroll', function() {
        if ($('.scp-hero').length) {
            const scroll = $(window).scrollTop();
            $('.scp-hero-bg').css({
                'transform': 'translateY(' + scroll * 0.5 + 'px)'
            });
        }
    });
    
    // Mobile menu toggle
    $('.scp-mobile-menu-toggle').on('click', function() {
        $(this).toggleClass('active');
        $('.scp-mobile-menu').toggleClass('active');
    });
    
    // Close modals on outside click
    $(document).on('click', '.scp-modal', function(e) {
        if ($(e.target).is('.scp-modal')) {
            $(this).fadeOut();
        }
    });
    
    // Close modals on ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.scp-modal').fadeOut();
        }
    });
    
})(jQuery);

// Add notification styles dynamically
const notificationStyles = `
    <style>
        .scp-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 10000;
            opacity: 0;
            transform: translateX(100px);
            transition: all 0.3s ease;
        }
        
        .scp-notification.scp-show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .scp-notification-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.25rem;
        }
        
        .scp-notification-success .scp-notification-icon {
            background: #10b981;
            color: white;
        }
        
        .scp-notification-error .scp-notification-icon {
            background: #ef4444;
            color: white;
        }
        
        .scp-notification-info .scp-notification-icon {
            background: #3b82f6;
            color: white;
        }
        
        .scp-notification-message {
            font-weight: 500;
            color: #1e293b;
        }
        
        .scp-tooltip {
            position: absolute;
            background: #1e293b;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            z-index: 10001;
            pointer-events: none;
        }
        
        .scp-tooltip::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #1e293b;
        }
    </style>
`;

jQuery(document).ready(function($) {
    $('head').append(notificationStyles);
});
