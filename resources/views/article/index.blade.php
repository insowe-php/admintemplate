@inject('adminTemplate', 'AdminTemplate')
@extends('layouts.admin', ['pageTitle' => $indexPageTitle])
@section('breadcrumb')
<ol class="breadcrumb float-sm-right">
    @foreach ($breadcrumbs as $i)
    <li class="breadcrumb-item">{{ $i }}</li>
    @endforeach
    <li class="breadcrumb-item active">{{ $indexPageTitle }}</li>
</ol>
@endsection

@section('content')
<div class="row">
    <div class="col-sm-4 text-right order-sm-last">
        <a class="btn btn-outline-primary mb-1" href="{{ route($formRoute, ['new']) }}">新增</a>
    </div>
    <div class="col-sm-8"></div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <table id="dataTable" class="table table-bordered table-hover" 
                       data-ajax="false"
                       data-url="{{ route($indexRoute, [0]) }}">
                    <thead>
                        <tr>
                            <th class="text-center" data-data="title">標題</th>
                            <th class="text-center" data-searchable="false" data-data="publish_at">發布時間</th>
                            <th class="text-center" data-searchable="false" data-data="online_at">上架時間</th>
                            <th class="text-center" data-searchable="false" data-data="offline_at">下架時間</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list as $i)
                        <tr>
                            <td>
                                <a href="{{ route($formRoute, [$i->id])}}">{{ $i->title }}</a>
                            </td>
                            <td>{{ is_null($i->publish_at) ? '' : $i->publish_at->format('Y-m-d') }}</td>
                            <td>{{ $i->online_at }}</td>
                            <td>{{ $i->offline_at }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(function(){
    Admin.initDataTable('#dataTable');
});
</script>
@endsection