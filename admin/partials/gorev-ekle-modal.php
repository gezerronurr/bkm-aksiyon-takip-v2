<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Görev Ekleme Modal -->
<div class="modal fade" id="gorevEkleModal" tabindex="-1" role="dialog" aria-labelledby="gorevEkleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gorevEkleModalLabel">Görev Ekle</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Kapat">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="gorevEkleForm">
                    <input type="hidden" name="aksiyon_id" id="modal_aksiyon_id">
                    
                    <div class="form-group">
                        <label for="gorev_icerigi">Görevin İçeriği</label>
                        <textarea class="form-control" id="gorev_icerigi" name="gorev_icerigi" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="baslangic_tarihi">Başlangıç Tarihi</label>
                        <input type="text" class="form-control datepicker" id="baslangic_tarihi" name="baslangic_tarihi" required>
                    </div>

                    <div class="form-group">
                        <label for="sorumlu_kisi">Sorumlu Kişi</label>
                        <select class="form-control select2" id="sorumlu_kisi" name="sorumlu_kisi" required>
                            <option value="">Seçiniz</option>
                            <?php
                            $users = get_users(['role__in' => ['administrator', 'editor', 'author']]);
                            foreach ($users as $user) {
                                echo '<option value="' . esc_attr($user->ID) . '">' . esc_html($user->display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="hedef_bitis_tarihi">Hedeflenen Bitiş Tarihi</label>
                        <input type="text" class="form-control datepicker" id="hedef_bitis_tarihi" name="hedef_bitis_tarihi" required>
                    </div>

                    <div class="form-group">
                        <label for="ilerleme_durumu">İlerleme Durumu (%)</label>
                        <input type="number" class="form-control" id="ilerleme_durumu" name="ilerleme_durumu" min="0" max="100" value="0" required>
                        <div class="progress mt-2">
                            <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="bkm-btn btn-secondary" data-dismiss="modal">İptal</button>
                <button type="button" class="bkm-btn btn-primary" id="gorevKaydetBtn">Kaydet</button>
            </div>
        </div>
    </div>
</div> 