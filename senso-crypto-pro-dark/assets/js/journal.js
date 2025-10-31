/**
 * Journal JS
 */

(function($) {
    'use strict';
    
    const scpJournal = {
        
        editor: null,
        currentEntry: null,
        
        init: function() {
            this.initQuill();
            this.bindEvents();
            this.loadEntries();
        },
        
        initQuill: function() {
            if ($('#scpJournalEditor').length) {
                // Load Quill CSS
                if (!$('link[href*="quill"]').length) {
                    $('head').append('<link rel="stylesheet" href="https://cdn.quilljs.com/1.3.7/quill.snow.css">');
                }
                
                // Load Quill JS
                if (typeof Quill === 'undefined') {
                    $.getScript('https://cdn.quilljs.com/1.3.7/quill.min.js', function() {
                        scpJournal.setupQuill();
                    });
                } else {
                    this.setupQuill();
                }
            }
        },
        
        setupQuill: function() {
            this.editor = new Quill('#scpJournalEditor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'color': [] }, { 'background': [] }],
                        ['blockquote', 'code-block'],
                        ['link', 'image'],
                        ['clean']
                    ]
                },
                placeholder: 'Почни писати свій запис...'
            });
        },
        
        bindEvents: function() {
            // New entry
            $(document).on('click', '.scp-new-journal-entry', this.showNewEntryModal.bind(this));
            
            // Save entry
            $(document).on('submit', '#scpJournalForm', this.saveEntry.bind(this));
            
            // Edit entry
            $(document).on('click', '.scp-edit-journal-entry', this.editEntry.bind(this));
            
            // Delete entry
            $(document).on('click', '.scp-delete-journal-entry', this.deleteEntry.bind(this));
            
            // Publish entry
            $(document).on('click', '.scp-publish-journal-entry', this.publishEntry.bind(this));
            
            // Search entries
            $(document).on('input', '#scpJournalSearch', this.searchEntries.bind(this));
            
            // Filter by mood
            $(document).on('change', '#scpMoodFilter', this.filterEntries.bind(this));
        },
        
        showNewEntryModal: function() {
            this.currentEntry = null;
            $('#scpJournalForm')[0].reset();
            if (this.editor) {
                this.editor.setText('');
            }
            $('#scpJournalModal').fadeIn();
        },
        
        saveEntry: function(e) {
            e.preventDefault();
            
            const $form = $(e.target);
            const $btn = $form.find('button[type="submit"]');
            const btnText = $btn.html();
            
            $btn.prop('disabled', true).html('<span class="scp-loading"></span> Збереження...');
            
            const content = this.editor ? this.editor.root.innerHTML : '';
            
            const formData = {
                action: 'scp_save_journal_entry',
                nonce: scpData.nonce,
                title: $form.find('[name="title"]').val(),
                content: content,
                entry_date: $form.find('[name="entry_date"]').val(),
                tags: $form.find('[name="tags"]').val(),
                mood: $form.find('[name="mood"]').val(),
                trade_result: $form.find('[name="trade_result"]').val()
            };
            
            if (this.currentEntry) {
                formData.entry_id = this.currentEntry;
            }
            
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        scpJournal.showNotification('Запис збережено!', 'success');
                        $('#scpJournalModal').fadeOut();
                        scpJournal.loadEntries();
                    } else {
                        scpJournal.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    scpJournal.showNotification('Помилка при збереженні', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(btnText);
                }
            });
        },
        
        editEntry: function(e) {
            e.preventDefault();
            
            const id = $(e.currentTarget).data('id');
            
            // Load entry data
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'scp_get_journal_entry',
                    nonce: scpData.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        scpJournal.populateForm(response.data);
                        $('#scpJournalModal').fadeIn();
                    }
                }
            });
        },
        
        populateForm: function(entry) {
            this.currentEntry = entry.id;
            
            $('#scpJournalForm [name="title"]').val(entry.title);
            $('#scpJournalForm [name="entry_date"]').val(entry.entry_date);
            $('#scpJournalForm [name="tags"]').val(entry.tags);
            $('#scpJournalForm [name="mood"]').val(entry.mood);
            $('#scpJournalForm [name="trade_result"]').val(entry.trade_result);
            
            if (this.editor) {
                this.editor.root.innerHTML = entry.content;
            }
        },
        
        deleteEntry: function(e) {
            e.preventDefault();
            
            if (!confirm('Ти впевнений що хочеш видалити цей запис?')) {
                return;
            }
            
            const id = $(e.currentTarget).data('id');
            
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'scp_delete_journal_entry',
                    nonce: scpData.nonce,
                    id: id
                },
                success: function(response) {
                    if (response.success) {
                        scpJournal.showNotification('Запис видалено!', 'success');
                        scpJournal.loadEntries();
                    } else {
                        scpJournal.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    scpJournal.showNotification('Помилка при видаленні', 'error');
                }
            });
        },
        
        publishEntry: function(e) {
            e.preventDefault();
            
            const id = $(e.currentTarget).data('id');
            const postType = $(e.currentTarget).data('post-type') || 'scp_ideas';
            
            if (!confirm('Опублікувати цей запис в блог?')) {
                return;
            }
            
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'scp_publish_journal_entry',
                    nonce: scpData.nonce,
                    entry_id: id,
                    post_type: postType
                },
                success: function(response) {
                    if (response.success) {
                        scpJournal.showNotification('Запис опубліковано!', 'success');
                        window.open(response.data.post_url, '_blank');
                        scpJournal.loadEntries();
                    } else {
                        scpJournal.showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    scpJournal.showNotification('Помилка при публікації', 'error');
                }
            });
        },
        
        loadEntries: function() {
            $.ajax({
                url: scpData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'scp_get_journal_entries',
                    nonce: scpData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        scpJournal.renderEntries(response.data);
                    }
                }
            });
        },
        
        renderEntries: function(entries) {
            if (!entries || entries.length === 0) {
                $('.scp-journal-list').html('<div class="scp-empty-state"><p>📝 Почни вести щоденник!</p></div>');
                return;
            }
            
            let html = '';
            entries.forEach(function(entry) {
                html += scpJournal.renderEntry(entry);
            });
            
            $('.scp-journal-list').html(html);
            
            // Animate
            if (typeof gsap !== 'undefined') {
                gsap.from('.scp-journal-item', {
                    opacity: 0,
                    y: 20,
                    stagger: 0.1,
                    duration: 0.6
                });
            }
        },
        
        renderEntry: function(entry) {
            const content = $(entry.content).text().substring(0, 200) + '...';
            const mood = entry.mood ? `<span class="scp-mood-badge">${entry.mood}</span>` : '';
            const result = entry.trade_result ? `<span class="scp-result-badge ${entry.trade_result}">${entry.trade_result}</span>` : '';
            
            return `
                <div class="scp-journal-item">
                    <div class="scp-journal-item-header">
                        <h4>${entry.title}</h4>
                        <span class="scp-journal-date">${this.formatDate(entry.entry_date)}</span>
                    </div>
                    <div class="scp-journal-item-content">
                        ${content}
                    </div>
                    <div class="scp-journal-item-meta">
                        ${mood}
                        ${result}
                    </div>
                    <div class="scp-journal-item-actions">
                        <button class="scp-btn scp-btn-sm scp-edit-journal-entry" data-id="${entry.id}">✏️ Редагувати</button>
                        <button class="scp-btn scp-btn-sm scp-publish-journal-entry" data-id="${entry.id}">📤 Опублікувати</button>
                        <button class="scp-btn scp-btn-sm scp-btn-danger scp-delete-journal-entry" data-id="${entry.id}">🗑️ Видалити</button>
                    </div>
                </div>
            `;
        },
        
        formatDate: function(date) {
            const d = new Date(date);
            return d.toLocaleDateString('uk-UA', { year: 'numeric', month: 'long', day: 'numeric' });
        },
        
        searchEntries: function(e) {
            // Implement search functionality
        },
        
        filterEntries: function(e) {
            // Implement filter functionality
        },
        
        showNotification: function(message, type = 'success') {
            const notification = $(`
                <div class="scp-notification scp-notification-${type}">
                    ${message}
                </div>
            `);
            
            $('body').append(notification);
            
            setTimeout(function() {
                notification.addClass('scp-show');
            }, 100);
            
            setTimeout(function() {
                notification.removeClass('scp-show');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    };
    
    // Initialize
    $(document).ready(function() {
        if ($('.scp-journal').length || $('#scpJournalEditor').length) {
            scpJournal.init();
        }
    });
    
    // Global function
    window.scpShowJournalModal = function() {
        scpJournal.showNewEntryModal();
    };
    
})(jQuery);
