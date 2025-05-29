<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Görev ekleme formu partial dosyası
 * 
 * @param int $aksiyon_id Aksiyonun ID'si
 * @param object $aksiyon Aksiyon verileri (opsiyonel)
 */
?>
<tr class="gorev-form-row" id="gorev-form-<?php echo esc_attr($aksiyon_id); ?>">
    <td colspan="9">
        <div class="gorev-form-dropdown">
            <form class="gorev-ekle-form" data-aksiyon-id="<?php echo esc_attr($aksiyon_id); ?>">
                <?php wp_nonce_field('bkm_gorev_nonce', 'gorev_nonce'); ?>
                
                <div class="form-grid">
                    <!-- Görev İçeriği -->
                    <div class="form-group">
                        <label for="gorev_icerik_<?php echo esc_attr($aksiyon_id); ?>">
                            <i class="fas fa-tasks"></i> Görev İçeriği:
                        </label>
                        <textarea 
                            name="gorev_icerik" 
                            id="gorev_icerik_<?php echo esc_attr($aksiyon_id); ?>" 
                            class="form-control" 
                            rows="3" 
                            placeholder="Görev açıklamasını giriniz..."
                            required
                        ></textarea>
                    </div>

                    <!-- Sorumlu Kişi -->
                    <div class="form-group">
                        <label for="sorumlu_kisi_<?php echo esc_attr($aksiyon_id); ?>">
                            <i class="fas fa-user"></i> Sorumlu Kişi:
                        </label>
                        <select 
                            name="sorumlu_kisi" 
                            id="sorumlu_kisi_<?php echo esc_attr($aksiyon_id); ?>" 
                            class="form-control select2" 
                            required
                        >
                            <option value="">Seçiniz...</option>
                            <?php
                            $users = get_users(['role__in' => ['administrator', 'editor', 'author']]);
                            foreach ($users as $user) {
                                echo sprintf(
                                    '<option value="%s">%s</option>',
                                    esc_attr($user->ID),
                                    esc_html($user->display_name)
                                );
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Hedef Tarih -->
                    <div class="form-group">
                        <label for="hedef_tarih_<?php echo esc_attr($aksiyon_id); ?>">
                            <i class="fas fa-calendar-alt"></i> Hedef Tarih:
                        </label>
                        <input 
                            type="text" 
                            name="hedef_tarih" 
                            id="hedef_tarih_<?php echo esc_attr($aksiyon_id); ?>" 
                            class="form-control datepicker" 
                            placeholder="Tarih seçiniz..."
                            required
                        >
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="bkm-btn btn-primary">
                        <i class="fas fa-save"></i> Kaydet
                    </button>
                    <button type="button" class="bkm-btn btn-secondary gorev-iptal">
                        <i class="fas fa-times"></i> İptal
                    </button>
                </div>
            </form>
        </div>
    </td>
</tr>