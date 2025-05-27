<?php
/**
 * BKM Aksiyon Takip Admin Sınıfı
 *
 * @package    BKM_Aksiyon_Takip
 * @subpackage BKM_Aksiyon_Takip/admin
 * @link       https://github.com/gezerronurr
 * @since      1.0.0
 */

/**
 * Admin tarafı işlemlerini yöneten sınıf
 *
 * @package    BKM_Aksiyon_Takip
 * @subpackage BKM_Aksiyon_Takip/admin
 * @author     Onur GEZER <gezerronurr@gmail.com>
 */
class Bkm_Aksiyon_Takip_Admin {

    /**
     * Plugin adı
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    Plugin adı
     */
    private $plugin_name;

    /**
     * Plugin sürümü
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    Plugin sürümü
     */
    private $version;

    /**
     * Sınıfı başlat
     *
     * @since    1.0.0
     * @param    string    $plugin_name    Plugin adı
     * @param    string    $version        Plugin sürümü
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Admin menüsünü ekle
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // AJAX handler'ları kaydet
        $this->register_ajax_handlers();
    }

    /**
     * Admin menüsünü oluştur
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        add_menu_page(
            'BKM Aksiyon Takip',
            'Aksiyon Takip',
            'manage_options',
            'bkm-aksiyon-takip',
            array($this, 'display_aksiyon_list_page'),
            'dashicons-list-view',
            6
        );

        add_submenu_page(
            'bkm-aksiyon-takip',
            'Yeni Aksiyon',
            'Yeni Aksiyon',
            'manage_options',
            'bkm-aksiyon-ekle',
            array($this, 'display_aksiyon_form_page')
        );
    }

    /**
     * Admin stil dosyalarını kaydet
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Ana stil dosyası
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'css/admin.css',
            array(),
            $this->version,
            'all'
        );

        // Select2 stil dosyası
        wp_enqueue_style(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
            array(),
            '4.1.0-rc.0'
        );

        // Flatpickr stil dosyası
        wp_enqueue_style(
            'flatpickr',
            'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
            array(),
            '4.6.13'
        );

        // DataTables stil dosyası
        wp_enqueue_style(
            'datatables',
            'https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css',
            array(),
            '1.10.24'
        );

        // Font Awesome stil dosyası
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            array(),
            '5.15.4'
        );
    }

    /**
     * Admin script dosyalarını kaydet
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // jQuery UI
        wp_enqueue_script('jquery-ui-datepicker');

        // Select2
        wp_enqueue_script(
            'select2',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            array('jquery'),
            '4.1.0-rc.0',
            true
        );

        // Flatpickr
        wp_enqueue_script(
            'flatpickr',
            'https://cdn.jsdelivr.net/npm/flatpickr',
            array('jquery'),
            '4.6.13',
            true
        );

        // Flatpickr Türkçe dil desteği
        wp_enqueue_script(
            'flatpickr-tr',
            'https://npmcdn.com/flatpickr/dist/l10n/tr.js',
            array('flatpickr'),
            '4.6.13',
            true
        );

        // DataTables
        wp_enqueue_script(
            'datatables',
            'https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js',
            array('jquery'),
            '1.10.24',
            true
        );

        // Plugin JavaScript
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url(__FILE__) . 'js/admin.js',
            array('jquery'),
            $this->version,
            true
        );

        // JavaScript'e değişken aktar
        wp_localize_script(
            $this->plugin_name,
            'bkm_vars',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bkm_aksiyon_nonce')
            )
        );
    }

    /**
     * Aksiyon listesi sayfasını göster
     *
     * @since    1.0.0
     */
    public function display_aksiyon_list_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/aksiyon-listele.php';
    }

    /**
     * Aksiyon form sayfasını göster
     *
     * @since    1.0.0
     */
    public function display_aksiyon_form_page() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/aksiyon-ekle.php';
    }

    /**
     * AJAX handler'ları kaydet
     *
     * @since    1.0.0
     */
    public function register_ajax_handlers() {
        add_action('wp_ajax_save_aksiyon', array($this, 'handle_save_aksiyon'));
        add_action('wp_ajax_delete_aksiyon', array($this, 'handle_delete_aksiyon'));
        add_action('wp_ajax_get_aksiyon_stats', array($this, 'handle_get_aksiyon_stats'));
        add_action('wp_ajax_auto_save_aksiyon', array($this, 'handle_auto_save_aksiyon'));
    }

    /**
     * Aksiyon kaydetme AJAX handler
     *
     * @since    1.0.0
     */
    public function handle_save_aksiyon() {
        check_ajax_referer('bkm_aksiyon_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Bu işlem için yetkiniz yok'));
        }

        // Form verilerini al ve temizle
        $data = array(
            'kategori_id' => intval($_POST['kategori_id']),
            'tanimlayan_id' => get_current_user_id(),
            'sorumlular' => sanitize_text_field($_POST['sorumlular']),
            'acilma_tarihi' => sanitize_text_field($_POST['acilma_tarihi']),
            'hedef_tarih' => sanitize_text_field($_POST['hedef_tarih']),
            'kapanma_tarihi' => !empty($_POST['kapanma_tarihi']) ? sanitize_text_field($_POST['kapanma_tarihi']) : null,
            'onem_derecesi' => intval($_POST['onem_derecesi']),
            'ilerleme_durumu' => intval($_POST['ilerleme_durumu']),
            'hafta' => intval($_POST['hafta'])
        );

        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';

        // Aksiyon ID varsa güncelle, yoksa yeni kayıt
        if (!empty($_POST['aksiyon_id'])) {
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => intval($_POST['aksiyon_id'])),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d'),
                array('%d')
            );
        } else {
            $result = $wpdb->insert(
                $table_name,
                $data,
                array('%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%d')
            );
        }

        if ($result === false) {
            wp_send_json_error(array('message' => 'Kayıt işlemi başarısız oldu'));
        }

        $aksiyon_id = !empty($_POST['aksiyon_id']) ? intval($_POST['aksiyon_id']) : $wpdb->insert_id;

        // Log kaydı oluştur
        $this->log_aksiyon(
            $aksiyon_id,
            !empty($_POST['aksiyon_id']) ? 'update' : 'insert'
        );

        wp_send_json_success(array(
            'aksiyon_id' => $aksiyon_id,
            'message' => 'Aksiyon başarıyla kaydedildi'
        ));
    }

    /**
     * Aksiyon silme AJAX handler
     *
     * @since    1.0.0
     */
    public function handle_delete_aksiyon() {
        check_ajax_referer('bkm_aksiyon_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Bu işlem için yetkiniz yok'));
        }

        $aksiyon_id = isset($_POST['aksiyon_id']) ? intval($_POST['aksiyon_id']) : 0;
        
        if (!$aksiyon_id) {
            wp_send_json_error(array('message' => 'Geçersiz aksiyon ID'));
        }

        global $wpdb;
        
        // Önce log kaydını oluştur
        $this->log_aksiyon($aksiyon_id, 'delete');
        
        // Aksiyonu sil
        $result = $wpdb->delete(
            $wpdb->prefix . 'bkm_aksiyonlar',
            array('id' => $aksiyon_id),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Silme işlemi başarısız oldu'));
        }

        wp_send_json_success(array('message' => 'Aksiyon başarıyla silindi'));
    }

    /**
     * İstatistikleri getirme AJAX handler
     *
     * @since    1.0.0
     */
    public function handle_get_aksiyon_stats() {
        check_ajax_referer('bkm_aksiyon_nonce', 'nonce');

        global $wpdb;
        $current_user_id = get_current_user_id();

        // Toplam aksiyon sayısı
        $total_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar"
        );

        // Açık aksiyon sayısı
        $open_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar WHERE ilerleme_durumu < 100"
        );

        // Acil aksiyon sayısı
        $urgent_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar 
            WHERE onem_derecesi = 1 AND ilerleme_durumu < 100"
        );

        // Benim aksiyonlarım
        $my_tasks = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar 
            WHERE FIND_IN_SET(%d, sorumlular) AND ilerleme_durumu < 100",
            $current_user_id
        ));

        wp_send_json_success(array(
            'total_count' => $total_count,
            'open_count' => $open_count,
            'completed_count' => $total_count - $open_count,
            'urgent_count' => $urgent_count,
            'my_tasks' => $my_tasks
        ));
    }

    /**
     * Otomatik kaydetme AJAX handler
     *
     * @since    1.0.0
     */
    public function handle_auto_save_aksiyon() {
        $this->handle_save_aksiyon();
    }

    /**
     * Aksiyon işlemlerini logla
     *
     * @since    1.0.0
     * @param    int       $aksiyon_id    Aksiyon ID
     * @param    string    $action        İşlem türü (insert, update, delete)
     */
    private function log_aksiyon($aksiyon_id, $action) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'bkm_aksiyon_logs',
            array(
                'aksiyon_id' => $aksiyon_id,
                'user_id' => get_current_user_id(),
                'action' => $action
            ),
            array('%d', '%d', '%s')
        );
    }
}