<?php

use \bxkj_common\DataFactory as DataFactory;

/*** menu ***/
$bean_name = config('app.product_setting.bean_name')?:'货币';

$rules['common@menu'] = array(
    'deny' => 'create_time,update_time,level',
    'validate' => array(
        array('pid', 'regex', 'require', '请选择上级菜单项'),
        array('pid', '@validatePid', '', '上级菜单项不存在'),
        array('name', 'regex', 'require', '菜单项名称不能为空'),
        array('mark', 'compare', '!=root', '标识符不能为root', DataFactory::NOT_EMPTY),
        array('mark', '@validateMark', '', '同一根节点标识符不能重复', DataFactory::NOT_EMPTY),
    ),
    'fill' => array(
        array('level', '@fillLevel', '', DataFactory::ANY),
        array('shortcut', 'arr_implode', '', DataFactory::NOT_EMPTY)
    )
);

$rules['add@menu'] = array(
    'extends' => 'common@menu',
    'must' => 'pid,name',
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
);

$rules['update@menu'] = array(
    'extends' => 'common@menu',
    'ignore' => 'mark:keep',
    'must' => 'id',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY)
    )
);

/*** article ***/

$rules['common@article'] = array(
    'alias' => 'cat_id 所属分类,title 文章标题,id 文章ID',
    'validate' => array(
        array('cat_id', 'regex', 'require', '请选择所属类目'),
        array('cat_id', '@validateCategoryId', '', '请选择所属类目'),
        array('title', 'regex', 'require', '文章标题不能为空'),
        array('mark', 'regex', 'mark', '标识符不正确', DataFactory::NOT_EMPTY),
        array('mark', 'exc_unique', '', '标识符不能重复', DataFactory::NOT_EMPTY),
        array('pv', 'regex', 'number', '浏览量不正确', DataFactory::NOT_EMPTY),
        array('share_num', 'regex', 'number', '分享量不正确', DataFactory::NOT_EMPTY),
        array('release_time', 'regex', '/\d{4}-\d{2}-\d{2}\s{1}\d{2}\:\d{2}/', '发布时间不正确', DataFactory::NOT_EMPTY)
    ),
    'fill' => array(
        array('release_time', 'strtotime', '', DataFactory::NOT_EMPTY),
        array('cat_id', '@fillPCatId', '', DataFactory::NOT_EMPTY),
    ),
);

$rules['add@article'] = array(
    'extends' => 'common@article',
    'must' => 'cat_id,title',
    'fill' => array(
        array('summary', '@fillSummary', '', DataFactory::NOT_HAS),
        array('status', 'string', '1', DataFactory::NOT_HAS),
        array('pv', 'string', '0', DataFactory::NOT_HAS),
        array('share_num', 'string', '0', DataFactory::NOT_HAS),
        array('images', '@fillImages', '', DataFactory::NOT_HAS),
        array('sort', 'string', '0', DataFactory::NOT_HAS),
        array('create_time', ':time', '', DataFactory::ANY),
        array('release_time', 'field', 'create_time', DataFactory::NOT_HAS),
    )
);

$rules['update@article'] = array(
    'extends' => 'common@article',
    'must' => 'id',
    'ignore' => 'cat_id:keep',
    'deny' => 'aid',
    'fill' => array(
        array('update_time', ':time', '', DataFactory::ANY)
    )
);

/*** robot ***/

$rules['common@robot'] = array(
    'alias' => '',
    'validate' => array(
        array('nickname', 'regex', 'require', '昵称不能为空'),
        array('avatar', 'regex', 'require', '头像不能为空')
    ),
    'fill' => array(
        array('birthday', 'strtotime', '', DataFactory::NOT_EMPTY),
    )
);

$rules['add@robot'] = [
    'deny' => '',
    'extends' => 'common@robot',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@robot'] = [
    'extends' => 'common@robot',
    'must' => 'user_id',
    'validate' => [],
    'fill' => array()
];

/*** live_channel ***/

$rules['common@live_channel'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '名称不能为空'),
        array('icon', 'regex', 'require', '图标不能为空'),
        array('description', 'regex', 'require', '描述不能为空')
    ),
    'fill' => array()
);

$rules['add@live_channel'] = [
    'deny' => 'id',
    'extends' => 'common@live_channel',
    'must' => '',
    'validate' => [],
    'fill' => array()
];

