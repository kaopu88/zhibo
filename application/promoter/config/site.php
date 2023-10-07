<?php
return [
    'page_tpl' => [
        'page_title' => '{$promoter_name}-{$company_name}',
        'page_description' => '{$desc}',
        'page_keywords' => '{$keywords}'
    ],
    'page_info' => [
    ],
    'company_name' => config('app.agent_setting.agent_name'),
    'promoter_company_full_name' => APP_PREFIX_NAME.config('app.agent_setting.promoter_name').'管理平台',
];

