jQuery(document).ready(function($) {
    const currentDate = '2025-05-29 08:05:55'; // UTC zaman bilgisi
    const currentUserLogin = 'gezerronurr';

    console.log('BKM Aksiyon Admin JS Loaded');

    // DataTables Başlatma
    if ($.fn.DataTable) {
        const dataTable = $('#aksiyonlar-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Turkish.json'
            },
            pageLength: 25,
            order: [[0, 'desc']],
            responsive: true,
            columnDefs: [
                { orderable: false, targets: -1 } // Son sütun için sıralama kapalı
            ],
            dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            initComplete: function() {
                console.log('DataTable initialized');
            }
        });

        // DataTables responsive özelliği için
        dataTable.on('responsive-display', function() {
            initializeDynamicElements();
        });
    }

    // Select2 Başlatma - Filtreler için
    $('.filter-select').select2({
        width: '100%',
        placeholder: 'Seçiniz...',
        allowClear: true,
        language: {
            noResults: function() {
                return "Sonuç bulunamadı";
            }
        }
    });

    // Select2 Başlatma - Aksiyon formu için
    $('.aksiyon-select').select2({
        width: '100%',
        placeholder: 'Seçiniz...',
        allowClear: true,
        language: {
            noResults: function() {
                return "Sonuç bulunamadı";
            }
        }
    });

    // Select2 - Çoklu seçim için özel tasarım
    $('.multiple-select').select2({
        width: '100%',
        placeholder: 'Seçiniz...',
        allowClear: true,
        language: {
            noResults: function() {
                return "Sonuç bulunamadı";
            }
        },
        templateResult: formatUser,
        templateSelection: formatUserSelection
    });

    // Select2 için özel format fonksiyonları
    function formatUser(user) {
        if (!user.id) return user.text;
        return $('<span><i class="fas fa-user"></i> ' + user.text + '</span>');
    }

    function formatUserSelection(user) {
        if (!user.id) return user.text;
        return $('<span>' + user.text + '</span>');
    }

    // Flatpickr - Tarih seçici başlatma
    $('.datepicker').flatpickr({
        dateFormat: "Y-m-d",
        locale: "tr",
        allowInput: true,
        minDate: "today",
        defaultDate: "today"
    });

    // Dinamik elementleri initialize etme
    function initializeDynamicElements() {
        // Select2 yeniden initialize
        $('.select2-dynamic').select2({
            width: '100%',
            placeholder: 'Seçiniz...'
        });

        // Flatpickr yeniden initialize
        $('.datepicker-dynamic').flatpickr({
            dateFormat: "Y-m-d",
            locale: "tr"
        });
    }

    // Görev formu initialize
    function initializeGorevForm($form) {
        console.log('Form initialize ediliyor...');
        
        // Select2
        $form.find('.select2').select2({
            width: '100%',
            dropdownParent: $form.closest('.gorev-form-dropdown'),
            placeholder: 'Seçiniz...',
            language: {
                noResults: function() {
                    return "Sonuç bulunamadı";
                }
            }
        });

        // Datepicker
        $form.find('.datepicker').flatpickr({
            dateFormat: "Y-m-d",
            locale: "tr",
            allowInput: true,
            minDate: "today",
            defaultDate: "today"
        });
    }