$rules['update@live_channel'] = [
    'extends' => 'common@live_channel',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** recharge_bean ***/

$rules['common@recharge_bean'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '类别名称不能为空'),
        array('price', 'regex', 'require', '价格不能为空'),
        array('bean_num', 'regex', 'require', $bean_name.'不能为空'),
    ),
    'fill' => array()
);

$rules['add@recharge_bean'] = [
    'deny' => 'id',
    'extends' => 'common@recharge_bean',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@recharge_bean'] = [
    'extends' => 'common@recharge_bean',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** help ***/

$rules['common@help'] = array(
    'alias' => 'cat_id 所属分类,title 标题,id ID',
    'validate' => array(
        array('cat_id', 'regex', 'require', '请选择所属类目'),
        array('cat_id', '@validateCategoryId', '', '请选择所属类目'),
        array('mark', 'regex', 'mark', '标识符不正确', DataFactory::NOT_EMPTY),
        array('mark', 'exc_unique', '', '标识符不能重复', DataFactory::NOT_EMPTY),
        array('title', 'regex', 'require', '标题不能为空')
    ),
    'fill' => array(
        array('cat_id', '@fillPCatId', '', DataFactory::NOT_EMPTY),
    ),
);

$rules['add@help'] = [
    'deny' => 'id',
    'extends' => 'common@help',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@help'] = [
    'extends' => 'common@help',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** vip ***/

$rules['common@vip'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '名称不能为空'),
        array('rmb', 'regex', 'require', '单价不能为空'),
        array('length', 'regex', 'require', '有效时长不能为空'),
        array('price', 'regex', 'require', '等值'.$bean_name.'不能为空')
    ),
    'fill' => array()
);

$rules['add@vip'] = [
    'deny' => 'id',
    'extends' => 'common@vip',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@vip'] = [
    'extends' => 'common@vip',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** payments ***/

$rules['common@payments'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '名称不能为空'),
        array('class_name', 'regex', 'require', '类型不能为空'),
        array('alias', 'regex', 'require', '别名不能为空'),
    ),
    'fill' => array()
);

$rules['add@payments'] = [
    'deny' => 'id',
    'extends' => 'common@vip',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('addtime', 'time', '', DataFactory::ANY)
    )
];

$rules['update@payments'] = [
    'extends' => 'common@payments',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** props_bean ***/

$rules['common@props_bean'] = array(
    'alias' => '',
    'validate' => array(
        array('props_id', 'regex', 'require', '道具必须选择'),
        array('price', 'regex', 'require', '单价不能为空'),
        array('discount', 'regex', 'require', '折扣不能为空'),
        array('length', 'regex', 'require', '有效时长不能为空'),
        array('conv_millet', 'regex', 'require', '等值'.$bean_name.'不能为空')
    ),
    'fill' => array()
);

$rules['add@props_bean'] = [
    'deny' => 'id',
    'extends' => 'common@props_bean',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@props_bean'] = [
    'extends' => 'common@props_bean',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** props ***/

$rules['common@props'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '标题不能为空'),
        array('cover_icon', 'regex', 'require', '道具封面不能为空'),
        array('user_icon', 'regex', 'require', '道具展示不能为空'),
        array('type', 'regex', 'require', '性质不能为空'),
        array('describe', 'regex', 'require', '描述不能为空')
    ),
    'fill' => array()
);

$rules['add@props'] = [
    'deny' => 'id',
    'extends' => 'common@props',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@props'] = [
    'extends' => 'common@props',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** impression ***/

$rules['common@impression'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '标题不能为空'),
        array('color', 'regex', 'require', '颜色不能为空')
    ),
    'fill' => array()
);

$rules['add@impression'] = [
    'deny' => 'id',
    'extends' => 'common@impression',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@impression'] = [
    'extends' => 'common@impression',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** user_credit_rule ***/

$rules['common@user_credit_rule'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '模板名称不能为空'),
        array('code', 'regex', 'require', '模板ID不能为空'),
        array('type', 'regex', 'require', '模板类型不能为空'),
        array('content', 'regex', 'require', '模板内容不能为空'),
    ),
    'fill' => array()
);

$rules['add@user_credit_rule'] = [
    'deny' => 'id',
    'extends' => 'common@user_credit_rule',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@user_credit_rule'] = [
    'extends' => 'common@user_credit_rule',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** sms_template ***/

$rules['common@sms_template'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '规则名称不能为空'),
        array('type', 'regex', 'require', '规则类型不能为空'),
        array('change_type', 'regex', 'require', '变更类型不能为空'),
        array('full_value', 'regex', 'require', '最大值不能为空'),
        array('full_score', 'regex', 'require', '最高分不能为空'),
        array('tpl', 'regex', 'require', '模板内容不能为空'),
    ),
    'fill' => array()
);

