<?php
class BKM_Aksiyon_Admin {
    private $plugin_name;
    private $version;
    private $current_date;
    private $current_user_login;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->current_date = '2025-05-29 05:45:19'; // UTC zaman bilgisi
        $this->current_user_login = 'gezerronurr';
    }

    public function add_plugin_admin_menu() {
        // Ana menü
        add_menu_page(
            'BKM Aksiyon Takip',   // Sayfa başlığı
            'Aksiyon Takip',       // Menü başlığı
            'edit_posts',          // Gerekli yetki
            'bkm-aksiyon-takip',   // Menü slug
            array($this, 'display_plugin_admin_dashboard'), // Callback fonksiyon
            'dashicons-clipboard',  // İkon
            30                     // Pozisyon
        );

        // Alt menüler
        add_submenu_page(
            'bkm-aksiyon-takip',   // Ana menü slug
            'Aksiyon Listesi',     // Sayfa başlığı
            'Aksiyon Listesi',     // Menü başlığı
            'edit_posts',          // Gerekli yetki
            'bkm-aksiyon-takip',   // Menü slug (ana menü ile aynı)
            array($this, 'display_plugin_admin_dashboard') // Callback fonksiyon
        );

        add_submenu_page(
            'bkm-aksiyon-takip',
            'Aksiyon Ekle',
            'Aksiyon Ekle',
            'edit_posts',
            'bkm-aksiyon-ekle',
            array($this, 'display_aksiyon_ekle_page')
        );

        add_submenu_page(
            'bkm-aksiyon-takip',
            'Kategoriler',
            'Kategoriler',
            'edit_posts',
            'bkm-kategoriler',
            array($this, 'display_kategoriler_page')
        );

        add_submenu_page(
            'bkm-aksiyon-takip',
            'Görevler',
            'Görevler',
            'edit_posts',
            'bkm-gorevler',
            array($this, 'display_gorevler_page')
        );

        add_submenu_page(
            'bkm-aksiyon-takip',
            'Raporlar',
            'Raporlar',
            'edit_posts',
            'bkm-raporlar',
            array($this, 'display_raporlar_page')
        );
    }

    public function enqueue_styles($hook) {
        if (strpos($hook, 'bkm-aksiyon') === false) {
            return;
        }

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    }

    public function enqueue_scripts($hook) {
        if (strpos($hook, 'bkm-aksiyon') === false) {
            return;
        }

        wp_enqueue_script('jquery');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);
        wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), null, true);
        wp_enqueue_script('flatpickr-tr', 'https://npmcdn.com/flatpickr/dist/l10n/tr.js', array('flatpickr'), null, true);
        wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js', array('jquery'), null, true);
        
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery', 'select2', 'flatpickr', 'datatables'), $this->version, true);

        wp_localize_script($this->plugin_name, 'bkm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bkm_ajax_nonce'),
            'current_date' => $this->current_date,
            'current_user' => $this->current_user_login
        ));
    }

    public function register_ajax_handlers() {
        // Aksiyon işlemleri
        add_action('wp_ajax_add_aksiyon', array($this, 'handle_add_aksiyon'));
        add_action('wp_ajax_edit_aksiyon', array($this, 'handle_edit_aksiyon'));
        add_action('wp_ajax_delete_aksiyon', array($this, 'handle_delete_aksiyon'));
        add_action('wp_ajax_auto_save_aksiyon', array($this, 'handle_auto_save_aksiyon'));
        
        // Görev işlemleri
        add_action('wp_ajax_add_gorev', array($this, 'handle_add_gorev'));
        add_action('wp_ajax_edit_gorev', array($this, 'handle_edit_gorev'));
        add_action('wp_ajax_delete_gorev', array($this, 'handle_delete_gorev'));
        add_action('wp_ajax_update_gorev_status', array($this, 'handle_update_gorev_status'));
        
        // İstatistik ve rapor işlemleri
        add_action('wp_ajax_get_aksiyon_stats', array($this, 'handle_get_aksiyon_stats'));
        add_action('wp_ajax_export_aksiyonlar', array($this, 'handle_export_aksiyonlar'));
    }

    // Görev ekleme işlemi
    public function handle_add_gorev() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Yetkiniz bulunmamaktadır.'));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_gorevler';

        $data = array(
            'aksiyon_id' => intval($_POST['aksiyon_id']),
            'gorev_icerik' => sanitize_textarea_field($_POST['gorev_icerik']),
            'baslangic_tarihi' => sanitize_text_field($_POST['baslangic_tarihi']),
            'sorumlu_kisi' => intval($_POST['sorumlu_kisi']),
            'hedef_tarih' => sanitize_text_field($_POST['hedef_tarih']),
            'ilerleme_durumu' => intval($_POST['ilerleme_durumu']),
            'created_at' => $this->current_date,
            'updated_at' => $this->current_date,
            'created_by' => get_current_user_id()
        );

        $result = $wpdb->insert($table_name, $data);

        if ($result === false) {
            wp_send_json_error(array('message' => 'Veritabanı hatası oluştu.'));
            return;
        }

        // Aksiyon ilerleme durumunu güncelle
        $this->update_aksiyon_progress($data['aksiyon_id']);

        wp_send_json_success(array(
            'message' => 'Görev başarıyla eklendi.',
            'id' => $wpdb->insert_id
        ));
    }

    // Görev güncelleme işlemi
    public function handle_edit_gorev() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Yetkiniz bulunmamaktadır.'));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_gorevler';
        $gorev_id = intval($_POST['gorev_id']);

        $data = array(
            'gorev_icerik' => sanitize_textarea_field($_POST['gorev_icerik']),
            'sorumlu_kisi' => intval($_POST['sorumlu_kisi']),
            'hedef_tarih' => sanitize_text_field($_POST['hedef_tarih']),
            'ilerleme_durumu' => intval($_POST['ilerleme_durumu']),
            'updated_at' => $this->current_date
        );

        // İlerleme %100 ise kapanma tarihini ekle
        if ($data['ilerleme_durumu'] == 100) {
            $data['gercek_bitis_tarihi'] = $this->current_date;
        }

        $result = $wpdb->update(
            $table_name,
            $data,
            array('id' => $gorev_id)
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Veritabanı hatası oluştu.'));
            return;
        }

        // Görevin bağlı olduğu aksiyonun ilerleme durumunu güncelle
        $aksiyon_id = $wpdb->get_var($wpdb->prepare(
            "SELECT aksiyon_id FROM $table_name WHERE id = %d",
            $gorev_id
        ));
        
        if ($aksiyon_id) {
            $this->update_aksiyon_progress($aksiyon_id);
        }

        wp_send_json_success(array('message' => 'Görev başarıyla güncellendi.'));
    }

    // Görev silme işlemi
    public function handle_delete_gorev() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Yetkiniz bulunmamaktadır.'));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_gorevler';
        $gorev_id = intval($_POST['gorev_id']);

        // Silinmeden önce aksiyon ID'sini al
        $aksiyon_id = $wpdb->get_var($wpdb->prepare(
            "SELECT aksiyon_id FROM $table_name WHERE id = %d",
            $gorev_id
        ));

        $result = $wpdb->delete(
            $table_name,
            array('id' => $gorev_id),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Veritabanı hatası oluştu.'));
            return;
        }

        // Aksiyonun ilerleme durumunu güncelle
        if ($aksiyon_id) {
            $this->update_aksiyon_progress($aksiyon_id);
        }

        wp_send_json_success(array('message' => 'Görev başarıyla silindi.'));
    }

    // Aksiyon ilerleme durumunu güncelleme
    private function update_aksiyon_progress($aksiyon_id) {
        global $wpdb;
        $gorevler_table = $wpdb->prefix . 'bkm_gorevler';
        $aksiyonlar_table = $wpdb->prefix . 'bkm_aksiyonlar';

        // Aksiyona ait görevlerin ortalama ilerleme durumunu hesapla
        $ortalama_ilerleme = $wpdb->get_var($wpdb->prepare(
            "SELECT ROUND(AVG(ilerleme_durumu)) as ortalama 
             FROM $gorevler_table 
             WHERE aksiyon_id = %d",
            $aksiyon_id
        ));

        // Eğer hiç görev yoksa, ilerleme durumunu 0 olarak ayarla
        $ortalama_ilerleme = $ortalama_ilerleme ?: 0;

        // Aksiyonun ilerleme durumunu güncelle
        $wpdb->update(
            $aksiyonlar_table,
            array(
                'ilerleme_durumu' => $ortalama_ilerleme,
                'updated_at' => $this->current_date
            ),
            array('id' => $aksiyon_id)
        );
    }

    // İstatistikleri getirme
    public function handle_get_aksiyon_stats() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');

        global $wpdb;
        $aksiyonlar_table = $wpdb->prefix . 'bkm_aksiyonlar';
        $current_user_id = get_current_user_id();

        $stats = array(
            'total_count' => $wpdb->get_var("SELECT COUNT(*) FROM $aksiyonlar_table"),
            'open_count' => $wpdb->get_var("SELECT COUNT(*) FROM $aksiyonlar_table WHERE ilerleme_durumu < 100"),
            'completed_count' => $wpdb->get_var("SELECT COUNT(*) FROM $aksiyonlar_table WHERE ilerleme_durumu = 100"),
            'urgent_count' => $wpdb->get_var("SELECT COUNT(*) FROM $aksiyonlar_table WHERE onem_derecesi = 1 AND ilerleme_durumu < 100"),
            'my_tasks' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $aksiyonlar_table WHERE FIND_IN_SET(%d, sorumlular) AND ilerleme_durumu < 100",
                $current_user_id
            ))
        );

        wp_send_json_success($stats);
    }

    // Excel export işlemi
    public function handle_export_aksiyonlar() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Yetkiniz bulunmamaktadır.'));
            return;
        }

        require_once plugin_dir_path(__FILE__) . 'includes/class-bkm-aksiyon-excel.php';
        $excel = new BKM_Aksiyon_Excel();
        
        $file_url = $excel->export_aksiyonlar();

        if ($file_url) {
            wp_send_json_success(array('file_url' => $file_url));
        } else {
            wp_send_json_error(array('message' => 'Excel dosyası oluşturulamadı.'));
        }
    }

    // Sayfa görüntüleme fonksiyonları
    public function display_plugin_admin_dashboard() {
        require_once plugin_dir_path(__FILE__) . 'partials/aksiyon-listele.php';
    }

    public function display_aksiyon_ekle_page() {
        require_once plugin_dir_path(__FILE__) . 'partials/aksiyon-ekle.php';
    }

    public function display_kategoriler_page() {
        require_once plugin_dir_path(__FILE__) . 'partials/kategoriler.php';
    }

    public function display_gorevler_page() {
        require_once plugin_dir_path(__FILE__) . 'partials/gorevler.php';
    }

    public function display_raporlar_page() {
        require_once plugin_dir_path(__FILE__) . 'partials/raporlar.php';
    }
}