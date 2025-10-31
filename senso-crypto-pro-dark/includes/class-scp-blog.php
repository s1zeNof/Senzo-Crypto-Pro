<?php
/**
 * Blog Helper Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SCP_Blog {
    
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        // Add custom taxonomies
        add_action('init', array($this, 'register_taxonomies'));
    }
    
    /**
     * Register custom taxonomies
     */
    public function register_taxonomies() {
        // Category taxonomy for academy
        register_taxonomy('academy_category', 'scp_academy', array(
            'labels' => array(
                'name' => __('Категорії Academy', 'senso-crypto-pro'),
                'singular_name' => __('Категорія', 'senso-crypto-pro'),
            ),
            'hierarchical' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
        ));
        
        // Difficulty level taxonomy
        register_taxonomy('difficulty', array('scp_academy'), array(
            'labels' => array(
                'name' => __('Рівень складності', 'senso-crypto-pro'),
                'singular_name' => __('Рівень', 'senso-crypto-pro'),
            ),
            'hierarchical' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
        ));
    }
}