$rules['add@sms_template'] = [
    'deny' => 'id',
    'extends' => 'common@sms_template',
    'must' => '',
    'validate' => [],
    'fill' => array()
];

$rules['update@sms_template'] = [
    'extends' => 'common@sms_template',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** gift ***/

$rules['common@gift'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '礼物标题不能为空'),
        array('picture_url', 'regex', 'require', '缩略图不能为空'),
        array('price', 'regex', 'require', '单价不能为空'),
        array('discount', 'regex', 'require', '折扣不能为空'),
        array('conv_millet', 'regex', 'require', '等值'.$bean_name.'不能为空'),
        array('file', '@giftResource', '', '在线资源包不能为空'),
    ),
    'fill' => array()
);

$rules['add@gift'] = [
    'deny' => 'id',
    'extends' => 'common@gift',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@gift'] = [
    'extends' => 'common@gift',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** music ***/

$rules['common@music'] = array(
    'alias' => '',
    'validate' => array(
        array('link', 'regex', 'require', '音乐链接不能为空'),
        array('lrc_link', 'regex', 'require', '歌词链接不能为空'),
        array('title', 'regex', 'require', '音乐标题不能为空'),
        array('image', 'regex', 'require', '缩略图不能为空'),
        array('singer_id', 'regex', 'require', '所属歌手不能为空'),
        array('album_id', 'regex', 'require', '所属专辑不能为空'),
        array('category_id', 'regex', 'require', '所属分类不能为空'),
    ),
    'fill' => array()
);

$rules['add@music'] = [
    'deny' => 'id',
    'extends' => 'common@music',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('release_time', 'strtotime', '', DataFactory::NOT_EMPTY),
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@music'] = [
    'extends' => 'common@music',
    'must' => 'id',
    'validate' => [],
    'fill' => array(
        array('release_time', 'strtotime', '', DataFactory::NOT_EMPTY),
    )
];

/*** music_category ***/

$rules['common@music_category'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '类别名称不能为空'),
        array('icon', 'regex', 'require', '图标不能为空'),
    ),
    'fill' => array()
);

$rules['add@music_category'] = [
    'deny' => 'id',
    'extends' => 'common@music_category',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@music_category'] = [
    'extends' => 'common@music_category',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** music_singer ***/

$rules['common@music_singer'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '歌手名称不能为空'),
        array('avatar', 'regex', 'require', '封面不能为空'),
    ),
    'fill' => array()
);

$rules['add@music_singer'] = [
    'deny' => 'id',
    'extends' => 'common@music_singer',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@music_singer'] = [
    'extends' => 'common@music_singer',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** music_album ***/

$rules['common@music_album'] = array(
    'alias' => '',
    'validate' => array(
        array('title', 'regex', 'require', '专辑名称不能为空'),
        array('image', 'regex', 'require', '封面不能为空'),
    ),
    'fill' => array()
);

$rules['add@music_album'] = [
    'deny' => 'id',
    'extends' => 'common@music_album',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('release_time', 'strtotime', '', DataFactory::NOT_EMPTY),
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@music_album'] = [
    'extends' => 'common@music_album',
    'must' => 'id',
    'validate' => [],
    'fill' => array(
        array('release_time', 'strtotime', '', DataFactory::NOT_EMPTY),
    )
];

/*** exp_level ***/

$rules['common@exp_level'] = array(
    'alias' => '',
    'validate' => array(
        array('levelname', 'regex', 'require', '类别名称不能为空'),
        array('icon', 'regex', 'require', '图标不能为空'),
        array('level_up', 'regex', 'require', '经验值不能为空'),
    ),
    'fill' => array()
);

$rules['add@exp_level'] = [
    'deny' => 'levelid',
    'extends' => 'common@exp_level',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('addtime', 'time', '', DataFactory::ANY)
    )
];

$rules['update@exp_level'] = [
    'extends' => 'common@exp_level',
    'must' => 'levelid',
    'validate' => [],
    'fill' => array()
];

/*** anchor_exp_level ***/

$rules['common@anchor_exp_level'] = array(
    'alias' => '',
    'validate' => array(
        array('levelname', 'regex', 'require', '类别名称不能为空'),
        array('icon', 'regex', 'require', '图标不能为空'),
        array('level_up', 'regex', 'require', '经验值不能为空'),
    ),
    'fill' => array()
);

