<?php
/**
 * Trading Journal Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class SCP_Journal {
    
    private static $_instance = null;
    private $db;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct() {
        $this->db = SCP_Database::instance();
    }
    
    /**
     * Get user journal entries
     */
    public function get_entries($user_id, $limit = 20) {
        return $this->db->get_journal_entries($user_id, $limit);
    }
    
    /**
     * Get single entry
     */
    public function get_entry($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_journal';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Add journal entry
     */
    public function add_entry($user_id, $data) {
        $data['user_id'] = $user_id;
        
        if (!isset($data['entry_date'])) {
            $data['entry_date'] = current_time('Y-m-d');
        }
        
        return $this->db->add_journal_entry($data);
    }
    
    /**
     * Update journal entry
     */
    public function update_entry($id, $data) {
        return $this->db->update_journal_entry($id, $data);
    }
    
    /**
     * Delete journal entry
     */
    public function delete_entry($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_journal';
        
        return $wpdb->delete($table, array('id' => $id));
    }
    
    /**
     * Publish journal entry as blog post
     */
    public function publish_to_blog($entry_id, $post_type = 'scp_ideas') {
        $entry = $this->get_entry($entry_id);
        
        if (!$entry) {
            return false;
        }
        
        // Create post
        $post_data = array(
            'post_title' => $entry->title,
            'post_content' => $entry->content,
            'post_status' => 'publish',
            'post_type' => $post_type,
            'post_author' => $entry->user_id,
        );
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id) {
            // Update journal entry with published post ID
            $this->update_entry($entry_id, array(
                'is_public' => 1,
                'published_post_id' => $post_id
            ));
            
            // Add tags if available
            if (!empty($entry->tags)) {
                $tags = explode(',', $entry->tags);
                wp_set_post_tags($post_id, $tags);
            }
            
            return $post_id;
        }
        
        return false;
    }
    
    /**
     * Get journal statistics
     */
    public function get_stats($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_journal';
        
        $stats = array(
            'total_entries' => 0,
            'public_entries' => 0,
            'this_month' => 0,
            'this_week' => 0,
            'mood_distribution' => array(),
            'trade_results' => array()
        );
        
        // Total entries
        $stats['total_entries'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d",
            $user_id
        ));
        
        // Public entries
        $stats['public_entries'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE user_id = %d AND is_public = 1",
            $user_id
        ));
        
        // This month
        $stats['this_month'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
            WHERE user_id = %d 
            AND MONTH(entry_date) = MONTH(CURDATE())
            AND YEAR(entry_date) = YEAR(CURDATE())",
            $user_id
        ));
        
        // This week
        $stats['this_week'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
            WHERE user_id = %d 
            AND WEEK(entry_date) = WEEK(CURDATE())
            AND YEAR(entry_date) = YEAR(CURDATE())",
            $user_id
        ));
        
        // Mood distribution
        $moods = $wpdb->get_results($wpdb->prepare(
            "SELECT mood, COUNT(*) as count 
            FROM $table 
            WHERE user_id = %d AND mood IS NOT NULL
            GROUP BY mood",
            $user_id
        ));
        
        foreach ($moods as $mood) {
            $stats['mood_distribution'][$mood->mood] = $mood->count;
        }
        
        // Trade results
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT trade_result, COUNT(*) as count 
            FROM $table 
            WHERE user_id = %d AND trade_result IS NOT NULL
            GROUP BY trade_result",
            $user_id
        ));
        
        foreach ($results as $result) {
            $stats['trade_results'][$result->trade_result] = $result->count;
        }
        
        return $stats;
    }
    
    /**
     * Search journal entries
     */
    public function search_entries($user_id, $query) {
        global $wpdb;
        $table = $wpdb->prefix . 'scp_journal';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table 
            WHERE user_id = %d 
            AND (title LIKE %s OR content LIKE %s OR tags LIKE %s)
            ORDER BY entry_date DESC
            LIMIT 50",
            $user_id,
            '%' . $wpdb->esc_like($query) . '%',
            '%' . $wpdb->esc_like($query) . '%',
            '%' . $wpdb->esc_like($query) . '%'
        ));
    }
}
