$(function() {
    $('.form-control-summernote').summernote({
        lang: 'zh-TW',
        toolbar: [
            ['misc', ['undo', 'redo']],
            ['style', ['style', 'fontname', 'bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        height: 200,
        maxHeight: 320,
        callbacks: {
            onImageUpload: function(images) {
                $(this).parents('.card').find('.overlay').removeClass('d-none');
                uploadImages(this, images, 0, $(this).data('url'));
            }
        }
    });

    function uploadImages(textarea, images, startAt, uploadUrl)
    {
        // laravel request files collection, 最多只有 20 個, 超過的話需要分段上傳
        var uploadSize = 20;
        var uploadLimit = startAt + uploadSize;
        if (uploadLimit > images.length) {
            uploadLimit = images.length;
        }

        var data = new FormData();
        data.append('folder', $('#ajax-form [name=folder]').val());
        for (var i=startAt; i<uploadLimit; i++) {
            data.append('file[]', images.item(i));
        }

        $.ajax({
            url: uploadUrl,
            cache: false,
            contentType: false,
            processData: false,
            data: data,
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('meta[name=csrf-token]').prop('content')},
            uploadSize: uploadSize,
            uploadLimit: uploadLimit,
            uploadUrl: uploadUrl,
            images: images,
            textarea: textarea
        }).always(function(resp, status)
        {
            if (this.uploadLimit < this.images.length) {
                uploadImages(this.textarea, images, this.uploadLimit, this.uploadUrl);
            }
            else {
                // upload complete!
                $(this.textarea).parents('.card').find('.overlay').addClass('d-none');
            }

            var errorMessage = '';
            if (status === 'success') 
            {
                for (var i in resp.data) {
                    $(this.textarea).summernote('insertImage', resp.data[i].url);
                }
            }
            else
            {
                errorMessage = '[' + resp.status + ']' + (resp.responseJSON.message || resp.responseJSON.exception);
                Admin.toast.fire({ type: 'error', title: errorMessage });
            }
        });
    }
});