$rules['add@anchor_exp_level'] = [
    'deny' => 'levelid',
    'extends' => 'common@anchor_exp_level',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('addtime', 'time', '', DataFactory::ANY)
    )
];

$rules['update@anchor_exp_level'] = [
    'extends' => 'common@anchor_exp_level',
    'must' => 'levelid',
    'validate' => [],
    'fill' => array()
];

/*** resources ***/

$rules['common@resources'] = array(
    'alias' => '',
    'validate' => array(
        array('title', 'regex', 'require', '资源标题不能为空'),
        array('name', 'regex', 'require', '资源名称不能为空'),
        array('cat_id', 'regex', 'require', '类目不能为空'),
        array('image', 'regex', 'require', '缩略图不能为空'),
        array('file_url', 'regex', 'require', '资源包不能为空'),
    ),
    'fill' => array()
);

$rules['add@resources'] = [
    'deny' => 'id',
    'extends' => 'common@resources',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@resources'] = [
    'extends' => 'common@resources',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** complaint_category ***/

$rules['common@complaint_category'] = array(
    'alias' => 'name 标题,id ID',
    'validate' => array(
        array('title', 'regex', 'require', '标题不能为空')
    ),
    'fill' => array(
    ),
);

$rules['add@complaint_category'] = [
    'deny' => 'id',
    'extends' => 'common@complaint_category',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@complaint_category'] = [
    'extends' => 'common@complaint_category',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** admin_notice ***/

$rules['common@admin_notice'] = array(
    'alias' => 'cat_id 所属分类,title 标题,id 文章ID',
    'validate' => array(
        array('title', 'regex', 'require', '标题不能为空')
    ),
    'fill' => array(),
);

$rules['add@admin_notice'] = [
    'deny' => 'id',
    'extends' => 'common@admin_notice',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@admin_notice'] = [
    'extends' => 'common@admin_notice',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

/*** category ***/

$rules['common@category'] = array(
    'deny' => 'create_time,update_time,level',
    'validate' => array(
        array('pid', 'regex', 'require', '请选择上级分类'),
        array('pid', '@validatePid', null, '上级分类不存在'),
        array('name', 'regex', 'require', '名称不能为空'),
        array('mark', 'regex', 'mark', '标识符格式不正确', DataFactory::NOT_EMPTY),
        array('mark', 'compare', '!=root', '标识符不能为root', DataFactory::NOT_EMPTY),
        array('mark', '@validateMark', null, '同一节点标识符不能重复', DataFactory::NOT_EMPTY),
    ),
    'fill' => array(
        array('update_time', 'time', null, DataFactory::ANY),
        array('pid', '@autoLevel', null, DataFactory::EXISTS)
    )
);

$rules['add@category'] = array(
    'extends' => 'common@category',
    'deny' => 'id',
    'must' => 'pid,name',
    'fill' => array(
        array('create_time', 'time', null, DataFactory::ANY),
    )
);

$rules['update@category'] = array(
    'extends' => 'common@category',
    'must' => 'id',
    'validate' => array(
        array('pid', '@validatePid2', null, '上级分类不能是其自身'),
    )
);

/*** recommend_space ***/

$rules['common@recommend_space'] = array(
    'deny' => 'create_time',
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '推荐位名称不能为空'),
        array('mark', 'regex', 'require', '标识符不能为空'),
        array('mark', 'regex', 'mark', '标识符不正确'),
        array('mark', 'exc_unique', '', '标识符不能重复'),
        array('type', 'regex', 'require', '请选择类型'),
        array('type', 'in_enum', 'recommend_space_types', '类型不正确'),
    ),
    'fill' => array()
);

$rules['add@recommend_space'] = array(
    'extends' => 'common@recommend_space',
    'must' => 'name,mark,type',
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
    )
);

$rules['update@recommend_space'] = array(
    'extends' => 'common@recommend_space',
    'ignore' => 'mark:keep',
    'must' => 'id',
    'fill' => array()
);

/*** ad_space ***/

$rules['common@ad_space'] = array(
    'deny' => 'delete_time,create_time',
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '广告位名称不能为空'),
        array('mark', 'regex', 'require', '标识符不能为空'),
        array('mark', 'regex', 'mark', '标识符不正确'),
        array('mark', 'exc_unique', '', '标识符不能重复'),
        array('type', 'regex', 'require', '请选择类型'),
        array('type', 'in_enum', 'ad_space_types', '类型不正确'),
        array('length', 'regex', 'require', '最大条数不能为空'),
        array('length', 'regex', 'number', '最大条数不正确'),
        array('platform', 'regex', 'require', '请选择适配平台'),
    ),
    'fill' => array()
);

