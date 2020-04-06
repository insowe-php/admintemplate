<?php

namespace Insowe\AdminTemplate\Traits;

use Illuminate\Http\Request;

trait DataTable
{
    protected function getDataTableResponse(Request $request, $query, $callback = null, $isSearchByOrWhere = false)
    {
        $columns = collect($request->input('columns'));
        $total = $filtered = $query->count();
        
        $search = $request->input('search', ['value' => null])['value'];
        if (!empty($search))
        {
            $searchColumns = $columns->where('searchable', 'true')->pluck('data')->filter(function($i){
                return strpos($i, '.') === false;
            })->values()->toArray();
            $searchValue = "%{$search}%";
            if (count($searchColumns) > 0)
            {
                $whereStr = $isSearchByOrWhere ? 'orWhere' : 'where';
                $query->$whereStr(function($q) use ($searchColumns, $searchValue, $whereStr) 
                {
                    $q->where($searchColumns[0], 'like', $searchValue);
                    for ($i = 1; $i < count($searchColumns); $i++) {
                        $q->orWhere($searchColumns[$i], 'like', $searchValue);
                    }
                });
                // 篩選之後的資料筆數
                $filtered = $query->count();
            }
        }
        
        $order = $request->input('order', [['column' => 0, 'dir' => 'asc']]);
        $data = $query->orderBy($columns[$order[0]['column']]['data'], $order[0]['dir'])
                ->skip($request->input('start', 0))
                ->take($request->input('length', 10))
                ->get();
        
        return response()->json([
            'draw' => $request->input('draw', 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => is_callable($callback) ? $data->map($callback) : $data,
        ]);
    }
}