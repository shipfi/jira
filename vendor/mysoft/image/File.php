<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\image;

/**
 * Description of File
 *
 * @author yangzhen
 */
class File extends ImageAbs
{
      /**
     * @var string $scr_img 带路径的原始图片的名称
     * @var string $dst_img 带路径的输出图片的名称
     * @var string $src_img_ext 原始文件的扩展名
     * @var string $dst_img_ext 目标文件的扩展名
     */
    public $src_img, $dst_img, $src_img_ext, $dst_img_ext;
    

    public function __construct($scr_img, $dst_img) {
        parent::__construct();
        
        if (file_exists($this->src_img)) {
            throw new \Exception('未找到源文件');
        }
        $this->src_img = $scr_img;
        $this->dst_img = $dst_img;
        $this->src_img_ext = pathinfo($this->src_img, PATHINFO_EXTENSION);
        $this->dst_img_ext = pathinfo($this->dst_img, PATHINFO_EXTENSION);

        
        $this->getImageInfo();
        
    }

    /**
     * 获取图片的宽度和高度、以及,mime类型
     *
     */
    public function getImageInfo() {
        
         list($this->width,$this->height) = getimagesize($this->src_img);
    }

    
    //制作画图
    public function making($dst_w,$dst_h)
    {
        $src_im = $this->_from($this->src_img);
        if ($src_im === FALSE) {
            return FALSE;
        }
        $dst_im = imagecreatetruecolor($dst_w, $dst_h);
        imagecopyresampled($dst_im, $src_im, 0, 0, 0, 0, $dst_w, $dst_h, $this->width, $this->height);
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
