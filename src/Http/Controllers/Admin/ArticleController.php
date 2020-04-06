<?php

namespace Insowe\AdminTemplate\Http\Controllers\Admin;

use DB;
use Illuminate\Http\Request;
use Insowe\AdminTemplate\Http\Controllers\Controller;
use Insowe\AdminTemplate\Models\Article;
use Insowe\AdminTemplate\Traits\File;
use Log;

abstract class ArticleController extends Controller
{
    use File;
    
    /**
     * 取得分類編號
     */
    abstract function getCategoryId();

    /**
     * 取得 view 的資料夾名稱
     */
    abstract function getViewPath();
    
    /**
     * 列表的 route name
     */
    abstract function getIndexRoute();

    /**
     * 表單的 route name
     */
    abstract function getFormRoute();
    
    /**
     * 列表之前的麵包屑
     */
    abstract function getBreadcrumbs();
    
    /**
     * 列表的頁面名稱
     */
    abstract function getIndexPageTitle();
    
    /**
     * 表單的頁面名稱
     */
    abstract function getFormPageTitle();

    /**
     * 取得在 Storage 上的資料夾
     */
    abstract function getStoragePath();

    public function index(Request $request)
    {
        $query = Article::where('category_id', $this->getCategoryId());
        if ($request->expectsJson())
        {
            return $this->getDataTableResponse($request, $query, null, true);
        }
        else
        {
            return view("{$this->getViewPath()}.index", array_merge($this->getViewParameters(), [
                'list' => $query->get(),
            ]));
        }
    }
    
    public function form(Request $request)
    {
        $id = intval($request->id);
        if ($id === 0)
        {
            $item = new Article;
            $item->id = 0;
            $item->category_id = $this->getCategoryId();
            $item->pictures = [];
            $item->online_at = date('Y-m-d 00:00:00');
            $item->publish_at = date('Y-m-d');
        }
        else
        {
            $item = Article::find($id);
            if (is_null($item))
            {
                return redirect(route($this->getIndexRoute()))
                        ->with('errorMessage', "文章 ID [{$id}] 不存在");
            }
            $item->pictures = $item->pictures ?: [];
        }
        
        return view("{$this->getViewPath()}.form", array_merge($this->getViewParameters(), [
            'item' => $item,
            'isCreating' => $item->id === 0,
        ]));
    }

    public function createOrUpdate(Request $request)
    {
        $this->validate($request, $this->getValidateRules());
        DB::beginTransaction();
        try
        {
            if (intval($request->id) === 0) {
                $item = $this->create($request);
            }
            else {
                $item = $this->update($request);
            }
            DB::commit();
        }
        catch (Exception $ex)
        {
            DB::rollback();
            Log::error($ex);
            return redirect(url()->previous())
                ->withInput($request->except([]))
                ->with('errorMessage', substr($ex->getMessage(), 0, strpos($ex->getMessage(), '(')));
        }
        
        if ($request->is_valid) {
            return redirect(route($this->getIndexRoute()))->with('successMessage', '儲存成功');
        } else {
            return redirect(route($this->getIndexRoute()))
                ->with('successMessage', '刪除成功');
        }
    }

    protected function create(Request $request)
    {
        $item = Article::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'pictures' => $this->getPictures($request),
            'online_at' => $request->input('online_at'),
            'offline_at' => $request->input('offline_at'),
            'publish_at' => $request->input('publish_at', now()),
            'category_id' => $this->getCategoryId(),
            'display_order' => 0,
        ]);
        
        // 將上傳的檔案從暫存搬到指定目錄
        $item->content = $this->moveContentImages($item->content, "{$this->getStoragePath()}/0", "{$this->getStoragePath()}/{$item->id}");
        $item->pictures = $this->moveFiles($item->pictures, "{$this->getStoragePath()}/0", "{$this->getStoragePath()}/{$item->id}");
        $item->save();
        return $item;
    }
    
    protected function update(Request $request)
    {
        $item = Article::find($request->id);
        if (empty($item)) {
            abort(404);
        }
        
        if ($request->is_valid)
        {
            $item->title = $request->input('title');
            $item->content = $request->input('content', '');
            $item->pictures = $this->getPictures($request);
            $item->online_at = $request->input('online_at');
            $item->offline_at = $request->input('offline_at');
            $item->publish_at = $request->input('publish_at', now());
            $item->save();
        }
        else
        {
            $item->delete();
        }
        return $item;
    }
    
    protected function getPictures(Request $request)
    {
        return array_filter($request->input('pictures', []), function($i){ return !empty($i); });
    }

    protected function getValidateRules()
    {
        return [
            'id' => 'required',
            'title' => 'required|max:255',
            'publish_at' => 'required',
        ];
    }
    
    protected function getViewParameters()
    {
        return [
            'indexRoute' => $this->getIndexRoute(),
            'formRoute' => $this->getFormRoute(),
            'breadcrumbs' => $this->getBreadcrumbs(),
            'indexPageTitle' => $this->getIndexPageTitle(),
            'formPageTitle' => $this->getFormPageTitle(),
            'storagePath' => $this->getStoragePath(),
        ];
    }
}