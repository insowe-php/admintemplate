var Admin = new function ()
{
    /**
     * 預設檔案上傳限制大小(MB)
     */
    const UPLOAD_SIZE_LIMIT = 20;
    
    const TOAST = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 3000
    });
    
    function init() 
    {
        $('body').addClass(localStorage.getItem('remember.lte.pushmenu') || '');
        $(document).ready(function () 
        {
            $(document).on('keyup keypress', 'input', function (e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
            
            $('form').on('click', '.btn-delete', function () {
                if (confirm('確認要刪除?')) {
                    $('[name=is_valid]').val(0);
                    $(this).parents('form').submit();
                }
            }).on('click', '.btn-reset', function () {
                var url = $('a.back-to-list').length > 0 ? $('a.back-to-list').prop('href') : location.href;
                location.href = url;
            }).on('change', '.preview-img-input', previewImgFromInput);
            
            initSelect2();
            initDateTimePickerByClassName();
            
            $('.toast-message').each(function(i, msg){
                TOAST.fire({
                    type: $(msg).data('type'),
                    title: $(msg).text()
                });
            });
        });
    }
    
    function ajaxErrorToast(resp)
    {
        var errorMessage = 'Unknown Error.';
        if (resp.responseJSON['errors']) {
            errorMessage = Object.keys(resp.responseJSON['errors']).map(function(i){ return (typeof(resp.responseJSON['errors'][i]) === 'string' ? resp.responseJSON['errors'][i] : resp.responseJSON['errors'][i][0]); }).join("\n");
        }
        else if (resp.responseJSON['message']) {
            errorMessage = resp.responseJSON['message'];
        }

        TOAST.fire({
            type: 'error',
            title: errorMessage
        });
    }
    
    function previewImgFromInput() 
    {
        var filesizeLimit = ($(this).data('limit') || UPLOAD_SIZE_LIMIT) * 1024 * 1024;
        var filesizeExceedMessage = '檔案大小超過限制(' + ($(this).data('limit') || UPLOAD_SIZE_LIMIT) + 'MB)';
        var $formGroup = $(this).parents('.form-group');
        var $label = $('.custom-file-label', $formGroup);
        var $img = $('.preview-img', $formGroup);
        $img.addClass('d-none');
        $label.text($label.data('default'));
        if (this.files.length == 0) {
            return;
        }

        // check filesize
        if (this.files[0].size > filesizeLimit) {
            this.setCustomValidity(filesizeExceedMessage);
        }
        else {
            this.setCustomValidity('');
            $label.text(this.files[0].name);
        }

        // preview
        var reader = new FileReader();
        reader.onload = function (e) {
            $img.prop('src', e.target.result);
            $img.removeClass('d-none');
        }
        reader.readAsDataURL(this.files[0]);
    }
    
    function initSelect2()
    {
        $('.form-control-select2').each(function(i, dom){
            $(dom).val($(dom).data('val')).select2();
        });
    }
    
    function initDateTimePickerByClassName()
    {
        if (typeof($.fn.daterangepicker) === 'undefined')
            return;
        
        $('.form-control-datetimepicker, .form-control-datepicker').each(function(i, dom){
            initDateTimePicker(dom);
        });
    }
    
    function initDateTimePicker(dom)
    {
        var hasTimePicker = $(dom).hasClass('form-control-datetimepicker');
        var config = {
            timePicker: hasTimePicker,
            singleDatePicker: true,
            autoUpdateInput: false,
            locale: {
                format: hasTimePicker ? 'YYYY-MM-DD HH:mm:ss' : 'YYYY-MM-DD',
                cancelLabel: '清除',
                applyLabel: '套用'
            }
        };
        
        if ($(dom).data('format') === 'date') {
            config.timePicker = false;
            config.locale.format = 'YYYY-MM-DD';
        }
        $(dom).daterangepicker(config);
        
        $(dom).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format(picker.locale.format));
        }).on('cancel.daterangepicker', function() {
            $(this).val('');
        });
    }
    
    function initDataTable(targetTable)
    {
        var config = {
            language: { url: '/plugin/datatables/dataTables.zh.json' },
            stateSave: true,
            order: [[0, 'asc']],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false
            }, {
                targets: 'no-search',
                searchable: false
            }, {
                targets: 'lnk',
                className: 'text-center text-break',
                render: function (data, type, row) {
                    if (data) {
                        var url = $(targetTable).data('url');
                        if (/0$/.test(url)) {
                            url = url.replace(/0$/, row.id);
                        }
                        else {
                            url = url.replace(/\/0\//, '/' + row.id + '/');
                        }
                        return $('<a>').text(data).prop('href', url)[0].outerHTML;
                    }
                    return data;
                }
            }, {
                targets: '_all',
                className: 'text-center text-break',
                render: function (data) {
                    if (data && typeof(data) === 'string') {
                        return data.replace(/\n/g, '<br>');
                    }
                    return data;
                }
            }],
            initComplete: function(){
                var searchableText = $(targetTable + ' thead th:not(.no-search)')
                        .filter(function(i, th){ return $(th).data('searchable')!==false; })
                        .map(function(i, th){ return $(th).text(); })
                        .toArray()
                        .join('、');
                $(targetTable + '_filter .form-control').prop('placeholder', searchableText);
            }
        };
        
        if ($(targetTable).data('ajax') !== false) {
            config['serverSide'] = true;
            config['ajax'] = $(targetTable).data('ajax');
        }
        
        return $(targetTable).DataTable(config);
    }
    
    return {
        init: init,
        ajaxErrorToast: ajaxErrorToast,
        toast: TOAST,
        initDataTable: initDataTable,
        initDateTimePicker: initDateTimePicker
    };
}