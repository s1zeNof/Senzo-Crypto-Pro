<?php
/**
 * Public Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SCP_Public {
    
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        add_filter('template_include', array($this, 'custom_templates'));
        add_action('wp_footer', array($this, 'add_gsap_animations'));
    }
    
    /**
     * Load custom templates
     */
    public function custom_templates($template) {
        if (is_singular('scp_academy')) {
            $custom_template = SCP_PLUGIN_DIR . 'templates/single-academy.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        if (is_singular('scp_ideas')) {
            $custom_template = SCP_PLUGIN_DIR . 'templates/single-ideas.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Add GSAP animations
     */
    public function add_gsap_animations() {
        ?>
        <script>
        jQuery(document).ready(function($) {
            // Animate elements on scroll
            if (typeof gsap !== 'undefined' && typeof ScrollTrigger !== 'undefined') {
                gsap.registerPlugin(ScrollTrigger);
                
                // Fade in elements
                gsap.utils.toArray('.scp-fade-in').forEach(function(elem) {
                    gsap.from(elem, {
                        scrollTrigger: {
                            trigger: elem,
                            start: 'top 80%',
                            toggleActions: 'play none none reverse'
                        },
                        opacity: 0,
                        y: 50,
                        duration: 1
                    });
                });
                
                // Scale elements
                gsap.utils.toArray('.scp-scale-in').forEach(function(elem) {
                    gsap.from(elem, {
                        scrollTrigger: {
                            trigger: elem,
                            start: 'top 80%',
                            toggleActions: 'play none none reverse'
                        },
                        scale: 0.8,
                        opacity: 0,
                        duration: 0.8,
                        ease: 'back.out(1.7)'
                    });
                });
            }
        });
        </script>
        <?php
    }
}
