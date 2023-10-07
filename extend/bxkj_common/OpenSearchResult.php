<?php

namespace bxkj_common;

class OpenSearchResult
{
    protected $result;

    public function __construct(\OpenSearch\Generated\Common\OpenSearchResult $resultObj)
    {
        $json = $resultObj->result;
        $this->result = json_decode($json, true);
    }

    public function __get($name)
    {
        return $this->result[$name];
    }

    public function getTotal()
    {
        return $this->result['result']['total'];
    }

    public function getIds($key)
    {
        $items = $this->result['result']['items'];
        if (empty($items))return [];
        foreach ($items as $item) {
            $fields = $item['fields'];
            $ids[] = $fields[$key];
        }
        return $ids;
    }

    public function sortList($ids,$key, $list)
    {
        $newList = [];
        foreach ($ids as $id) {
            foreach ($list as $item) {
                if ($item[$key] == $id) {
                    $newList[] = $item;
                    break;
                }
            }
        }
        return $newList;
    }

    public function getError()
    {
        $errors = $this->result['errors'];
        foreach ($errors as $error) {
            return make_error($error['message'], $error['code']);
        }
    }
}