<?php
/**
 * Database Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SCP_Database {
    
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Portfolio table
        $table_portfolio = $wpdb->prefix . 'scp_portfolio';
        $sql_portfolio = "CREATE TABLE IF NOT EXISTS $table_portfolio (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            exchange varchar(50) NOT NULL,
            symbol varchar(20) NOT NULL,
            amount decimal(20,8) NOT NULL,
            buy_price decimal(20,8) NOT NULL,
            current_price decimal(20,8) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY exchange (exchange)
        ) $charset_collate;";
        
        // Portfolio history table
        $table_history = $wpdb->prefix . 'scp_portfolio_history';
        $sql_history = "CREATE TABLE IF NOT EXISTS $table_history (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            portfolio_id bigint(20),
            date date NOT NULL,
            total_value decimal(20,2) NOT NULL,
            profit_loss decimal(20,2) NOT NULL,
            profit_loss_percent decimal(10,2) NOT NULL,
            notes text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY date (date)
        ) $charset_collate;";
        
        // Journal entries table
        $table_journal = $wpdb->prefix . 'scp_journal';
        $sql_journal = "CREATE TABLE IF NOT EXISTS $table_journal (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            title varchar(255) NOT NULL,
            content longtext NOT NULL,
            entry_date date NOT NULL,
            is_public tinyint(1) DEFAULT 0,
            published_post_id bigint(20) DEFAULT NULL,
            tags text,
            mood varchar(50),
            trade_result varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY entry_date (entry_date),
            KEY is_public (is_public)
        ) $charset_collate;";
        
        // API Keys table
        $table_api = $wpdb->prefix . 'scp_api_keys';
        $sql_api = "CREATE TABLE IF NOT EXISTS $table_api (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            exchange varchar(50) NOT NULL,
            api_key varchar(255) NOT NULL,
            api_secret varchar(255) NOT NULL,
            api_passphrase varchar(255),
            is_active tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY exchange (exchange)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_portfolio);
        dbDelta($sql_history);
        dbDelta($sql_journal);
        dbDelta($sql_api);
    }
    
    /**
     * Get portfolio items
     */
    public function get_portfolio_items($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_portfolio';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ));
    }
    
    /**
     * Add portfolio item
     */
    public function add_portfolio_item($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_portfolio';
        
        return $wpdb->insert($table, $data);
    }
    
    /**
     * Update portfolio item
     */
    public function update_portfolio_item($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_portfolio';
        
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    /**
     * Delete portfolio item
     */
    public function delete_portfolio_item($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_portfolio';
        
        return $wpdb->delete($table, array('id' => $id));
    }
    
    /**
     * Add history entry
     */
    public function add_history_entry($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_portfolio_history';
        
        return $wpdb->insert($table, $data);
    }
    
    /**
     * Get history entries
     */
    public function get_history_entries($user_id, $days = 30) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_portfolio_history';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table 
            WHERE user_id = %d 
            AND date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
            ORDER BY date DESC",
            $user_id,
            $days
        ));
    }
    
    /**
     * Get journal entries
     */
    public function get_journal_entries($user_id, $limit = 20) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_journal';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d ORDER BY entry_date DESC LIMIT %d",
            $user_id,
            $limit
        ));
    }
    
    /**
     * Add journal entry
     */
    public function add_journal_entry($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_journal';
        
        return $wpdb->insert($table, $data);
    }
    
    /**
     * Update journal entry
     */
    public function update_journal_entry($id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_journal';
        
        return $wpdb->update($table, $data, array('id' => $id));
    }
    
    /**
     * Get API keys
     */
    public function get_api_keys($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_api_keys';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE user_id = %d AND is_active = 1",
            $user_id
        ));
    }
    
    /**
     * Save API key
     */
    public function save_api_key($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_api_keys';
        
        // Encrypt sensitive data
        $data['api_key'] = $this->encrypt($data['api_key']);
        $data['api_secret'] = $this->encrypt($data['api_secret']);
        
        if (isset($data['api_passphrase'])) {
            $data['api_passphrase'] = $this->encrypt($data['api_passphrase']);
        }
        
        return $wpdb->insert($table, $data);
    }
    
    /**
     * Simple encryption
     */
    private function encrypt($data) {
        if (defined('AUTH_KEY')) {
            return base64_encode(openssl_encrypt($data, 'AES-128-ECB', AUTH_KEY));
        }
        return base64_encode($data);
    }
    
    /**
     * Simple decryption
     */
    private function decrypt($data) {
        if (defined('AUTH_KEY')) {
            return openssl_decrypt(base64_decode($data), 'AES-128-ECB', AUTH_KEY);
        }
        return base64_decode($data);
    }
}
