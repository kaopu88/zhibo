<?php

namespace app\taoke\service;

class CreateImages
{
    /**
     *生成商品分享图片
     *@param  $image 商品图片
     *@param  $title 商品标题
     *@param  $discountPrice 商品券后价
     *@param  $price 商品原价
     *@param  $coupon 商品券额
     *@param  $type  商品类型  B天猫 C淘宝 P拼多多 J京东
     *@param  $url   商品跳转路径
     *@param  $code   邀请码
     */
    public function generatedGoodsImage($image, $title, $discountPrice, $price, $coupon, $type, $url, $code=''){
        $fontFile = ROOT_PATH  . 'public/bx_static/taoke/img/good/font.ttf';
        $bgImg = ROOT_PATH . 'public/bx_static/taoke/img/good/bg.png';
        $textImg = ROOT_PATH . 'public/bx_static/taoke/img/good/text.png';
        $couponImg = ROOT_PATH . 'public/bx_static/taoke/img/good/money.png';
        switch ($type) {
            case 'B':
                $shopImg = ROOT_PATH . 'public/bx_static/taoke/img/good/tmall.png';
                break;
            case 'C':
                $shopImg = ROOT_PATH . 'public/bx_static/taoke/img/good/taobao.png';
                break;
            case 'P':
                $shopImg = ROOT_PATH . 'public/bx_static/taoke/img/good/pinduoduo.png';
                break;
            case 'J':
                $shopImg = ROOT_PATH . 'public/bx_static/taoke/img/good/jingdong.png';
                break;
            default:
                $shopImg = ROOT_PATH . 'public/bx_static/taoke/img/good/taobao.png';
                break;
        }
        $url = urldecode($url);
        $url = htmlspecialchars_decode($url);
        $qrCode = DOMAIN_URL . '/api/taoke.share/createQrcode?content=' . urlencode($url) . '&size=255';
        if(mb_strlen($title) > 35) {
            $title = mb_substr($title, 0, 35) . "...";
        }
        $config = $this->config1($fontFile, $title, $discountPrice, $price, $coupon, $qrCode, $image, $shopImg, $textImg, $couponImg, $bgImg);
        return $this->createPoster($config);
    }


