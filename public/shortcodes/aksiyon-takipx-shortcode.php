<?php
if (!defined('ABSPATH')) {
    exit;
}

class BKM_Aksiyon_TakipX_Shortcode {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        add_shortcode('aksiyon_takipx', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // AJAX handlers
        add_action('wp_ajax_aksiyon_takipx_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_aksiyon_takipx_login', array($this, 'handle_login'));
        add_action('wp_ajax_load_user_aksiyonlar', array($this, 'load_user_aksiyonlar'));
        add_action('wp_ajax_add_gorev', array($this, 'handle_add_gorev'));
        add_action('wp_ajax_update_gorev', array($this, 'handle_update_gorev'));
        add_action('wp_ajax_complete_gorev', array($this, 'handle_complete_gorev'));
        add_action('wp_ajax_delete_gorev', array($this, 'handle_delete_gorev'));
    }

    public function enqueue_scripts() {
        if (!is_admin() && has_shortcode(get_post()->post_content, 'aksiyon_takipx')) {
            wp_enqueue_style('bkm-aksiyon-takipx', plugin_dir_url(dirname(__FILE__)) . 'css/bkm-aksiyon-takipx.css', array(), $this->version);
            wp_enqueue_script('bkm-aksiyon-takipx', plugin_dir_url(dirname(__FILE__)) . 'js/bkm-aksiyon-takipx.js', array('jquery'), $this->version, true);
            
            wp_localize_script('bkm-aksiyon-takipx', 'bkmAksiyonTakipX', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bkm_aksiyon_takipx_nonce'),
                'strings' => array(
                    'loginError' => __('Kullanıcı adı veya şifre hatalı!', 'bkm-aksiyon-takip'),
                    'gorevEklendi' => __('Görev başarıyla eklendi.', 'bkm-aksiyon-takip'),
                    'gorevGuncellendi' => __('Görev başarıyla güncellendi.', 'bkm-aksiyon-takip'),
                    'gorevTamamlandi' => __('Görev başarıyla tamamlandı.', 'bkm-aksiyon-takip'),
                    'gorevSilindi' => __('Görev başarıyla silindi.', 'bkm-aksiyon-takip'),
                    'hata' => __('Bir hata oluştu!', 'bkm-aksiyon-takip')
                )
            ));
        }
    }

    public function render_shortcode($atts) {
        if (!is_user_logged_in()) {
            return $this->render_login_form();
        }

        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $is_admin_or_editor = array_intersect(array('administrator', 'editor'), $user_roles);

        ob_start();
        ?>
        <div class="bkm-aksiyon-takipx-container">
            <div class="bkm-aksiyon-list">
                <h2><?php _e('Aksiyonlarım', 'bkm-aksiyon-takip'); ?></h2>
                <div class="bkm-table-responsive">
                    <table class="bkm-table" id="user-aksiyonlar-table">
                        <thead>
                            <tr>
                                <th><?php _e('ID', 'bkm-aksiyon-takip'); ?></th>
                                <th><?php _e('Kategori', 'bkm-aksiyon-takip'); ?></th>
                                <th><?php _e('Önem', 'bkm-aksiyon-takip'); ?></th>
                                <th><?php _e('Açılma Tarihi', 'bkm-aksiyon-takip'); ?></th>
                                <th><?php _e('Hafta', 'bkm-aksiyon-takip'); ?></th>
                                <th><?php _e('Sorumlular', 'bkm-aksiyon-takip'); ?></th>
                                <th><?php _e('Hedef Tarih', 'bkm-aksiyon-takip'); ?></th>
                                <th><?php _e('İlerleme', 'bkm-aksiyon-takip'); ?></th>
                                <th><?php _e('İşlemler', 'bkm-aksiyon-takip'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- AJAX ile doldurulacak -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Görev Ekleme Modal -->
            <?php if ($is_admin_or_editor): ?>
            <div id="gorev-ekle-modal" class="bkm-modal">
                <div class="bkm-modal-content">
                    <span class="bkm-close">&times;</span>
                    <h3><?php _e('Görev Ekle', 'bkm-aksiyon-takip'); ?></h3>
                    <form id="gorev-ekle-form">
                        <input type="hidden" name="aksiyon_id" id="aksiyon_id">
                        <div class="form-group">
                            <label for="gorev_icerigi"><?php _e('Görev İçeriği', 'bkm-aksiyon-takip'); ?></label>
                            <textarea name="gorev_icerigi" id="gorev_icerigi" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="baslangic_tarihi"><?php _e('Başlangıç Tarihi', 'bkm-aksiyon-takip'); ?></label>
                            <input type="date" name="baslangic_tarihi" id="baslangic_tarihi" required>
                        </div>
                        <div class="form-group">
                            <label for="sorumlu_kisi"><?php _e('Sorumlu Kişi', 'bkm-aksiyon-takip'); ?></label>
                            <select name="sorumlu_kisi" id="sorumlu_kisi" required>
                                <?php
                                $users = get_users(array('role__in' => array('administrator', 'editor', 'author')));
                                foreach ($users as $user) {
                                    echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="hedef_bitis_tarihi"><?php _e('Hedeflenen Bitiş Tarihi', 'bkm-aksiyon-takip'); ?></label>
                            <input type="date" name="hedef_bitis_tarihi" id="hedef_bitis_tarihi" required>
                        </div>
                        <div class="form-group">
                            <label for="ilerleme_durumu"><?php _e('İlerleme Durumu (%)', 'bkm-aksiyon-takip'); ?></label>
                            <input type="number" name="ilerleme_durumu" id="ilerleme_durumu" min="0" max="100" required>
                        </div>
                        <button type="submit" class="bkm-btn primary"><?php _e('Kaydet', 'bkm-aksiyon-takip'); ?></button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Görev Düzenleme Modal -->
            <div id="gorev-duzenle-modal" class="bkm-modal">
                <div class="bkm-modal-content">
                    <span class="bkm-close">&times;</span>
                    <h3><?php _e('Görevi Düzenle', 'bkm-aksiyon-takip'); ?></h3>
                    <form id="gorev-duzenle-form">
                        <input type="hidden" name="gorev_id" id="edit_gorev_id">
                        <div class="form-group">
                            <label for="edit_gorev_icerigi"><?php _e('Görev İçeriği', 'bkm-aksiyon-takip'); ?></label>
                            <textarea name="gorev_icerigi" id="edit_gorev_icerigi" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit_ilerleme_durumu"><?php _e('İlerleme Durumu (%)', 'bkm-aksiyon-takip'); ?></label>
                            <input type="number" name="ilerleme_durumu" id="edit_ilerleme_durumu" min="0" max="100" required>
                        </div>
                        <button type="submit" class="bkm-btn primary"><?php _e('Güncelle', 'bkm-aksiyon-takip'); ?></button>
                    </form>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_login_form() {
        ob_start();
        ?>
        <div class="bkm-login-container">
            <form id="bkm-login-form" class="bkm-form">
                <h2><?php _e('Giriş Yap', 'bkm-aksiyon-takip'); ?></h2>
                <div class="form-group">
                    <label for="username"><?php _e('Kullanıcı Adı', 'bkm-aksiyon-takip'); ?></label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="form-group">
                    <label for="password"><?php _e('Şifre', 'bkm-aksiyon-takip'); ?></label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="bkm-btn primary"><?php _e('Giriş Yap', 'bkm-aksiyon-takip'); ?></button>
                </div>
                <div id="login-message"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_login() {
        check_ajax_referer('bkm_aksiyon_takipx_nonce', 'nonce');

        $username = $_POST['username'];
        $password = $_POST['password'];

        $user = wp_authenticate($username, $password);

        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => __('Kullanıcı adı veya şifre hatalı!', 'bkm-aksiyon-takip')));
        }

        wp_set_auth_cookie($user->ID);
        wp_send_json_success(array('redirect' => get_permalink()));
    }

    public function load_user_aksiyonlar() {
        check_ajax_referer('bkm_aksiyon_takipx_nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => __('Oturum açmanız gerekiyor!', 'bkm-aksiyon-takip')));
        }

        global $wpdb;
        $current_user_id = get_current_user_id();

        $aksiyonlar = $wpdb->get_results($wpdb->prepare("
            SELECT DISTINCT a.*, k.kategori_adi, 
                   GROUP_CONCAT(DISTINCT u.display_name SEPARATOR ', ') as sorumlu_isimler
            FROM {$wpdb->prefix}bkm_aksiyonlar a
            LEFT JOIN {$wpdb->prefix}bkm_kategoriler k ON a.kategori_id = k.id
            LEFT JOIN {$wpdb->users} u ON FIND_IN_SET(u.ID, a.sorumlular)
            WHERE FIND_IN_SET(%d, a.sorumlular)
            GROUP BY a.id
            ORDER BY a.created_at DESC
        ", $current_user_id));

        ob_start();
        if ($aksiyonlar) {
            foreach ($aksiyonlar as $aksiyon) {
                include plugin_dir_path(dirname(__FILE__)) . 'partials/aksiyon-row-template.php';
            }
        } else {
            echo '<tr><td colspan="9" class="text-center">' . __('Kayıt bulunamadı', 'bkm-aksiyon-takip') . '</td></tr>';
        }
        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
    }

    public function handle_add_gorev() {
        check_ajax_referer('bkm_aksiyon_takipx_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Bu işlem için yetkiniz yok!', 'bkm-aksiyon-takip')));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_gorevler';

        $data = array(
            'aksiyon_id' => intval($_POST['aksiyon_id']),
            'gorev_icerigi' => sanitize_textarea_field($_POST['gorev_icerigi']),
            'baslangic_tarihi' => sanitize_text_field($_POST['baslangic_tarihi']),
            'sorumlu_id' => intval($_POST['sorumlu_kisi']),
            'hedef_bitis_tarihi' => sanitize_text_field($_POST['hedef_bitis_tarihi']),
            'ilerleme_durumu' => intval($_POST['ilerleme_durumu']),
            'created_at' => current_time('mysql')
        );

        $result = $wpdb->insert($table_name, $data);

        if ($result === false) {
            wp_send_json_error(array('message' => __('Görev eklenirken bir hata oluştu!', 'bkm-aksiyon-takip')));
        }

        wp_send_json_success(array(
            'message' => __('Görev başarıyla eklendi.', 'bkm-aksiyon-takip'),
            'gorev_id' => $wpdb->insert_id
        ));
    }

    public function handle_update_gorev() {
        check_ajax_referer('bkm_aksiyon_takipx_nonce', 'nonce');

        global $wpdb;
        $gorev_id = intval($_POST['gorev_id']);
        
        // Görev sahibi veya yönetici kontrolü
        $gorev = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}bkm_gorevler WHERE id = %d",
            $gorev_id
        ));

        if (!$gorev || (!current_user_can('manage_options') && $gorev->sorumlu_id != get_current_user_id())) {
            wp_send_json_error(array('message' => __('Bu görevi düzenleme yetkiniz yok!', 'bkm-aksiyon-takip')));
        }

        $data = array(
            'gorev_icerigi' => sanitize_textarea_field($_POST['gorev_icerigi']),
            'ilerleme_durumu' => intval($_POST['ilerleme_durumu']),
            'updated_at' => current_time('mysql')
        );

        $result = $wpdb->update(
            $wpdb->prefix . 'bkm_gorevler',
            $data,
            array('id' => $gorev_id)
        );

        if ($result === false) {
            wp_send_json_error(array('message' => __('Görev güncellenirken bir hata oluştu!', 'bkm-aksiyon-takip')));
        }

        wp_send_json_success(array('message' => __('Görev başarıyla güncellendi.', 'bkm-aksiyon-takip')));
    }

    public function handle_complete_gorev() {
        check_ajax_referer('bkm_aksiyon_takipx_nonce', 'nonce');

        global $wpdb;
        $gorev_id = intval($_POST['gorev_id']);
        
        // Görev sahibi kontrolü
        $gorev = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}bkm_gorevler WHERE id = %d",
            $gorev_id
        ));

        if (!$gorev || $gorev->sorumlu_id != get_current_user_id()) {
            wp_send_json_error(array('message' => __('Bu görevi tamamlama yetkiniz yok!', 'bkm-aksiyon-takip')));
        }

        $result = $wpdb->update(
            $wpdb->prefix . 'bkm_gorevler',
            array(
                'ilerleme_durumu' => 100,
                'gercek_bitis_tarihi' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('id' => $gorev_id)
        );

        if ($result === false) {
            wp_send_json_error(array('message' => __('Görev tamamlanırken bir hata oluştu!', 'bkm-aksiyon-takip')));
        }

        wp_send_json_success(array('message' => __('Görev başarıyla tamamlandı.', 'bkm-aksiyon-takip')));
    }

    public function handle_delete_gorev() {
        check_ajax_referer('bkm_aksiyon_takipx_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Bu işlem için yetkiniz yok!', 'bkm-aksiyon-takip')));
        }

        global $wpdb;
        $gorev_id = intval($_POST['gorev_id']);

        $result = $wpdb->delete(
            $wpdb->prefix . 'bkm_gorevler',
            array('id' => $gorev_id)
        );

        if ($result === false) {
            wp_send_json_error(array('message' => __('Görev silinirken bir hata oluştu!', 'bkm-aksiyon-takip')));
        }

        wp_send_json_success(array('message' => __('Görev başarıyla silindi.', 'bkm-aksiyon-takip')));
    }
} 