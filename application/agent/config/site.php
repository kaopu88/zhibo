<?php
return [
    'page_tpl' => [
        'page_title' => '{$agent_name}-{$company_name}',
        'page_description' => '{$desc}',
        'page_keywords' => '{$keywords}'
    ],
    'page_info' => [
    ],
    'company_name' => config('app.agent_setting.agent_name'),
    'company_full_name' => config('app.product_setting.prefix_name').config('app.agent_setting.agent_name').'管理平台',
];
