<?php
return [
    'page_tpl' => [
        'page_title' => '{$admin_name}-{$company_name}',
        'page_description' => '{$desc}',
        'page_keywords' => '{$keywords}'
    ],
    'page_info' => [
    ],
    'company_name' => config('app.product_setting.prefix_name').'ERP',
    'company_full_name' => config('app.product_setting.prefix_name').'ERP管理系统',
];

