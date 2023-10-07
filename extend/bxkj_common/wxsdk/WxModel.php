<?php

namespace bxkj_common\wxsdk;
class WxModel
{
    protected $data = array();
    protected $error;

    public function __construct()
    {
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($message = '', $code = 1)
    {
        $this->error = is_error($message) ? $message : make_error($message, $code);
        return false;
    }

    protected function getRowData()
    {
        return $this->data;
    }

    protected function updateRowData($row)
    {
        $row = array_merge($this->data, $row);
        $this->setRowData($row);
    }

    protected function setRowData($row)
    {
        $this->data = $row;
    }
}