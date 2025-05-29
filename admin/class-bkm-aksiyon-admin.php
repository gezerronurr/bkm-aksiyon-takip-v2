<?php
/**
 * BKM Aksiyon Takip Admin sınıfı
 *
 * @package BKM_Aksiyon_Takip
 * @subpackage Admin
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class BKM_Aksiyon_Admin {
    /**
     * Plugin adı
     * @var string
     */
    private $plugin_name;

    /**
     * Plugin versiyonu
     * @var string
     */
    private $version;

    /**
     * Sınıf örneği
     * @var object
     */
    private static $instance = null;

    /**
     * Constructor
     *
     * @param string $plugin_name Plugin adı
     * @param string $version Plugin versiyonu
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_add_gorev', array($this, 'handle_add_gorev'));
        add_action('wp_ajax_delete_aksiyon', array($this, 'handle_delete_aksiyon'));
        add_action('wp_ajax_update_ilerleme_durumu', array($this, 'handle_update_ilerleme_durumu'));
        add_action('wp_ajax_save_aksiyon', array($this, 'handle_save_aksiyon'));
        add_action('wp_ajax_delete_gorev', array($this, 'handle_delete_gorev'));
        add_action('wp_ajax_update_gorev', array($this, 'handle_update_gorev'));
        add_action('admin_notices', array($this, 'display_admin_notices'));
    }

    /**
     * Singleton instance getter
     */
    public static function get_instance($plugin_name = '', $version = '') {
        if (null === self::$instance) {
            self::$instance = new self($plugin_name, $version);
        }
        return self::$instance;
    }

    /**
     * Admin menü ekleme
     */
    public function add_admin_menu() {
        add_menu_page(
            'BKM Aksiyon Takip',
            'Aksiyon Takip',
            'manage_options',
            'bkm-aksiyon',
            array($this, 'display_aksiyon_page'),
            'dashicons-clipboard',
            30
        );

        add_submenu_page(
            'bkm-aksiyon',
            'Tüm Aksiyonlar',
            'Tüm Aksiyonlar',
            'manage_options',
            'bkm-aksiyon',
            array($this, 'display_aksiyon_page')
        );

        add_submenu_page(
            'bkm-aksiyon',
            'Yeni Aksiyon',
            'Yeni Aksiyon',
            'manage_options',
            'bkm-aksiyon-new',
            array($this, 'display_aksiyon_form')
        );

        add_submenu_page(
            'bkm-aksiyon',
            'Kategoriler',
            'Kategoriler',
            'manage_options',
            'bkm-aksiyon-categories',
            array($this, 'display_categories_page')
        );

        add_submenu_page(
            'bkm-aksiyon',
            'Ayarlar',
            'Ayarlar',
            'manage_options',
            'bkm-aksiyon-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Script ve stilleri yükleme
     */
    public function enqueue_scripts($hook) {
        // Current date ve login
        $current_date = '2025-05-29 08:21:28'; // UTC zaman bilgisi
        $current_user_login = 'gezerronurr';

        if (strpos($hook, 'bkm-aksiyon') === false) {
            return;
        }

        // jQuery UI
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');

        // DataTables
        wp_enqueue_style('datatables', 'https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css');
        wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js', array('jquery'), null, true);
        wp_enqueue_style('datatables-responsive', 'https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css');
        wp_enqueue_script('datatables-responsive', 'https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js', array('datatables'), null, true);
        wp_enqueue_style('datatables-buttons', 'https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css');
        wp_enqueue_script('datatables-buttons', 'https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js', array('datatables'), null, true);
        wp_enqueue_script('jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array(), null, true);
        wp_enqueue_script('pdfmake', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array(), null, true);
        wp_enqueue_script('vfs-fonts', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array('pdfmake'), null, true);
        wp_enqueue_script('buttons-html5', 'https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js', array('datatables-buttons'), null, true);
        wp_enqueue_script('buttons-print', 'https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js', array('datatables-buttons'), null, true);

        // Select2
        wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
        wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
        wp_enqueue_script('select2-tr', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/tr.js', array('select2'), '4.1.0', true);

        // Flatpickr
        wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array('jquery'), null, true);
        wp_enqueue_script('flatpickr-tr', 'https://npmcdn.com/flatpickr/dist/l10n/tr.js', array('flatpickr'), null, true);

        // Font Awesome
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

        // Chart.js
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);

        // TinyMCE
        if (strpos($hook, 'bkm-aksiyon-new') !== false || strpos($hook, 'bkm-aksiyon&action=edit') !== false) {
            wp_enqueue_editor();
        }

        // Plugin CSS ve JS
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/admin.css', array(), $this->version, 'all');
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/admin.js', array(
            'jquery',
            'select2',
            'flatpickr',
            'jquery-ui-sortable',
            'jquery-ui-draggable',
            'jquery-ui-droppable'
        ), $this->version, true);

        // AJAX URL ve nonce
        wp_localize_script($this->plugin_name, 'bkm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bkm_ajax_nonce'),
            'current_date' => $current_date,
            'current_user_login' => $current_user_login
        ));
    }

    /**
     * Ana sayfa görüntüleme
     */
    public function display_aksiyon_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';

        // Current date ve login
        $current_date = '2025-05-29 08:24:02'; // UTC zaman bilgisi
        $current_user_login = 'gezerronurr';

        // Filtre parametreleri
        $kategori = isset($_GET['kategori']) ? sanitize_text_field($_GET['kategori']) : '';
        $durum = isset($_GET['durum']) ? sanitize_text_field($_GET['durum']) : '';
        $sorumlu = isset($_GET['sorumlu']) ? intval($_GET['sorumlu']) : '';
        $onem = isset($_GET['onem']) ? sanitize_text_field($_GET['onem']) : '';
        $tarih_baslangic = isset($_GET['tarih_baslangic']) ? sanitize_text_field($_GET['tarih_baslangic']) : '';
        $tarih_bitis = isset($_GET['tarih_bitis']) ? sanitize_text_field($_GET['tarih_bitis']) : '';

        // SQL sorgusu hazırlama
        $where = array('1=1');
        $params = array();

        if (!empty($kategori)) {
            $where[] = 'kategori_id = %d';
            $params[] = $kategori;
        }

        if (!empty($durum)) {
            $where[] = 'durum = %s';
            $params[] = $durum;
        }

        if (!empty($sorumlu)) {
            $where[] = 'FIND_IN_SET(%d, sorumlular)';
            $params[] = $sorumlu;
        }

        if (!empty($onem)) {
            $where[] = 'onem_derecesi = %s';
            $params[] = $onem;
        }

        if (!empty($tarih_baslangic)) {
            $where[] = 'son_tarih >= %s';
            $params[] = $tarih_baslangic;
        }

        if (!empty($tarih_bitis)) {
            $where[] = 'son_tarih <= %s';
            $params[] = $tarih_bitis;
        }

        // Yetki kontrolü
        if (!current_user_can('manage_options')) {
            $current_user_id = get_current_user_id();
            $where[] = '(FIND_IN_SET(%d, sorumlular) OR olusturan_id = %d)';
            $params[] = $current_user_id;
            $params[] = $current_user_id;
        }

        $where_clause = implode(' AND ', $where);
        $query = $wpdb->prepare(
            "SELECT a.*, 
                    k.kategori_adi,
                    COUNT(DISTINCT g.id) as toplam_gorev,
                    SUM(CASE WHEN g.durum = 'tamamlandi' THEN 1 ELSE 0 END) as tamamlanan_gorev,
                    GROUP_CONCAT(DISTINCT u.display_name) as sorumlu_kisiler
             FROM $table_name a
             LEFT JOIN {$wpdb->prefix}bkm_kategoriler k ON a.kategori_id = k.id
             LEFT JOIN {$wpdb->prefix}bkm_gorevler g ON a.id = g.aksiyon_id
             LEFT JOIN {$wpdb->users} u ON FIND_IN_SET(u.ID, a.sorumlular)
             WHERE $where_clause
             GROUP BY a.id
             ORDER BY a.id DESC",
            $params
        );

        $aksiyonlar = $wpdb->get_results($query);

        // İstatistikler
        $stats = $this->get_dashboard_stats();

        // Template yükleme
        require_once plugin_dir_path(__FILE__) . 'partials/aksiyon-list.php';
    }

    /**
     * Dashboard istatistikleri
     */
    private function get_dashboard_stats() {
        global $wpdb;
        $stats = array();

        // Toplam aksiyon sayısı
        $stats['toplam_aksiyon'] = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar
        ");

        // Tamamlanan aksiyon sayısı
        $stats['tamamlanan_aksiyon'] = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar
            WHERE durum = 'tamamlandi'
        ");

        // Bekleyen görev sayısı
        $stats['bekleyen_gorev'] = $wpdb->get_var("
            SELECT COUNT(*) FROM {$wpdb->prefix}bkm_gorevler
            WHERE durum = 'beklemede'
        ");

        // Geciken görev sayısı
        $stats['geciken_gorev'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}bkm_gorevler
            WHERE durum != 'tamamlandi'
            AND hedef_tarih < %s",
            date('Y-m-d')
        ));

        // Kategori bazlı aksiyon dağılımı
        $stats['kategori_dagilimi'] = $wpdb->get_results("
            SELECT k.kategori_adi, COUNT(a.id) as toplam
            FROM {$wpdb->prefix}bkm_kategoriler k
            LEFT JOIN {$wpdb->prefix}bkm_aksiyonlar a ON k.id = a.kategori_id
            GROUP BY k.id
            ORDER BY toplam DESC
        ");

        // Önem derecesi dağılımı
        $stats['onem_dagilimi'] = $wpdb->get_results("
            SELECT onem_derecesi, COUNT(*) as toplam
            FROM {$wpdb->prefix}bkm_aksiyonlar
            GROUP BY onem_derecesi
        ");

        return $stats;
    }

    /**
     * Kategori sayfası görüntüleme
     */
    public function display_categories_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_kategoriler';

        // Kategori ekleme/düzenleme
        if (isset($_POST['submit_kategori'])) {
            check_admin_referer('bkm_kategori_nonce');

            $kategori_adi = sanitize_text_field($_POST['kategori_adi']);
            $kategori_id = isset($_POST['kategori_id']) ? intval($_POST['kategori_id']) : 0;

            if ($kategori_id > 0) {
                // Güncelleme
                $wpdb->update(
                    $table_name,
                    array('kategori_adi' => $kategori_adi),
                    array('id' => $kategori_id),
                    array('%s'),
                    array('%d')
                );
                $message = 'Kategori güncellendi.';
            } else {
                // Yeni ekleme
                $wpdb->insert(
                    $table_name,
                    array('kategori_adi' => $kategori_adi),
                    array('%s')
                );
                $message = 'Kategori eklendi.';
            }
        }

        // Kategori silme
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['kategori_id'])) {
            $kategori_id = intval($_GET['kategori_id']);
            check_admin_referer('delete_kategori_' . $kategori_id);

            // Kategoriyle ilişkili aksiyonları kontrol et
            $aksiyon_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar WHERE kategori_id = %d",
                $kategori_id
            ));

            if ($aksiyon_count > 0) {
                $error_message = 'Bu kategori kullanımda olduğu için silinemiyor.';
            } else {
                $wpdb->delete($table_name, array('id' => $kategori_id), array('%d'));
                $message = 'Kategori silindi.';
            }
        }

        // Kategorileri getir
        $kategoriler = $wpdb->get_results("
            SELECT k.*, COUNT(a.id) as aksiyon_sayisi
            FROM $table_name k
            LEFT JOIN {$wpdb->prefix}bkm_aksiyonlar a ON k.id = a.kategori_id
            GROUP BY k.id
            ORDER BY k.kategori_adi ASC
        ");

        // Template yükleme
        require_once plugin_dir_path(__FILE__) . 'partials/categories-page.php';
    }

    /**
     * Ayarlar sayfası görüntüleme
     */
    public function display_settings_page() {
        // Ayarları kaydet
        if (isset($_POST['submit_settings'])) {
            check_admin_referer('bkm_settings_nonce');

            $settings = array(
                'email_bildirim' => isset($_POST['email_bildirim']) ? 1 : 0,
                'bildirim_siklik' => sanitize_text_field($_POST['bildirim_siklik']),
                'rapor_alicilar' => sanitize_textarea_field($_POST['rapor_alicilar']),
                'varsayilan_gorev_suresi' => intval($_POST['varsayilan_gorev_suresi']),
                'max_dosya_boyutu' => intval($_POST['max_dosya_boyutu'])
            );

            update_option('bkm_aksiyon_settings', $settings);
            $message = 'Ayarlar kaydedildi.';
        }

        // Mevcut ayarları getir
        $settings = get_option('bkm_aksiyon_settings', array(
            'email_bildirim' => 1,
            'bildirim_siklik' => 'gunluk',
            'rapor_alicilar' => '',
            'varsayilan_gorev_suresi' => 7,
            'max_dosya_boyutu' => 5
        ));

        // Template yükleme
        require_once plugin_dir_path(__FILE__) . 'partials/settings-page.php';
    }

    /**
     * Aksiyon kaydetme handler
     */
    public function handle_save_aksiyon() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Yetkiniz yok!'));
        }

        // Current date ve login
        $current_date = '2025-05-29 08:25:31'; // UTC zaman bilgisi
        $current_user_login = 'gezerronurr';

        $aksiyon_id = isset($_POST['aksiyon_id']) ? intval($_POST['aksiyon_id']) : 0;
        $data = array(
            'kategori_id' => intval($_POST['kategori_id']),
            'baslik' => sanitize_text_field($_POST['baslik']),
            'detay' => wp_kses_post($_POST['detay']),
            'sorumlular' => sanitize_text_field($_POST['sorumlular']),
            'durum' => sanitize_text_field($_POST['durum']),
            'onem_derecesi' => sanitize_text_field($_POST['onem_derecesi']),
            'son_tarih' => sanitize_text_field($_POST['son_tarih']),
            'guncelleme_tarihi' => current_time('mysql')
        );

        // Dosya yükleme işlemi
        if (!empty($_FILES['ekler'])) {
            $uploaded_files = array();
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx');
            $max_size = get_option('bkm_aksiyon_max_file_size', 5) * 1024 * 1024; // MB to bytes

            foreach ($_FILES['ekler']['name'] as $key => $value) {
                if ($_FILES['ekler']['error'][$key] === 0) {
                    $file = array(
                        'name' => $_FILES['ekler']['name'][$key],
                        'type' => $_FILES['ekler']['type'][$key],
                        'tmp_name' => $_FILES['ekler']['tmp_name'][$key],
                        'error' => $_FILES['ekler']['error'][$key],
                        'size' => $_FILES['ekler']['size'][$key]
                    );

                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    
                    // Validasyonlar
                    if (!in_array($ext, $allowed_types)) {
                        wp_send_json_error(array('message' => 'Geçersiz dosya türü: ' . $ext));
                    }

                    if ($file['size'] > $max_size) {
                        wp_send_json_error(array('message' => 'Dosya boyutu çok büyük!'));
                    }

                    $upload = wp_handle_upload($file, array('test_form' => false));

                    if (!isset($upload['error'])) {
                        $uploaded_files[] = $upload['url'];
                    }
                }
            }

            if (!empty($uploaded_files)) {
                $data['ekler'] = serialize($uploaded_files);
            }
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';

        if ($aksiyon_id > 0) {
            // Güncelleme
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $aksiyon_id),
                array(
                    '%d', '%s', '%s', '%s', '%s', 
                    '%s', '%s', '%s', '%s'
                ),
                array('%d')
            );
        } else {
            // Yeni ekleme
            $data['olusturma_tarihi'] = current_time('mysql');
            $data['olusturan_id'] = get_current_user_id();
            
            $result = $wpdb->insert(
                $table_name,
                $data,
                array(
                    '%d', '%s', '%s', '%s', '%s', 
                    '%s', '%s', '%s', '%s', '%s', '%d'
                )
            );
            $aksiyon_id = $wpdb->insert_id;
        }

        if ($result === false) {
            wp_send_json_error(array(
                'message' => 'Aksiyon kaydedilirken bir hata oluştu!',
                'debug' => $wpdb->last_error
            ));
        }

        // Email bildirimi gönder
        if (get_option('bkm_aksiyon_email_bildirim', 1)) {
            $this->send_notification_email($aksiyon_id, $data);
        }

        wp_send_json_success(array(
            'message' => 'Aksiyon başarıyla kaydedildi!',
            'redirect_url' => admin_url('admin.php?page=bkm-aksiyon'),
            'aksiyon_id' => $aksiyon_id
        ));
    }

    /**
     * Görev güncelleme handler
     */
    public function handle_update_gorev() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Yetkiniz yok!'));
        }

        $gorev_id = intval($_POST['gorev_id']);
        $data = array(
            'gorev_icerik' => sanitize_textarea_field($_POST['gorev_icerik']),
            'sorumlu_kisi' => intval($_POST['sorumlu_kisi']),
            'hedef_tarih' => sanitize_text_field($_POST['hedef_tarih']),
            'ilerleme_durumu' => intval($_POST['ilerleme_durumu']),
            'durum' => sanitize_text_field($_POST['durum']),
            'guncelleme_tarihi' => current_time('mysql')
        );

        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_gorevler';

        $result = $wpdb->update(
            $table_name,
            $data,
            array('id' => $gorev_id),
            array('%s', '%d', '%s', '%d', '%s', '%s'),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Görev güncellenirken bir hata oluştu!'));
        }

        // Aksiyon ilerleme durumunu güncelle
        $this->update_aksiyon_progress($gorev_id);

        wp_send_json_success(array('message' => 'Görev başarıyla güncellendi!'));
    }

    /**
     * Görev silme handler
     */
    public function handle_delete_gorev() {
        check_ajax_referer('bkm_ajax_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Yetkiniz yok!'));
        }

        $gorev_id = intval($_POST['gorev_id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_gorevler';

        // Görevi sil
        $result = $wpdb->delete(
            $table_name,
            array('id' => $gorev_id),
            array('%d')
        );

        if ($result === false) {
            wp_send_json_error(array('message' => 'Görev silinirken bir hata oluştu!'));
        }

        wp_send_json_success(array('message' => 'Görev başarıyla silindi!'));
    }

    /**
     * Aksiyon ilerleme durumu güncelleme
     */
    private function update_aksiyon_progress($gorev_id) {
        global $wpdb;

        // Görevin bağlı olduğu aksiyonu bul
        $aksiyon_id = $wpdb->get_var($wpdb->prepare(
            "SELECT aksiyon_id FROM {$wpdb->prefix}bkm_gorevler WHERE id = %d",
            $gorev_id
        ));

        if (!$aksiyon_id) return;

        // Aksiyona ait görevlerin ortalama ilerleme durumunu hesapla
        $progress = $wpdb->get_var($wpdb->prepare(
            "SELECT AVG(ilerleme_durumu) FROM {$wpdb->prefix}bkm_gorevler WHERE aksiyon_id = %d",
            $aksiyon_id
        ));

        // Aksiyonu güncelle
        $wpdb->update(
            $wpdb->prefix . 'bkm_aksiyonlar',
            array('ilerleme_durumu' => round($progress)),
            array('id' => $aksiyon_id),
            array('%d'),
            array('%d')
        );
    }

    /**
     * Email bildirimi gönderme
     */
    private function send_notification_email($aksiyon_id, $data) {
        // Current date ve login
        $current_date = '2025-05-29 08:27:02'; // UTC zaman bilgisi
        $current_user_login = 'gezerronurr';

        $sorumlular = explode(',', $data['sorumlular']);
        $to_emails = array();

        foreach ($sorumlular as $user_id) {
            $user_info = get_userdata($user_id);
            if ($user_info) {
                $to_emails[] = $user_info->user_email;
            }
        }

        if (empty($to_emails)) return;

        $subject = sprintf('[%s] Yeni Aksiyon: %s', get_bloginfo('name'), $data['baslik']);

        $message = sprintf(
            'Merhaba,<br><br>"%s" başlıklı yeni bir aksiyon oluşturuldu.<br><br>',
            $data['baslik']
        );
        $message .= sprintf('Önem Derecesi: %s<br>', $data['onem_derecesi']);
        $message .= sprintf('Son Tarih: %s<br>', $data['son_tarih']);
        $message .= sprintf('Durum: %s<br><br>', $data['durum']);
        $message .= sprintf('Detaylar:<br>%s<br><br>', wp_strip_all_tags($data['detay']));
        $message .= sprintf(
            'Aksiyonu görüntülemek için <a href="%s">tıklayınız</a>.<br><br>',
            admin_url('admin.php?page=bkm-aksiyon&action=edit&id=' . $aksiyon_id)
        );
        $message .= sprintf(
            'Bu bildirim %s tarafından %s tarihinde oluşturulmuştur.',
            $current_user_login,
            $current_date
        );

        $headers = array('Content-Type: text/html; charset=UTF-8');

        wp_mail($to_emails, $subject, $message, $headers);
    }

    /**
     * Admin bildirimleri gösterme
     */
    public function display_admin_notices() {
        $screen = get_current_screen();
        
        if (strpos($screen->id, 'bkm-aksiyon') === false) {
            return;
        }

        // Geciken görevleri kontrol et
        global $wpdb;
        $geciken_gorevler = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM {$wpdb->prefix}bkm_gorevler
            WHERE durum != 'tamamlandi'
            AND hedef_tarih < %s",
            date('Y-m-d')
        ));

        if ($geciken_gorevler > 0) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <strong>Dikkat!</strong> 
                    <?php echo sprintf(
                        _n(
                            '%d görev son tarihini geçmiş!',
                            '%d görev son tarihlerini geçmiş!',
                            $geciken_gorevler,
                            'bkm-aksiyon'
                        ),
                        $geciken_gorevler
                    ); ?>
                    <a href="<?php echo admin_url('admin.php?page=bkm-aksiyon&filter=geciken'); ?>">
                        Görüntüle
                    </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Dashboard widget'ı ekleme
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'bkm_aksiyon_dashboard_widget',
            'BKM Aksiyon Özeti',
            array($this, 'display_dashboard_widget')
        );
    }

    /**
     * Dashboard widget içeriği
     */
    public function display_dashboard_widget() {
        $stats = $this->get_dashboard_stats();
        ?>
        <div class="bkm-dashboard-widget">
            <div class="bkm-stat-grid">
                <div class="bkm-stat-item">
                    <span class="bkm-stat-label">Toplam Aksiyon</span>
                    <span class="bkm-stat-value"><?php echo $stats['toplam_aksiyon']; ?></span>
                </div>
                <div class="bkm-stat-item">
                    <span class="bkm-stat-label">Tamamlanan</span>
                    <span class="bkm-stat-value"><?php echo $stats['tamamlanan_aksiyon']; ?></span>
                </div>
                <div class="bkm-stat-item">
                    <span class="bkm-stat-label">Bekleyen Görev</span>
                    <span class="bkm-stat-value"><?php echo $stats['bekleyen_gorev']; ?></span>
                </div>
                <div class="bkm-stat-item">
                    <span class="bkm-stat-label">Geciken Görev</span>
                    <span class="bkm-stat-value status-warning"><?php echo $stats['geciken_gorev']; ?></span>
                </div>
            </div>

            <?php if (!empty($stats['kategori_dagilimi'])): ?>
                <div class="bkm-chart-container">
                    <canvas id="bkmKategoriChart"></canvas>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        const ctx = document.getElementById('bkmKategoriChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: <?php echo json_encode(wp_list_pluck($stats['kategori_dagilimi'], 'kategori_adi')); ?>,
                                datasets: [{
                                    data: <?php echo json_encode(wp_list_pluck($stats['kategori_dagilimi'], 'toplam')); ?>,
                                    backgroundColor: [
                                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                                    ]
                                }]
                            },
                            options: {
                                maintainAspectRatio: false,
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12
                                    }
                                }
                            }
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Veritabanı kurulumu
     */
    public static function install() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Aksiyonlar tablosu
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            kategori_id bigint(20) NOT NULL,
            baslik varchar(255) NOT NULL,
            detay longtext,
            sorumlular varchar(255) NOT NULL,
            durum varchar(50) NOT NULL DEFAULT 'beklemede',
            ilerleme_durumu int(3) NOT NULL DEFAULT 0,
            onem_derecesi varchar(50) NOT NULL DEFAULT 'orta',
            son_tarih date,
            ekler longtext,
            olusturan_id bigint(20) NOT NULL,
            olusturma_tarihi datetime DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY kategori_id (kategori_id),
            KEY durum (durum),
            KEY olusturan_id (olusturan_id)
        ) $charset_collate;";
        dbDelta($sql);

        // Görevler tablosu
        $table_name = $wpdb->prefix . 'bkm_gorevler';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            aksiyon_id bigint(20) NOT NULL,
            gorev_icerik text NOT NULL,
            sorumlu_kisi bigint(20) NOT NULL,
            hedef_tarih date NOT NULL,
            ilerleme_durumu int(3) NOT NULL DEFAULT 0,
            durum varchar(50) NOT NULL DEFAULT 'beklemede',
            olusturma_tarihi datetime DEFAULT CURRENT_TIMESTAMP,
            guncelleme_tarihi datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY aksiyon_id (aksiyon_id),
            KEY sorumlu_kisi (sorumlu_kisi),
            KEY durum (durum)
        ) $charset_collate;";
        dbDelta($sql);

        // Kategoriler tablosu
        $table_name = $wpdb->prefix . 'bkm_kategoriler';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            kategori_adi varchar(255) NOT NULL,
            olusturma_tarihi datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY kategori_adi (kategori_adi)
        ) $charset_collate;";
        dbDelta($sql);

        // Varsayılan ayarları ekle
        add_option('bkm_aksiyon_settings', array(
            'email_bildirim' => 1,
            'bildirim_siklik' => 'gunluk',
            'rapor_alicilar' => '',
            'varsayilan_gorev_suresi' => 7,
            'max_dosya_boyutu' => 5
        ));
    }

    /**
     * Plugin kaldırma
     */
    public static function uninstall() {
        global $wpdb;

        // Tabloları sil
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bkm_aksiyonlar");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bkm_gorevler");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bkm_kategoriler");

        // Ayarları sil
        delete_option('bkm_aksiyon_settings');
    }
}