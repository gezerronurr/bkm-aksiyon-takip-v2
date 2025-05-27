jQuery(document).ready(function($) {
    // Login form işlemleri
    $('#bkm-login-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'aksiyon_takipx_login',
            nonce: bkmAksiyonTakipX.nonce,
            username: $('#username').val(),
            password: $('#password').val()
        };

        $.post(bkmAksiyonTakipX.ajaxurl, formData, function(response) {
            if (response.success) {
                window.location.href = response.data.redirect;
            } else {
                $('#login-message').html('<div class="error">' + response.data.message + '</div>');
            }
        });
    });

    // Aksiyonları yükle
    function loadAksiyonlar() {
        $.post(bkmAksiyonTakipX.ajaxurl, {
            action: 'load_user_aksiyonlar',
            nonce: bkmAksiyonTakipX.nonce
        }, function(response) {
            if (response.success) {
                $('#user-aksiyonlar-table tbody').html(response.data.html);
            }
        });
    }

    if ($('#user-aksiyonlar-table').length) {
        loadAksiyonlar();
    }

    // Modal işlemleri
    function openModal(modalId) {
        $('#' + modalId).fadeIn();
    }

    function closeModal(modalId) {
        $('#' + modalId).fadeOut();
        if (modalId === 'gorev-ekle-modal') {
            $('#gorev-ekle-form')[0].reset();
        } else if (modalId === 'gorev-duzenle-modal') {
            $('#gorev-duzenle-form')[0].reset();
        }
    }

    $('.bkm-close').on('click', function() {
        closeModal($(this).closest('.bkm-modal').attr('id'));
    });

    $(window).on('click', function(e) {
        if ($(e.target).hasClass('bkm-modal')) {
            closeModal($(e.target).attr('id'));
        }
    });

    // Görev ekleme butonu
    $(document).on('click', '.show-gorevler-btn', function() {
        var aksiyonId = $(this).data('aksiyon-id');
        $('#aksiyon_id').val(aksiyonId);
        openModal('gorev-ekle-modal');
    });

    // Görev ekleme formu
    $('#gorev-ekle-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'add_gorev',
            nonce: bkmAksiyonTakipX.nonce,
            aksiyon_id: $('#aksiyon_id').val(),
            gorev_icerigi: $('#gorev_icerigi').val(),
            baslangic_tarihi: $('#baslangic_tarihi').val(),
            sorumlu_kisi: $('#sorumlu_kisi').val(),
            hedef_bitis_tarihi: $('#hedef_bitis_tarihi').val(),
            ilerleme_durumu: $('#ilerleme_durumu').val()
        };

        $.post(bkmAksiyonTakipX.ajaxurl, formData, function(response) {
            if (response.success) {
                closeModal('gorev-ekle-modal');
                loadAksiyonlar();
                alert(bkmAksiyonTakipX.strings.gorevEklendi);
            } else {
                alert(response.data.message);
            }
        });
    });

    // Görev düzenleme butonu
    $(document).on('click', '.gorev-duzenle-btn', function() {
        var gorevId = $(this).data('gorev-id');
        var gorevIcerigi = $(this).data('gorev-icerigi');
        var ilerleme = $(this).data('ilerleme');

        $('#edit_gorev_id').val(gorevId);
        $('#edit_gorev_icerigi').val(gorevIcerigi);
        $('#edit_ilerleme_durumu').val(ilerleme);

        openModal('gorev-duzenle-modal');
    });

    // Görev düzenleme formu
    $('#gorev-duzenle-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = {
            action: 'update_gorev',
            nonce: bkmAksiyonTakipX.nonce,
            gorev_id: $('#edit_gorev_id').val(),
            gorev_icerigi: $('#edit_gorev_icerigi').val(),
            ilerleme_durumu: $('#edit_ilerleme_durumu').val()
        };

        $.post(bkmAksiyonTakipX.ajaxurl, formData, function(response) {
            if (response.success) {
                closeModal('gorev-duzenle-modal');
                loadAksiyonlar();
                alert(bkmAksiyonTakipX.strings.gorevGuncellendi);
            } else {
                alert(response.data.message);
            }
        });
    });

    // Görev tamamlama butonu
    $(document).on('click', '.gorev-tamamla-btn', function() {
        if (!confirm('Bu görevi tamamlamak istediğinizden emin misiniz?')) {
            return;
        }

        var gorevId = $(this).data('gorev-id');
        
        $.post(bkmAksiyonTakipX.ajaxurl, {
            action: 'complete_gorev',
            nonce: bkmAksiyonTakipX.nonce,
            gorev_id: gorevId
        }, function(response) {
            if (response.success) {
                loadAksiyonlar();
                alert(bkmAksiyonTakipX.strings.gorevTamamlandi);
            } else {
                alert(response.data.message);
            }
        });
    });

    // Görev silme butonu (sadece yöneticiler için)
    $(document).on('click', '.gorev-sil-btn', function() {
        if (!confirm('Bu görevi silmek istediğinizden emin misiniz?')) {
            return;
        }

        var gorevId = $(this).data('gorev-id');
        
        $.post(bkmAksiyonTakipX.ajaxurl, {
            action: 'delete_gorev',
            nonce: bkmAksiyonTakipX.nonce,
            gorev_id: gorevId
        }, function(response) {
            if (response.success) {
                loadAksiyonlar();
                alert(bkmAksiyonTakipX.strings.gorevSilindi);
            } else {
                alert(response.data.message);
            }
        });
    });
}); 