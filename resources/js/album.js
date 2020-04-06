$(function() {
    var fileUploadedCount = 0;
    var fileUploadErrorCount = 0;
    var fileUploadErrorFiles = [];
    $('#fileupload').fileupload({
        url: $(this).data('url'),
        headers: {"X-CSRF-TOKEN": $('meta[name=csrf-token]').prop('content')},
        add: function (e, data) {
            // preview
            var $div = $('#sort-container').find('.preview-block.d-none:first').clone()
                    .prop('id', data.files[0].name + '---' + new Date().toJSON())
                    .appendTo('#sort-container');

            var reader = new FileReader();
            reader.onload = function (e) {
                $div.removeClass('d-none').children('img').prop('src', e.target.result);
            }
            reader.readAsDataURL(data.files[0]);

            // additional form data to upload
            data.formData = new FormData(ajaxForm);
            data['preview_id'] = $div.prop('id');
            data.submit();
        },
        done: function (e, data) {
            var resp = data.response();
            if (resp.result.message && resp.result.message.length > 0) {
                alert();
                $('#' + $.escapeSelector(data.preview_id)).remove();
                // 顯示上傳狀態
                fileUploadErrorCount++;
                document.getElementById('fileUploadErrorCount').innerHTML = fileUploadErrorCount;
                fileUploadErrorFiles.push(data.files[0].name);
                document.getElementById('fileUploadErrorFiles').innerText  = fileUploadErrorFiles.join('、');
            }
            else {
                $('#' + $.escapeSelector(data.preview_id))
                        .removeClass('in-queue')
                        .css('opacity', 1)
                        .find('input').val(resp.result.data.path);
                // 顯示上傳狀態
                fileUploadedCount++;
                document.getElementById('fileUploadedCount').innerHTML = fileUploadedCount;
            }
        }
    }).on('fileuploadchange', function (e, data) {
        fileUploadedCount = 0;
        fileUploadErrorCount = 0;
        fileUploadErrorFiles = [];
        document.getElementById('fileSelectedCount').innerText  = data.files.length;
        document.getElementById('fileUploadedCount').innerText  = fileUploadedCount;
        document.getElementById('fileUploadErrorCount').innerText  = fileUploadErrorCount;
        document.getElementById('fileUploadErrorFiles').innerText  = fileUploadErrorFiles.join('、');
    }).on('fileuploadprogress', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        if (progress > 50) {
            $('#' + $.escapeSelector(data.preview_id)).css('opacity', 0.5);
        }
    }).on('fileuploadfail', function (e, data) {
        var resp = data.response();
        alert(resp.jqXHR.responseJSON.message);
        $('#' + $.escapeSelector(data.preview_id)).remove();
        // 顯示上傳狀態
        fileUploadErrorCount++;
        document.getElementById('fileUploadErrorCount').innerHTML = fileUploadErrorCount;
        fileUploadErrorFiles.push(data.files[0].name);
        document.getElementById('fileUploadErrorFiles').innerText  = fileUploadErrorFiles.join('、');
    }).prop('disabled', !$.support.fileInput);

    new Sortable($('#sort-container')[0], {
        filter: '.in-queue',
        animation: 150,
        fallbackOnBody: true,
        swapThreshold: 0.65
    });

    $('#sort-container').on('click', '.btn-remove', function(){
        if (!confirm('確認要刪除?'))
            return;

        $(this).parents('.preview-block').remove();
    });
    
    $(document).on('submit', 'form', function(e){
        if ($(".preview-block.in-queue:not('.d-none')").length <= 0
                || confirm('還有未上傳完成的檔案，確定要中斷上傳並送出表單資料?')) {
            $('.preview-block.in-queue', this).remove();
        }
        else {
            e.preventDefault();
            e.stopPropagation();
        }
    });
});