// Sayfa yüklendiğinde tüm formları gizle
$(document).ready(function() {
    $('.gorev-form-row').hide();
});

    // Görev ekleme toggle işlevi
    $(document).on('click', '.gorev-ekle-toggle', function(e) {
        e.preventDefault();
        console.log('Görev Ekle butonuna tıklandı');
        
        const aksiyonId = $(this).data('aksiyon-id');
        console.log('Aksiyon ID:', aksiyonId);
        
        const formRow = $('#gorev-form-' + aksiyonId);
        console.log('Form Row bulundu:', formRow.length > 0);

        // Diğer açık formları kapat
        $('.gorev-form-row').not(formRow).each(function() {
            const $this = $(this);
            $this.find('.gorev-form-dropdown').slideUp(300, function() {
                $this.removeClass('active').hide();
            });
        });

        // Seçilen formu toggle et
        if (formRow.hasClass('active')) {
            formRow.find('.gorev-form-dropdown').slideUp(300, function() {
                formRow.removeClass('active').hide();
            });
        } else {
            formRow.addClass('active').show();
            formRow.find('.gorev-form-dropdown').slideDown(300, function() {
                initializeGorevForm(formRow.find('form'));
            });
        }
    });

    // İptal butonuna tıklandığında
    $(document).on('click', '.gorev-iptal', function(e) {
        e.preventDefault();
        const formRow = $(this).closest('.gorev-form-row');
        formRow.find('.gorev-form-dropdown').slideUp(300, function() {
            formRow.removeClass('active').hide();
        });
    });

    // Aksiyon silme işlemi
    $(document).on('click', '.delete-aksiyon', function(e) {
        e.preventDefault();
        const aksiyonId = $(this).data('id');
        
        if (confirm('Bu aksiyonu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
            $.ajax({
                url: bkm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_aksiyon',
                    aksiyon_id: aksiyonId,
                    nonce: bkm_ajax.nonce
                },
                beforeSend: function() {
                    showLoader();
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('success', 'Aksiyon başarıyla silindi!');
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
        }
    });

    // Görev formu submit
    $(document).on('submit', '.gorev-ekle-form', function(e) {
        e.preventDefault();
        console.log('Form submit edildi');
        
        const $form = $(this);
        const aksiyonId = $form.data('aksiyon-id');
        
        const formData = {
            action: 'add_gorev',
            nonce: $form.find('#gorev_nonce').val(),
            aksiyon_id: aksiyonId,
            gorev_icerik: $form.find('textarea[name="gorev_icerik"]').val(),
            sorumlu_kisi: $form.find('select[name="sorumlu_kisi"]').val(),
            hedef_tarih: $form.find('input[name="hedef_tarih"]').val(),
            ilerleme_durumu: 0
        };

        // Form validasyonu
        if (!formData.gorev_icerik || !formData.sorumlu_kisi || !formData.hedef_tarih) {
            showNotification('error', 'Lütfen tüm alanları doldurun!');
            return;
        }

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
                    $form[0].reset();
                    $form.closest('.gorev-form-row').removeClass('active').hide();
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

    // Aksiyon ekleme/düzenleme formu submit
    $('#aksiyon-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const formData = new FormData(this);
        
        // Temel validasyon
        if (!formData.get('kategori_id') || !formData.get('sorumlular[]')) {
            showNotification('error', 'Lütfen tüm zorunlu alanları doldurun!');
            return;
        }

        // TinyMCE içeriğini formData'ya ekle
        if (typeof tinyMCE !== 'undefined') {
            const editor = tinyMCE.get('aksiyon_detay');
            if (editor) {
                formData.set('aksiyon_detay', editor.getContent());
            }
        }

        $.ajax({
            url: bkm_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                showLoader();
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', 'Aksiyon başarıyla kaydedildi!');
                    setTimeout(function() {
                        window.location.href = response.data.redirect_url;
                    }, 1000);
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

    // Filtreleme formu submit
    $('#aksiyon-filter-form').on('submit', function(e) {
        e.preventDefault();
        const filterData = $(this).serialize();
        window.location.href = window.location.pathname + '?' + filterData;
    });

    // Filtre temizleme
    $('#clear-filters').on('click', function(e) {
        e.preventDefault();
        window.location.href = window.location.pathname;
    });

    // İlerleme durumu güncelleme
    $(document).on('change', '.ilerleme-durumu', function() {
        const aksiyonId = $(this).data('aksiyon-id');
        const yeniDurum = $(this).val();

        $.ajax({
            url: bkm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'update_ilerleme_durumu',
                aksiyon_id: aksiyonId,
                ilerleme_durumu: yeniDurum,
                nonce: bkm_ajax.nonce
            },
            beforeSend: function() {
                showLoader();
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', 'İlerleme durumu güncellendi!');
                    updateProgressBadge(aksiyonId, yeniDurum);
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

    // İlerleme durumu badge güncelleme
    function updateProgressBadge(aksiyonId, progress) {
        const badge = $(`#progress-badge-${aksiyonId}`);
        let statusClass = 'status-inactive';
        
        if (progress >= 100) {
            statusClass = 'status-active';
        } else if (progress >= 50) {
            statusClass = 'status-pending';
        }

        badge.removeClass('status-active status-pending status-inactive')
             .addClass(statusClass)
             .text('%' + progress);
    }

    // Sayfa yüklendiğinde tüm formları gizle
    $('.gorev-form-row').hide();

    // Dışarı tıklandığında açık formları kapat
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.gorev-form-row, .gorev-ekle-toggle, .select2-container').length) {
            $('.gorev-form-row.active').each(function() {
                const $this = $(this);
                $this.find('.gorev-form-dropdown').slideUp(300, function() {
                    $this.removeClass('active').hide();
                });
            });
        }
    });

    // Loader göster/gizle fonksiyonları
    function showLoader() {
        if ($('#loader').length === 0) {
            $('body').append('<div id="loader" class="loader-overlay"><div class="loader"></div></div>');
        }
        $('#loader').fadeIn();
    }

    function hideLoader() {
        $('#loader').fadeOut(function() {
            $(this).remove();
        });
    }

    // Bildirim göster fonksiyonu
    function showNotification(type, message) {
        const notificationClass = type === 'success' ? 'notice-success' : 'notice-error';
        const $notification = $(`
            <div class="notice ${notificationClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Bu bildirimi kapat.</span>
                </button>
            </div>
        `);

        $('.wrap > h1').after($notification);

        // 5 saniye sonra otomatik kapat
        setTimeout(function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);

        // Kapat butonuna tıklanınca
        $notification.find('.notice-dismiss').on('click', function() {
            $notification.fadeOut(function() {
                $(this).remove();
            });
        });
    }

    // Dosya yükleme alanı için özel işlevler
    $('.custom-file-input').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName || 'Dosya seçiniz...');
    });

    // AJAX hata yönetimi
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.error('AJAX Error:', error);
        showNotification('error', 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.');
        hideLoader();
    });

    // Sayfa yükleme tamamlandığında
    $(window).on('load', function() {
        // Tüm dinamik elementleri initialize et
        initializeDynamicElements();
        
        // URL'de hash varsa ilgili sekmeye git
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            $(`a[href="#${hash}"]`).tab('show');
        }
    });
});