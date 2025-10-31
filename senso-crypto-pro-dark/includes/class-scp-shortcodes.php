<?php
/**
 * Shortcodes Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SCP_Shortcodes {
    
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        add_shortcode('scp_dashboard', array($this, 'dashboard'));
        add_shortcode('scp_portfolio', array($this, 'portfolio'));
        add_shortcode('scp_journal', array($this, 'journal'));
        add_shortcode('scp_crypto_price', array($this, 'crypto_price'));
        add_shortcode('scp_trending', array($this, 'trending'));
        add_shortcode('scp_portfolio_chart', array($this, 'portfolio_chart'));
    }
    
    /**
     * Dashboard shortcode
     */
    public function dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>Будь ласка, увійдіть щоб переглянути dashboard.</p>';
        }
        
        ob_start();
        include SCP_PLUGIN_DIR . 'templates/dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * Portfolio shortcode
     */
    public function portfolio($atts) {
        if (!is_user_logged_in()) {
            return '<p>Будь ласка, увійдіть щоб переглянути портфель.</p>';
        }
        
        $atts = shortcode_atts(array(
            'view' => 'full' // full, compact, chart
        ), $atts);
        
        ob_start();
        include SCP_PLUGIN_DIR . 'templates/portfolio.php';
        return ob_get_clean();
    }
    
    /**
     * Journal shortcode
     */
    public function journal($atts) {
        if (!is_user_logged_in()) {
            return '<p>Будь ласка, увійдіть щоб переглянути щоденник.</p>';
        }
        
        ob_start();
        include SCP_PLUGIN_DIR . 'templates/journal.php';
        return ob_get_clean();
    }
    
    /**
     * Crypto price widget
     */
    public function crypto_price($atts) {
        $atts = shortcode_atts(array(
            'symbol' => 'BTC',
            'currency' => 'USD',
        ), $atts);
        
        $api = SCP_API_Manager::instance();
        $prices = $api->get_crypto_prices(array($atts['symbol']));
        
        ob_start();
        ?>
        <div class="scp-crypto-price-widget" data-symbol="<?php echo esc_attr($atts['symbol']); ?>">
            <div class="scp-price-loading">Завантаження...</div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Trending coins shortcode
     */
    public function trending($atts) {
        $atts = shortcode_atts(array(
            'limit' => 5,
        ), $atts);
        
        ob_start();
        include SCP_PLUGIN_DIR . 'templates/trending.php';
        return ob_get_clean();
    }
    
    /**
     * Portfolio chart shortcode
     */
    public function portfolio_chart($atts) {
        if (!is_user_logged_in()) {
            return '<p>Будь ласка, увійдіть щоб переглянути графік.</p>';
        }
        
        $atts = shortcode_atts(array(
            'type' => 'line', // line, pie, doughnut
            'days' => 30,
        ), $atts);
        
        ob_start();
        include SCP_PLUGIN_DIR . 'templates/portfolio-chart.php';
        return ob_get_clean();
    }
}
