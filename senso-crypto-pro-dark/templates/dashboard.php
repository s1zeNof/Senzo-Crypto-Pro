<?php
/**
 * Dashboard Template - Fixed Version
 */

if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
    echo '<div class="scp-empty-state scp-glass-card" style="padding: 4rem 2rem; text-align: center;">
        <p style="font-size: 1.25rem; color: var(--scp-text-secondary);">Please login to view your dashboard 🔐</p>
    </div>';
    return;
}

$user_id = get_current_user_id();
$portfolio = SCP_Portfolio::instance();
$journal = SCP_Journal::instance();

$portfolio_data = $portfolio->get_user_portfolio($user_id);
$portfolio_stats = $portfolio->get_performance_stats($user_id);
$journal_stats = $journal->get_stats($user_id);
?>

<div class="scp-dashboard scp-fade-in">
    <!-- Header -->
    <div class="scp-dashboard-header">
        <h1>Welcome back, <?php echo esc_html(wp_get_current_user()->display_name); ?>! 👋</h1>
        <p class="scp-dashboard-subtitle">Here's your crypto portfolio overview</p>
    </div>
    
    <!-- Stats Grid -->
    <div class="scp-stats-grid">
        <!-- Total Value -->
        <div class="scp-stat-card" style="animation-delay: 0.1s">
            <div class="scp-stat-icon">💰</div>
            <div class="scp-stat-content">
                <h3>Total Portfolio</h3>
                <p class="scp-stat-value">$<?php echo number_format($portfolio_data['total_value'], 2); ?></p>
                <p class="scp-stat-change <?php echo $portfolio_data['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $portfolio_data['profit_loss'] >= 0 ? '▲' : '▼'; ?>
                    <?php echo number_format(abs($portfolio_data['profit_loss_percent']), 2); ?>%
                </p>
            </div>
        </div>
        
        <!-- Profit/Loss -->
        <div class="scp-stat-card" style="animation-delay: 0.2s">
            <div class="scp-stat-icon">📈</div>
            <div class="scp-stat-content">
                <h3>Profit / Loss</h3>
                <p class="scp-stat-value <?php echo $portfolio_data['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $portfolio_data['profit_loss'] >= 0 ? '+' : ''; ?>$<?php echo number_format($portfolio_data['profit_loss'], 2); ?>
                </p>
                <p class="scp-stat-label">All Time</p>
            </div>
        </div>
        
        <!-- Win Rate -->
        <div class="scp-stat-card" style="animation-delay: 0.3s">
            <div class="scp-stat-icon">🎯</div>
            <div class="scp-stat-content">
                <h3>Win Rate</h3>
                <p class="scp-stat-value"><?php echo number_format($portfolio_stats['win_rate'], 1); ?>%</p>
                <p class="scp-stat-label"><?php echo $portfolio_stats['winning_trades']; ?> wins / <?php echo $portfolio_stats['total_trades']; ?> total</p>
            </div>
        </div>
        
        <!-- Journal Entries -->
        <div class="scp-stat-card" style="animation-delay: 0.4s">
            <div class="scp-stat-icon">📝</div>
            <div class="scp-stat-content">
                <h3>Journal Entries</h3>
                <p class="scp-stat-value"><?php echo $journal_stats['total_entries']; ?></p>
                <p class="scp-stat-label"><?php echo $journal_stats['this_month']; ?> this month</p>
            </div>
        </div>
    </div>
    
    <!-- Portfolio Section -->
    <div class="scp-dashboard-section">
        <div class="scp-section-header">
            <h2>📊 Portfolio</h2>
            <button class="scp-btn scp-btn-primary" onclick="scpShowAddPortfolioModal()">
                <span>+</span> Add Coin
            </button>
        </div>
        
        <div class="scp-portfolio-grid">
            <?php if (!empty($portfolio_data['items'])): ?>
                <?php foreach ($portfolio_data['items'] as $item): ?>
                    <div class="scp-portfolio-item scp-fade-in">
                        <div class="scp-portfolio-item-header">
                            <div class="scp-portfolio-item-symbol">
                                <span class="scp-crypto-icon"><?php echo esc_html($item->symbol); ?></span>
                                <span class="scp-exchange-badge"><?php echo esc_html($item->exchange); ?></span>
                            </div>
                            <div class="scp-portfolio-item-actions">
                                <button class="scp-icon-btn" onclick="scpEditPortfolioItem(<?php echo $item->id; ?>)" title="Edit">✏️</button>
                                <button class="scp-icon-btn" onclick="scpDeletePortfolioItem(<?php echo $item->id; ?>)" title="Delete">🗑️</button>
                            </div>
                        </div>
                        
                        <div class="scp-portfolio-item-content">
                            <div class="scp-portfolio-row">
                                <span class="scp-label">Amount:</span>
                                <span class="scp-value"><?php echo number_format($item->amount, 8); ?></span>
                            </div>
                            
                            <div class="scp-portfolio-row">
                                <span class="scp-label">Current Price:</span>
                                <span class="scp-value">$<?php echo number_format($item->current_price, 2); ?></span>
                            </div>
                            
                            <div class="scp-portfolio-row">
                                <span class="scp-label">Value:</span>
                                <span class="scp-value">$<?php echo number_format($item->current_value, 2); ?></span>
                            </div>
                            
                            <div class="scp-portfolio-row">
                                <span class="scp-label">P&L:</span>
                                <span class="scp-value <?php echo $item->profit_loss >= 0 ? 'positive' : 'negative'; ?>">
                                    <?php echo $item->profit_loss >= 0 ? '+' : ''; ?>$<?php echo number_format($item->profit_loss, 2); ?>
                                    (<?php echo number_format($item->profit_loss_percent, 2); ?>%)
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="scp-empty-state">
                    <p>📊 Your portfolio is empty. Add your first assets!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Chart Section -->
    <div class="scp-dashboard-section">
        <?php echo do_shortcode('[scp_portfolio_chart type="line" days="30"]'); ?>
    </div>
