<?php

namespace Insowe\AdminTemplate;

use Auth;
use Route;
use Storage;

class AdminTemplate
{
    public function getSidebarMenu()
    {
        $sidebarMenu = config('admintemplate.sidebar');
        $role = Auth::guard()->user()->role;
        $currentNavName = with(Route::current())->action['navName'] ?? '';
        foreach ($sidebarMenu as $lv1 => $i)
        {
            // 檢查是否有權限設定，使用者是否符合角色，否則不要顯示連結
            if (key_exists('roles', $i) && is_array($i['roles'])
                    && !in_array($role, $i['roles']))
            {
                unset($sidebarMenu[$lv1]);
                continue;
            }
            
            // 標示 active 並取得實際的網址
            if (key_exists('children', $i) && is_array($i['children'])) 
            {
                foreach ($i['children'] as $lv2 => $j)
                {
                    if (!key_exists('url', $j)) {
                        $sidebarMenu[$lv1]['children'][$lv2]['url'] = key_exists('route', $j) ? route($j['route']) : '#';
                    }
                    
                    if (key_exists('is_active', $sidebarMenu[$lv1]) && $sidebarMenu[$lv1]['is_active']) {
                        $sidebarMenu[$lv1]['children'][$lv2]['is_active'] = false;
                        continue;
                    }
                    
                    $sidebarMenu[$lv1]['children'][$lv2]['is_active'] 
                        = $sidebarMenu[$lv1]['is_active']
                        = (key_exists('navName', $j) && $j['navName'] === $currentNavName);
                }
            }
            else
            {
                $sidebarMenu[$lv1]['is_active'] = (key_exists('navName', $i) && $i['navName'] === $currentNavName);
            }
            
            // 將 route name 換成實際的網址
            if (!key_exists('url', $i)) {
                $sidebarMenu[$lv1]['url'] = key_exists('route', $i) ? route($i['route']) : '#';
            }
        }
        return $sidebarMenu;
    }
    
    /**
     * 取得 Cloud 公開網址
     * @param string $path
     * @param bool $isThumbnail (defailt: false)
     * @return string
     */
    public function getCloudUrl($path, $isThumbnail = false)
    {
        if (preg_match('/^https?:\/\//', $path)) {
            // It's complete URL
            return $path;
        }
        
        $url = Storage::cloud()->url($path);
        if ($isThumbnail)
        {
            return pathinfo($url, PATHINFO_DIRNAME)
                    . '/thumbnail/'
                    . pathinfo($url, PATHINFO_BASENAME);
        }
        return $url;
    }
    
    /**
     * 清除字串裡的空格
     * https://www.itdaan.com/tw/cd4c21fa7e70ae6b4f300ab86e7d5d59
     * @param string $string
     * @return string
     */
    public function cleanUpSpace($string)
    {
        return preg_replace('/[\s\xC2\xA0]+/', '', $string);
    }
}