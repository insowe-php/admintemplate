@inject('adminTemplate', 'AdminTemplate')
@extends('layouts.admin', ['pageTitle' => $formPageTitle])
@section('header')
<link rel="stylesheet" href="{{ asset('plugins/jquery-file-upload/css/jquery.fileupload.css') }}" />
<link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.css') }}" />
<link rel="stylesheet" href="{{ asset('css/admin/model-form.css') }}" />
<link rel="stylesheet" href="{{ asset('plugins/daterangepicker/daterangepicker.css') }}">
@endsection

@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    @foreach ($breadcrumbs as $i)
    <li class="breadcrumb-item">{{ $i }}</li>
    @endforeach
    <li class="breadcrumb-item"><a href="{{ route($indexRoute) }}" class="back-to-list">{{ $indexPageTitle }}</a></li>
    <li class="breadcrumb-item active">{{ $formPageTitle }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <form id="main-form" action="{{ route($formRoute, [$item->id]) }}" method="POST">
            <div class="overlay d-none"><i class="fas fa-3x fa-sync-alt fa-spin"></i></div>
            <div class="card">
                <div class="card-body">
                    <div class="form-group{{ $errors->has('title') ? ' is-invalid' : '' }}">
                        <label>標題</label>
                        <input type="text" name="title" value="{{ old('title', $item->title) }}" class="form-control" maxlength="255" autocomplete="off" required>
                        <small class="form-error-message">{{ $errors->has('title') ? $errors->first('title') : '' }}</small>
                    </div>
                    <div class="form-group{{ $errors->has('content') ? ' is-invalid' : '' }}">
                        <label>內文</label>
                        <textarea name="content" class="form-control-summernote" data-url="{{ route('admin.upload') }}">{!! old('content', $item->content) !!}</textarea>
                        <small class="form-error-message">{{ $errors->has('content') ? $errors->first('content') : '' }}</small>
                    </div>
                    <div class="form-group{{ $errors->has('pictures[]') ? ' is-invalid' : '' }}">
                        <a href="#" class="btn btn-primary fileinput-button">
                            文章相簿 (上傳)
                            <input id="fileupload" name="file" type="file" multiple accept="image/jpeg,image/png,image/gif" data-url="{{ route('admin.upload', ['image']) }}" />
                        </a>
                        <small class="text-muted ml-1">
                            上傳 <span id="fileSelectedCount">0</span> 張照片，<span id="fileUploadedCount">0</span> 成功 <span id="fileUploadErrorCount">0</span> 失敗
                        </small>
                        <small class="form-error-message">{{ $errors->has('pictures[]') ? $errors->first('usages[]') : '' }}</small>
                    </div>
                    <span id="fileUploadErrorFiles" style="color: #F00"></span>
                    <div id="sort-container">
                        @foreach (old('pictures', $item->pictures) as $p)
                        @if (!empty($p))
                        <div class="preview-block">
                            <img src="{{ $adminTemplate->getCloudUrl($p, true) }}">
                            <input type="hidden" name="pictures[]" value="{{ $p }}">
                            <a href="javascript:;" class="btn-remove text-secondary"><i class="fas fa-window-close"></i></a>
                        </div>
                        @endif
                        @endforeach
                        <div class="preview-block d-none in-queue">
                            <img src="">
                            <input type="hidden" name="pictures[]">
                            <a href="javascript:;" class="btn-remove text-secondary"><i class="fas fa-window-close"></i></a>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group{{ $errors->has('online_at') ? ' is-invalid' : '' }}">
                                <label>上架時間 <i class="fa fa-info-circle" title="文章在上架時間後顯示，留空則不顯示"></i></label>
                                <input type="text" name="online_at" value="{{ old('online_at', $item->online_at) }}" class="form-control form-control-datetimepicker" autocomplete="off">
                                <small class="form-error-message">{{ $errors->has('online_at') ? $errors->first('online_at') : '' }}</small>
                            </div>
                            <div class="form-group{{ $errors->has('offline_at') ? ' is-invalid' : '' }}">
                                <label>下架時間 <i class="fa fa-info-circle" title="文章在下架時間後隱藏，留空則永久顯示"></i></label>
                                <input type="text" name="offline_at" value="{{ old('offline_at', $item->offline_at) }}" class="form-control form-control-datetimepicker" autocomplete="off">
                                <small class="form-error-message">{{ $errors->has('offline_at') ? $errors->first('offline_at') : '' }}</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group{{ $errors->has('publish_at') ? ' is-invalid' : '' }}">
                                <label>發佈時間 <i class="fa fa-info-circle" title="顯示於文章內的發佈時間"></i></label>
                                <input type="text" name="publish_at" value="{{ old('publish_at', $item->publish_at) }}" class="form-control form-control-datepicker" autocomplete="off" required>
                                <small class="form-error-message">{{ $errors->has('publish_at') ? $errors->first('publish_at') : '' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-footer text-right">
                    <input type="hidden" name="id" value="{{ $item->id }}">
                    <input type="hidden" name="is_valid" value="1">
                    <button type="submit" class="btn btn-primary float-right">儲存</button>
                    <button type="button" class="btn btn-reset btn-outline-secondary mr-1">取消</button>
                    @unless ($isCreating)
                    <button type="button" class="btn btn-delete btn-outline-danger float-left">刪除</button>
                    @endunless
                </div>
            </div>
        </form>
    </div>
</div>

<form name="ajaxForm" id="ajax-form" class="d-none">
    <input type="hidden" name="folder" value="{{ "{$storagePath}/{$item->id}" }}">
    <input type="hidden" name="thumbnail_settings[0][folder]" value="{{ "{$storagePath}/{$item->id}/thumbnail" }}">
    <input type="hidden" name="thumbnail_settings[0][width]" value="215">
    <input type="hidden" name="thumbnail_settings[0][height]" value="215">
</form>
@endsection

@section('scripts')
<script src="{{ asset('plugins/jquery-file-upload/js/vendor/jquery.ui.widget.js') }}"></script>
<script src="{{ asset('plugins/jquery-file-upload/js/jquery.iframe-transport.js') }}"></script>
<script src="{{ asset('plugins/jquery-file-upload/js/jquery.fileupload.js') }}"></script>
<script src="{{ asset('plugins/sortable/Sortable.min.js') }}"></script>
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script src="{{ asset('plugins/summernote/lang/summernote-zh-TW.min.js') }}"></script>
<script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('plugins/daterangepicker/daterangepicker.js') }}"></script>
<script src="{{ asset('js/admin/album.js') }}"></script>
<script src="{{ asset('js/admin/summernote.js') }}"></script>
@endsection