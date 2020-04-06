<?php

namespace Insowe\AdminTemplate\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Insowe\AdminTemplate\Models\Article;

trait ArticleTriat
{
    /**
     * 取得目前分類Id
     * @return type
     * @throws Exception
     */
    protected function getCategoryId()
    {
        if (property_exists($this, 'categoryId')) {
            return $this->categoryId;
        }
        throw new Exception('Please define property: $categoryId.');
    }
    
    /**
     * 取得每頁的資料筆數，若不設定則預設5筆
     * @return int
     */
    protected function getPageRows()
    {
        if (property_exists($this, 'pageRows')) {
            return $this->pageRows;
        }
        return 5; // default
    }

    /**
     * 取得 view 的資料夾名稱
     */
    protected function getViewPath()
    {
        if (property_exists($this, 'viewPath')) {
            return $this->viewPath;
        }
        throw new Exception('Please define property: $viewPath.');
    }
    
    /**
     * 列表的 route name
     */
    protected function getListPath()
    {
        if (property_exists($this, 'listPath')) {
            return $this->listPath;
        }
        else if (property_exists($this, 'listRoute')) {
            return route($this->listRoute);
        }
        throw new Exception('Please define property: $listPath or $listRoute.');
    }

    /**
     * 文章列表
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $list = Article::where('category_id', $this->getCategoryId())
                ->where('online_at', '<=', now())
                ->where(function($q){
                    $q->whereNull('offline_at')
                      ->orWhere('offline_at', '>', now());
                })
                ->orderBy('display_order', 'desc')
                ->orderBy('publish_at', 'desc')
                ->paginate($this->getPageRows());
        
        return view("{$this->getViewPath()}.index", [
            'list' => $list,
        ]);
    }
    
    /**
     * 文章內容
     * @param Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function view(Request $request, $id)
    {
        $item = Article::where([
                    'id' => $id,
                    'category_id' => $this->getCategoryId(),
                ])->where('online_at', '<=', now())
                ->where(function($q){
                    $q->whereNull('offline_at')
                      ->orWhere('offline_at', '>', now());
                })->first();
        
        if (is_null($item))
        {
            //abort(404);
            return redirect($this->getListPath());
        }
        
        return view("{$this->getViewPath()}.view", [
            'item' => $item,
        ]);
    }
}