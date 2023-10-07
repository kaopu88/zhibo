<?php

use \bxkj_common\DataFactory as DataFactory;

/*** goods ***/

$rules['index@goods'] = array(
    'validate' => array(
        array('pid', 'regex', 'require', '请选择上级菜单项'),
        array('pid', '@validatePid', '', '上级菜单项不存在'),
        array('name', 'regex', 'require', '菜单项名称不能为空'),
        array('mark', 'compare', '!=root', '标识符不能为root', DataFactory::NOT_EMPTY),
        array('mark', '@validateMark', '', '同一根节点标识符不能重复', DataFactory::NOT_EMPTY),
    ),
);

$rules['add@goods'] = array(
    'extends' => 'index@goods',
    'fill' => array(
        array('create_time', ':time', '', DataFactory::ANY),
    )
);

$rules['update@goods'] = array(
    'extends' => 'index@goods',
    'fill' => array(
        array('update_time', 'time', '', DataFactory::ANY),
    )
);

/*** anchor_goods_cate ***/

$rules['index@anchor_goods_cate'] = array(
    'validate' => array(
        array('cate_name', 'regex', 'require', '菜单名称不能为空'),
        array('user_id', 'regex', 'require', '上级菜单项不存在'),
    ),
);

$rules['add@anchor_goods_cate'] = array(
    'extends' => 'index@anchor_goods_cate',
);

$rules['update@anchor_goods_cate'] = array(
    'extends' => 'index@anchor_goods_cate',
);


return $rules;