$rules['add@ad_space'] = array(
    'extends' => 'common@ad_space',
    'must' => 'name,mark,type,length,platform',
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
    )
);

$rules['update@ad_space'] = array(
    'extends' => 'common@ad_space',
    'ignore' => 'mark:keep',
    'must' => 'id',
    'fill' => array()
);

/*** ad_content ***/

$rules['common@ad_content'] = array(
    'deny' => 'create_time',
    'alias' => 'spec_id 所属广告位',
    'validate' => array(
        array('space_id', 'regex', 'require', '请选择所属广告位'),
        array('space_id', 'has_row', 'ad_space,id', '所属广告位不存在'),
        array('title,image,video,url', 'least', '', '标题、图片、视频、链接地址至少填写一项'),
        array('start_time', 'se_range', 'end_time', '时间范围不正确'),
        array('os', '@validateOs', '', '投放平台不正确'),
        array('city_id', '@validateCity', '', '投放城市不存在', DataFactory::NOT_EMPTY),
    ),
    'fill' => array(
        array('start_time', 'strtotime', '', DataFactory::NOT_EMPTY),
        array('end_time', 'strtotime', '', DataFactory::NOT_EMPTY),
        array('start_time', 'time', '', DataFactory::NOT_HAS),
        array('end_time', 'later', '1y', DataFactory::NOT_HAS),
        array('code_min', 'string', '0', DataFactory::NOT_HAS),
        array('code_max', 'string', '100', DataFactory::NOT_HAS),
        array('os', 'arr_implode', ','),
        array('purview', 'arr_implode', ','),
    )
);

$rules['add@ad_content'] = array(
    'extends' => 'common@ad_content',
    'must' => 'space_id',
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('sort', 'string', '0', DataFactory::NOT_HAS),
    )
);

$rules['update@ad_content'] = array(
    'extends' => 'common@ad_content',
    'ignore' => 'space_id:keep',
    'must' => 'id',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY),
    )
);

/*** admin_rule ***/

$rules['common@admin_rule'] = array(
    'deny' => 'create_time,update_time',
    'alias' => 'cid 所属分组,name 规则名称,title 规则标题',
    'validate' => array(
        array('cid', 'regex', 'require', '请选择所属分类'),
        array('title', 'regex', 'require', '规则标题不能为空'),
        array('name', 'regex', 'require', '规则名称不能为空'),
        array('cid', '@validateCid', '', '所属分类不正确'),
        array('name', 'regex', 'mark', '规则名称格式不正确'),
        array('name', 'unique', '', '规则名称已经存在'),
    ),
    'fill' => array()
);

$rules['add@admin_rule'] = array(
    'extends' => 'common@admin_rule',
    'must' => 'cid,title,name',
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('update_time', 'time', '', DataFactory::ANY),
        array('type', 'string', '1', DataFactory::NOT_HAS),
    )
);

$rules['update@admin_rule'] = array(
    'extends' => 'common@admin_rule',
    'ignore' => 'cid:keep,name:keep',
    'must' => 'id',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY),
    )
);

/*** admin_group ***/

$rules['common@admin_group'] = [
    'deny' => 'create_time,update_time',
    'ignore' => 'rules',
    'alias' => 'name 分组名称',
    'validate' => array(
        array('name', 'regex', 'require', '分组名称不能为空'),
        array('name', 'exc_unique', '', '分组名称重复'),
        array('rules', '@validateRules', '', '规则名错误或者不存在'),
    ),
    'fill' => array(
        array('works', 'arr_implode', ','),
    )
];

$rules['add@admin_group'] = [
    'extends' => 'common@admin_group',
    'must' => 'name',
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('update_time', 'time', '', DataFactory::ANY),
        array('status', 'string', '1', DataFactory::NOT_HAS),
    )
];

$rules['update@admin_group'] = [
    'extends' => 'common@admin_group',
    'ignore' => 'rules:keep,name:keep',
    'must' => 'id',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY),
    )
];

/*** admin ***/

