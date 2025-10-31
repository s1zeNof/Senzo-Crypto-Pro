<?php
/**
 * API Manager - Handle crypto exchange APIs
 */

if (!defined('ABSPATH')) {
    exit;
}

class SCP_API_Manager {
    
    private static $_instance = null;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Get current crypto prices from CoinGecko
     */
    public function get_crypto_prices($symbols = array()) {
        if (empty($symbols)) {
            return false;
        }
        
        // Convert symbols to CoinGecko IDs
        $coin_ids = $this->convert_to_coingecko_ids($symbols);
        
        $url = 'https://api.coingecko.com/api/v3/simple/price?ids=' . implode(',', $coin_ids) . '&vs_currencies=usd,eur,uah&include_24hr_change=true';
        
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'headers' => array(
                'Accept' => 'application/json'
            )
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    /**
     * Get detailed coin information
     */
    public function get_coin_details($coin_id) {
        $url = 'https://api.coingecko.com/api/v3/coins/' . $coin_id . '?localization=false&tickers=false&community_data=false&developer_data=false';
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    /**
     * Get market chart data
     */
    public function get_market_chart($coin_id, $days = 30) {
        $url = 'https://api.coingecko.com/api/v3/coins/' . $coin_id . '/market_chart?vs_currency=usd&days=' . $days;
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    /**
     * Get trending coins
     */
    public function get_trending_coins() {
        $transient_key = 'scp_trending_coins';
        $cached = get_transient($transient_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $url = 'https://api.coingecko.com/api/v3/search/trending';
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Cache for 1 hour
        set_transient($transient_key, $data, HOUR_IN_SECONDS);
        
        return $data;
    }
    
    /**
     * Get top gainers and losers
     */
    public function get_gainers_losers() {
        $url = 'https://api.coingecko.com/api/v3/coins/markets?vs_currency=usd&order=market_cap_desc&per_page=100&page=1&sparkline=false&price_change_percentage=24h';
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Sort by price change
        usort($data, function($a, $b) {
            return $b['price_change_percentage_24h'] <=> $a['price_change_percentage_24h'];
        });
        
        return array(
            'gainers' => array_slice($data, 0, 10),
            'losers' => array_slice(array_reverse($data), 0, 10)
        );
    }
    
    /**
     * Convert trading symbols to CoinGecko IDs
     */
    private function convert_to_coingecko_ids($symbols) {
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
            'XLM' => 'stellar',
            'TON' => 'the-open-network',
            'ICP' => 'internet-computer',
            'APT' => 'aptos',
            'NEAR' => 'near',
        );
        
        $ids = array();
        foreach ($symbols as $symbol) {
            $symbol = strtoupper(str_replace('USDT', '', $symbol));
            if (isset($map[$symbol])) {
                $ids[] = $map[$symbol];
            }
        }
        
        return $ids;
    }
    
    /**
     * Get exchange rate
     */
    public function get_exchange_rate($from = 'USD', $to = 'UAH') {
        $transient_key = 'scp_exchange_rate_' . $from . '_' . $to;
        $cached = get_transient($transient_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        // Using exchangerate-api.com (free tier)
        $url = 'https://api.exchangerate-api.com/v4/latest/' . $from;
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return 1;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        $rate = isset($data['rates'][$to]) ? $data['rates'][$to] : 1;
        
        // Cache for 6 hours
        set_transient($transient_key, $rate, 6 * HOUR_IN_SECONDS);
        
        return $rate;
    }
    
    /**
     * Search coins
     */
    public function search_coins($query) {
        $url = 'https://api.coingecko.com/api/v3/search?query=' . urlencode($query);
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
    
    /**
     * Get global crypto market data
     */
    public function get_global_market_data() {
        $transient_key = 'scp_global_market_data';
        $cached = get_transient($transient_key);
        
        if ($cached !== false) {
            return $cached;
        }
        
        $url = 'https://api.coingecko.com/api/v3/global';
        
        $response = wp_remote_get($url, array('timeout' => 15));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Cache for 30 minutes
        set_transient($transient_key, $data, 30 * MINUTE_IN_SECONDS);
        
        return $data;
    }
}
