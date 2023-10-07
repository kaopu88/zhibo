<?php

namespace bxkj_common;

class SectionListExecuter extends SectionExecuter
{
    public function getOptions()
    {
        return [
            'offset' => 0
        ];
    }

    //首次执行
    public function first()
    {
        return [];
    }

    public function getList($offset = 0, $length = 10)
    {
    }

    public function itemHandler(&$ids, $index, $item)
    {
    }

}