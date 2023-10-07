<?php

namespace app\common\behavior;

use bxkj_common\DataTemplate;
use think\facade\Env;
use think\Request;
use think\Response;

class ResponseSend
{
    public static $dataType = null;

    public function run(Response $response)
    {
        $data = $response->getData();
        if (is_error($data)) {
            $response->contentType('content:application/json');
            $response->code(200);
            $response->content(json_encode([
                'code' => $data->getStatus(),
                'msg' => $data->getMessage()
            ]));
        } else if (is_array($data)) {
            $controller = strtolower(\think\facade\Request::controller());
            $action = strtolower(\think\facade\Request::action());
            $modulePath = Env::get('module_path');
            $method = strtolower(\think\facade\Request::method());
            $dataType = self::$dataType;
            $str = isset($dataType) ? ".{$dataType}" : "";
            $filename = "$controller.$action{$str}.json";
            $filePath = $modulePath . 'data' . DIRECTORY_SEPARATOR . $method . '/' . $filename;
            if (is_file($filePath)) {
                $json = file_get_contents($filePath);
                if ($json) {
                    $jsonData = json_decode($json, true);
                    if (isset($jsonData)) {
                        DataTemplate::parse($jsonData, $data);
                        $response->content(json_encode($data));
                    }
                }
            }
        }
    }
}