</div>

<!-- Add Portfolio Modal -->
<div id="scpAddPortfolioModal" class="scp-modal">
    <div class="scp-modal-content">
        <div class="scp-modal-header">
            <h3>Add Coin to Portfolio</h3>
            <button class="scp-modal-close" onclick="scpCloseModal('scpAddPortfolioModal')">&times;</button>
        </div>
        <form id="scpAddPortfolioForm">
            <div class="scp-form-group">
                <label>Exchange</label>
                <select name="exchange" required>
                    <option value="">Select Exchange</option>
                    <option value="Binance">Binance</option>
                    <option value="ByBit">ByBit</option>
                    <option value="Coinbase">Coinbase</option>
                    <option value="Kraken">Kraken</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            
            <div class="scp-form-group">
                <label>Coin Symbol (e.g. BTC)</label>
                <input type="text" name="symbol" placeholder="BTC" required>
            </div>
            
            <div class="scp-form-group">
                <label>Amount</label>
                <input type="number" name="amount" step="0.00000001" placeholder="0.5" required>
            </div>
            
            <div class="scp-form-group">
                <label>Buy Price ($)</label>
                <input type="number" name="buy_price" step="0.01" placeholder="45000" required>
            </div>
            
            <div class="scp-form-group">
                <button type="submit" class="scp-btn scp-btn-primary" style="width: 100%;">
                    Add to Portfolio
                </button>
            </div>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    console.log('Dashboard loaded');
    
    // Add Portfolio Form Handler
    $('#scpAddPortfolioForm').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="scp-loading"></span> Adding...');
        
        const formData = new FormData(this);
        formData.append('action', 'scp_add_portfolio_item');
        formData.append('nonce', scpData.nonce);
        
        $.ajax({
            url: scpData.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    scpShowNotification('Coin added successfully! 🎉', 'success');
                    scpCloseModal('scpAddPortfolioModal');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    scpShowNotification(response.data.message || 'Error adding coin', 'error');
                }
            },
            error: function() {
                scpShowNotification('Network error. Please try again.', 'error');
            },
            complete: function() {
                $btn.prop('disabled', false).html(btnText);
            }
        });
    });
});

// Global functions
function scpShowAddPortfolioModal() {
    document.getElementById('scpAddPortfolioModal').style.display = 'flex';
}

function scpCloseModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function scpEditPortfolioItem(id) {
    alert('Edit functionality coming soon! Item ID: ' + id);
}

function scpDeletePortfolioItem(id) {
    if (!confirm('Are you sure you want to delete this position?')) {
        return;
    }
    
    jQuery.ajax({
        url: scpData.ajaxUrl,
        type: 'POST',
        data: {
            action: 'scp_delete_portfolio_item',
            nonce: scpData.nonce,
            id: id
        },
        success: function(response) {
            if (response.success) {
                scpShowNotification('Position deleted successfully! 🗑️', 'success');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                scpShowNotification(response.data.message || 'Error deleting position', 'error');
            }
        }
    });
}

function scpShowNotification(message, type) {
    const notification = jQuery('<div class="scp-notification scp-notification-' + type + '">' + message + '</div>');
    jQuery('body').append(notification);
    
    setTimeout(function() {
        notification.addClass('scp-show');
    }, 100);
    
    setTimeout(function() {
        notification.removeClass('scp-show');
        setTimeout(function() {
            notification.remove();
        }, 400);
    }, 3000);
}
</script>
