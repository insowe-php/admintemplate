<?php

namespace Insowe\AdminTemplate\Traits;

use Exception;
use Illuminate\Http\Request;
use PHPHtmlParser\Dom;
use Ramsey\Uuid\Uuid;
use Storage;

trait File
{    
    private $disk;
    
    private $diskName = null;

    protected function getDisk()
    {
        if (is_null($this->disk)) {
            $this->disk = Storage::disk($this->diskName);
        }
        return $this->disk;
    }
    
    /**
     * 上傳檔案
     * @param Request $request Http Request
     * @param string $fieldName Http Request 欄位名稱
     * @param string $folderName 上傳資料夾名稱
     * @param array $thumbnailSettings 縮圖設定
     * @param string $visibility 可見性(public, private)
     * @return array
     * @throws Exception
     */
    public function uploadFile(Request $request, $fieldName, $folderName, array $thumbnailSettings = [], $visibility = 'public')
    {
        if (!$request->hasFile($fieldName)) {
            throw new Exception('Uploaded file is null.');
        }
        
        // 支援單檔與陣列，統一用陣列去跑
        $files = $request->file($fieldName);
        if (!is_array($files))
        {
            $files = [ $files ];
        }
        
        // 支援多組縮圖設定，統一用陣列去跑
        if (is_array($thumbnailSettings) && count($thumbnailSettings) > 0)
        {
            if (key_exists('folder', $thumbnailSettings)) {
                // 只有一組縮圖設定
                $thumbnailSettings = [$thumbnailSettings];
            }
        }
        else
        {
            $thumbnailSettings = [];
        }
        
        $returnData = [];
        foreach ($files as $file)
        {
            if (!$file->isValid()) {
                throw new Exception('upload file [' . $file->getClientOriginalName() . '] is invalid. ' . $file->getErrorMessage());
            }
            
            if (preg_match('/\.[\w]+$/', $folderName)) 
            {
                $filename = pathinfo($folderName, PATHINFO_BASENAME);
                $folderName = pathinfo($folderName, PATHINFO_DIRNAME);
            }
            else 
            {
                $filename = Uuid::uuid4() . '.' . strtolower($file->getClientOriginalExtension());
            }
            
            $result['path'] = $this->getDisk()->putFileAs($folderName, $file, $filename, $visibility);
            $result['url'] = $this->getDisk()->url($result['path']);
            foreach ($thumbnailSettings as $setting)
            {
                $this->makeThumbnail($file, $setting['width'] ?? 0, $setting['height'] ?? 0, $setting['quality'] ?? 70);
                $thumbnail = $this->getDisk()->putFileAs($setting['folder'], $file, $filename, $visibility);
                $result['thumbnail'] = $this->getDisk()->url($thumbnail);
            }
            
            $returnData[] = $result;
        }
        
        return is_array($request->file($fieldName)) ? $returnData : $returnData[0];
    }

