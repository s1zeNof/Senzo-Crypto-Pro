/**
 * Portfolio JS
 */

(function($) {
    'use strict';
    
    const scpPortfolio = {
        
        init: function() {
            this.bindEvents();
            this.loadPortfolio();
        },
        
        bindEvents: function() {
            // Add portfolio item form
            $(document).on('submit', '#scpAddPortfolioForm', this.addPortfolioItem);
            
            // Update portfolio item form
            $(document).on('submit', '#scpUpdatePortfolioForm', this.updatePortfolioItem);
            
            // Delete portfolio item
            $(document).on('click', '.scp-delete-portfolio-item', this.deletePortfolioItem);
            
            // Refresh prices
            $(document).on('click', '.scp-refresh-prices', this.refreshPrices);
        },
        
        loadPortfolio: function() {
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'scp_get_portfolio',
                    nonce: scpData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        scpPortfolio.renderPortfolio(response.data);
                    }
                }
            });
        },
        
        addPortfolioItem: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            const btnText = $btn.html();
            
            $btn.prop('disabled', true).html('<span class="scp-loading"></span> Додавання...');
            
            const formData = $form.serialize();
            
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: formData + '&action=scp_add_portfolio_item&nonce=' + scpData.nonce,
                success: function(response) {
                    if (response.success) {
                        scpPortfolio.showNotification('Монету додано успішно!', 'success');
                        $form[0].reset();
                        scpPortfolio.closeModal('scpAddPortfolioModal');
                        scpPortfolio.loadPortfolio();
                    } else {
                        scpPortfolio.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    scpPortfolio.showNotification('Помилка при додаванні монети', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(btnText);
                }
            });
        },
        
        updatePortfolioItem: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $btn = $form.find('button[type="submit"]');
            const btnText = $btn.html();
            
            $btn.prop('disabled', true).html('<span class="scp-loading"></span> Оновлення...');
            
            const formData = $form.serialize();
            
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: formData + '&action=scp_update_portfolio_item&nonce=' + scpData.nonce,
                success: function(response) {
                    if (response.success) {
                        scpPortfolio.showNotification('Оновлено успішно!', 'success');
                        scpPortfolio.closeModal('scpEditPortfolioModal');
                        scpPortfolio.loadPortfolio();
                    } else {
                        scpPortfolio.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    scpPortfolio.showNotification('Помилка при оновленні', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(btnText);
                }
            });
        },
        
        deletePortfolioItem: function(e) {
            e.preventDefault();
            
            if (!confirm('Ти впевнений що хочеш видалити цю позицію?')) {
                return;
            }
            
            const id = $(this).data('id');
            
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'scp_delete_portfolio_item',
                    nonce: scpData.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        scpPortfolio.showNotification('Видалено успішно!', 'success');
                        scpPortfolio.loadPortfolio();
                    } else {
                        scpPortfolio.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    scpPortfolio.showNotification('Помилка при видаленні', 'error');
                }
            });
        },
        
        refreshPrices: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const btnText = $btn.html();
            
            $btn.prop('disabled', true).html('<span class="scp-loading"></span>');
            
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'scp_refresh_prices',
                    nonce: scpData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        scpPortfolio.showNotification('Ціни оновлено!', 'success');
                        scpPortfolio.renderPortfolio(response.data);
                    } else {
                        scpPortfolio.showNotification('Помилка оновлення', 'error');
                    }
                },
                complete: function() {
                    $btn.prop('disabled', false).html(btnText);
                }
            });
        },
        
        renderPortfolio: function(data) {
            // Update total values
            $('.scp-total-value').text('$' + this.formatNumber(data.total_value));
            $('.scp-total-invested').text('$' + this.formatNumber(data.total_invested));
            $('.scp-profit-loss').text('$' + this.formatNumber(Math.abs(data.profit_loss)));
            $('.scp-profit-loss-percent').text(this.formatNumber(data.profit_loss_percent) + '%');
            
            // Update profit/loss class
            if (data.profit_loss >= 0) {
                $('.scp-profit-loss').addClass('positive').removeClass('negative');
            } else {
                $('.scp-profit-loss').addClass('negative').removeClass('positive');
            }
            
            // Render portfolio items
            if (data.items && data.items.length > 0) {
                let html = '';
                data.items.forEach(function(item) {
                    html += scpPortfolio.renderPortfolioItem(item);
                });
                $('.scp-portfolio-grid').html(html);
            } else {
                $('.scp-portfolio-grid').html('<div class="scp-empty-state"><p>📊 Твій портфель ще порожній.</p></div>');
            }
            
            // Animate items
            if (typeof gsap !== 'undefined') {
                gsap.from('.scp-portfolio-item', {
                    opacity: 0,
                    y: 20,
                    stagger: 0.1,
                    duration: 0.6,
                    ease: 'power2.out'
                });
            }
        },
        
        renderPortfolioItem: function(item) {
            const profitClass = item.profit_loss >= 0 ? 'positive' : 'negative';
            const profitSign = item.profit_loss >= 0 ? '+' : '';
            
            return `
                <div class="scp-portfolio-item">
                    <div class="scp-portfolio-item-header">
                        <div class="scp-portfolio-item-symbol">
                            <span class="scp-crypto-icon">${item.symbol}</span>
                            <span class="scp-exchange-badge">${item.exchange}</span>
                        </div>
                        <div class="scp-portfolio-item-actions">
                            <button class="scp-icon-btn scp-edit-portfolio-item" data-id="${item.id}">✏️</button>
                            <button class="scp-icon-btn scp-delete-portfolio-item" data-id="${item.id}">🗑️</button>
                        </div>
                    </div>
                    
                    <div class="scp-portfolio-item-content">
                        <div class="scp-portfolio-row">
                            <span class="scp-label">Кількість:</span>
                            <span class="scp-value">${this.formatNumber(item.amount, 8)}</span>
                        </div>
                        
                        <div class="scp-portfolio-row">
                            <span class="scp-label">Поточна ціна:</span>
                            <span class="scp-value">$${this.formatNumber(item.current_price)}</span>
                        </div>
                        
                        <div class="scp-portfolio-row">
                            <span class="scp-label">Вартість:</span>
                            <span class="scp-value">$${this.formatNumber(item.current_value)}</span>
                        </div>
                        
                        <div class="scp-portfolio-row">
                            <span class="scp-label">P&L:</span>
                            <span class="scp-value ${profitClass}">
                                ${profitSign}$${this.formatNumber(Math.abs(item.profit_loss))}
                                (${this.formatNumber(item.profit_loss_percent)}%)
                            </span>
                        </div>
                    </div>
                </div>
            `;
        },
        
        formatNumber: function(num, decimals = 2) {
            if (num === null || num === undefined) return '0.00';
            return parseFloat(num).toFixed(decimals).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        
        showNotification: function(message, type = 'success') {
            const notification = $(`
                <div class="scp-notification scp-notification-${type}">
                    ${message}
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
        
        closeModal: function(modalId) {
            $('#' + modalId).fadeOut();
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        if ($('.scp-portfolio').length || $('.scp-dashboard').length) {
            scpPortfolio.init();
        }
    });
    
    // Make functions global
    window.scpEditPortfolioItem = function(id) {
        // Load item data and show edit modal
        // Implementation here
    };
    
    window.scpDeletePortfolioItem = function(id) {
        if (confirm('Ти впевнений що хочеш видалити цю позицію?')) {
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'scp_delete_portfolio_item',
                    nonce: scpData.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        scpPortfolio.showNotification('Видалено успішно!', 'success');
                        location.reload();
                    }
                }
            });
        }
    };
    
    window.scpShowAddPortfolioModal = function() {
        $('#scpAddPortfolioModal').fadeIn();
    };
    
    window.scpCloseModal = function(modalId) {
        $('#' + modalId).fadeOut();
    };
    
})(jQuery);
