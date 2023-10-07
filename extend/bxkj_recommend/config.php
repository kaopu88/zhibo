<?php
$timeIntervals = [
    [86400, -3000],//1天
    [172800, -12000],//2天
    [345600, -35000],//4天
    [604800, -25000],//7天
    [1209600, -15000],//14天
    [31536000, -10000],//365天
];

$likeRatioThr = [
    [10, 0.5],
    [20, 0.3],
    [30, 0.2]
];
$likeRatioCoe = [
    '0,500' => 0.9,
    '0,1000' => 0.7,
    '0,1500' => 0.6,
    '0,3000' => 0.4,
    '0,5000' => 0.3,
    '0,10000' => 0.2,
    '0,10000000' => 0.1
];
$playedOutCoe = [
    '0,500' => 0.9,
    '0,1000' => 0.7,
    '0,1500' => 0.6,
    '0,3000' => 0.5,
    '0,5000' => 0.3,
    '0,10000' => 0.2,
    '0,10000000' => 0.1
];
$switchCoe = [
    '0,500' => 0.9,
    '0,1000' => 0.7,
    '0,1500' => 0.6,
    '0,3000' => 0.5,
    '0,5000' => 0.3,
    '0,10000' => 0.2,
    '0,10000000' => 0.1
];
$commentCoe = [
    '0,500' => 0.5,
    '0,1000' => 0.4,
    '0,1500' => 0.3,
    '0,3000' => 0.2,
    '0,5000' => 0.15,
    '0,10000' => 0.1,
    '0,10000000' => 0.05
];
$watchRatioCoe = [
    '0,7500' => 2.5,
    '0,15000' => 1,
    '0,22500' => 0.8,
    '0,45000' => 0.6,
    '0,75000' => 0.5,
    '0,150000' => 0.3,
    '0,150000000' => 0.1
];
$shareRatioCoe = [
    '0,500' => 0.4,
    '0,1000' => 0.3,
    '0,1500' => 0.2,
    '0,3000' => 0.15,
    '0,5000' => 0.1,
    '0,10000' => 0.05,
    '0,10000000' => 0.05
];
$downloadRatioCoe = [
    '0,500' => 0.35,
    '0,1000' => 0.25,
    '0,1500' => 0.15,
    '0,3000' => 0.1,
    '0,5000' => 0.08,
    '0,10000' => 0.04,
    '0,10000000' => 0.04
];

$shareRatioThr = [
    [5, 0.5],
    [10, 0.3],
    [10, 0.2]
];

$downloadRatioThr = [
    [5, 0.5],
    [10, 0.3],
    [10, 0.2]
];

$watchRatioThr = [
    [15 * 30, 0.5],
    [15 * 50, 0.3],
    [15 * 20, 0.2]
];

$playedOutThr = [
    [5, 0.5],
    [10, 0.3],
    [10, 0.2]
];

$switchThr = [
    [10, 0.5],
    [15, 0.3],
    [20, 0.2]
];

$commentThr = [
    [5, 0.5],
    [12, 0.3],
    [13, 0.2]
];