    /**
     * 產生縮圖
     * @param string|\Illuminate\Http\UploadedFile $file 檔案路徑或 Http Uploaded File 物件
     * @param int $thumbnailWidth 縮圖寬
     * @param int $thumbnailHeight 縮圖高
     * @param int $thumbnailQuality 縮圖品質(for jpg)
     * @throws Exception
     */
    public function makeThumbnail($file, $thumbnailWidth, $thumbnailHeight, $thumbnailQuality)
    {
        if (is_string($file))
        {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $path = $file;
        }
        else if (get_class($file) === 'Illuminate\Http\UploadedFile')
        {
            $ext = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();
        }
        else
        {
            if (config('app.debug')) {
                $inputClass = get_class($file);
                throw new Exception("Unsupported input class.({$inputClass})");
            }
            else {
                return;
            }
        }
        
        switch($ext) {
            case 'jpg':
                $image = imagecreatefromjpeg($path);
                break;
            case 'gif':
                $image = imagecreatefromgif($path);
                break;
            case 'png':
                $image = imagecreatefrompng($path);
                break;
            default:
                if (config('app.debug')) {
                    throw new Exception("Unsupport image format for compression.({$ext})");
                }
                else {
                    return;
                }
        }
        
        $originWidth = imagesx($image);
        $originHeight = imagesy($image);
        
        $ratio = max($thumbnailWidth / $originWidth, $thumbnailHeight / $originHeight);
        $destWidth = ceil($originWidth * $ratio);
        $destHeight = ceil($originHeight * $ratio);
        $destImage = imagecreatetruecolor($destWidth, $destHeight); // 缩略图
        
        if ($ext !== 'jpg')
        {
            // 透明背景
            imagesavealpha($destImage, true);
            $color = imagecolorallocatealpha($destImage, 0, 0, 0, 127);
            imagefill($destImage, 0, 0, $color);
        }
        
        imagecopyresampled($destImage, $image, 0, 0, 0, 0,
                $destWidth, $destHeight, $originWidth, $originHeight);
        
        switch($ext) {
            case 'jpg':
                imagejpeg($destImage, $path, $thumbnailQuality);
                break;
            case 'gif':
                imagegif($destImage, $path);
                break;
            case 'png':
                imagepng($destImage, $path);
                break;
        }
    }
    
    /**
     * 搬移檔案
     * @param array $files 搬移檔案路徑
     * @param string $fromDir 來源目錄(欲取代掉的路徑)
     * @param string $destDir 目標目錄
     * @return array
     */
    public function moveFiles(array $files, $fromDir, $destDir)
    {
        $destFiles = [];
        $thumbnails = [];
        $destThumbnails = [];
        try
        {
            foreach ($files as $key => $fromPath)
            {
                if (preg_match('/^https?:\/\//', $fromPath)) {
                    // It's third-party file url
                    continue;
                }
                
                $destPath = str_replace($fromDir, $destDir, $fromPath);
                $this->getDisk()->copy($fromPath, $destPath);
                $destFiles[$key] = $destPath;
                
                // 檢查是否有縮圖，有的話也要搬
                $thumbnailPath = pathinfo($fromPath, PATHINFO_DIRNAME)
                        . '/thumbnail/'
                        . pathinfo($fromPath, PATHINFO_BASENAME);
                
                if ($this->getDisk()->exists($thumbnailPath))
                {
                    $thumbnails[$key] = $thumbnailPath;
                    $destThumbnailPath = pathinfo($destPath, PATHINFO_DIRNAME)
                            . '/thumbnail/'
                            . pathinfo($destPath, PATHINFO_BASENAME);
                    
                    $this->getDisk()->copy($thumbnailPath, $destThumbnailPath);
                    $destThumbnails[$key] = $destThumbnailPath;
                }
            }
            
            // Delete Original Files
            $this->getDisk()->delete($files);
            $this->getDisk()->delete($thumbnails);
            return $destFiles;
        }
        catch (Exception $ex)
        {
            // rollback
            $this->getDisk()->delete($destFiles);
            $this->getDisk()->delete($destThumbnails);
            throw $ex;
        }
    }
    
    /**
     * 搬移 HTML 內容裡 <img> 的檔案並回傳更新後的 HTML
     * @param string $content HTML 內容
     * @param string $fromDir 來源目錄(欲取代掉的路徑)
     * @param string $destDir 目標目錄
     * @return string
     */
    public function moveContentImages($content, $fromDir, $destDir)
    {
        $dom = new Dom;
        $dom->load($content ?: '');
        $images = $dom->getElementsbyTag('img');
        $imgSrcs = [];
        $baseUrl = $this->getDisk()->url('');
        foreach ($images as $key => $img) {
            $imgSrcs[$key] = str_replace($baseUrl, '', $img->getAttribute('src'));
        }
        
        $destImgSrcs = $this->moveFiles($imgSrcs, $fromDir, $destDir);
        foreach ($images as $key => $img) {
            if (key_exists($key, $destImgSrcs)) {
                $img->setAttribute('src', $baseUrl . $destImgSrcs[$key]);
            }
        }
        
        // return replaced html
        return $dom->root->innerHtml();
    }
}