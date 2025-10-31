<?php
/**
 * Plugin Name: Senso Crypto Pro
 * Plugin URI: https://dev-senzocrypto.pantheonsite.io
 * Description: Професійний плагін для керування крипто-портфелем, блогом та щоденником трейдера
 * Version: 1.0.0
 * Author: Senso Crypto
 * Author URI: https://dev-senzocrypto.pantheonsite.io
 * Text Domain: senso-crypto-pro
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('SCP_VERSION', '1.0.0');
define('SCP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SCP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SCP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Senso Crypto Pro Class
 */
final class Senso_Crypto_Pro {
    
    /**
     * The single instance of the class
     */
    private static $_instance = null;
    
    /**
     * Main Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->includes();
    }
    
    /**
     * Hook into actions and filters
     */
    private function init_hooks() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        add_action('init', array($this, 'init'), 0);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
    }
    
    /**
     * Include required core files
     */
    public function includes() {
        // Core
        require_once SCP_PLUGIN_DIR . 'includes/class-scp-database.php';
        require_once SCP_PLUGIN_DIR . 'includes/class-scp-api-manager.php';
        require_once SCP_PLUGIN_DIR . 'includes/class-scp-portfolio.php';
        require_once SCP_PLUGIN_DIR . 'includes/class-scp-journal.php';
        require_once SCP_PLUGIN_DIR . 'includes/class-scp-blog.php';
        
        // Admin
        if (is_admin()) {
            require_once SCP_PLUGIN_DIR . 'admin/class-scp-admin.php';
        }
        
        // Frontend
        require_once SCP_PLUGIN_DIR . 'public/class-scp-public.php';
        require_once SCP_PLUGIN_DIR . 'includes/class-scp-shortcodes.php';
        
        // AJAX Handlers
        require_once SCP_PLUGIN_DIR . 'includes/class-scp-ajax.php';
    }
    
    /**
     * Init when WordPress Initialises
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('senso-crypto-pro', false, dirname(SCP_PLUGIN_BASENAME) . '/languages');
        
        // Register Custom Post Types
        $this->register_post_types();
        
        // Initialize classes
        SCP_Database::instance();
        SCP_API_Manager::instance();
        SCP_Portfolio::instance();
        SCP_Journal::instance();
        SCP_Blog::instance();
        SCP_Shortcodes::instance();
        SCP_Ajax::instance();
        
        if (is_admin()) {
            SCP_Admin::instance();
        } else {
            SCP_Public::instance();
        }
    }
    
    /**
     * Register Custom Post Types
     */
    private function register_post_types() {
        // Academy Post Type
        register_post_type('scp_academy', array(
            'labels' => array(
                'name' => __('Academy', 'senso-crypto-pro'),
                'singular_name' => __('Academy Article', 'senso-crypto-pro'),
                'add_new' => __('Додати статтю', 'senso-crypto-pro'),
                'add_new_item' => __('Додати нову статтю', 'senso-crypto-pro'),
                'edit_item' => __('Редагувати статтю', 'senso-crypto-pro'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-book-alt',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields'),
            'show_in_rest' => true,
            'taxonomies' => array('category', 'post_tag'),
        ));
        
        // Trading Ideas Post Type
        register_post_type('scp_ideas', array(
            'labels' => array(
                'name' => __('Trading Ideas', 'senso-crypto-pro'),
                'singular_name' => __('Trading Idea', 'senso-crypto-pro'),
                'add_new' => __('Додати ідею', 'senso-crypto-pro'),
            ),
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-lightbulb',
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'author'),
            'show_in_rest' => true,
        ));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        // Styles
        wp_enqueue_style('scp-main', SCP_PLUGIN_URL . 'assets/css/main.css', array(), SCP_VERSION);
        wp_enqueue_style('scp-dashboard', SCP_PLUGIN_URL . 'assets/css/dashboard.css', array(), SCP_VERSION);
        
        // AOS - Animate On Scroll
        wp_enqueue_style('aos', 'https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css', array(), '2.3.4');
        
        // Libraries CSS
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0');
        
        // Scripts
        wp_enqueue_script('aos', 'https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js', array(), '2.3.4', true);
        wp_enqueue_script('gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js', array(), '3.12.5', true);
        wp_enqueue_script('gsap-scroll-trigger', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js', array('gsap'), '3.12.5', true);
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', array(), '4.4.0', true);
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
        
        // Main plugin script
        wp_enqueue_script('scp-main', SCP_PLUGIN_URL . 'assets/js/main.js', array('jquery', 'gsap', 'chartjs', 'aos'), SCP_VERSION, true);
        wp_enqueue_script('scp-portfolio', SCP_PLUGIN_URL . 'assets/js/portfolio.js', array('jquery', 'chartjs'), SCP_VERSION, true);
        wp_enqueue_script('scp-journal', SCP_PLUGIN_URL . 'assets/js/journal.js', array('jquery'), SCP_VERSION, true);
        
        // Initialize AOS
        wp_add_inline_script('aos', 'if(typeof AOS !== "undefined") { AOS.init({ duration: 800, once: true, offset: 100 }); }');
        
        // Localize script
        wp_localize_script('scp-main', 'scpData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('scp_nonce'),
            'userId' => get_current_user_id(),
            'apiUrl' => rest_url('scp/v1/'),
            'restNonce' => wp_create_nonce('wp_rest'),
        ));
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        wp_enqueue_style('scp-admin', SCP_PLUGIN_URL . 'assets/css/admin.css', array(), SCP_VERSION);
        wp_enqueue_script('scp-admin', SCP_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), SCP_VERSION, true);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        SCP_Database::create_tables();
        
        // Set default options
        add_option('scp_version', SCP_VERSION);
        add_option('scp_db_version', '1.0.0');
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

/**
 * Returns the main instance of Senso_Crypto_Pro
 */
function SCP() {
    return Senso_Crypto_Pro::instance();
}

// Initialize the plugin
SCP();
