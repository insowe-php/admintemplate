<?php

namespace Insowe\AdminTemplate\Traits;

use DB;
use Illuminate\Http\Request;

trait Select2Model
{
    public function getAjaxResult(Request $request, $pageSize = 20)
    {
        $query = DB::table($this->getTable())
                ->select($this->ajaxResultFieldName)
                ->distinct();
        
        // 資料總數
        $totalCount = $query->count();
        
        // 搜尋
        $term = $request->term;
        if (!empty($term)) {
            $query->where($this->ajaxResultFieldName, 'like', "%{$term}%");
        }
        
        // 搜尋結果
        $page = $request->input('page', 1);
        $results = $query->skip($page * $pageSize - $pageSize)
                      ->take($pageSize)
                      ->get()
                      ->pluck($this->ajaxResultFieldName)
                      ->map(function($i){
                          return ['id' => $i, 'text' => $i];
                      })->toArray();
        
        return [
            'total_count' => $totalCount,
            'incomplete_results' => $page * $pageSize < $totalCount, // 是否還有未載完的內容
            'results' => $results,
        ];
    }
}