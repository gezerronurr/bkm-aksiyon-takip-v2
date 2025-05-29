jQuery(document).ready(function($) {
    const currentDate = '2025-05-28 14:23:39'; // UTC zaman bilgisi
    const currentUserLogin = 'gezerronurr';

    // Form submit işlemi sırasında sayfa yönlendirme kontrolünü devre dışı bırak
    window.onbeforeunload = null;

    // Select2 başlatma
    initializeSelect2();
    
    function initializeSelect2() {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Seçiniz...',
            allowClear: true,
            language: {
                noResults: function() {
                    return 'Sonuç bulunamadı';
                }
            }
        }).on('select2:select', function(e) {
            $(this).trigger('change');
        });
    }

    // DatePicker başlatma
    $('.datepicker').flatpickr({
        dateFormat: "Y-m-d",
        locale: "tr",
        allowInput: true,
        minDate: "today",
        defaultDate: "today"
    });

    // Önem derecesi seçimi değiştiğinde görsel güncelleme
    $('#onem_derecesi').on('change', function() {
        const value = $(this).val();
        let badge = $(this).siblings('.onem-badge');
        
        if (badge.length === 0) {
            $(this).after('<span class="onem-badge"></span>');
            badge = $(this).siblings('.onem-badge');
        }
        
        badge.removeClass('high medium low').empty();
        
        if (value) {
            switch(value) {
                case '1':
                    badge.addClass('high').html('<i class="fas fa-exclamation-circle"></i> Yüksek');
                    break;
                case '2':
                    badge.addClass('medium').html('<i class="fas fa-exclamation"></i> Orta');
                    break;
                case '3':
                    badge.addClass('low').html('<i class="fas fa-info-circle"></i> Düşük');
                    break;
            }
        }
    }).trigger('change');

    // İlerleme çubuğu kontrolü
    $('#ilerleme_durumu').on('input', function() {
        const value = $(this).val();
        $('.progress-bar').css('width', value + '%');
        $('.progress-value').text(value + '%');
    });

// Görev ekleme toggle işlevi - Güncellenmiş hali
$(document).on('click', '.gorev-ekle-toggle', function(e) {
    e.preventDefault();
    const aksiyonId = $(this).data('aksiyon-id');
    const formRow = $(`#gorev-form-${aksiyonId}`);
    const formDropdown = formRow.find('.gorev-form-dropdown');
    
    // Diğer açık formları kapat
    $('.gorev-form-dropdown').not(formDropdown).slideUp();
    $('.gorev-form-row').not(formRow).removeClass('active');
    
    // Seçilen formun görünürlüğünü toggle et
    formDropdown.slideToggle();
    formRow.toggleClass('active');
    
    // Select2'yi yeniden initialize et
    formRow.find('.select2').select2({
        width: '100%',
        dropdownParent: formRow
    });
});

