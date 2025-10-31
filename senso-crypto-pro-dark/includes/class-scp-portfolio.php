<?php
/**
 * Portfolio Management Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SCP_Portfolio {
    
    private static $_instance = null;
    private $db;
    private $api;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        $this->db = SCP_Database::instance();
        $this->api = SCP_API_Manager::instance();
        
        // Schedule daily update
        add_action('scp_daily_portfolio_update', array($this, 'update_all_portfolios'));
        
        if (!wp_next_scheduled('scp_daily_portfolio_update')) {
            wp_schedule_event(time(), 'daily', 'scp_daily_portfolio_update');
        }
    }
    
    /**
     * Get user portfolio
     */
    public function get_user_portfolio($user_id) {
        $items = $this->db->get_portfolio_items($user_id);
        
        if (empty($items)) {
            return array(
                'items' => array(),
                'total_value' => 0,
                'total_invested' => 0,
                'profit_loss' => 0,
                'profit_loss_percent' => 0
            );
        }
        
        // Get current prices
        $symbols = array_column($items, 'symbol');
        $prices = $this->api->get_crypto_prices($symbols);
        
        $total_value = 0;
        $total_invested = 0;
        
        foreach ($items as &$item) {
            $coin_id = $this->get_coingecko_id($item->symbol);
            
            if (isset($prices[$coin_id])) {
                $item->current_price = $prices[$coin_id]['usd'];
                $item->current_value = $item->amount * $item->current_price;
                $item->invested_value = $item->amount * $item->buy_price;
                $item->profit_loss = $item->current_value - $item->invested_value;
                $item->profit_loss_percent = ($item->profit_loss / $item->invested_value) * 100;
                $item->change_24h = $prices[$coin_id]['usd_24h_change'];
                
                // Update current price in database
                $this->db->update_portfolio_item($item->id, array(
                    'current_price' => $item->current_price
                ));
                
                $total_value += $item->current_value;
                $total_invested += $item->invested_value;
            }
        }
        
        $profit_loss = $total_value - $total_invested;
        $profit_loss_percent = $total_invested > 0 ? ($profit_loss / $total_invested) * 100 : 0;
        
        return array(
            'items' => $items,
            'total_value' => $total_value,
            'total_invested' => $total_invested,
            'profit_loss' => $profit_loss,
            'profit_loss_percent' => $profit_loss_percent
        );
    }
    
    /**
     * Add portfolio item
     */
    public function add_item($user_id, $data) {
        $data['user_id'] = $user_id;
        
        // Get current price if not provided
        if (!isset($data['current_price'])) {
            $prices = $this->api->get_crypto_prices(array($data['symbol']));
            $coin_id = $this->get_coingecko_id($data['symbol']);
            $data['current_price'] = isset($prices[$coin_id]) ? $prices[$coin_id]['usd'] : 0;
        }
        
        $result = $this->db->add_portfolio_item($data);
        
        if ($result) {
            // Record history entry
            $this->record_daily_snapshot($user_id);
        }
        
        return $result;
    }
    
    /**
     * Update portfolio item
     */
    public function update_item($id, $data) {
        $result = $this->db->update_portfolio_item($id, $data);
        
        if ($result) {
            // Get user_id from item
            global $wpdb;
            $table = $wpdb->prefix . 'scp_portfolio';
            $user_id = $wpdb->get_var($wpdb->prepare(
                "SELECT user_id FROM $table WHERE id = %d",
                $id
            ));
            
            if ($user_id) {
                $this->record_daily_snapshot($user_id);
            }
        }
        
        return $result;
    }
    
    /**
     * Delete portfolio item
     */
    public function delete_item($id) {
        return $this->db->delete_portfolio_item($id);
    }
    
    /**
     * Record daily portfolio snapshot
     */
    public function record_daily_snapshot($user_id, $notes = '') {
        $portfolio = $this->get_user_portfolio($user_id);
        
        $data = array(
            'user_id' => $user_id,
            'date' => current_time('Y-m-d'),
            'total_value' => $portfolio['total_value'],
            'profit_loss' => $portfolio['profit_loss'],
            'profit_loss_percent' => $portfolio['profit_loss_percent'],
            'notes' => $notes
        );
        
        // Check if entry for today already exists
        global $wpdb;
        $table = $wpdb->prefix . 'scp_portfolio_history';
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE user_id = %d AND date = %s",
            $user_id,
            $data['date']
        ));
        
        if ($exists) {
            // Update existing entry
            return $wpdb->update($table, $data, array('id' => $exists));
        } else {
            // Insert new entry
            return $this->db->add_history_entry($data);
        }
    }
    
    /**
     * Get portfolio history
     */
    public function get_history($user_id, $days = 30) {
        return $this->db->get_history_entries($user_id, $days);
    }
    
    /**
     * Get portfolio performance stats
     */
    public function get_performance_stats($user_id) {
        $history = $this->get_history($user_id, 365);
        
        if (empty($history)) {
            return array(
                'best_day' => 0,
                'worst_day' => 0,
                'avg_daily_return' => 0,
                'total_trades' => 0,
                'winning_trades' => 0,
                'losing_trades' => 0,
                'win_rate' => 0
            );
        }
        
        $returns = array_column($history, 'profit_loss_percent');
        $best_day = max($returns);
        $worst_day = min($returns);
        $avg_daily_return = array_sum($returns) / count($returns);
        
        $winning_trades = count(array_filter($returns, function($r) { return $r > 0; }));
        $losing_trades = count(array_filter($returns, function($r) { return $r < 0; }));
        $total_trades = $winning_trades + $losing_trades;
        $win_rate = $total_trades > 0 ? ($winning_trades / $total_trades) * 100 : 0;
        
        return array(
            'best_day' => $best_day,
            'worst_day' => $worst_day,
            'avg_daily_return' => $avg_daily_return,
            'total_trades' => $total_trades,
            'winning_trades' => $winning_trades,
            'losing_trades' => $losing_trades,
            'win_rate' => $win_rate
        );
    }
    
    /**
     * Update all user portfolios (called by cron)
     */
    public function update_all_portfolios() {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_portfolio';
        
        $user_ids = $wpdb->get_col("SELECT DISTINCT user_id FROM $table");
        
        foreach ($user_ids as $user_id) {
            $this->record_daily_snapshot($user_id);
        }
    }
    
    /**
     * Get CoinGecko ID from symbol
     */
    private function get_coingecko_id($symbol) {
        $map = array(
            'BTC' => 'bitcoin',
            'ETH' => 'ethereum',
            'BNB' => 'binancecoin',
            'XRP' => 'ripple',
            'ADA' => 'cardano',
            'DOGE' => 'dogecoin',
            'SOL' => 'solana',
            'TRX' => 'tron',
            'DOT' => 'polkadot',
            'MATIC' => 'matic-network',
            'LTC' => 'litecoin',
            'AVAX' => 'avalanche-2',
            'LINK' => 'chainlink',
            'UNI' => 'uniswap',
            'ATOM' => 'cosmos',
        );
        
        $symbol = strtoupper(str_replace('USDT', '', $symbol));
        return isset($map[$symbol]) ? $map[$symbol] : 'bitcoin';
    }
}
