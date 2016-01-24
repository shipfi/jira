<?php
/**
 * 与图片文件相关的处理类
 * 只支持[gd2,gd,gif,jpg,jpeg,png]格式类型的相互转换,使用方法:
 *  $image=new ImageHelper('a.png','a_compress.png')\r\n
 *  $status=$image->compress();//成功返回true,失败返回false
 * @author gongz <gongz@mysoft.com.cn>
 */

namespace mysoft\helpers;

class ImageHelper {

    /**
     * @var string $scr_img 带路径的原始图片的名称
     * @var string $dst_img 带路径的输出图片的名称
     * @var string $src_img_ext 原始文件的扩展名
     * @var string $dst_img_ext 目标文件的扩展名
     */
    public $src_img, $dst_img, $src_img_ext, $dst_img_ext;

    public function __construct($scr_img, $dst_img) {
        if (!extension_loaded('gd')) {
            throw new \Exception('请安装gd扩展');
        }
        if (file_exists($this->src_img)) {
            throw new \Exception('未找到源文件');
        }
        $this->src_img = $scr_img;
        $this->dst_img = $dst_img;
        $this->src_img_ext = pathinfo($this->src_img, PATHINFO_EXTENSION);
        $this->dst_img_ext = pathinfo($this->dst_img, PATHINFO_EXTENSION);
        if (!file_exists(pathinfo($this->dst_img, PATHINFO_DIRNAME))) {
            mkdir(pathinfo($this->dst_img, PATHINFO_DIRNAME), 0777, TRUE);
        }
    }

    /**
     * 获取图片的宽度和高度、以及,mime类型
     * @return boolean/array 成功返回数组/失败返回false
     */
    public function getImageInfo() {
        return getimagesize($this->src_img);
    }

    /**
     * 对一个大图片，按照一定比例进行压缩
     * @param float $percent 压缩比例，大于等于0的一个浮点数，默认等于1
     * @since 2015年8月14日 13:35:55
     * @return string 压缩成功返回true失败返回fasle
     */
    public function compress($percent = 1) {
        list($width, $height) = $this->getImageInfo();
        /* 目标图片的宽度和高度计算 */
        $dst_w = $width * $percent;
        $dst_h = $height * $percent;
        $src_im = $this->_from($this->src_img);
        if ($src_im === FALSE) {
            return FALSE;
        }
        $dst_im = imagecreatetruecolor($dst_w, $dst_h);
        imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $dst_w, $dst_h, $width, $height);
        $this->_to($dst_im);
        imagedestroy($src_im);
        imagedestroy($dst_im);
        if (file_exists($this->dst_img)) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 根据原始文件的扩展名，返回从原始文件创建的一个画布
     * @return resource 返回从原始文件取得的一个图像
     */
    private function _from() {
        switch ($this->src_img_ext) {
            case "gd2":
                return imagecreatefromgd2($this->src_img);
            case "gd":
                return imagecreatefromgd($this->src_img);
            case "gif":
                return imagecreatefromgif($this->src_img);
            case "jpeg":
                return imagecreatefromjpeg($this->src_img);
            case "jpg":
                return imagecreatefromjpeg($this->src_img);
            case "png":
                return imagecreatefrompng($this->src_img);
            default:
                return FALSE;
        }
    }

    /**
     * 将当前画布存放到文件中
     * @param resource $dst_im 目标文件的当前画布
     * @return resource 生成一个图片文件
     */
    public function _to($dst_im) {
        switch ($this->dst_img_ext) {
            case "gd2":
                imagegd2($dst_im, $this->dst_img);
                break;
            case "gd":
                imagegd($dst_im, $this->dst_img);
                break;
            case "gif":
                imagegif($dst_im, $this->dst_img);
                break;
            case "jpeg":
                imagejpeg($dst_im, $this->dst_img);
                break;
            case "jpg":
                imagejpeg($dst_im, $this->dst_img);
                break;
            case "png":
                imagepng($dst_im, $this->dst_img);
                break;
            default:
                break;
        }
        if (file_exists($this->dst_img)) {
            return TRUE;
        }
        return FALSE;
    }

}
