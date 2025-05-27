<?php
if (!defined('ABSPATH')) {
    exit;
}

// Geçerli tarih ve kullanıcı bilgilerini al
$current_date = '2025-05-27 06:10:01';
$current_user_login = 'gezerronurr';
?>
<div class="bkm-aksiyon-container">
    <?php if (current_user_can('edit_posts') || current_user_can('manage_options')): ?>
        <button class="bkm-add-task-btn" id="bkmAddTaskBtn">
            <i class="fas fa-plus"></i> Görev Ekle
        </button>
    <?php endif; ?>
    
    <div class="bkm-tasks-table-container">
        <table class="bkm-tasks-table">
            <thead>
                <tr>
                    <th>Görev İçeriği</th>
                    <th>Başlangıç Tarihi</th>
                    <th>Sorumlu Kişi</th>
                    <th>Hedef Bitiş</th>
                    <th>İlerleme</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody id="bkmTasksList">
                <!-- Görevler AJAX ile yüklenecek -->
            </tbody>
        </table>
    </div>
</div>

<?php if (current_user_can('edit_posts') || current_user_can('manage_options')): ?>
<!-- Görev Ekleme/Düzenleme Modal -->
<div id="bkmTaskModal" class="bkm-modal">
    <div class="bkm-modal-content">
        <span class="bkm-modal-close">&times;</span>
        <h3 id="bkmModalTitle">Yeni Görev Ekle</h3>
        
        <form id="bkmTaskForm">
            <?php wp_nonce_field('bkm_ajax_nonce', 'task_nonce'); ?>
            <input type="hidden" name="task_id" id="taskId">
            
            <div class="form-group">
                <label for="taskContent">Görevin İçeriği:</label>
                <textarea name="content" id="taskContent" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="startDate">Başlangıç Tarihi:</label>
                <input type="date" name="start_date" id="startDate" required>
            </div>
            
            <div class="form-group">
                <label for="assignedUser">Sorumlu Kişi:</label>
                <select name="assigned_user" id="assignedUser" required>
                    <?php
                    $users = get_users(['role__in' => ['administrator', 'editor', 'author']]);
                    foreach($users as $user) {
                        echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="targetDate">Hedeflenen Bitiş Tarihi:</label>
                <input type="date" name="target_date" id="targetDate" required>
            </div>
            
            <div class="form-group">
                <label for="progress">İlerleme Durumu (%):</label>
                <input type="range" name="progress" id="progress" min="0" max="100" value="0">
                <span id="progressValue">0%</span>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="bkm-btn bkm-btn-primary">Kaydet</button>
                <button type="button" class="bkm-btn bkm-btn-secondary bkm-modal-close">İptal</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>