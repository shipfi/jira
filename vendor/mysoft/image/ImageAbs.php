<?php
/*
 * 图片处理基础类
 * 
 * 
 */

namespace mysoft\image;

/**
 * 图片处理父类 ImageAbs
 *
 * @author yangzhen
 */
abstract class ImageAbs 
{
    /**
     * 原图宽度
     * @var type 
     */
    public $width;
    
    /**
     * 原图高度
     * @var type 
     */
    public $height;
    
    
    public function __construct() {
        
       if (!extension_loaded('gd')) {
           throw new \Exception('请安装gd扩展');
       }
       
    }
    
    //获取图片信息
    abstract public function getImageInfo();
            
    //绘制缩略图
    abstract public function making($dst_w,$dst_h);
    
    
        
    //入口函数
    public function compress($type,$params)
    {
        $result = false;
        
        switch((string)$type){
            case 'p':
                $result = $this->compressByFixedPercent($params);
                break;
            case 'l':
                $result = $this->compressByFixedLen($params);
                break;
            default :
                throw new \Exception("不存在的压缩类型");
        }
        
        return $result;
    }




    /**
     * 指定固定比例压缩
     * @param type $percent
     * @return type
     */
    public function compressByFixedPercent( $percent = 0.2 ) {
        
        if($percent == 1) return false;
        
        
        /* 目标图片的宽度和高度计算 */
        $dst_w = $this->width  * $percent;
        $dst_h = $this->height * $percent;
           
        
        return $this->making($dst_w, $dst_h);
    }

    
    /**
     * 通过固定长度算压缩比例
     * @param type $len
     * @return boolean
     */
    public function compressByFixedLen($len)
    {
        $percent = 1;
        
        if( $this->width > $len && $this->height > $len )//超过len
        {
            if($this->width >= $this->height){
                
                $percent = round($len/$this->height,4);
                
            }else{
                
                $percent = round($len/$this->width,4);
                           
            }
            
            
        }
        
        if( $percent == 1 ) return false;
        
        return $this->compressByFixedPercent($percent);
        
    }
    
}
