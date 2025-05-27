<?php
class BKM_Aksiyon_Public {
    public function register_ajax_handlers() {
        add_action('wp_ajax_bkm_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_bkm_login', array($this, 'handle_login'));
        
        add_action('wp_ajax_bkm_load_tasks', array($this, 'handle_load_tasks'));
        add_action('wp_ajax_bkm_save_task', array($this, 'handle_save_task'));
        add_action('wp_ajax_bkm_complete_task', array($this, 'handle_complete_task'));
        add_action('wp_ajax_bkm_get_task', array($this, 'handle_get_task'));
    }
    
    public function handle_login() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');
        
        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        
        $user = wp_authenticate($username, $password);
        
        if (is_wp_error($user)) {
            wp_send_json_error('Geçersiz kullanıcı adı veya şifre.');
        }
        
        $result = wp_signon([
            'user_login' => $username,
            'user_password' => $password,
            'remember' => true
        ]);
        
        if (is_wp_error($result)) {
            wp_send_json_error('Giriş yapılırken bir hata oluştu.');
        }
        
        wp_send_json_success('Giriş başarılı.');
    }
    
    public function handle_load_tasks() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Yetkiniz yok.');
        }
        
        global $wpdb;
        $current_user_id = get_current_user_id();
        $is_admin = current_user_can('manage_options');
        $is_editor = current_user_can('edit_posts');
        
        $tasks = $wpdb->get_results(
            "SELECT t.*, u.display_name as assigned_user_name 
            FROM {$wpdb->prefix}bkm_tasks t 
            LEFT JOIN {$wpdb->prefix}users u ON t.assigned_user = u.ID 
            ORDER BY t.created_at DESC"
        );
        
        $formatted_tasks = array_map(function($task) use ($current_user_id, $is_admin, $is_editor) {
            return array(
                'id' => $task->id,
                'content' => $task->content,
                'start_date' => $task->start_date,
                'assigned_user' => $task->assigned_user,
                'assigned_user_name' => $task->assigned_user_name,
                'target_date' => $task->target_date,
                'progress' => $task->progress,
                'completed' => !empty($task->completion_date),
                'can_edit' => $is_admin || $is_editor || $task->created_by == $current_user_id,
                'can_complete' => $task->assigned_user == $current_user_id || $is_admin
            );
        }, $tasks);
        
        wp_send_json_success($formatted_tasks);
    }
    
    public function handle_save_task() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Yetkiniz yok.');
        }
        
        $task_id = isset($_POST['task_id']) ? intval($_POST['task_id']) : 0;
        
        $data = array(
            'content' => sanitize_textarea_field($_POST['content']),
            'start_date' => sanitize_text_field($_POST['start_date']),
            'assigned_user' => intval($_POST['assigned_user']),
            'target_date' => sanitize_text_field($_POST['target_date']),
            'progress' => intval($_POST['progress'])
        );
        
        global $wpdb;
        
        if ($task_id > 0) {
            // Güncelleme
            $task = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}bkm_tasks WHERE id = %d",
                $task_id
            ));
            
            if (!$task) {
                wp_send_json_error('Görev bulunamadı.');
            }
            
            if (!current_user_can('manage_options') && $task->created_by != get_current_user_id()) {
                wp_send_json_error('Bu görevi düzenleme yetkiniz yok.');
            }
            
            $wpdb->update(
                "{$wpdb->prefix}bkm_tasks",
                $data,
                array('id' => $task_id)
            );
        } else {
            // Yeni görev
            $data['created_by'] = get_current_user_id();
            $data['created_at'] = current_time('mysql');
            
            $wpdb->insert("{$wpdb->prefix}bkm_tasks", $data);
        }
        
        wp_send_json_success('Görev kaydedildi.');
    }
    
    public function handle_complete_task() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');
        
        $task_id = intval($_POST['task_id']);
        
        global $wpdb;
        $task = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}bkm_tasks WHERE id = %d",
            $task_id
        ));
        
        if (!$task) {
            wp_send_json_error('Görev bulunamadı.');
        }
        
        if (!current_user_can('manage_options') && $task->assigned_user != get_current_user_id()) {
            wp_send_json_error('Bu görevi tamamlama yetkiniz yok.');
        }
        
        $wpdb->update(
            "{$wpdb->prefix}bkm_tasks",
            array(
                'completion_date' => current_time('mysql'),
                'progress' => 100
            ),
            array('id' => $task_id)
        );
        
        wp_send_json_success('Görev tamamlandı.');
    }
    
    public function handle_get_task() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');
        
        $task_id = intval($_POST['task_id']);
        
        global $wpdb;
        $task = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}bkm_tasks WHERE id = %d",
            $task_id
        ));
        
        if (!$task) {
            wp_send_json_error('Görev bulunamadı.');
        }
        
        wp_send_json_success($task);
    }
}