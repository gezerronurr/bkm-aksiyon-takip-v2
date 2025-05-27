jQuery(document).ready(function($) {
    // Date formatı için yardımcı fonksiyon
    function formatDate(date) {
        return new Date(date).toLocaleDateString('tr-TR');
    }
    
    // Progress bar güncelleme
    function updateProgressBar(element, value) {
        $(element).find('.progress-bar-fill').css('width', value + '%');
        $(element).find('.progress-value').text(value + '%');
    }
    
    // Modal işlemleri
    function showModal(title = 'Yeni Görev Ekle') {
        $('#bkmModalTitle').text(title);
        $('#bkmTaskModal').fadeIn(300);
    }
    
    function hideModal() {
        $('#bkmTaskModal').fadeOut(300);
        $('#bkmTaskForm')[0].reset();
        $('#taskId').val('');
    }
    
    // Modal kapatma
    $('.bkm-modal-close').click(function() {
        hideModal();
    });
    
    $(window).click(function(e) {
        if ($(e.target).hasClass('bkm-modal')) {
            hideModal();
        }
    });
    
    // Progress range input değer gösterimi
    $('#progress').on('input', function() {
        $('#progressValue').text($(this).val() + '%');
    });
    
    // Login form işleme
    $('#bkmLoginForm').submit(function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $error = $form.find('.form-error');
        
        $.ajax({
            url: bkm_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'bkm_login',
                username: $('#username').val(),
                password: $('#password').val(),
                nonce: bkm_ajax.nonce
            },
            beforeSend: function() {
                $form.find('button').prop('disabled', true);
                $error.hide();
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    $error.html(response.data).fadeIn();
                }
            },
            error: function() {
                $error.html('Bir hata oluştu. Lütfen tekrar deneyin.').fadeIn();
            },
            complete: function() {
                $form.find('button').prop('disabled', false);
            }
        });
    });
    
    // Görev formu gönderimi
    $('#bkmTaskForm').submit(function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'bkm_save_task');
        formData.append('nonce', bkm_ajax.nonce);
        
        $.ajax({
            url: bkm_ajax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#bkmTaskForm button').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    hideModal();
                    loadTasks();
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('Bir hata oluştu. Lütfen tekrar deneyin.');
            },
            complete: function() {
                $('#bkmTaskForm button').prop('disabled', false);
            }
        });
    });
    
    // Görev tamamlama
    $(document).on('click', '.complete-task-btn', function() {
        if (!confirm('Bu görevi tamamlamak istediğinizden emin misiniz?')) {
            return;
        }
        
        var taskId = $(this).data('task-id');
        
        $.ajax({
            url: bkm_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'bkm_complete_task',
                task_id: taskId,
                nonce: bkm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    loadTasks();
                } else {
                    alert(response.data);
                }
            }
        });
    });
    
    // Görevleri yükleme
    function loadTasks() {
        $.ajax({
            url: bkm_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'bkm_load_tasks',
                nonce: bkm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var tasks = response.data;
                    var html = '';
                    
                    tasks.forEach(function(task) {
                        var rowClass = task.completed ? 'task-completed' : '';
                        
                        html += `
                            <tr class="${rowClass}" data-task-id="${task.id}">
                                <td>${task.content}</td>
                                <td>${formatDate(task.start_date)}</td>
                                <td>${task.assigned_user_name}</td>
                                <td>${formatDate(task.target_date)}</td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-bar-fill" style="width: ${task.progress}%"></div>
                                        <span class="progress-value">${task.progress}%</span>
                                    </div>
                                </td>
                                <td>${task.completed ? 'Tamamlandı' : 'Devam Ediyor'}</td>
                                <td>
                                    ${!task.completed && (task.can_edit || task.can_complete) ? `
                                        <div class="task-actions">
                                            ${task.can_edit ? `
                                                <button class="bkm-btn bkm-btn-secondary edit-task-btn" data-task-id="${task.id}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            ` : ''}
                                            ${task.can_complete ? `
                                                <button class="bkm-btn bkm-btn-primary complete-task-btn" data-task-id="${task.id}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            ` : ''}
                                        </div>
                                    ` : ''}
                                </td>
                            </tr>
                        `;
                    });
                    
                    $('#bkmTasksList').html(html);
                }
            }
        });
    }
    
    // Sayfa yüklendiğinde görevleri getir
    loadTasks();
    
    // Yeni görev ekleme butonuna tıklandığında
    $('#bkmAddTaskBtn').click(function() {
        showModal();
    });
    
    // Görev düzenleme
    $(document).on('click', '.edit-task-btn', function() {
        var taskId = $(this).data('task-id');
        
        $.ajax({
            url: bkm_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'bkm_get_task',
                task_id: taskId,
                nonce: bkm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var task = response.data;
                    
                    $('#taskId').val(task.id);
                    $('#taskContent').val(task.content);
                    $('#startDate').val(task.start_date);
                    $('#assignedUser').val(task.assigned_user);
                    $('#targetDate').val(task.target_date);
                    $('#progress').val(task.progress);
                    $('#progressValue').text(task.progress + '%');
                    
                    showModal('Görevi Düzenle');
                }
            }
        });
    });
});