    /**
     *分享类型1
     *@param $fontFile 字体文件
     *@param $title    标题
     *@param $discountPrice 券后价
     *@param $price    商品原价
     *@param $coupon   券额
     *@param $qrCode   二维码资源
     *@param $image    商品图片
     *@param $shopImg 商品类型图标
     *@param $bgImg 商品背景图
     */
    public function config1($fontFile, $title, $discountPrice, $price, $coupon, $qrCode, $image, $shopImg, $textImg, $couponImg, $bgImg){
        $medFont = ROOT_PATH . "public/bx_static/taoke/img/good/pingfang-sc-medium.otf";
        $semFont = ROOT_PATH . "public/bx_static/taoke/img/good/PingFang-SC-Semibold.ttf";
        $regFont = ROOT_PATH . "public/bx_static/taoke/img/good/PingFang-SC-Regular.otf";
        $config = [
            'text'=>[
                [
                    'text'      => $this->autowrap(40, $medFont, "    ".$title, 890),
                    'left'      => 69,
                    'top'       => 171,
                    'fontPath'  => $medFont, //字体文件
                    'fontSize'  => 40, //字号
                    'fontColor' => '#282828', //字体颜色
                    'angle'     => 0,
                ],
                [
                    'text'      => $this->autowrap(26, $regFont, "￥", 42),
                    'left'      => 276,
                    'top'       => 1461,
                    'fontPath'  => $regFont, //字体文件
                    'fontSize'  => 42, //字号
                    'fontColor' => '#ff464e', //字体颜色
                    'angle'     => 0,
                ],
                [
                    'text'      => $this->autowrap(26, $semFont, floatval($discountPrice), 249),
                    'left'      => 328,
                    'top'       => 1461,
                    'fontPath'  => $semFont, //字体文件
                    'fontSize'  => 63, //字号
                    'fontColor' => '#ff464e', //字体颜色
                    'angle'     => 0,
                ],
                [
                    'text'      => $this->autowrap(42, $regFont, "￥".floatval($price), 249),
                    'left'      => 78,
                    'top'       => 1368,
                    'fontPath'  => $regFont, //字体文件
                    'fontSize'  => 42, //字号
                    'fontColor' => '#8C8C8C', //字体颜色
                    'angle'     => 0,
                    'is_line'   => 1,
                ],
                [
                    'text'      => $this->autowrap(26, $regFont, "券后价", 126),
                    'left'      => 78,
                    'top'       => 1455,
                    'fontPath'  => $regFont,
                    'fontSize'  => 42,
                    'fontColor' => '#8C8C8C',
                    'angle'     => 0,
                ],
            ],
            'image'      => [
                [
                    'url'     => $qrCode, //二维码资源
                    'stream'  => 0,
                    'left'    => 798,
                    'top'     => 1626,
                    'right'   => 0,
                    'bottom'  => 0,
                    'width'   => 204,
                    'height'  => 204,
                    'opacity' => 100,
                ],
                [
                    'url'     => $image,
                    'stream'  => 0,
                    'left'    => 81,
                    'top'     => 309,
                    'right'   => 0,
                    'bottom'  => 0,//距离下面的距离
                    'width'   => 966,//宽度
                    'height'  => 966,//高度
                    'opacity' => 100,
                ],
                [
                    'url'     => $shopImg,
                    'stream'  => 0,
                    'left'    => 78,
                    'top'     => 129,
                    'right'   => 0,
                    'bottom'  => 0,//距离下面的距离
                    'width'   => 43.2,//宽度
                    'height'  => 43.2,//高度
                    'opacity' => 100,
                ],
                [//文字图片
                    'url'     => $textImg,
                    'stream'  => 0,
                    'left'    => 213,
                    'top'     => 1671,
                    'right'   => 0,
                    'bottom'  => 0,//距离下面的距离
                    'width'   => 543,//宽度
                    'height'  => 144,//高度
                    'opacity' => 100,
                ],
                [//优惠券图片
                    'url'     => $couponImg,
                    'stream'  => 0,
                    'left'    => 780,
                    'top'     => 1338,
                    'right'   => 0,
                    'bottom'  => 0,//距离下面的距离
                    'width'   => 267,//宽度
                    'height'  => 108,//高度
                    'opacity' => 100,
                ],

            ],
            'background_image' => $bgImg, //背景图
        ];

        if($coupon >= 0 && $coupon < 10){
            $text = $this->autowrap(36, $regFont, "￥".floatval($coupon), 182);
            $couponConfig = $this->setCouponConfig($text, 810, 1418, 54, $regFont);

        }elseif ($coupon >= 10 && $coupon < 100){
            $text = $this->autowrap(36, $regFont, "￥".floatval($coupon), 182);
            $couponConfig = $this->setCouponConfig($text, 800, 1418, 48, $regFont);

        }elseif ($coupon >= 100 && $coupon < 1000){
            $text = $this->autowrap(36, $regFont, "￥".floatval($coupon), 182);
            $couponConfig = $this->setCouponConfig($text, 795, 1413, 42, $regFont);

        }elseif ($coupon >= 1000){
            $text = $this->autowrap(36, $regFont, "￥".floatval($coupon), 182);
            $couponConfig = $this->setCouponConfig($text, 790, 1408, 36, $regFont);

        }
        array_push($config['text'], $couponConfig);

        return $config;
    }

    protected function setCouponConfig($text, $left, $top, $fontSize, $regFont)
    {
        $config = [
            'text'      => $text,
            'left'      => $left,
            'top'       => $top,
            'fontPath'  => $regFont,
            'fontSize'  => $fontSize,
            'fontColor' => '#F8F8F8',
            'angle'     => 0,
        ];
        return $config;
    }

    private $imageDefault = [
        'left'    => 0,
        'top'     => 0,
        'right'   => 0,
        'bottom'  => 0,
        'width'   => 100,
        'height'  => 100,
        'opacity' => 100,
    ];

    private $textDefault = [
        'text'      => '',
        'left'      => 0,
        'top'       => 0,
        'fontSize'  => 32,
        'fontColor' => '#333',
        'angle'     => 0,
    ];

