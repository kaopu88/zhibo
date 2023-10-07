<?php

namespace bxkj_common;

class UserAgent
{
    protected $userAgent;
    protected $server;
    protected $info;

    public function __construct($userAgent = null, $server = null)
    {
        $this->userAgent = isset($userAgent) ? $userAgent : $_SERVER['HTTP_USER_AGENT'];
        $this->server = isset($server) ? $server : array();
        $this->parse();
    }

    public function getInfo()
    {
        return $this->info;
    }

    //解析
    protected function parse()
    {
        $this->parseBrowser();
        $this->parseOs();
    }

    private function parseBrowser()
    {
        $v = array();
        //firefox
        if (preg_match('/firefox/i', $this->userAgent)) {
            preg_match('/firefox\/([0-9\.\s]+)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'firefox';
        }
        //safari
        if (preg_match('/safari/i', $this->userAgent)) {
            preg_match('/safari\/([0-9\.\s]+)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'safari';
        }
        //chrome
        if (preg_match('/chrome/i', $this->userAgent)) {
            preg_match('/chrome\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'chrome';
        }
        //trident内核看成是ie
        if (preg_match('/trident/i', $this->userAgent)) {
            preg_match('/trident\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'ie';
        }
        //ie
        if (preg_match('/msie/i', $this->userAgent)) {
            preg_match('/msie\s+(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'ie';
        }
        //edge
        if (preg_match('/edge/i', $this->userAgent)) {
            preg_match('/edge\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'edge';
        }
        //opera
        if (preg_match('/opr/i', $this->userAgent)) {
            preg_match('/opr\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'opera';
        }
        //360
        if (preg_match('/360/i', $this->userAgent)) {
            preg_match('/360\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = '360';
            if (preg_match('/360se/i', $this->userAgent)) $this->info['browser'] = '360se';
            if (preg_match('/360ee/i', $this->userAgent)) $this->info['browser'] = '360ee';
            if (preg_match('/360browser/i', $this->userAgent)) $this->info['browser'] = '360browser';
        }
        //uc
        if (preg_match('/ucbrowser/i', $this->userAgent)) {
            preg_match('/ucbrowser\/+(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'ucbrowser';
        }
        //sougoubrowser
        if (preg_match('/metasr/i', $this->userAgent)) {
            preg_match('/metasr\s+(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'sougoubrowser';
        }
        //lbbrowser
        if (preg_match('/lbbrowser/i', $this->userAgent)) {
            preg_match('/lbbrowser\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'lbbrowser';
        }
        //qq内置
        if (preg_match('/qqbrowser/i', $this->userAgent)) {
            preg_match('/qqbrowser\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'qqbrowser';
        }
        //微信内置
        if (preg_match('/micromessenger/i', $this->userAgent)) {
            preg_match('/micromessenger\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'micromessenger';
        }
        //淘宝内置
        if (preg_match('/taobrowser/i', $this->userAgent)) {
            preg_match('/taobrowser\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'taobrowser';
        }
        //theworld 世界之窗
        if (preg_match('/theworld/i', $this->userAgent)) {
            preg_match('/theworld\s+(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'theworld';
        }
        //傲游
        if (preg_match('/maxthon/i', $this->userAgent)) {
            preg_match('/maxthon\/(\d+\.?\d*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'maxthon';
        }
        //百度浏览器
        if (preg_match('/bidubrowser/i', $this->userAgent)) {
            preg_match('/bidubrowser\/(\d+\.?\w*)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'bidubrowser';
        }
        //小米内置浏览器
        if (preg_match('/miuibrowser/i', $this->userAgent)) {
            preg_match('/miuibrowser\/([0-9\.\s]+)/i', $this->userAgent, $v);
            $this->info['browser_version'] = $v[1];
            $this->info['browser'] = 'miuibrowser';
        }
    }

    private function parseOs()
    {
        if (isset ($this->server['HTTP_X_WAP_PROFILE']))
            $this->info['device_type'] = 'mobile';
        if (isset ($this->server['HTTP_CLIENT']) && 'PhoneClient' == $this->server['HTTP_CLIENT'])
            $this->info['device_type'] = 'mobile';
        if (isset ($this->server['HTTP_VIA']) && stristr($this->server['HTTP_VIA'], 'wap'))
            $this->info['device_type'] = 'mobile';
        $v = array();
        $v2 = array();
        $this->info['is_apple'] = false;
        if (preg_match('/linux/i', $this->userAgent)) {
            preg_match('/linux\s?(\w*)/i', $this->userAgent, $v);
            $this->info['os_version'] = $v[1];
            $this->info['os'] = 'linux';
        }
        //unix
        if (preg_match('/unix/i', $this->userAgent) || preg_match('/x11/i', $this->userAgent)) {
            preg_match('/unix\s?(\w*)/i', $this->userAgent, $v);
            $this->info['os_version'] = $v[1];
            $this->info['os'] = 'unix';
        }
        //mother
        $other = array("nokia", "untrusted\/1\.0", "symbianos", "symbian", "phone", "maui", "windows ce", "blackberry", 'mobile', 'webos');
        for ($i = 0; $i < count($other); $i++) {
            if (preg_match('/' . $other[$i] . '/i', $this->userAgent)) {
                $v = array();
                preg_match('/' . $other[$i] . '\s?(\w*)/i', $this->userAgent, $v);
                $this->info['os_version'] = $v[1];
                $this->info['os'] = 'mother';
            }
        }
        //android
        if (preg_match('/android/i', $this->userAgent)) {
            preg_match('/android\s+(\d+\.?\d*)/i', $this->userAgent, $v);
            preg_match('/android\/+(\d+\.?\d*)/i', $this->userAgent, $v2);
            $v = empty($v) ? $v2 : $v;
            $this->info['os_version'] = $v[1];
            $this->info['os'] = 'android';
            $this->info['device_type'] = 'mobile';
        }
        //windows phone
        if (preg_match('/windows\s+phone\s*os/i', $this->userAgent)) {
            preg_match('/windows\s+phone\s*os\s+(\d+\.*\d*)/i', $this->userAgent, $v);
            $this->info['os_version'] = $v[1];
            $this->info['os'] = 'windows phone';
            $this->info['device_type'] = 'mobile';
        }
        //mac
        if (preg_match('/mac/i', $this->userAgent)) {
            preg_match('/mac os\s+[a-z]*\s*(\d+_*\d*_*\d*)/i', $this->userAgent, $v);
            $this->info['os_version'] = $v[1];
            $this->info['os'] = 'mac';
            $this->info['is_apple'] = true;
            $this->info['device_type'] = 'pc';
        }
        //iphone+ipod
        if (preg_match('/iphone/i', $this->userAgent) || preg_match('/ipod/i', $this->userAgent)) {
            preg_match('/iphone\s+os\s+(\w+_*\w*_*\w*)/i', $this->userAgent, $v);
            $this->info['os_version'] = $v[1];
            $this->info['os'] = 'ios';
            $this->info['is_apple'] = true;
            $this->info['device_type'] = 'mobile';
        }
        //ipad
        if (preg_match('/ipad/i', $this->userAgent)) {
            preg_match('/os\s+(\w+_*\w*_*\w*)/i', $this->userAgent, $v);
            $this->info['os_version'] = $v[1];
            $this->info['os'] = 'ios';
            $this->info['is_apple'] = true;
            $this->info['device_type'] = 'ipad';
        }
        //windows
        if (preg_match('/win32/i', $this->userAgent) || preg_match('/win64/i', $this->userAgent) || preg_match('/windows/i', $this->userAgent)) {
            if (preg_match('/windows 98/i', $this->userAgent)) $this->info['os_version'] = "98";
            if (preg_match('/windows nt 5.0/i', $this->userAgent)) $this->info['os_version'] = "2000";
            if (preg_match('/windows nt 5.1/i', $this->userAgent)) $this->info['os_version'] = "xp";
            if (preg_match('/windows nt 5.2/i', $this->userAgent)) $this->info['os_version'] = "2003";
            if (preg_match('/windows nt 6.0/i', $this->userAgent)) $this->info['os_version'] = "vista";
            if (preg_match('/windows nt 6.1/i', $this->userAgent)) $this->info['os_version'] = "7";
            if (preg_match('/windows nt 6.2/i', $this->userAgent)) $this->info['os_version'] = "8";
            if (preg_match('/windows nt 6.3/i', $this->userAgent)) $this->info['os_version'] = "8.1";
            if (preg_match('/windows nt 10/i', $this->userAgent)) $this->info['os_version'] = "10";
            $this->info['os'] = 'windows';
            $this->info['device_type'] = 'pc';
        }
    }
}

?>