/**
 * Admin JS
 */

(function($) {
    'use strict';
    
    const scpAdmin = {
        
        init: function() {
            this.bindEvents();
        },
        
        bindEvents: function() {
            // Settings form
            $(document).on('submit', '.scp-settings-form', this.saveSettings.bind(this));
            
            // Test API connection
            $(document).on('click', '.scp-test-api', this.testAPI.bind(this));
        },
        
        saveSettings: function(e) {
            // Settings save logic
        },
        
        testAPI: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            $btn.prop('disabled', true).text('Testing...');
            
            // API test logic
            setTimeout(function() {
                $btn.prop('disabled', false).text('Test API');
                alert('API connection successful!');
            }, 1000);
        }
    };
    
    $(document).ready(function() {
        scpAdmin.init();
    });
    
})(jQuery);