    /**
     * @param $$config   array 合成图片相关信息
     * @param $filename  string 字符编码
     * @return $match   返回一个字符的数组
     */
    public function createPoster($config = [], $filename = "")
    {
        ob_clean();
        $background       = $config['background_image'];
        $backgroundInfo   = getimagesize($background);
        $backgroundFun    = 'imagecreatefrom' . image_type_to_extension($backgroundInfo[2], false);
        $background       = $backgroundFun($background);
        $backgroundWidth  = imagesx($background); //背景宽度
        $backgroundHeight = imagesy($background); //背景高度
        $imageRes         = imageCreatetruecolor($backgroundWidth, $backgroundHeight);
        $color            = imagecolorallocatealpha($imageRes, 0, 0, 0,127);
        imagefill($imageRes, 0, 0, $color);
        imageColorTransparent($imageRes, $color);  //颜色透明
        imagecopyresampled($imageRes, $background, 0, 0, 0, 0, imagesx($background), imagesy($background), imagesx($background), imagesy($background));
        if (!empty($config['image'])) {
            foreach ($config['image'] as $key => $val) {
                $val      = array_merge($this->imageDefault, $val);
                $info     = getimagesize($val['url']);
                $function = 'imagecreatefrom' . image_type_to_extension($info[2], false);
                if ($val['stream']) {
                    $info     = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                }
                $res       = $function($val['url']);
                $resWidth  = $info[0];
                $resHeight = $info[1];
                //建立画板 ，缩放图片至指定尺寸
                $canvas = imagecreatetruecolor($val['width'], $val['height']);
                imagefill($canvas, 0, 0, $color);
                //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'], $resWidth, $resHeight);
                $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) - $val['width'] : $val['left'];
                $val['top']  = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) - $val['height'] : $val['top'];
                //放置图像
                imagecopy($imageRes, $canvas, $val['left'], $val['top'], $val['right'], $val['bottom'], $val['width'], $val['height']); //左，上，右，下，宽度，高度，透明度
            }
        }
        //处理文字
        if (!empty($config['text'])) {
            foreach ($config['text'] as $key => $val) {
                $val = array_merge($this->textDefault, $val);
                $rbg = $this->hexToRgb($val['fontColor']);
                if(is_array($rbg) && $rbg){
                    $R = $rbg['red'];
                    $G = $rbg['green'];
                    $B = $rbg['blue'];
                }else{
                    list($R, $G, $B) = explode(',', $val['fontColor']);
                }
                $fontColor   = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left'] = $val['left'] < 0 ? $backgroundWidth - abs($val['left']) : $val['left'];
                $val['top']  = $val['top'] < 0 ? $backgroundHeight - abs($val['top']) : $val['top'];
                imagettftext($imageRes, $val['fontSize'], $val['angle'], $val['left'], $val['top'], $fontColor, $val['fontPath'], $val['text']);

                if($val['is_line'] == 1){
                    $arr = imagettfbbox($val['fontSize'], $val['angle'], $val['fontPath'], $val['text']);
                    $width = $arr[4] - $arr[6];
                    imageline($imageRes, $val['left']+10, ($val['top']-$val['fontSize']/2), ($val['left'] + $width)+10, ($val['top']-$val['fontSize']/2), $fontColor);//画一条直线
                    imageline($imageRes, $val['left']+10, ($val['top']-$val['fontSize']/2)+2, ($val['left'] + $width)+10, ($val['top']-$val['fontSize']/2)+2, $fontColor);//画一条直线
                    imageline($imageRes, $val['left']+10, ($val['top']-$val['fontSize']/2)+1, ($val['left'] + $width)+10, ($val['top']-$val['fontSize']/2)+1, $fontColor);//画一条直线
                }
            }
        }

        //生成图片
        if (!empty($filename)) {
            $filePath = ROOT_PATH . $filename;
            $path = dirname($filePath);
            mk_dir($path);
            $res = imagepng($imageRes, $filePath, 90);
            imagedestroy($imageRes);
            if (!$res) {
                return false;
            }
            halt($filename);
        } else {
            header("content-type: image/png");
            imagepng($imageRes);
            imagedestroy($imageRes);
            die();
        }
    }

    /**
     * 根据预设宽度让文字自动换行
     * @param $fontsize   字体大小
     * @param $ttfpath    字体名称
     * @param $str    字符串
     * @param $width    预设宽度
     * @param $fontangle  角度
     * @param $charset    编码
     * @return $_string  字符串
     */
    public function autowrap($fontsize, $ttfpath, $str, $width, $fontangle=0)
    {
        $_string = "";
        $_width  = 0;
        $temp    = $this->chararray($str);
        foreach ($temp[0] as $v) {
            $w = $this->charwidth($fontsize, $fontangle, $ttfpath, $v);
            $_width += intval($w);
            if (($_width > $width) && ($v !== "")) {
                $_string .= PHP_EOL;
                $_width = 0;
            }
            $_string .= $v;
        }
        return $_string;
    }

    /**
     * 返回一个字符的数组
     * @param $str
     * @param string $charset
     * @return mixed
     */
    protected function charArray($str, $charset = "utf-8")
    {
        $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        return $match;
    }

    /**
     * 返回一个字符串在图片中所占的宽度
     * @param $fontsize字体大小
     * @param $fontangle角度
     * @param $ttfpath字体文件
     * @param $char字符
     * @return $width
     */
    protected function charwidth($fontsize, $fontangle, $ttfpath, $char)
    {
        $box   = @imagettfbbox($fontsize, $fontangle, $ttfpath, $char);
        $width = max($box[2], $box[4]) - min($box[0], $box[6]);
        return $width;
    }

    /**
     * @param $colour   sting   hex颜色值
     * @return 返回一个字符的数组
     */
    public function hexToRgb($colour)
    {
        if ($colour[0] == '#') {
            $colour = substr($colour, 1);
        }
        if (strlen($colour) == 6) {
            list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
        } elseif (strlen($colour) == 3) {
            list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
        } else {
            return false;
        }
        $r = hexdec($r);
        $g = hexdec($g);
        $b = hexdec($b);
        return array('red' => $r, 'green' => $g, 'blue' => $b);
    }
}