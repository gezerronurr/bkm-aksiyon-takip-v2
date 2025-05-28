<?php
// Direct access kontrolü
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$current_user_id = get_current_user_id();
?>

<div class="wrap">
    <!-- Header -->
    <div class="bkm-header">
        <div class="header-left">
            <h1>Aksiyon Listesi</h1>
            <p>Tüm aksiyonların listesi ve yönetimi</p>
        </div>
        <div class="header-actions">
            <a href="<?php echo admin_url('admin.php?page=bkm-aksiyon-ekle'); ?>" class="bkm-btn btn-primary">
                <i class="fas fa-plus"></i> Yeni Aksiyon
            </a>
            <button class="bkm-btn btn-secondary" id="export-excel">
                <i class="fas fa-file-excel"></i> Excel
            </button>
        </div>
    </div>

    <!-- İstatistik Kartları -->
    <div class="stats-container">
        <?php
        // Toplam aksiyon sayısı
        $total_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar");
        
        // Açık aksiyon sayısı
        $open_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar WHERE ilerleme_durumu < 100");
        
        // Acil aksiyon sayısı
        $urgent_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar WHERE onem_derecesi = 1 AND ilerleme_durumu < 100");
        
        // Benim aksiyonlarım
        $my_tasks = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}bkm_aksiyonlar WHERE FIND_IN_SET(%d, sorumlular) AND ilerleme_durumu < 100",
            $current_user_id
        ));
        ?>
        
        <div class="stat-card stat-pending">
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <div class="stat-value"><?php echo $open_count; ?></div>
            <div class="stat-label">Açık Aksiyon</div>
        </div>

        <div class="stat-card stat-completed">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-value"><?php echo $total_count - $open_count; ?></div>
            <div class="stat-label">Tamamlanan Aksiyon</div>
        </div>

        <div class="stat-card stat-urgent">
            <div class="stat-icon"><i class="fas fa-exclamation-circle"></i></div>
            <div class="stat-value"><?php echo $urgent_count; ?></div>
            <div class="stat-label">Acil Aksiyon</div>
        </div>

        <div class="stat-card stat-mytasks">
            <div class="stat-icon"><i class="fas fa-user-circle"></i></div>
            <div class="stat-value"><?php echo $my_tasks; ?></div>
            <div class="stat-label">Benim Aksiyonlarım</div>
        </div>
    </div>

    <!-- Filtreler -->
    <div class="form-container">
        <form id="filter-form" method="get">
            <input type="hidden" name="page" value="bkm-aksiyon-takip">
            <div class="form-grid">
                <!-- Kategori Filtresi -->
                <div class="form-group">
                    <label for="filter_kategori">Kategori</label>
                    <select name="filter_kategori" id="filter_kategori" class="select2">
                        <option value="">Tümü</option>
                        <?php
                        $kategoriler = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bkm_kategoriler ORDER BY kategori_adi ASC");
                        foreach ($kategoriler as $kategori) {
                            $selected = isset($_GET['filter_kategori']) && $_GET['filter_kategori'] == $kategori->id ? 'selected' : '';
                            echo '<option value="' . esc_attr($kategori->id) . '" ' . $selected . '>' . esc_html($kategori->kategori_adi) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Tanımlayan Filtresi -->
                <div class="form-group">
                    <label for="filter_tanimlayan">Tanımlayan</label>
                    <select name="filter_tanimlayan" id="filter_tanimlayan" class="select2">
                        <option value="">Tümü</option>
                        <?php
                        $users = get_users(['role__in' => ['administrator', 'editor', 'author']]);
                        foreach ($users as $user) {
                            $selected = isset($_GET['filter_tanimlayan']) && $_GET['filter_tanimlayan'] == $user->ID ? 'selected' : '';
                            echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Aksiyon Sorumlusu Filtresi -->
                <div class="form-group">
                    <label for="filter_sorumlu">Aksiyon Sorumlusu</label>
                    <select name="filter_sorumlu" id="filter_sorumlu" class="select2">
                        <option value="">Tümü</option>
                        <?php
                        foreach ($users as $user) {
                            $selected = isset($_GET['filter_sorumlu']) && $_GET['filter_sorumlu'] == $user->ID ? 'selected' : '';
                            echo '<option value="' . esc_attr($user->ID) . '" ' . $selected . '>' . esc_html($user->display_name) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <!-- Durum Filtresi -->
                <div class="form-group">
                    <label for="filter_durum">Durum</label>
                    <select name="filter_durum" id="filter_durum" class="select2">
                        <option value="">Tümü</option>
                        <option value="open" <?php echo isset($_GET['filter_durum']) && $_GET['filter_durum'] == 'open' ? 'selected' : ''; ?>>Açık</option>
                        <option value="completed" <?php echo isset($_GET['filter_durum']) && $_GET['filter_durum'] == 'completed' ? 'selected' : ''; ?>>Tamamlanan</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button type="submit" class="bkm-btn btn-primary">
                    <i class="fas fa-filter"></i> Filtrele
                </button>
                <a href="<?php echo admin_url('admin.php?page=bkm-aksiyon-takip'); ?>" class="bkm-btn btn-secondary" id="clear-filters">
                    <i class="fas fa-times"></i> Temizle
                </a>
            </div>
        </form>
    </div>

    <!-- Aksiyon Tablosu -->
    <div class="form-container">
        <table id="aksiyonlar-table" class="bkm-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kategori</th>
                    <th>Tanımlayan</th>
                    <th>Aksiyon Sorumlusu</th>
                    <th>Açılma Tarihi</th>
                    <th>Hedef Tarih</th>
                    <th>Önem</th>
                    <th>İlerleme</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Filtre parametrelerini al
                $where = array("1=1");
                $where_values = array();

                if (!empty($_GET['filter_kategori'])) {
                    $where[] = "a.kategori_id = %d";
                    $where_values[] = intval($_GET['filter_kategori']);
                }

                if (!empty($_GET['filter_tanimlayan'])) {
                    $where[] = "a.tanimlayan_id = %d";
                    $where_values[] = intval($_GET['filter_tanimlayan']);
                }

                if (!empty($_GET['filter_sorumlu'])) {
                    $where[] = "FIND_IN_SET(%d, a.sorumlular)";
                    $where_values[] = intval($_GET['filter_sorumlu']);
                }

                if (!empty($_GET['filter_durum'])) {
                    if ($_GET['filter_durum'] === 'completed') {
                        $where[] = "a.ilerleme_durumu = 100";
                    } else if ($_GET['filter_durum'] === 'open') {
                        $where[] = "a.ilerleme_durumu < 100";
                    }
                }

                // SQL sorgusunu oluştur - Sorumlular için GROUP_CONCAT eklendi
                $query = $wpdb->prepare(
                    "SELECT a.*, k.kategori_adi, u.display_name as tanimlayan_adi,
                            GROUP_CONCAT(DISTINCT usr.display_name ORDER BY usr.display_name ASC SEPARATOR ', ') as sorumlular_liste
                    FROM {$wpdb->prefix}bkm_aksiyonlar a
                    LEFT JOIN {$wpdb->prefix}bkm_kategoriler k ON a.kategori_id = k.id
                    LEFT JOIN {$wpdb->users} u ON a.tanimlayan_id = u.ID
                    LEFT JOIN {$wpdb->users} usr ON FIND_IN_SET(usr.ID, a.sorumlular)
                    WHERE " . implode(' AND ', $where) . "
                    GROUP BY a.id
                    ORDER BY a.id DESC",
                    $where_values
                );

                $aksiyonlar = $wpdb->get_results($query);

                foreach ($aksiyonlar as $aksiyon):
                    $onem_derecesi_class = '';
                    $onem_derecesi_text = '';
                    $onem_derecesi_icon = '';

                    switch ($aksiyon->onem_derecesi) {
                        case 1:
                            $onem_derecesi_class = 'high';
                            $onem_derecesi_text = __('Yüksek', 'bkm-aksiyon-takip');
                            $onem_derecesi_icon = 'exclamation-circle';
                            break;
                        case 2:
                            $onem_derecesi_class = 'medium';
                            $onem_derecesi_text = __('Orta', 'bkm-aksiyon-takip');
                            $onem_derecesi_icon = 'exclamation-triangle';
                            break;
                        case 3:
                            $onem_derecesi_class = 'low';
                            $onem_derecesi_text = __('Düşük', 'bkm-aksiyon-takip');
                            $onem_derecesi_icon = 'info-circle';
                            break;
                    }

                    $hedef_gecikme_class = '';
                    if (!$aksiyon->kapanma_tarihi && strtotime($aksiyon->hedef_tarih) < current_time('timestamp')) {
                        $hedef_gecikme_class = 'gecikme';
                    }
                ?>
                <tr>
                    <td>#<?php echo $aksiyon->id; ?></td>
                    <td><?php echo esc_html($aksiyon->kategori_adi); ?></td>
                    <td><?php echo esc_html($aksiyon->tanimlayan_adi); ?></td>
                    <td><?php echo esc_html($aksiyon->sorumlular_liste); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($aksiyon->acilma_tarihi)); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($aksiyon->hedef_tarih)); ?></td>
                    <td>
                        <?php
                        $onem_class = '';
                        $onem_text = '';
                        $onem_icon = '';
                        
                        switch ($aksiyon->onem_derecesi) {
                            case 1:
                                $onem_class = 'high';
                                $onem_text = __('Yüksek', 'bkm-aksiyon-takip');
                                $onem_icon = 'exclamation-circle';
                                break;
                            case 2:
                                $onem_class = 'medium';
                                $onem_text = __('Orta', 'bkm-aksiyon-takip');
                                $onem_icon = 'exclamation-triangle';
                                break;
                            case 3:
                                $onem_class = 'low';
                                $onem_text = __('Düşük', 'bkm-aksiyon-takip');
                                $onem_icon = 'info-circle';
                                break;
                        }
                        ?>
                        <span class="onem-badge <?php echo $onem_class; ?>">
                            <i class="fas fa-<?php echo $onem_icon; ?>"></i> <?php echo $onem_text; ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $ilerleme_class = '';
                        if ($aksiyon->ilerleme_durumu == 100) {
                            $ilerleme_class = 'completed';
                        } elseif ($aksiyon->ilerleme_durumu >= 50) {
                            $ilerleme_class = 'in-progress';
                        } else {
                            $ilerleme_class = 'pending';
                        }
                        ?>
                        <div class="progress-bar-container">
                            <div class="progress-bar <?php echo $ilerleme_class; ?>" style="width: <?php echo $aksiyon->ilerleme_durumu; ?>%"></div>
                            <span class="progress-text"><?php echo $aksiyon->ilerleme_durumu; ?>%</span>
                        </div>
                    </td>
                    <td>
                        <div class="btn-group">
                            <a href="<?php echo admin_url('admin.php?page=bkm-aksiyon-ekle&id=' . $aksiyon->id); ?>" 
                               class="bkm-btn btn-info btn-sm" 
                               title="<?php esc_attr_e('Düzenle', 'bkm-aksiyon-takip'); ?>">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="bkm-btn btn-primary btn-sm gorev-ekle-btn" 
                                    data-id="<?php echo esc_attr($aksiyon->id); ?>" 
                                    title="<?php esc_attr_e('Görev Ekle', 'bkm-aksiyon-takip'); ?>">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" 
                                    class="bkm-btn btn-danger btn-sm delete-aksiyon" 
                                    data-id="<?php echo $aksiyon->id; ?>" 
                                    title="<?php esc_attr_e('Sil', 'bkm-aksiyon-takip'); ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr class="gorev-form-row" id="gorev-form-<?php echo esc_attr($aksiyon->id); ?>" style="display: none;">
                    <td colspan="9">
                        <div class="gorev-form-container">
                            <h4><?php _e('Yeni Görev Ekle', 'bkm-aksiyon-takip'); ?></h4>
                            <form class="gorev-ekle-form" data-aksiyon-id="<?php echo esc_attr($aksiyon->id); ?>">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="gorev_icerigi_<?php echo esc_attr($aksiyon->id); ?>"><?php _e('Görev İçeriği', 'bkm-aksiyon-takip'); ?></label>
                                        <textarea name="gorev_icerigi" id="gorev_icerigi_<?php echo esc_attr($aksiyon->id); ?>" class="form-control" required></textarea>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="baslangic_tarihi_<?php echo esc_attr($aksiyon->id); ?>"><?php _e('Başlangıç Tarihi', 'bkm-aksiyon-takip'); ?></label>
                                        <input type="date" name="baslangic_tarihi" id="baslangic_tarihi_<?php echo esc_attr($aksiyon->id); ?>" class="form-control datepicker" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="hedef_bitis_tarihi_<?php echo esc_attr($aksiyon->id); ?>"><?php _e('Hedef Bitiş Tarihi', 'bkm-aksiyon-takip'); ?></label>
                                        <input type="date" name="hedef_bitis_tarihi" id="hedef_bitis_tarihi_<?php echo esc_attr($aksiyon->id); ?>" class="form-control datepicker" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="sorumlu_kisi_<?php echo esc_attr($aksiyon->id); ?>"><?php _e('Sorumlu Kişi', 'bkm-aksiyon-takip'); ?></label>
                                        <select name="sorumlu_kisi" id="sorumlu_kisi_<?php echo esc_attr($aksiyon->id); ?>" class="form-control" required>
                                            <option value=""><?php _e('Seçiniz...', 'bkm-aksiyon-takip'); ?></option>
                                            <?php
                                            $users = get_users(array('role__in' => array('administrator', 'editor', 'author')));
                                            foreach ($users as $user) {
                                                echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="ilerleme_durumu_<?php echo esc_attr($aksiyon->id); ?>"><?php _e('İlerleme Durumu (%)', 'bkm-aksiyon-takip'); ?></label>
                                        <input type="number" name="ilerleme_durumu" id="ilerleme_durumu_<?php echo esc_attr($aksiyon->id); ?>" class="form-control" min="0" max="100" value="0" required>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="bkm-btn primary"><?php _e('Kaydet', 'bkm-aksiyon-takip'); ?></button>
                                    <button type="button" class="bkm-btn gorev-form-iptal"><?php _e('İptal', 'bkm-aksiyon-takip'); ?></button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>