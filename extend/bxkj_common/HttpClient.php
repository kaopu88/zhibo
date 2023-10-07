<?php

namespace bxkj_common;


class HttpClient
{
    private $config;
    private $result;
    private $reqError;
    private $error;//错误消息
    private $happenError;
    private $reqUrl;
    private $userAgent;//用户代理信息
    private $cookieArr;//cookie数组
    private $header;
    private $ca;//只信任CA证书
    private $sll = false;
    private $caFile;//CA证书位置
    private $params;
    private $resHeader = array();//返回的头信息
    private $resFormat = '';//返回的数据格式
    private $referer = '';//引用页
    const FORMAT_URL = 'application/x-www-form-urlencoded';
    const FORMAT_JSON = 'application/json';
    const FORMAT_XML = 'text/xml';
    protected static $apis = [];

    public function __construct($config = null)
    {
        $this->config = array_merge(array(
            'format' => '',
            'ca' => false,
            'caFile' => getcwd() . '/cacert.pem',
            'timeout' => 0,
            'retries' => 0,
            'params' => array(),
            'userAgent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36',
            'cookieArr' => '',
            'header' => '',
            'debug' => false
        ), isset($config) ? $config : array());
        $this->happenError = false;
    }

    //设置默认配置
    public function setConfig($name)
    {
        $name = is_array($name) ? $name : config($name);
        $this->config = array_merge($this->config, $name);
        return $this;
    }

    //设置get参数变量
    public function setGetParams($params)
    {
        $this->params = is_array($params) ? $params : array();
        return $this;
    }

    //设置用户代理
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    //设置cookie
    public function setCookie($cookieArr)
    {
        $this->cookieArr = $cookieArr;
        return $this;
    }

    public function setReferer($referer)
    {
        $this->referer = $referer;
        return $this;
    }

    public function setAjax()
    {
        $this->header = is_array($this->header) ? $this->header : [];
        $this->header[] = 'X-Requested-With: XMLHttpRequest';
        return $this;
    }

    //设置ssl协议
    public function setCA($ca = true, $caFile = null)
    {
        $this->ca = $ca;
        if ($this->ca) {
            $this->caFile = isset($caFile) ? $caFile : getcwd() . '/cacert.pem';
        }
        return $this;
    }

    public function setssl($sll = false)
    {
        $this->sll = $sll;
        return $this;
    }

    public function setHeaderItem($item)
    {
        $this->header = is_array($this->header) ? $this->header : array();
        $this->header[] = $item;
        return $this;
    }

    public function setContentType($format)
    {
        $this->header = is_array($this->header) ? $this->header : array();
        switch ($format) {
            case 'json':
                $format = self::FORMAT_JSON;
                break;
            case 'xml':
                $format = self::FORMAT_XML;
                break;
            case 'url':
                $format = self::FORMAT_URL;
                break;
        }
        $this->header[] = 'content-type:' . $format;
        return $this;
    }

    public function get($url, $data = '', $timeout = null, $retries = null)
    {
        return $this->curl($url, 'get', $data, $timeout, $retries);
    }

    public function post($url, $data = '', $timeout = null, $retries = null)
    {   
        return $this->curl($url, 'post', $data, $timeout, $retries);
    }

