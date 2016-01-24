<?php
/*
 * 数据流的方式处理文件裁图
 * 
 * 
 */


namespace mysoft\image;

/**
 * image for Stream
 *
 * @author yangzhen
 */
class Stream extends ImageAbs
{
      /**
     * 二进制数据流源
     * @var string
     */
    private $_StreamData;
    
    private $_mimetype;
    
    private $image;


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
         
         if(!in_array($this->_mimetype, ["jpeg","gif","png","jpg","bmp","webp"])){
             throw new \yii\base\NotSupportedException("Unsupported file type");
         }
        
         $this->getImageInfo();
    }
    

    //获取原始图片信息
    public function getImageInfo()
    {
         $this->image  = imagecreatefromstring($this->_StreamData);
         $this->width  = imagesx($this->image); //原图width
         $this->height = imagesy($this->image); //原图height
    }
    
    
    //绘制缩略图
    public function making($dst_w,$dst_h)
    {
         $thumb_img = imagecreatetruecolor($dst_w,$dst_h);
         imagecopyresampled($thumb_img,$this->image,0,0,0,0,$dst_w,$dst_h,$this->width,$this->height);

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
                imagepng($thumb_img,null,9);
                break;
            case "webp":
                imagewebp($thumb_img,null,100);
                break;
            default:
                throw new \yii\base\NotSupportedException("Unsupport imageX method!");
                break;
        }
         
         imagedestroy($this->image);   //destory origin
         
         return ob_get_clean(); //返回二进制数据流
         
    }


    
}
