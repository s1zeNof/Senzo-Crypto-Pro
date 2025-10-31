<?php
/**
 * Trending Coins Template
 */

if (!defined('ABSPATH')) exit;

$api = SCP_API_Manager::instance();
$trending = $api->get_trending_coins();

$limit = isset($atts['limit']) ? intval($atts['limit']) : 5;
?>

<div class="scp-trending-wrapper scp-glass-card">
    <div class="scp-trending-header">
        <h3 class="scp-neon-text">🔥 Trending Coins</h3>
    </div>
    
    <div class="scp-trending-list">
        <?php 
        if ($trending && isset($trending['coins'])):
            $coins = array_slice($trending['coins'], 0, $limit);
            foreach ($coins as $index => $coin):
                $item = $coin['item'];
        ?>
            <div class="scp-trending-item scp-fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s">
                <div class="scp-trending-rank">#<?php echo ($index + 1); ?></div>
                <div class="scp-trending-info">
                    <div class="scp-trending-name">
                        <?php if (isset($item['thumb'])): ?>
                            <img src="<?php echo esc_url($item['thumb']); ?>" alt="<?php echo esc_attr($item['name']); ?>" class="scp-coin-thumb">
                        <?php endif; ?>
                        <span class="scp-coin-name"><?php echo esc_html($item['name']); ?></span>
                        <span class="scp-coin-symbol"><?php echo esc_html($item['symbol']); ?></span>
                    </div>
                    <?php if (isset($item['data']['price'])): ?>
                        <div class="scp-trending-price">
                            $<?php echo number_format($item['data']['price'], 2); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php 
            endforeach;
        else:
        ?>
            <div class="scp-empty-state">
                <p>Unable to load trending coins</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.scp-trending-wrapper {
    padding: 2rem;
}

.scp-trending-header h3 {
    margin: 0 0 1.5rem 0;
    font-size: 1.5rem;
}

.scp-trending-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.scp-trending-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(30, 41, 59, 0.4);
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.1);
    transition: all 0.3s ease;
}

.scp-trending-item:hover {
    background: rgba(30, 41, 59, 0.6);
    border-color: var(--scp-cyan);
    transform: translateX(5px);
}

.scp-trending-rank {
    font-size: 1.5rem;
    font-weight: 800;
    background: var(--scp-gradient-mixed);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    min-width: 40px;
}

.scp-trending-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.scp-trending-name {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.scp-coin-thumb {
    width: 32px;
    height: 32px;
    border-radius: 50%;
}

.scp-coin-name {
    font-weight: 700;
    color: var(--scp-text-primary);
}

.scp-coin-symbol {
    color: var(--scp-text-secondary);
    text-transform: uppercase;
    font-size: 0.875rem;
}

.scp-trending-price {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--scp-cyan);
    font-family: 'Space Mono', monospace;
}
</style>