    public function curl($url = '', $type = 'get', $data = '', $timeout = null, $retries = null)
    {
        $startTime = get_millisecond();
        //过滤空值
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (is_null($value)) unset($data[$key]);
            }
        }
        $timeout = isset($timeout) ? $timeout : $this->config['timeout'];
        $retries = isset($retries) ? $retries : $this->config['retries'];
        $this->userAgent = isset($this->userAgent) ? $this->userAgent : $this->config['userAgent'];
        $this->cookieArr = isset($this->cookieArr) ? $this->cookieArr : $this->config['cookieArr'];
        $this->header = isset($this->header) ? $this->header : $this->config['header'];
        $this->ca = isset($this->ca) ? $this->ca : $this->config['ca'];
        $this->caFile = isset($this->caFile) ? $this->caFile : $this->config['caFile'];
        $url = $this->generateUrl($url, $type, $data);
        $this->reqUrl = $url;
        //初始化
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $ssl = strtolower(substr($url, 0, 8)) == "https://" ? true : false;
        //https协议
        if ($ssl) {
            if ($this->ca) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);   // 只信任CA颁布的证书
                curl_setopt($curl, CURLOPT_CAINFO, $this->caFile); // CA根证书（用来验证的网站证书是否是CA颁布）
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
            } else {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
                if ($this->sll) {
                    curl_setopt($curl,CURLOPT_SSLCERT,ROOT_PATH.'data/wxpay_cert/apiclient_cert.pem');
                    curl_setopt($curl,CURLOPT_SSLKEY,ROOT_PATH.'data/wxpay_cert/apiclient_key.pem');
                }

            }
        }
        //cookie
        if (!empty($this->cookieArr)) {
            curl_setopt($curl, CURLOPT_COOKIE, $this->generateCookie($this->cookieArr));
        }
        //user_agent
        if (!empty($this->userAgent)) {
            curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
        }

        if (!empty($this->referer)) {
            curl_setopt($curl, CURLOPT_REFERER, $this->referer);
        }
        //header
        $this->header = $this->header ? $this->header : array('content-type:' . self::FORMAT_URL);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->header);
        $format = self::FORMAT_URL;
        foreach ($this->header as $item) {
            list($name, $value) = explode(':', $item);
            $name = strtolower(trim($name));
            $value = strtolower(trim($value));
            if ($name == 'content-type') {
                $format = $value;
            }
        }
        //post
        if (strtolower($type) == 'post') {
            curl_setopt($curl, CURLOPT_POST, 1);
            $postFields = $data;
            
            // var_dump(json_encode($postFields));die;
            if (is_array($postFields)) {
                if ($format == self::FORMAT_URL) {
                    $postFields = http_build_query($postFields);
                } elseif ($format == self::FORMAT_JSON) {
                    $postFields = json_encode($postFields);
                } else if ($format == self::FORMAT_XML) {
                    $postFields = array_to_xml($postFields);
                }
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        }
        //超时设置
        if ($timeout > 0) {
            if ($timeout < 1) {
                curl_setopt($curl, CURLOPT_NOSIGNAL, true);
                curl_setopt($curl, CURLOPT_TIMEOUT_MS, $timeout * 1000);
                //curl_setopt($curl, CURLOPT_CONNECTTIMEOUT_MS, $timeout * 1000);
            } else {
                curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
                //curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
            }
            
        }
        $retries += 1;
        do {
            $output = curl_exec($curl);
            $curlInfo = curl_getinfo($curl);
            $retries--;
        } while ($retries > 0 && (curl_errno($curl) || $curlInfo['http_code'] != '200'));
        if (curl_errno($curl) || $curlInfo['http_code'] != '200') {
            $this->reqError = make_error($curlInfo['http_code'] != '200' ? 'http_code:' . $curlInfo['http_code'] : curl_error($curl));
            $this->happenError = true;
        } else {
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);// 获得响应结果里的：头大小
            $headerStr = substr($output, 0, $headerSize);// 根据头大小去获取头信息内容
            $headerArr = parse_headers($headerStr);
            $contentType = $headerArr['content-type'];
            $contentFormat = $headerArr['content-format'];
            $this->resHeader = $headerArr;
            $this->resFormat = $contentFormat;
            $output = substr($output, $headerSize);
            $this->reqError = null;
            $this->happenError = false;
            $this->result = $output;
        }

        curl_close($curl);
        if ($this->config['debug']) {
            $endTime = get_millisecond();
            $this->performance($endTime - $startTime, strtolower($type), $url, $postFields);
        }
        $this->reset();
        return $this;
    }

    //生成完整的url
    private function generateUrl($url, $type, $data)
    {
        $url = isset($this->config['base']) ? $this->config['base'] . $url : $url;
        $this->params = isset($this->params) ? $this->params : $this->config['params'];
        foreach ($this->params as $key => $value) {
            $url = preg_replace('/\{\$' . $key . '\}/', urlencode($value), $url);
        }
        $url = preg_replace('/\{\$\w+\}/', '', $url);
        $getData = array();
        if (isset($this->config['token'])) {
            $getData['time'] = time();
            $getData['code'] = get_ucode();
            $token = $this->config['token'] ? $this->config['token'] : config('app.app_setting.data_token');
            $getData['sign'] = generate_sign(array_merge($getData, (($type == 'post' && $data) ? $data : array())), $token);
        }
        if ($type == 'get') {
            $getData = array_merge($getData, $data ? $data : array());
        }
        if (!empty($getData)) {
            $query = is_array($getData) ? http_build_query($getData) : $getData;
            $url = strpos($url, '?') === false ? $url . '?' . $query : rtrim($url, '&') . '&' . $query;
        }
        return $url;
    }

    //生成cookie字符串
    private function generateCookie($arr)
    {
        $str = '';
        foreach ($arr as $key => $value) {
            $str .= $key . '=' . $value . ';';
        }
        $str = preg_replace('/\;$/', '', $str);
        return $str;
    }

    //获取解析后的json数据
    public function getData($format = null)
    {
        if ($this->happenError) {
            $this->error = make_error('http request error');
            return false;
        }
        $format = isset($format) ? $format : $this->config['format'];
        if ($format == 'custom' || $format == 'json' || $format == 'wx') {
            $data = json_decode($this->result, true);
            if (is_null($data)) {
                $this->error = make_error('数据格式错误');
                return false;
            }
            if ($format == 'json') {
                return $data;
            } elseif ($format == 'wx') {
                if (isset($data['errcode'])) {
                    $this->error = make_error('[' . $data['errcode'] . ']' . $data['errmsg']);
                    return false;
                }
                return $data;
            } else {
                if ($data['status'] != '0') {
                    $this->error = make_error('[' . $data['status'] . ']' . $data['info']);
                    return false;
                }
                return $data['data'];
            }
        } else if ($format == 'xml') {
            return xml_to_array($this->result);
        }
        return $this->result;
    }

    //重置参数
    private function reset()
    {
        $this->userAgent = null;
        $this->cookieArr = null;
        $this->ca = null;
        $this->caFile = null;
        $this->params = null;
        $this->header = null;
        $this->resHeader = array();
        $this->resFormat = '';
        $this->referer = '';
    }

    //获取数据错误
    public function getError()
    {
        return $this->error;
    }

    //获取请求错误
    public function getReqError()
    {
        return $this->reqError;
    }

    public function getUrl()
    {
        return $this->reqUrl;
    }

    //性能记录
    protected function performance($time, $type, $url, $data)
    {
        $str = $type == 'get' ? "{$url}" : "{$url} post-data: {$data}";
        if (class_exists('\think\Loader', false)) {
        } else {
            $time = round($time, 2);
            $num = count(self::$apis);
            self::$apis[] = "[{$num} - {$time}ms]{$str}";

        }
    }

}