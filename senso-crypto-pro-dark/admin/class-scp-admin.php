<?php
/**
 * Admin Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SCP_Admin {
    
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_menu_pages'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_menu_pages() {
        add_menu_page(
            __('Senso Crypto Pro', 'senso-crypto-pro'),
            __('Senso Crypto', 'senso-crypto-pro'),
            'manage_options',
            'senso-crypto-pro',
            array($this, 'admin_page'),
            'dashicons-chart-line',
            30
        );
        
        add_submenu_page(
            'senso-crypto-pro',
            __('Налаштування', 'senso-crypto-pro'),
            __('Налаштування', 'senso-crypto-pro'),
            'manage_options',
            'scp-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Main admin page
     */
    public function admin_page() {
        ?>
        <div class="wrap scp-admin-wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <div class="scp-admin-dashboard">
                <div class="scp-stats-grid">
                    <div class="scp-stat-card">
                        <h3>Всього користувачів з портфелем</h3>
                        <p class="scp-stat-number"><?php echo $this->get_total_users(); ?></p>
                    </div>
                    
                    <div class="scp-stat-card">
                        <h3>Записів у щоденнику</h3>
                        <p class="scp-stat-number"><?php echo $this->get_total_journal_entries(); ?></p>
                    </div>
                    
                    <div class="scp-stat-card">
                        <h3>Academy статей</h3>
                        <p class="scp-stat-number"><?php echo $this->get_total_academy_posts(); ?></p>
                    </div>
                    
                    <div class="scp-stat-card">
                        <h3>Trading Ideas</h3>
                        <p class="scp-stat-number"><?php echo $this->get_total_ideas(); ?></p>
                    </div>
                </div>
                
                <div class="scp-admin-info">
                    <h2>Доступні шорткоди:</h2>
                    <ul>
                        <li><code>[scp_dashboard]</code> - Повний dashboard користувача</li>
                        <li><code>[scp_portfolio]</code> - Портфель користувача</li>
                        <li><code>[scp_journal]</code> - Щоденник трейдера</li>
                        <li><code>[scp_crypto_price symbol="BTC"]</code> - Віджет ціни криптовалюти</li>
                        <li><code>[scp_trending limit="5"]</code> - Трендові монети</li>
                        <li><code>[scp_portfolio_chart type="line" days="30"]</code> - Графік портфеля</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('scp_settings');
                do_settings_sections('scp_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Get total users with portfolio
     */
    private function get_total_users() {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_portfolio';
        return $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table");
    }
    
    /**
     * Get total journal entries
     */
    private function get_total_journal_entries() {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_journal';
        return $wpdb->get_var("SELECT COUNT(*) FROM $table");
    }
    
    /**
     * Get total academy posts
     */
    private function get_total_academy_posts() {
        return wp_count_posts('scp_academy')->publish;
    }
    
    /**
     * Get total ideas
     */
    private function get_total_ideas() {
        return wp_count_posts('scp_ideas')->publish;
    }
}
