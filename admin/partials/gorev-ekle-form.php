<?php
/**
 * Görev ekleme formu partial dosyası
 *
 * @package BKM_Aksiyon_Takip
 * @since 1.0.0
 * 
 * @param int $aksiyon_id Aksiyonun ID'si
 * @param object $aksiyon Aksiyon verileri (opsiyonel)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Güvenlik kontrolü
if (!isset($aksiyon_id) || !$aksiyon_id) {
    return;
}

// Gerekli değişkenler
$current_date = '2025-05-29 08:08:41'; // UTC zaman bilgisi
$current_user_login = 'gezerronurr';
?>

<tr class="gorev-form-row" id="gorev-form-<?php echo esc_attr($aksiyon_id); ?>">
    <td colspan="9">
        <div class="gorev-form-dropdown">
            <form class="gorev-ekle-form" data-aksiyon-id="<?php echo esc_attr($aksiyon_id); ?>">
                <?php 
                // Güvenlik nonce
                wp_nonce_field('bkm_gorev_nonce', 'gorev_nonce'); 
                ?>
                
                <div class="form-grid">
                    <!-- Görev İçeriği -->
                    <div class="form-group">
                        <label for="gorev_icerik_<?php echo esc_attr($aksiyon_id); ?>">
                            <i class="fas fa-tasks"></i> Görev İçeriği
                        </label>
                        <textarea 
                            name="gorev_icerik" 
                            id="gorev_icerik_<?php echo esc_attr($aksiyon_id); ?>" 
                            class="form-control" 
                            rows="3" 
                            placeholder="Görev açıklamasını giriniz..."
                            required
                        ></textarea>
                        <small class="form-text text-muted">
                            Görev içeriğini detaylı bir şekilde açıklayınız.
                        </small>
                    </div>

                    <!-- Sorumlu Kişi -->
                    <div class="form-group">
                        <label for="sorumlu_kisi_<?php echo esc_attr($aksiyon_id); ?>">
                            <i class="fas fa-user"></i> Sorumlu Kişi
                        </label>
                        <select 
                            name="sorumlu_kisi" 
                            id="sorumlu_kisi_<?php echo esc_attr($aksiyon_id); ?>" 
                            class="form-control select2" 
                            required
                        >
                            <option value="">Seçiniz...</option>
                            <?php
                            // Kullanıcıları getir
                            $users = get_users([
                                'role__in' => ['administrator', 'editor', 'author'],
                                'orderby'  => 'display_name',
                                'order'    => 'ASC'
                            ]);

                            foreach ($users as $user) {
                                echo sprintf(
                                    '<option value="%s">%s</option>',
                                    esc_attr($user->ID),
                                    esc_html($user->display_name)
                                );
                            }
                            ?>
                        </select>
                        <small class="form-text text-muted">
                            Görevi atayacağınız kişiyi seçiniz.
                        </small>
                    </div>

                    <!-- Hedef Tarih -->
                    <div class="form-group">
                        <label for="hedef_tarih_<?php echo esc_attr($aksiyon_id); ?>">
                            <i class="fas fa-calendar"></i> Hedef Tarih
                        </label>
                        <input 
                            type="text" 
                            name="hedef_tarih" 
                            id="hedef_tarih_<?php echo esc_attr($aksiyon_id); ?>" 
                            class="form-control datepicker" 
                            placeholder="Tarih seçiniz..."
                            required
                            autocomplete="off"
                        >
                        <small class="form-text text-muted">
                            Görevin tamamlanması gereken tarihi seçiniz.
                        </small>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="bkm-btn btn-primary" id="submit-gorev-<?php echo esc_attr($aksiyon_id); ?>">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                    <button type="button" class="bkm-btn btn-secondary gorev-iptal">
                        <i class="fas fa-times"></i> İptal
                    </button>

                    <?php if (current_user_can('manage_options')): ?>
                        <div class="form-text mt-2">
                            <small>
                                <i class="fas fa-info-circle"></i> 
                                Son güncelleme: <?php echo esc_html($current_date); ?> | 
                                Kullanıcı: <?php echo esc_html($current_user_login); ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Form Validation Messages -->
            <div class="alert alert-danger validation-error" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <span class="message"></span>
            </div>
        </div>
    </td>
</tr>