$config = [
    //[更新器]
    'vupdater_frequency' => [
        ['range' => '0,14400', 'type' => 'newest', 'process' => 1, 'usleep' => 5000, 'interval' => 15 * 60],//四小时内15分钟更新一次
        ['range' => '0,86400', 'type' => 'today', 'process' => 2, 'usleep' => 15000, 'interval' => 60 * 60],//一天内1小时更新一次
        ['range' => '0,259200', 'type' => 'recent', 'process' => 3, 'usleep' => 30000, 'interval' => 2 * 60 * 60],//三天内2小时更新一次
        ['range' => '0,604800', 'type' => 'week', 'process' => 4, 'usleep' => 60000, 'interval' => 4 * 60 * 60],//一周内4小时更新一次
        ['range' => '0,2592000', 'type' => 'month', 'process' => 5, 'usleep' => 100000, 'interval' => 24 * 60 * 60],//一个月内一天更新一次
    ],
    //[索引]
    'index_expire' => 2 * 3600,//索引有效期 s
    'instock_early' => 200,//索引预警值
    'index_short_expire' => 600,//索引短时记忆有效期
    'index_fetch_retrymax' => 50,
    'index_fetch_offsetttl' => 86400,
    'index_fetch_offsetmax' => 3000,
    'index_full_length' => 260,//索引满格量
    //索引生成占比
    'index_compositions' => [
        ['name' => 'friends', 'pro' => 0.1], //关注的好友
        ['name' => 'interest', 'pro' => 0.7], //兴趣
        ['name' => 'quality', 'pro' => 0.2] //扩展兴趣
    ],
    'index_slowest' => 3600,//至少需要在3600s内生成完毕
    'index_delayed_release' => 10,
    'index_per_sta' => true,//索引性能统计
    'index_while_max' => 500,
    'index_pgd_period' => 7 * 86400,//宣发最大有效期
    'index_remove_sscancount' => 50,//移除单次扫描数量
    'index_remove_maxsscan' => 2000,//最大移除数量
    'index_fnewv_period' => 7 * 86400,//好友视频有效期
    'index_sort_totalscore' => 100000,
    'index_sort_starttime' => 1560268800,//时间起点2019-06-12 00:00:00
    'index_sort_interval' => 86400 * 365,

    //[观看记录]
    'viewed_max_length' => 12000,//观看记录最大长度
    'viewed_period' => 30 * 86400,//观看记录最大保存时间

    'helper_id' => 10000,//小助手号

    //[回收]
    'recycling_period' => 30 * 86400,//超出30天的则回收
    'recycling_max_quantity' => 30000,//视频数量

    //[池子]
    'pool_max_quantity' => 10000,
    'pool_thr' => 5000,

    //[视频]
    'video_full_score' => 100000,
    'video_score_proportion' => [
        //一天内
        '0,86400' => [
            'time' => [
                'pro' => 0.28,
                'full' => 100000,
                'intervals' => $timeIntervals
            ],
            'played_out_ratio' => [
                'pro' => 0.15,
                'full' => 100000,
                'thr' => $playedOutThr,
                'coe' => $playedOutCoe
            ],
            'comment_ratio' => [
                'pro' => 0.14,
                'full' => 100000,
                'thr' => $commentThr,
                'coe' => $commentCoe,
            ],
            'like_ratio' => [
                'pro' => 0.132,
                'full' => 100000,
                'thr' => $likeRatioThr,
                'coe' => $likeRatioCoe
            ],
            'user_weight' => [
                'pro' => 0.09,
                'full' => 100000
            ],
            'tag_weight' => [
                'pro' => 0.08,
                'full' => 100000
            ],
            'watch_ratio' => [
                'pro' => 0.06,
                'full' => 100000,
                'thr' => $watchRatioThr,
                'coe' => $watchRatioCoe
            ],
            'rating' => [
                'pro' => 0.025,
                'max' => 100,
                'e' => 1000
            ],
            'share_ratio' => [
                'pro' => 0.02,
                'full' => 100000,
                'thr' => $shareRatioThr,
                'coe' => $shareRatioCoe
            ],
            'download_ratio' => [
                'pro' => 0.018,
                'full' => 100000,
                'thr' => $downloadRatioThr,
                'coe' => $downloadRatioCoe
            ],
            'duration' => [
                'pro' => 0.005,
                'full' => 100000,
                'max' => 15,
            ],
            'switch_ratio' => [
                'pro' => -0.12,
                'full' => 100000,
                'thr' => $switchThr,
                'coe' => $switchCoe
            ]
        ],
        //三天内
        '0,259200' => [
            'time' => [
                'pro' => 0.2,
                'full' => 100000,
                'intervals' => $timeIntervals
            ],
            'comment_ratio' => [
                'pro' => 0.175,
                'full' => 100000,
                'thr' => $commentThr,
                'coe' => $commentCoe,
            ],
            'like_ratio' => [
                'pro' => 0.175,
                'full' => 100000,
                'thr' => $likeRatioThr,
                'coe' => $likeRatioCoe
            ],
            'played_out_ratio' => [
                'pro' => 0.155,
                'full' => 100000,
                'thr' => $playedOutThr,
                'coe' => $playedOutCoe
            ],
            'user_weight' => [
                'pro' => 0.09,
                'full' => 100000
            ],
            'tag_weight' => [
                'pro' => 0.08,
                'full' => 100000
            ],
            'watch_ratio' => [
                'pro' => 0.04,
                'full' => 100000,
                'thr' => $watchRatioThr,
                'coe' => $watchRatioCoe
            ],
            'share_ratio' => [
                'pro' => 0.035,
                'full' => 100000,
                'thr' => $shareRatioThr,
                'coe' => $shareRatioCoe
            ],
            'download_ratio' => [
                'pro' => 0.025,
                'full' => 100000,
                'thr' => $downloadRatioThr,
                'coe' => $downloadRatioCoe
            ],
            'rating' => [
                'pro' => 0.02,
                'e' => 1000,
                'max' => 100
            ],
            'duration' => [
                'pro' => 0.005,
                'full' => 100000,
                'max' => 15,
            ],
            'switch_ratio' => [
                'pro' => -0.14,
                'full' => 100000,
                'thr' => $switchThr,
                'coe' => $switchCoe
            ]
        ],
        //一周内
        '0,604800' => [
            'like_ratio' => [
                'pro' => 0.19,
                'full' => 100000,
                'thr' => $likeRatioThr,
                'coe' => $likeRatioCoe
            ],
            'comment_ratio' => [
                'pro' => 0.185,
                'full' => 100000,
                'thr' => $commentThr,
                'coe' => $commentCoe,
            ],
            'played_out_ratio' => [
                'pro' => 0.18,
                'full' => 100000,
                'thr' => $playedOutThr,
                'coe' => $playedOutCoe
            ],
            'time' => [
                'pro' => 0.15,
                'full' => 100000,
                'intervals' => $timeIntervals
            ],
            'user_weight' => [
                'pro' => 0.08,
                'full' => 100000
            ],
            'share_ratio' => [
                'pro' => 0.065,
                'full' => 100000,
                'thr' => $shareRatioThr,
                'coe' => $shareRatioCoe
            ],
            'tag_weight' => [
                'pro' => 0.06,
                'full' => 100000
            ],
            'watch_ratio' => [
                'pro' => 0.034,
                'full' => 100000,
                'thr' => $watchRatioThr,
                'coe' => $watchRatioCoe
            ],
            'download_ratio' => [
                'pro' => 0.03,
                'full' => 100000,
                'thr' => $downloadRatioThr,
                'coe' => $downloadRatioCoe
            ],
            'rating' => [
                'pro' => 0.02,
                'full' => 100000,
                'max' => 100,
                'e' => 1000
            ],
            'duration' => [
                'pro' => 0.006,
                'full' => 100000,
                'max' => 15,
            ],
            'switch_ratio' => [
                'pro' => -0.145,
                'full' => 100000,
                'thr' => $switchThr,
                'coe' => $switchCoe
            ]
        ],
        //两个月内
        '0,5184000' => [
            'like_ratio' => [
                'pro' => 0.2,
                'full' => 100000,
                'thr' => $likeRatioThr,
                'coe' => $likeRatioCoe
            ],
            'played_out_ratio' => [
                'pro' => 0.19,
                'full' => 100000,
                'thr' => $playedOutThr,
                'coe' => $playedOutCoe
            ],
            'comment_ratio' => [
                'pro' => 0.18,
                'full' => 100000,
                'thr' => $commentThr,
                'coe' => $commentCoe,
            ],
            'share_ratio' => [
                'pro' => 0.11,
                'full' => 100000,
                'thr' => $shareRatioThr,
                'coe' => $shareRatioCoe
            ],
            'time' => [
                'pro' => 0.1,
                'full' => 100000,
                'intervals' => $timeIntervals
            ],
            'user_weight' => [
                'pro' => 0.08,
                'full' => 100000
            ],
            'download_ratio' => [
                'pro' => 0.06,
                'full' => 100000,
                'thr' => $downloadRatioThr,
                'coe' => $downloadRatioCoe
            ],
            'watch_ratio' => [
                'pro' => 0.03,
                'full' => 100000,
                'thr' => $watchRatioThr,
                'coe' => $watchRatioCoe
            ],
            'tag_weight' => [
                'pro' => 0.025,
                'full' => 100000,
            ],
            'rating' => [
                'pro' => 0.02,
                'full' => 100000,
                'max' => 100,
                'e' => 1000
            ],
            'duration' => [
                'pro' => 0.005,
                'full' => 100000,
                'max' => 15,
            ],
            'switch_ratio' => [
                'pro' => -0.14,
                'full' => 100000,
                'thr' => $switchThr,
                'coe' => $switchCoe
            ]
        ]
    ],
];

return $config;