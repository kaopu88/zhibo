<?php
return [
    'module_init' => [
        'app\\common\\behavior\\ApiInit',
    ],
    'app_end' => [
        'app\\common\\behavior\\ApiEnd',
    ],
    'response_send'=>[
        'app\\common\\behavior\\ResponseSend',
    ]
];