// İptal butonuna tıklandığında
$(document).on('click', '.gorev-iptal', function(e) {
    e.preventDefault();
    const formDropdown = $(this).closest('.gorev-form-dropdown');
    const formRow = formDropdown.closest('.gorev-form-row');
    
    formDropdown.slideUp();
    formRow.removeClass('active');
});

    // Görev ekleme formu submit
    $(document).on('submit', '.gorev-ekle-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const aksiyonId = $form.data('aksiyon-id');
        
        if (!validateGorevForm($form)) {
            return false;
        }

        const formData = {
            action: 'add_gorev',
            nonce: $form.find('#gorev_nonce').val(),
            aksiyon_id: aksiyonId,
            gorev_icerik: $form.find('textarea[name="gorev_icerik"]').val(),
            sorumlu_kisi: $form.find('select[name="sorumlu_kisi"]').val(),
            hedef_tarih: $form.find('input[name="hedef_tarih"]').val(),
            ilerleme_durumu: 0,
            baslangic_tarihi: formatDate(new Date())
        };

        $.ajax({
            url: bkm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            beforeSend: function() {
                showLoader();
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', 'Görev başarıyla eklendi!');
                    $form.closest('.gorev-form-dropdown').slideUp();
                    $form[0].reset();
                    // Sayfayı yenile
                    location.reload();
                } else {
                    showNotification('error', response.data.message || 'Bir hata oluştu!');
                }
            },
            error: function() {
                showNotification('error', 'Sunucu hatası oluştu!');
            },
            complete: function() {
                hideLoader();
            }
        });
    });

    // Form validasyonu
    function validateGorevForm($form) {
        let isValid = true;
        const requiredFields = $form.find('[required]');
        
        requiredFields.each(function() {
            const field = $(this);
            if (!field.val()) {
                isValid = false;
                showFieldError(field, 'Bu alan zorunludur');
            } else {
                removeFieldError(field);
            }
        });

        // Tarih kontrolleri
        const hedefTarih = new Date($form.find('input[name="hedef_tarih"]').val());
        const bugun = new Date();
        bugun.setHours(0, 0, 0, 0);

        if (hedefTarih < bugun) {
            isValid = false;
            showFieldError($form.find('input[name="hedef_tarih"]'), 'Hedef tarih bugünden küçük olamaz');
        }

        return isValid;
    }

    // Alan hatası göster/gizle
    function showFieldError(field, message) {
        removeFieldError(field);
        field.addClass('error').after('<div class="field-error">' + message + '</div>');
    }

    function removeFieldError(field) {
        field.removeClass('error');
        field.next('.field-error').remove();
    }

    // Aksiyon silme işlemi
    $(document).on('click', '.delete-aksiyon', function(e) {
        e.preventDefault();
        const button = $(this);
        const aksiyonId = button.data('id');
        
        if (confirm('Bu aksiyonu silmek istediğinizden emin misiniz?')) {
            $.ajax({
                url: bkm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_aksiyon',
                    nonce: bkm_ajax.nonce,
                    aksiyon_id: aksiyonId
                },
                beforeSend: function() {
                    showLoader();
                    button.prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Aksiyon başarıyla silindi');
                        button.closest('tr').fadeOut(400, function() {
                            $(this).next('.gorev-form-row').remove();
                            $(this).remove();
                            updateStats();
                        });
                    } else {
                        showNotification('error', response.data.message || 'Bir hata oluştu');
                        button.prop('disabled', false);
                    }
                },
                error: function() {
                    showNotification('error', 'Sunucu hatası oluştu');
                    button.prop('disabled', false);
                },
                complete: function() {
                    hideLoader();
                }
            });
        }
    });

    // Form değişikliklerini izleme
    let formChanged = false;
    $('#bkm-aksiyon-form').on('change input', 'input, select, textarea', function() {
        formChanged = true;
    });

    // Sayfa yönlendirme kontrolü
    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Otomatik kaydetme
    let autoSaveTimeout;
    const AUTO_SAVE_DELAY = 30000; // 30 saniye

    function setupAutoSave() {
        const formFields = $('#bkm-aksiyon-form').find('input, select, textarea');
        
        formFields.on('change', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(autoSave, AUTO_SAVE_DELAY);
        });
    }

    function autoSave() {
        if (!formChanged) return;

        const formData = new FormData($('#bkm-aksiyon-form')[0]);
        formData.append('action', 'auto_save_aksiyon');
        formData.append('nonce', bkm_ajax.nonce);

        $.ajax({
            url: bkm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('info', 'Taslak otomatik kaydedildi');
                    formChanged = false;
                }
            }
        });
    }

    // DataTables başlatma
    if ($('#aksiyonlar-table').length) {
        $('#aksiyonlar-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json'
            },
            pageLength: 25,
            order: [[0, 'desc']],
            responsive: true,
            drawCallback: function() {
                initializeSelect2();
            }
        });
    }

    // Yükleniyor göstergesi
    function showLoader() {
        if (!$('.bkm-loader').length) {
            $('body').append('<div class="bkm-loader"></div>');
        }
        $('.bkm-loader').fadeIn(200);
    }

    function hideLoader() {
        $('.bkm-loader').fadeOut(200);
    }

    // Bildirim gösterici
    function showNotification(type, message) {
        const notification = $('<div>')
            .addClass(`bkm-notification ${type}`)
            .html(`<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>${message}`);

        $('body').append(notification);
        setTimeout(() => {
            notification.addClass('show');
        }, 100);

        setTimeout(() => {
            notification.removeClass('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }

    // İstatistikleri güncelleme
    function updateStats() {
        $.ajax({
            url: bkm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'get_aksiyon_stats',
                nonce: bkm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.stat-pending .stat-value').text(response.data.open_count);
                    $('.stat-completed .stat-value').text(response.data.completed_count);
                    $('.stat-urgent .stat-value').text(response.data.urgent_count);
                    $('.stat-mytasks .stat-value').text(response.data.my_tasks);
                }
            }
        });
    }

    // Filtre temizleme
    $('#clear-filters').on('click', function(e) {
        e.preventDefault();
        window.location.href = $(this).attr('href');
    });

    // Excel export işlemi
    $('#export-excel').on('click', function() {
        showLoader();
        $.ajax({
            url: bkm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'export_aksiyonlar',
                nonce: bkm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const link = document.createElement('a');
                    link.href = response.data.file_url;
                    link.download = 'aksiyonlar.xlsx';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    showNotification('success', 'Excel dosyası indiriliyor...');
                } else {
                    showNotification('error', response.data.message || 'Excel export işlemi başarısız oldu.');
                }
            },
            error: function() {
                showNotification('error', 'Sunucu hatası oluştu');
            },
            complete: function() {
                hideLoader();
            }
        });
    });

    // Tarih formatı
    function formatDate(date) {
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Sayfa yüklendiğinde çalıştırılacak fonksiyonlar
    $(document).ready(function() {
        setupAutoSave();
        initializeSelect2();
        $('#onem_derecesi, #ilerleme_durumu').trigger('change');
    });
});