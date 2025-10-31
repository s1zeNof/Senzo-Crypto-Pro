<?php
/**
 * AJAX Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SCP_Ajax {
    
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        // Portfolio actions
        add_action('wp_ajax_scp_add_portfolio_item', array($this, 'add_portfolio_item'));
        add_action('wp_ajax_scp_update_portfolio_item', array($this, 'update_portfolio_item'));
        add_action('wp_ajax_scp_delete_portfolio_item', array($this, 'delete_portfolio_item'));
        add_action('wp_ajax_scp_get_portfolio', array($this, 'get_portfolio'));
        add_action('wp_ajax_scp_refresh_prices', array($this, 'refresh_prices'));
        
        // Journal actions
        add_action('wp_ajax_scp_save_journal_entry', array($this, 'save_journal_entry'));
        add_action('wp_ajax_scp_delete_journal_entry', array($this, 'delete_journal_entry'));
        add_action('wp_ajax_scp_publish_journal_entry', array($this, 'publish_journal_entry'));
        add_action('wp_ajax_scp_get_journal_entries', array($this, 'get_journal_entries'));
        
        // API actions
        add_action('wp_ajax_scp_search_crypto', array($this, 'search_crypto'));
        add_action('wp_ajax_scp_get_crypto_price', array($this, 'get_crypto_price'));
        add_action('wp_ajax_scp_get_trending', array($this, 'get_trending'));
    }
    
    /**
     * Verify nonce
     */
    private function verify_nonce() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'scp_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            exit;
        }
    }
    
    /**
     * Add portfolio item
     */
    public function add_portfolio_item() {
        $this->verify_nonce();
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'User not logged in'));
        }
        
        $data = array(
            'exchange' => sanitize_text_field($_POST['exchange']),
            'symbol' => sanitize_text_field($_POST['symbol']),
            'amount' => floatval($_POST['amount']),
            'buy_price' => floatval($_POST['buy_price']),
        );
        
        $portfolio = SCP_Portfolio::instance();
        $result = $portfolio->add_item($user_id, $data);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Item added successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to add item'));
        }
    }
    
    /**
     * Update portfolio item
     */
    public function update_portfolio_item() {
        $this->verify_nonce();
        
        $id = intval($_POST['id']);
        $data = array(
            'amount' => floatval($_POST['amount']),
            'buy_price' => floatval($_POST['buy_price']),
        );
        
        $portfolio = SCP_Portfolio::instance();
        $result = $portfolio->update_item($id, $data);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Item updated successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update item'));
        }
    }
    
    /**
     * Delete portfolio item
     */
    public function delete_portfolio_item() {
        $this->verify_nonce();
        
        $id = intval($_POST['id']);
        
        $portfolio = SCP_Portfolio::instance();
        $result = $portfolio->delete_item($id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Item deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete item'));
        }
    }
    
    /**
     * Get portfolio
     */
    public function get_portfolio() {
        $this->verify_nonce();
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'User not logged in'));
        }
        
        $portfolio = SCP_Portfolio::instance();
        $data = $portfolio->get_user_portfolio($user_id);
        
        wp_send_json_success($data);
    }
    
    /**
     * Refresh prices
     */
    public function refresh_prices() {
        $this->verify_nonce();
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'User not logged in'));
        }
        
        $portfolio = SCP_Portfolio::instance();
        $data = $portfolio->get_user_portfolio($user_id);
        
        wp_send_json_success($data);
    }
    
    /**
     * Save journal entry
     */
    public function save_journal_entry() {
        $this->verify_nonce();
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'User not logged in'));
        }
        
        $data = array(
            'title' => sanitize_text_field($_POST['title']),
            'content' => wp_kses_post($_POST['content']),
            'entry_date' => sanitize_text_field($_POST['entry_date']),
            'tags' => sanitize_text_field($_POST['tags']),
            'mood' => sanitize_text_field($_POST['mood']),
            'trade_result' => sanitize_text_field($_POST['trade_result']),
        );
        
        $journal = SCP_Journal::instance();
        
        if (isset($_POST['entry_id']) && $_POST['entry_id']) {
            $result = $journal->update_entry(intval($_POST['entry_id']), $data);
        } else {
            $result = $journal->add_entry($user_id, $data);
        }
        
        if ($result) {
            wp_send_json_success(array('message' => 'Entry saved successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save entry'));
        }
    }
    
    /**
     * Delete journal entry
     */
    public function delete_journal_entry() {
        $this->verify_nonce();
        
        $id = intval($_POST['id']);
        
        $journal = SCP_Journal::instance();
        $result = $journal->delete_entry($id);
        
        if ($result) {
            wp_send_json_success(array('message' => 'Entry deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete entry'));
        }
    }
    
    /**
     * Publish journal entry to blog
     */
    public function publish_journal_entry() {
        $this->verify_nonce();
        
        $entry_id = intval($_POST['entry_id']);
        $post_type = sanitize_text_field($_POST['post_type']);
        
        $journal = SCP_Journal::instance();
        $post_id = $journal->publish_to_blog($entry_id, $post_type);
        
        if ($post_id) {
            wp_send_json_success(array(
                'message' => 'Entry published successfully',
                'post_url' => get_permalink($post_id)
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to publish entry'));
        }
    }
    
    /**
     * Get journal entries
     */
    public function get_journal_entries() {
        $this->verify_nonce();
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'User not logged in'));
        }
        
        $journal = SCP_Journal::instance();
        $entries = $journal->get_entries($user_id);
        
        wp_send_json_success($entries);
    }
    
    /**
     * Search crypto
     */
    public function search_crypto() {
        $this->verify_nonce();
        
        $query = sanitize_text_field($_POST['query']);
        
        $api = SCP_API_Manager::instance();
        $results = $api->search_coins($query);
        
        wp_send_json_success($results);
    }
    
    /**
     * Get crypto price
     */
    public function get_crypto_price() {
        $this->verify_nonce();
        
        $symbol = sanitize_text_field($_POST['symbol']);
        
        $api = SCP_API_Manager::instance();
        $prices = $api->get_crypto_prices(array($symbol));
        
        wp_send_json_success($prices);
    }
    
    /**
     * Get trending coins
     */
    public function get_trending() {
        $this->verify_nonce();
        
        $api = SCP_API_Manager::instance();
        $data = $api->get_trending_coins();
        
        wp_send_json_success($data);
    }
}
