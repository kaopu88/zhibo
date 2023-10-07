<?php

namespace app\core\service;
use Wcs\Config;

class Wsyun
{
    /**
    * 网宿云点播上传凭证
    */
    public function getQcloudVodSign($fileName='')
    {
        $accessKey = Config::WCS_ACCESS_KEY;
        $url = 'https://open.chinanetcenter.com/vod/videoManage/getUploadToken';
        // $url = 'https://api.cloudv.haplat.net/vod/videoManage/getVideoList';
        // $body = [
        //     'videoName'=>'a',
        //     'pageSize'=>'5',
        //     'pageIndex'=>'2'
        // ];
        $body = [
            'originFileName'=>$fileName
            ];
        $header = self::getHeader($body);
        
        $result = self::sendCurl($url,$data,$header);
        return $result;
    }
    private function getHeader($body=[])
    {
        $header = [];
        $time = time();
        $sign = self::getSign($body,$time);
        $accessKey = Config::WCS_ACCESS_KEY;
        $header[] = "Authorization:WS3-HMAC-SHA256 Credential=$accessKey, SignedHeaders=content-type;host, Signature=$sign";
        $header[] = 'Content-Type:application/json; charset=utf-8';
        // $header[] = 'Host:api.cloudv.haplat.net';
        $header[] = 'Host:open.chinanetcenter.com';
        $header[] = "X-WS-AccessKey:".$accessKey;
        $header[] = 'X-WS-Timestamp:'.$time;
        return $header;
    }
    private function getSign($body,$time)
    {
        $HashedCanonicalRequest = self::getHashedCanonicalRequest($body);
        $StringToSign = 'WS3-HMAC-SHA256'."\n".$time."\n".hash('sha256',$HashedCanonicalRequest);
        $accessKey = Config::WCS_ACCESS_KEY;
        $tem = hash_hmac('SHA256', $StringToSign, $accessKey, true);
        return bin2hex($tem);
    }
    private function getHashedCanonicalRequest($body=[])
    {

        $RequestURI ='/vod/videoManage/getUploadToken';
        // $RequestURI ='/vod/videoManage/getVideoList';
        $QueryString = '';
        $CanonicalHeaders = 'content-type:application/json; charset=utf-8'."\n".'host:open.chinanetcenter.com'."\n";
        // $CanonicalHeaders = 'content-type:application/json; charset=utf-8'."\n".'host:api.cloudv.haplat.net'."\n";
        $SignedHeaders = 'content-type;host';
        $json = json_encode($body);
        $HashedRequestPayload =  strtolower(hash('sha256', $json, false));
        $CanonicalRequest =
            'POST' . "\n" .
            $RequestURI . "\n" .
            $QueryString . "\n" .
            $CanonicalHeaders . "\n" .
            $SignedHeaders . "\n" .
            $HashedRequestPayload;
        return $CanonicalRequest;
    }

    private function sendCurl($url,$data=[],$headers=[],$type="POST")
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        if($type == 'GET'){
            curl_setopt($curl, CURLOPT_HEADER, 1);        //设置头文件的信息作为数据流输出
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//设置获取的信息以文件流的形式返回，而不是直接输出
        }

        if($type == 'POST'){
            $data = empty($data)?'':json_encode($data);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        }
        $res = curl_exec($curl);
        curl_close($curl);
        return $res;
    }


    /**
     * 传入数组进行HTTP POST请求
     */
    function curlPost($url, $post_data = array(), $header = "", $data_type = "json", $timeout = 5)
    {
        $header = empty($header) ? '' : $header;
        //支持json数据数据提交
        if ($data_type == 'json') {
            $post_string = json_encode($post_data);
        } elseif ($data_type == 'array') {
            $post_string = $post_data;
        } elseif (is_array($post_data)) {
            $post_string = http_build_query($post_data, '', '&');
        }

        $ch = curl_init();    // 启动一个CURL会话
        curl_setopt($ch, CURLOPT_URL, $url);     // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);     // Post提交的数据包
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);     // 设置超时限制防止死循环
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头
        $result = curl_exec($ch);

        // 打印请求的header信息
        //$a = curl_getinfo($ch);
        //var_dump($a);

        curl_close($ch);
        return $result;
    }

}