$rules['common@admin'] = [
    'deny' => 'create_time,update_time,login_time,login_ip',
    'alias' => 'username 用户名,password 密码,phone 手机号',
    'validate' => array(
        array('username', 'regex', 'require', '用户名不能为空'),
        array('username', 'strlen', '4,30', '用户名4-30位字符'),
        array('username', 'regex', 'no_blank', '用户名不能包含空白字符'),
        array('username', 'not_regex', 'phone', '用户名不能使用手机号格式'),
        array('username', 'not_regex', 'email', '用户名不能使用邮箱格式'),
        array('username', 'unique', '', '用户名已经存在'),
        array('password', 'regex', 'require', '密码不能为空'),
        array('password', 'regex', 'no_blank', '密码不能包含空白字符'),
        array('password', 'strlen', '6,16', '密码6-16位字符'),
        array('confirm_password', 'confirm', 'password', '密码两次输入不一致'),
        array('phone', 'regex', 'phone', '手机号不正确', DataFactory::NOT_EMPTY),
        array('phone', 'unique', '', '手机号已经存在', DataFactory::NOT_EMPTY)
    ),
];

$rules['add@admin'] = [
    'extends' => 'common@admin',
    'must' => 'username,password',
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('status', 'string', '1', DataFactory::NOT_HAS),
    )
];

$rules['update@admin'] = [
    'extends' => 'common@admin',
    'ignore' => 'username:keep,phone:keep',
    'deny' => 'username,realname,phone,password',
    'must' => 'id',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY),
    )
];

/*** agent ***/

$rules['common@agent'] = [
    'deny' => 'create_time,update_time,sec_num,root_id,promoter_num,anchor_num',
    'validate' => array(
        array('name', 'regex', 'require', config('app.agent_setting.agent_name').'名称不能为空'),
        array('name', 'exc_unique', '', config('app.agent_setting.agent_name').'名称已存在'),
        array('grade', 'regex', 'require', '请选择'.config('app.agent_setting.agent_name').'级别'),
        array('grade', 'in_enum', 'agent_grades', config('app.agent_setting.agent_name').'级别不存在'),
        array('area_id', 'regex', 'require', '请选择地区'),
        array('area_id', 'region', '3', '地区不存在'),
        array('subject_type', 'regex', 'require', '请选择主体类型'),
        array('subject_type', 'in_enum', 'agent_subject_types', '主体类型不存在'),
        array('legal_name', 'regex', 'require', '法人姓名不能为空'),
        array('legal_id', 'regex', 'require', '法人身份证号不能为空'),
        array('legal_id', 'regex', '/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/i', '法人身份证号不正确'),
        array('contact_name', 'regex', 'require', '联系人不能为空'),
        array('contact_phone', 'regex', 'require', '联系电话不能为空'),
        array('contact_phone', 'regex', 'phone', '联系电话不正确'),
        array('contact_email', 'regex', 'email', '联系邮箱不正确', DataFactory::NOT_EMPTY),
        array('expire_time', 'regex', 'require', '请选择到期时间'),
        array('max_sec_num', '@validateMaxNum', '', '二级'.config('app.agent_setting.agent_name').'限额不正确或者允许新增的情况下限额值需要大于0'),
        array('max_promoter_num', '@validateMaxNum', '', config('app.agent_setting.promoter_name').'限额不正确或者允许新增的情况下限额值需要大于0'),
        array('max_anchor_num', '@validateMaxNum', '', '主播限额不正确或者允许新增的情况下限额值需要大于0'),
        array('max_virtual_num', '@validateMaxNum', '', '虚拟号限额不正确或者允许新增的情况下限额值需要大于0'),
    ),
    'fill' => array(
        array('expire_time', 'strtotime', '', DataFactory::NOT_EMPTY),
        array('area_id', 'region_extend', '1,2,3', DataFactory::NOT_EMPTY),
    )
];

$rules['add@agent'] = [
    'extends' => 'common@agent',
    'must' => 'name,grade,area_id,subject_type,contact_name,contact_phone',
    'validate' => array(
        array('expire_time', 'expire', '', '到期时间不正确'),
    ),
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('status', 'string', '1', DataFactory::NOT_HAS),
        array('add_sec', 'string', '0', DataFactory::NOT_HAS),
        array('max_sec_num', 'string', '0', DataFactory::NOT_HAS),
        array('add_anchor', 'string', '1', DataFactory::NOT_HAS),
        array('max_anchor_num', 'string', '500', DataFactory::NOT_HAS),
        array('add_promoter', 'string', '1', DataFactory::NOT_HAS),
        array('max_promoter_num', 'string', '500', DataFactory::NOT_HAS),
        array('add_virtual', 'string', '1', DataFactory::NOT_HAS),
        array('max_virtual_num', 'string', '500', DataFactory::NOT_HAS),
    )
];

