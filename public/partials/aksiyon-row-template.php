<?php
if (!defined('ABSPATH')) {
    exit;
}

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

$can_edit = current_user_can('edit_posts');
?>
<tr data-id="<?php echo esc_attr($aksiyon->id); ?>">
    <td>
        <button class="bkm-btn small primary show-gorevler-btn" 
                data-aksiyon-id="<?php echo esc_attr($aksiyon->id); ?>" 
                title="<?php esc_attr_e('Görevleri Göster', 'bkm-aksiyon-takip'); ?>">
            <i class="fas fa-tasks"></i>
        </button>
    </td>
    <td><?php echo esc_html($aksiyon->id); ?></td>
    <td><?php echo esc_html($aksiyon->kategori_adi); ?></td>
    <td>
        <span class="onem-badge <?php echo esc_attr($onem_derecesi_class); ?>">
            <i class="fas fa-<?php echo esc_attr($onem_derecesi_icon); ?>"></i> 
            <?php echo esc_html($onem_derecesi_text); ?>
        </span>
    </td>
    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($aksiyon->acilma_tarihi))); ?></td>
    <td><?php echo esc_html($aksiyon->hafta); ?></td>
    <td><?php echo esc_html($aksiyon->sorumlu_isimler); ?></td>
    <td class="<?php echo esc_attr($hedef_gecikme_class); ?>">
        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($aksiyon->hedef_tarih))); ?>
    </td>
    <td>
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: <?php echo esc_attr($aksiyon->ilerleme_durumu); ?>%"></div>
            <span class="progress-text"><?php echo esc_html($aksiyon->ilerleme_durumu); ?>%</span>
        </div>
    </td>
    <td class="actions">
        <button class="bkm-btn small primary aksiyon-detay-btn" 
                data-id="<?php echo esc_attr($aksiyon->id); ?>" 
                title="<?php esc_attr_e('Detay', 'bkm-aksiyon-takip'); ?>">
            <i class="fas fa-search"></i>
        </button>
        <?php if ($can_edit): ?>
        <button class="bkm-btn small aksiyon-duzenle-btn" 
                data-id="<?php echo esc_attr($aksiyon->id); ?>" 
                title="<?php esc_attr_e('Düzenle', 'bkm-aksiyon-takip'); ?>">
            <i class="fas fa-edit"></i>
        </button>
        <?php endif; ?>
    </td>
</tr> 