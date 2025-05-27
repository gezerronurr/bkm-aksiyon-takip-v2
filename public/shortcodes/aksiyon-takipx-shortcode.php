<?php
if (!defined('ABSPATH')) {
    exit;
}

function bkm_aksiyon_takipx_shortcode($atts) {
    // Kullanıcı giriş yapmamışsa login formunu göster
    if (!is_user_logged_in()) {
        return bkm_aksiyon_login_form();
    }

    // CSS ve JS dosyalarını yükle
    wp_enqueue_style('bkm-aksiyon-takipx-style');
    wp_enqueue_script('bkm-aksiyon-takipx-script');

    // Çıktıyı buffer'a al
    ob_start();
    ?>
    <div class="bkm-aksiyon-container">
        <?php
        // Yönetici veya editör kontrolü
        if (current_user_can('edit_posts') || current_user_can('manage_options')) {
            ?>
            <button class="bkm-add-task-btn" id="bkmAddTaskBtn">
                <i class="fas fa-plus"></i> Görev Ekle
            </button>
            <?php
        }
        ?>
        
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

    <!-- Görev Ekleme/Düzenleme Modal -->
    <div id="bkmTaskModal" class="bkm-modal">
        <div class="bkm-modal-content">
            <span class="bkm-modal-close">&times;</span>
            <h3 id="bkmModalTitle">Yeni Görev Ekle</h3>
            
            <form id="bkmTaskForm">
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
    <?php
    return ob_get_clean();
}

function bkm_aksiyon_login_form() {
    ob_start();
    ?>
    <div class="bkm-login-container">
        <form id="bkmLoginForm" method="post">
            <h3>Giriş Yapın</h3>
            
            <div class="form-group">
                <label for="username">Kullanıcı Adı:</label>
                <input type="text" name="username" id="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Şifre:</label>
                <input type="password" name="password" id="password" required>
            </div>
            
            <div class="form-error" style="display: none;"></div>
            
            <button type="submit" class="bkm-btn bkm-btn-primary">Giriş Yap</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}