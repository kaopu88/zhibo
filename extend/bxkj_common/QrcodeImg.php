<?php

namespace bxkj_common;

if (!class_exists('\QRcode', false)) {
    require ROOT_PATH . 'extend/phpqrcode/phpqrcode.php';
}

use \QRcode as QRcode;

class QrcodeImg
{
    public function generate($content, $filename, $logoUrl = '')
    {
        $errorCorrectionLevel = 'H';//容错级别
        $matrixPointSize = 4.45;//生成图片大小
        //生成二维码图片
        QRcode::png($content, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        if ($logoUrl) {
            $qrcodeFile = preg_match('/^http|https/', $filename) ? $this->download($filename) : file_get_contents($filename);
            $qrcode = imagecreatefromstring($qrcodeFile);
            //$qrcode = $this->openImage($filename);
            $logoFile = preg_match('/^http|https/', $logoUrl) ? $this->download($logoUrl) : file_get_contents($logoUrl);
            $logo = imagecreatefromstring($logoFile);
            //$logo = $this->openImage($logoUrl);
            imageantialias($qrcode, true);
            imageantialias($logo, true);
            $qrcodeWidth = imagesx($qrcode);
            $qrcodeHeight = imagesy($qrcode);
            $logoWidth = imagesx($logo);
            $logoHeight = imagesy($logo);
            $this->rounder($logo, 0.2);
            $logoQrWidth = $qrcodeWidth / 4;     //组合之后logo的宽度(占二维码的1/5)
            $scale = $logoWidth / $logoQrWidth;    //logo的宽度缩放比(本身宽度/组合后的宽度)
            $logoQrHeight = $logoHeight / $scale;  //组合之后logo的高度
            $from_width = ($qrcodeWidth - $logoQrWidth) / 2;   //组合之后logo左上角所在坐标点
            imagecopyresampled($qrcode, $logo, $from_width, $from_width, 0, 0, $logoQrWidth, $logoQrHeight, $logoWidth, $logoHeight);
            imagepng($qrcode, $filename, 1);
            imagedestroy($logo);
            imagedestroy($qrcode);
        }
        return true;
    }

    protected function openImage($src)
    {
        list($width, $height, $type, $attr) = getimagesize($src);
        $imageinfo = array(
            'width' => $width,
            'height' => $height,
            'type' => image_type_to_extension($type, false),
            'attr' => $attr
        );
        $fun = "imagecreatefrom" . $imageinfo['type'];
        $image = $fun($src);
        return $image;
    }

    protected function deal($src_img, $src_w, $src_h, $des_w, $des_h)
    {
        $scale_w = $src_w / $des_w;   //获取真实宽度与目标宽度的比例
        $scale_h = $src_h / $des_h;   //获取真实高度与目标高度的比例
        if ($src_w <= $des_w && $src_h <= $des_h) {
            $true_w = $src_w;
            $true_h = $src_h;
            $des_img = imagecreatetruecolor($true_w, $true_h);
            //若scale_w > scale_h ,即原图片的宽大于高，横向图片,依据宽为基准
        } elseif ($scale_w >= $scale_h) {
            $true_w = $src_w / $scale_w;
            $true_h = $src_h / $scale_w;
            $des_img = imagecreatetruecolor($true_w, $true_h);
            //否则就是原图片的高大于宽，竖向图片,则依据高为基准来缩放
        } else {
            $true_w = $src_w / $scale_h;
            $true_h = $src_h / $scale_h;
            $des_img = imagecreatetruecolor($true_w, $true_h);
        }
        imagecopyresampled($des_img, $src_img, 0, 0, 0, 0, $true_w, $true_h, $src_w, $src_h);
        return $des_img;
    }

    protected function rounder(&$resource, $scale)
    {
        $image_width = imagesx($resource);
        $image_height = imagesy($resource);
        $radius = round($image_width * $scale);
        // lt(左上角)
        $ltCorner = $this->getLtRounderCorner($radius);
        imagecopymerge($resource, $ltCorner, 0, 0, 0, 0, $radius, $radius, 100);
        // lb(左下角)
        $lb_corner = imagerotate($ltCorner, 90, 0);
        imagecopymerge($resource, $lb_corner, 0, $image_height - $radius, 0, 0, $radius, $radius, 100);
        // rb(右上角)
        $rb_corner = imagerotate($ltCorner, 180, 0);
        imagecopymerge($resource, $rb_corner, $image_width - $radius, $image_height - $radius, 0, 0, $radius, $radius, 100);
        // rt(右下角)
        $rt_corner = imagerotate($ltCorner, 270, 0);
        imagecopymerge($resource, $rt_corner, $image_width - $radius, 0, 0, 0, $radius, $radius, 100);
    }

    protected function getLtRounderCorner($radius)
    {
        $img = imagecreatetruecolor($radius, $radius);  // 创建一个正方形的图像
        imageantialias($img, true);
        $bgcolor = imagecolorallocate($img, 255, 255, 255);   // 图像的背景
        $fgcolor = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $bgcolor);
        imagefilledarc($img, $radius, $radius, $radius * 2, $radius * 2, 180, 270, $fgcolor, IMG_ARC_PIE);
        imagecolortransparent($img, $fgcolor);
        return $img;
    }

    protected function download($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        return $file;
    }
}