$rules['add2@agent'] = [
    'extends' => 'common@agent',
    'must' => 'name,area_id,subject_type,contact_name,contact_phone',
    'validate' => array(
        array('expire_time', 'expire', '', '到期时间不正确'),
    ),
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('status', 'string', '1', DataFactory::NOT_HAS)
    )
];

$rules['update@agent'] = [
    'extends' => 'common@agent',
    'ignore' => 'name:keep',
    'must' => 'id',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY),
    )
];

$rules['update2@agent'] = [
    'extends' => 'common@agent',
    'ignore' => 'name:keep',
    'must' => 'id',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY),
    )
];

$rules['set_root@agent_admin'] = [
    'deny' => '',
    'must' => 'username,password,agent_id',
    'validate' => array(
        array('username', 'regex', 'require', '用户名不能为空'),
        array('username', 'strlen', '4,30', '用户名4-30位字符'),
        array('username', 'regex', 'no_blank', '用户名不能包含空白字符'),
        array('username', 'not_regex', 'phone', '用户名不能使用手机号格式'),
        array('username', 'not_regex', 'email', '用户名不能使用邮箱格式'),
        array('username', '@validateUsername', '', '用户名已经存在'),
        array('phone', 'regex', 'phone', '手机号不正确', DataFactory::NOT_EMPTY),
        array('phone', '@validatePhone', '', '手机号已经存在', DataFactory::NOT_EMPTY),
        array('password', 'regex', 'require', '密码不能为空'),
        array('password', 'regex', 'no_blank', '密码不能包含空格'),
        array('password', 'not_regex', 'number', '密码不能为纯数字'),
        array('password', 'length', '6,16', '密码6-16位字符'),
        array('confirm_password', 'confirm', 'password', '两次密码输入不一致'),
    ),
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('status', 'string', '1', DataFactory::NOT_HAS),
    )
];

/*** recharge_log ***/

$rules['common@recharge_log'] = [
    'deny' => 'audit_status',
    'validate' => array(
        array('rec_type', 'regex', 'require', '充值账户类型不能为空'),
        array('rec_account', 'regex', 'require', '充值账户不能为空'),
        array('rec_account', '@validateRecAccount', '', '充值账户不存在'),
        array('total_fee', 'regex', 'require', '充值金额不能为空'),
        array('total_fee', 'regex', 'currency', '充值金额不正确'),
        array('pay_method', 'regex', 'require', '请选择支付方式'),
        array('pay_method', 'in_enum', 'recharge_pay_methods', '支付方式不支持'),
        array('pay_name', '@validatePayInfo', '', '请填写付款人'),
        array('pay_account', '@validatePayInfo', '', '请填写付款账号'),
    )
];

