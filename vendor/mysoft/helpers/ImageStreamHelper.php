<?php

/*
 * 数据流的方式处理文件裁图
 * 
 * 
 */

namespace mysoft\helpers;


/**
 * ImageStreamHelper
 *
 * @author yangzhen
 */
class ImageStreamHelper 
{
    
    /**
     * 二进制数据流源
     * @var string
     */
    private $_StreamData;
    
    private $_mimetype;


    /**
     * 
     * @param type $data , 二进制数据流
     * @param type $minetype ，数据类型
     * @param type $imgurl  ,请求图片地址，当data =false,mimetype=false时候启用
     */
    public function __construct($data,$mimetype,$imgurl='') {
        
         $this->_StreamData = $data;
         $this->_mimetype   = $mimetype;
         
         $this->_mimetype = str_replace("image/", "", $this->_mimetype);//兼容"image/png" 与 "png"
         
         if(!in_array($this->_mimetype, ["jpeg","gif","png","jpg","bmp"])){
             throw new yii\base\NotSupportedException("Unsupported file type");
         }
        
    }
    

    /**
     * 按比例压缩图片
     * @param int $percent
     * @return string  二进制数据流
     */
    public function compress($percent)
    {
         $image  = imagecreatefromstring($this->_StreamData);
         $width  = imagesx($image); //原图width
         $height = imagesy($image); //原图height
         
         $t_w = $percent * $width; //缩略图width
         $t_h = $percent * $height;//缩略图height
         
         $thumb_img = imagecreatetruecolor($t_w,$t_h);
         imagecopyresampled($thumb_img,$image,0,0,0,0,$t_w,$t_h,$width,$height);

         ob_start();
         
          switch ($this->_mimetype) {
            case "gd2":
                imagegd2($thumb_img,null,100);
                break;
            case "gd":
                imagegd($thumb_img,null,100);
                break;
            case "gif":
                imagegif($thumb_img,null,100);
                break;
            case "jpeg":
                imagejpeg($thumb_img,null,100);
                break;
            case "jpg":
                imagejpeg($thumb_img,null,100);
                break;
            case "png":
                imagepng($thumb_img,null,100);
                break;
            default:
                throw new \yii\base\NotSupportedException("Unsupport imageX method!");
                break;
        }
         
         imagedestroy($image);   //destory origin
         
         return ob_get_clean(); //返回二进制数据流
         
         
    }
    
    
        
}
