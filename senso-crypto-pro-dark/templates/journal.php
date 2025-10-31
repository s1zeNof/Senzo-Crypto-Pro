<?php
/**
 * Journal Template
 */

if (!defined('ABSPATH')) exit;

$user_id = get_current_user_id();
if (!$user_id) {
    echo '<p class="scp-error">Будь ласка, увійди щоб переглянути щоденник.</p>';
    return;
}

$journal = SCP_Journal::instance();
$entries = $journal->get_entries($user_id, 20);
?>

<div class="scp-journal scp-fade-in">
    <div class="scp-journal-header" data-aos="fade-down">
        <h2 class="scp-gradient-text">📝 Щоденник трейдера</h2>
        <button class="scp-btn scp-btn-primary scp-new-journal-entry">
            + Новий запис
        </button>
    </div>
    
    <div class="scp-journal-filters" data-aos="fade-up">
        <input type="text" 
               id="scpJournalSearch" 
               placeholder="🔍 Пошук записів..."
               class="scp-search-input">
        
        <select id="scpMoodFilter" class="scp-filter-select">
            <option value="">Всі настрої</option>
            <option value="happy">😊 Радісний</option>
            <option value="confident">😎 Впевнений</option>
            <option value="neutral">😐 Нейтральний</option>
            <option value="nervous">😰 Нервовий</option>
            <option value="frustrated">😤 Розчарований</option>
        </select>
    </div>
    
    <div class="scp-journal-list">
        <!-- Entries will be loaded via AJAX -->
    </div>
</div>

<!-- Journal Modal -->
<div id="scpJournalModal" class="scp-modal">
    <div class="scp-modal-content">
        <div class="scp-modal-header">
            <h3>Новий запис щоденника</h3>
            <button class="scp-modal-close" onclick="scpCloseModal('scpJournalModal')">&times;</button>
        </div>
        
        <form id="scpJournalForm">
            <div class="scp-form-group">
                <label>Заголовок</label>
                <input type="text" name="title" required placeholder="Назва запису...">
            </div>
            
            <div class="scp-form-group">
                <label>Контент</label>
                <div id="scpJournalEditor" style="min-height: 300px; background: rgba(255,255,255,0.05); border-radius: 0.5rem;"></div>
            </div>
            
            <div class="scp-form-group">
                <label>Дата</label>
                <input type="date" name="entry_date" value="<?php echo current_time('Y-m-d'); ?>" required>
            </div>
            
            <div class="scp-form-group">
                <label>Настрій</label>
                <select name="mood">
                    <option value="">Оберіть настрій</option>
                    <option value="happy">😊 Радісний</option>
                    <option value="confident">😎 Впевнений</option>
                    <option value="neutral">😐 Нейтральний</option>
                    <option value="nervous">😰 Нервовий</option>
                    <option value="frustrated">😤 Розчарований</option>
                </select>
            </div>
            
            <div class="scp-form-group">
                <label>Результат трейду</label>
                <select name="trade_result">
                    <option value="">Оберіть результат</option>
                    <option value="win">✅ Прибуток</option>
                    <option value="loss">❌ Збиток</option>
                    <option value="breakeven">➖ В нулі</option>
                </select>
            </div>
            
            <div class="scp-form-group">
                <label>Теги (через кому)</label>
                <input type="text" name="tags" placeholder="BTC, лонг, скальпінг...">
            </div>
            
            <div class="scp-form-group">
                <button type="submit" class="scp-btn scp-btn-primary scp-btn-block">
                    💾 Зберегти запис
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.scp-journal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.scp-journal-header h2 {
    font-size: 2rem;
    margin: 0;
}

.scp-journal-filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.scp-search-input,
.scp-filter-select {
    padding: 1rem 1.25rem;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid rgba(6, 182, 212, 0.2);
    border-radius: 0.75rem;
    color: var(--scp-text-primary);
    font-size: 1rem;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 200px;
}

.scp-search-input:focus,
.scp-filter-select:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.08);
    border-color: var(--scp-cyan);
    box-shadow: 0 0 20px rgba(6, 182, 212, 0.3);
}

/* Quill Editor Dark Theme */
.ql-toolbar.ql-snow {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(6, 182, 212, 0.2);
    border-radius: 0.75rem 0.75rem 0 0;
}

.ql-container.ql-snow {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(6, 182, 212, 0.2);
    border-radius: 0 0 0.75rem 0.75rem;
    color: var(--scp-text-primary);
}

.ql-editor {
    min-height: 300px;
    color: var(--scp-text-primary);
}

.ql-snow .ql-stroke {
    stroke: var(--scp-cyan);
}

.ql-snow .ql-fill {
    fill: var(--scp-cyan);
}

.ql-snow .ql-picker-label {
    color: var(--scp-text-primary);
}
</style>
