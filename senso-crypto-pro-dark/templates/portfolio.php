<?php
/**
 * Portfolio Template
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
if (!$user_id) {
    echo '<p class="scp-error">Будь ласка, увійди щоб переглянути портфель.</p>';
    return;
}

$portfolio = SCP_Portfolio::instance();
$portfolio_data = $portfolio->get_user_portfolio($user_id);
$view = isset($atts['view']) ? $atts['view'] : 'full';
?>

<div class="scp-portfolio scp-fade-in">
    <?php if ($view === 'full' || $view === 'compact'): ?>
        <div class="scp-portfolio-header" data-aos="fade-down">
            <h2 class="scp-gradient-text">💰 Мій портфель</h2>
            <div class="scp-portfolio-actions">
                <button class="scp-btn-neon scp-refresh-prices" data-tooltip="Оновити ціни">
                    🔄 Refresh
                </button>
                <button class="scp-btn scp-btn-primary" onclick="scpShowAddPortfolioModal()">
                    + Додати монету
                </button>
            </div>
        </div>
        
        <div class="scp-portfolio-summary" data-aos="fade-up">
            <div class="scp-summary-card scp-glass-card">
                <div class="scp-summary-label">Загальна вартість</div>
                <div class="scp-summary-value scp-total-value">
                    $<?php echo number_format($portfolio_data['total_value'], 2); ?>
                </div>
            </div>
            
            <div class="scp-summary-card scp-glass-card">
                <div class="scp-summary-label">Інвестовано</div>
                <div class="scp-summary-value scp-total-invested">
                    $<?php echo number_format($portfolio_data['total_invested'], 2); ?>
                </div>
            </div>
            
            <div class="scp-summary-card scp-glass-card">
                <div class="scp-summary-label">P&L</div>
                <div class="scp-summary-value scp-profit-loss <?php echo $portfolio_data['profit_loss'] >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $portfolio_data['profit_loss'] >= 0 ? '+' : ''; ?>$<?php echo number_format($portfolio_data['profit_loss'], 2); ?>
                    <span class="scp-profit-loss-percent">(<?php echo number_format($portfolio_data['profit_loss_percent'], 2); ?>%)</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="scp-portfolio-grid">
        <!-- Portfolio items will be loaded here via AJAX -->
    </div>
</div>

<style>
.scp-portfolio-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.scp-portfolio-header h2 {
    font-size: 2rem;
    margin: 0;
}

.scp-portfolio-actions {
    display: flex;
    gap: 1rem;
}

.scp-portfolio-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.scp-summary-card {
    padding: 2rem;
    text-align: center;
}

.scp-summary-label {
    font-size: 0.875rem;
    color: var(--scp-text-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.5rem;
}

.scp-summary-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--scp-text-primary);
}

.scp-summary-value span {
    font-size: 1.25rem;
    font-weight: 600;
}
</style>