$rules['add@recharge_log'] = [
    'deny' => 'id',
    'extends' => 'common@recharge_log',
    'must' => 'total_fee,pay_method',
    'validate' => array(),
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

/*** packages ***/

$rules['common@packages'] = [
    'deny' => 'download_num',
    'validate' => array(
        array('name', 'regex', 'require', '安装包名称不能为空'),
        array('version', 'regex', 'require', '外部版本号不能为空'),
        array('code', 'regex', 'require', '内部版本号不能为空'),
        array('code', 'regex', 'integer', '内部版本号需要是正整数'),
        array('os', 'in_enum', 'packages_os', '运行平台不支持'),
        array('channel', 'in_enum', 'packages_channel', '发布渠道不支持'),
        array('update_type', 'in_enum', 'packages_update_types', '更新类型不支持'),
        array('url,file_path', 'least', '', '第三方地址或者安装包必填其中一项'),
    )
];

$rules['add@packages'] = [
    'deny' => 'id',
    'extends' => 'common@packages',
    'must' => 'name,version,channel',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@packages'] = [
    'extends' => 'common@packages',
    'must' => 'id',
    'fill' => array(//array('update_time', 'time', '', DataFactory::ANY),
    )
];

/*** live_film ***/

$rules['common@live_film'] = [
    'deny' => '',
    'validate' => array(
        array('video_title', 'regex', 'require', '视频标题不能为空'),
        array('video_cover', 'regex', 'require', '视频封面不能为空'),
        array('video_duration', 'regex', 'require', '视频时长不能为空'),
        array('video_duration', 'regex', 'integer', '视频时长需要是正整数'),
        array('video_rate', 'regex', 'require', '视频宽高比不能为空')
    )
];

$rules['add@live_film'] = [
    'deny' => 'id',
    'extends' => 'common@live_film',
    'must' => 'video_title,video_cover,video_duration,video_rate',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@live_film'] = [
    'extends' => 'common@live_film',
    'must' => 'id',
    'fill' => array()
];

/*** live_film_ad ***/

$rules['common@live_film_ad'] = [
    'deny' => '',
    'validate' => array(
        array('ad_title', 'regex', 'require', '广告标题不能为空'),
        array('video_duration', 'regex', 'require', '视频时长不能为空'),
        array('video_duration', 'regex', 'integer', '视频时长需要是正整数'),
        array('video_rate', 'regex', 'require', '视频宽高比不能为空'),
    )
];

$rules['add@live_film_ad'] = [
    'deny' => 'id',
    'extends' => 'common@live_film_ad',
    'must' => 'ad_title,video_duration,video_rate',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

/*** topic ***/

$rules['common@topic'] = [
    'deny' => '',
    'validate' => array(
        array('title', 'regex', 'require', '标题不能为空'),
        array('title', 'exc_unique', '', '标题不能重复'),
        array('icon', 'regex', 'require', '请上传图标'),
        array('descr', 'regex', 'require', '描述不能为空'),
    )
];

$rules['add@topic'] = [
    'deny' => 'id',
    'extends' => 'common@topic',
    'must' => 'title,icon,descr',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@topic'] = [
    'extends' => 'common@topic',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

$rules['common@video_tags'] = [
    'deny' => '',
    'validate' => array(
        array('name', 'regex', 'require', '名称不能为空'),
        array('name', 'exc_unique', '', '名称不能重复'),
    )
];

$rules['add@video_tags'] = [
    'deny' => 'id',
    'extends' => 'common@video_tags',
    'must' => 'name,descr',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@video_tags'] = [
    'extends' => 'common@video_tags',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];


/*** work_types ***/

$rules['common@work_types'] = [
    'deny' => '',
    'validate' => array(
        array('name', 'regex', 'require', '标题不能为空'),
        array('name', 'exc_unique', '', '标题不能重复'),
        array('type', 'regex', 'require', '类型标识不能为空'),
    )
];

$rules['add@work_types'] = [
    'deny' => 'id',
    'extends' => 'common@work_types',
    'must' => 'name,type',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@work_types'] = [
    'extends' => 'common@work_types',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];


/*** lottery_type ***/

$rules['common@lottery_type'] = array(
    'alias' => 'name 标题,id ID',
    'validate' => array(
        array('name', 'regex', 'require', '标题不能为空')
    ),
    'fill' => array(
    ),
);

$rules['add@lottery_type'] = [
    'deny' => 'id',
    'extends' => 'common@lottery_type',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY)
    )
];

$rules['update@lottery_type'] = [
    'extends' => 'common@lottery_type',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];


/*** lottery ***/

$rules['add@lottery'] = [
    'deny' => '',
    'validate' => array(
        array('lottery_type', 'regex', 'require', '请选择活动类型'),
        array('name', 'regex', 'require', '活动名称不能为空'),
    ),
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('start_time', 'strtotime', '', DataFactory::ANY),
        array('end_time', 'strtotime', '', DataFactory::ANY)
    )
];

$rules['update@lottery'] = [
    'must' => 'id',
    'validate' => array(
        array('lottery_type', 'regex', 'require', '请选择活动类型'),
        array('name', 'regex', 'require', '活动名称不能为空'),
    ),
    'fill' => array(
        array('create_time', 'time', '', DataFactory::ANY),
        array('start_time', 'strtotime', '', DataFactory::ANY),
        array('end_time', 'strtotime', '', DataFactory::ANY)
    )
];

/*** liang ***/

$rules['common@liang'] = array(
    'alias' => '',
    'validate' => array(
        array('name', 'regex', 'require', '靓号不能为空'),
        array('coin', 'regex', 'require', '钻石价格不能为空'),
        array('score', 'regex', 'require', '积分价格不能为空')
    ),
    'fill' => array()
);

$rules['add@liang'] = [
    'deny' => 'id',
    'extends' => 'common@liang',
    'must' => '',
    'validate' => [],
    'fill' => array(
        array('addtime', 'time', '', DataFactory::ANY)
    )
];

$rules['update@liang'] = [
    'extends' => 'common@liang',
    'must' => 'id',
    'validate' => [],
    'fill' => array()
];

return $rules;