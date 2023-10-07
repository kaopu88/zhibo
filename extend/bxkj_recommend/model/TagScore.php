<?php

namespace bxkj_recommend\model;

class TagScore extends Model
{
    protected $userVideoTag;
    protected $detail;
    protected $proportion=[];

    public function __construct(UserVideoTag $userVideoTag)
    {
        parent::__construct();
        $this->userVideoTag = $userVideoTag;
        $this->detail = $this->userVideoTag->getDetail();
    }
}