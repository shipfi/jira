<?php

/*
 * 本地上传模式
 * To change this template file, choose Tools | Templates
 * 
 */

namespace mysoft\upload;

use yii\helpers\Url;

/**
 * Description of LocalFile
 *
 * @author yangzhen
 */
class LocalFile extends UploadAbs 
{
    
    protected $root;


    public function init() {
        parent::init();
        
        if ($this->rootDirName)
            $this->root = $this->rootDirName;
        
     }
    
    //建立需要的文件夹
    protected  function mkdir($allpath)
    {
        $dirs = explode('/',$allpath);

        $path = \Yii::getAlias('@webroot');
        
        foreach($dirs as $dir){
            $path .= '/'.$dir;
           
            if(!file_exists($path) || !is_dir($path)){
                 mkdir($path,0777);
            }           
        }
        
       
  
    }


    /**
     * 本地写文件形式
     * @param string $source
     * @param string $object
     * @return type
     */
    public function uploadFile($source, $object) {
          $allpath = 'uploads/'.$this->root.'/'.$object;          
          $this->mkdir(dirname($allpath));
          $target = rtrim(\Yii::getAlias('@webroot'),'/').'/'.$allpath;
          copy($source, $target);
          
          $webpath = Url::to('@web/uploads/'.$this->root.'/'.$object);
          
          if($this->percent >0)//缩略图
          {
             list($file,$ext) = explode('.',$target);
             if(in_array($ext,['png','jpg','jpeg','bmp','gif'])){
                 
                $thumb = $file.'_thumb.'.$ext;
//                \mysoft\helpers\Image::thumb($target, $thumb,$this->t_w,$this->t_h); 
                
                $img  = new \mysoft\helpers\ImageHelper($webpath,$thumb);
                $img->compress($this->percent);
                
                $this->thumb_img = str_replace(".{$ext}", "_thumb.{$ext}", $webpath);//替换标识
                $this->useThumb(false);//生成缩略图，立刻reset数据
                
             }
             
              
          }
          
          
//           return $this->_getURI('uploads/'.$this->root.'/'.$object);
          //fix，如果带端口，带路径的这种URI无效
          return $webpath;
    }
    
    /**
     * 拼接URI地址
     * @param type $path
     * @return typea
     */
    private function _getURI($path)
    {
        return 'http://'.$_SERVER['HTTP_HOST'].'/'.$path;
    }


    /**
     * 获取当前根目录名
     * @return string
     */
    public function getRoot() {
        return $this->root;
    }
    
}
