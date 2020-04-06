<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu
    |--------------------------------------------------------------------------
    | Lv1 Link 可設定 roles 以便對不同角色的 user 顯示選單
    | Lv1, Lv2, 的網址設定 url(外部完整網址）, route(此應用的網址),不設定則自動帶"#"
    | navName 需要搭配 routes 設定使用，將 sidebar 選項標示為 active
    */
    
    'sidebar' => [
        [
            'name' => 'Lv1 Link',
            'icon' => 'fas fa-baby-carriage',
            'roles' => ['employee', 'manager'],
            'children' => [
                [
                    'route' => 'admin.models',
                    'name' => 'Lv2 Link',
                    'navName' => 'products',
                ],
            ],
        ],
    ],
    
];