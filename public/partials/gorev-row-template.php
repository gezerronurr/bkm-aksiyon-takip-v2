<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_user_id = get_current_user_id();
$is_admin = current_user_can('manage_options');
$is_owner = $gorev->sorumlu_id == $current_user_id;
$is_completed = $gorev->ilerleme_durumu == 100;
$row_class = $is_completed ? 'completed-task' : '';
?>
<tr class="<?php echo esc_attr($row_class); ?>" data-gorev-id="<?php echo esc_attr($gorev->id); ?>">
    <td><?php echo esc_html($gorev->gorev_icerigi); ?></td>
    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($gorev->baslangic_tarihi))); ?></td>
    <td><?php echo esc_html(get_userdata($gorev->sorumlu_id)->display_name); ?></td>
    <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($gorev->hedef_bitis_tarihi))); ?></td>
    <td>
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: <?php echo esc_attr($gorev->ilerleme_durumu); ?>%"></div>
            <span class="progress-text"><?php echo esc_html($gorev->ilerleme_durumu); ?>%</span>
        </div>
    </td>
    <td>
        <?php if ($gorev->gercek_bitis_tarihi): ?>
            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($gorev->gercek_bitis_tarihi))); ?>
        <?php else: ?>
            -
        <?php endif; ?>
    </td>
    <td class="actions">
        <?php if (!$is_completed && ($is_owner || $is_admin)): ?>
            <button class="bkm-btn small gorev-duzenle-btn" 
                    data-gorev-id="<?php echo esc_attr($gorev->id); ?>"
                    data-gorev-icerigi="<?php echo esc_attr($gorev->gorev_icerigi); ?>"
                    data-ilerleme="<?php echo esc_attr($gorev->ilerleme_durumu); ?>"
                    title="<?php esc_attr_e('Görevi Düzenle', 'bkm-aksiyon-takip'); ?>">
                <i class="fas fa-edit"></i>
            </button>
        <?php endif; ?>
        
        <?php if (!$is_completed && $is_owner): ?>
            <button class="bkm-btn small primary gorev-tamamla-btn"
                    data-gorev-id="<?php echo esc_attr($gorev->id); ?>"
                    title="<?php esc_attr_e('Görevi Tamamla', 'bkm-aksiyon-takip'); ?>">
                <i class="fas fa-check"></i>
            </button>
        <?php endif; ?>
        
        <?php if ($is_admin): ?>
            <button class="bkm-btn small gorev-sil-btn"
                    data-gorev-id="<?php echo esc_attr($gorev->id); ?>"
                    title="<?php esc_attr_e('Görevi Sil', 'bkm-aksiyon-takip'); ?>">
                <i class="fas fa-trash"></i>
            </button>
        <?php endif; ?>
    </td>
</tr> 