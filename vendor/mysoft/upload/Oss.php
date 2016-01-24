<?php

/**
 * 上传组件:阿里OSS文件服务<构造函数在Object>
 * 关联 mysoft\upload\oss package
 * 当前组件的全局配置在父类里查看
 * 
 * @author yangzhen
 *
 */

namespace mysoft\upload;

use mysoft\pubservice\Conf;

require __DIR__ . '/oss/sdk.class.php';

class Oss extends UploadAbs {

    private $oss;
    private $bucket;
    private $domain;
    protected $root = 'whyd'; //默认根目录

    /**
     * 初始化参数 
     * @example 
     *  new \mysoft\upload\Oss('sales');
     * @param array|string $config
     */

    public function init() {
        parent::init();

        $oss = Conf::fromCache('oss');
        $oss = json_decode($oss, true);
        
        if(!is_array($oss)){
           throw new \yii\base\InvalidConfigException('oss配置异常');
        }

        $this->bucket   = isset($oss['oss_bucket']) ? $oss['oss_bucket'] : '';
        $this->domain   = isset($oss['oss_access_uri']) ? $oss['oss_access_uri'] : ''; //拼接文件返回地址的 http host部分
        $hostname       = isset($oss['oss_host']) ? $oss['oss_host'] : ''; //定义操作的指定节点hostname
        $OSS_ACCESS_ID  = isset($oss['oss_access_key_id']) ? $oss['oss_access_key_id'] : ''; //接入的ID
        $OSS_ACCESS_KEY = isset($oss['oss_access_key_secret']) ? $oss['oss_access_key_secret'] : ''; //接入的密钥

        if ($this->rootDirName)
            $this->root = $this->rootDirName;

        $this->oss = new \ALIOSS($OSS_ACCESS_ID, $OSS_ACCESS_KEY, $hostname);
    }

    /**
     * 实现上传抽象方法
     */
    public function uploadFile($source, $object) {
        $object = $this->root . '/' . ltrim($object, '/'); //add root用于区分

        $response = $this->oss->upload_file_by_file($this->bucket, $object, $source);
        if ($this->debug)
            $this->_format($response);
        if ($response->status == '200'){
            
           $webpath = $this->domain . $object;
           
           if($this->use_thumb_type !== false)//使用缩略图
          {
             list($file,$ext) = explode('.',$object);
             
             if(in_array($ext,['png','jpg','jpeg','bmp','gif','webp'])){
                 
                $thumb_tmp = $source.'_thumb.'.$ext;//temp

                $img = new \mysoft\image\File($webpath,$thumb_tmp);
                $res = $img->compress($this->use_thumb_type, $this->use_thumb_params);
              
                if( $res !== false ){
                    $object_thumb    = str_replace(".{$ext}", "_thumb.{$ext}", $object);    //缩略图对应的object


                    $res = $this->oss->upload_file_by_file($this->bucket,$object_thumb,$thumb_tmp);//缩略图的上传逻辑
                    @unlink($thumb_tmp);
                
                    if($res->status == '200'){
                       $this->thumb_img = str_replace(".{$ext}", "_thumb.{$ext}", $webpath);//缩略图的Http地址
                    }
                    
                    
                }else{
                    
                    $this->thumb_img = false; //不生成缩略图
                }
                
                
             }
             
              
          }
            
            
            
            
           return $webpath; 
        }
            

        return '';
    }
    
    /**
     * (non-PHPdoc)
     * @see \mysoft\upload\UploadAbs::uploadByStream()
     * @author fangl
     */
    public function uploadByStream($content,$object) {
        //$content = base64_encode($content);
        $object = $this->root . '/' . ltrim($object, '/'); //add root用于区分
        $filetype = $this->_fileType($content);
        
        $upload_file_options = array(
            'content' => $content,
            'length' => strlen($content),
            \ALIOSS::OSS_CONTENT_TYPE=>$filetype,
        );
        
        $response = $this->oss->upload_file_by_content($this->bucket,$object,$upload_file_options);
        if($response->isOK()) {

            //符合要求的图片进行压缩处理
            if($this->use_thumb_type !== false && in_array($filetype,["jpg","gif","jpeg","png","bmp","webp"]))
            {
 
               $img = new \mysoft\image\Stream($content,$filetype);
               $thumb = $img->compress($this->use_thumb_type,$this->use_thumb_params); 
               
               if( $thumb !== false ){//进行缩略图上传
               
                    list($file,$ext) = explode('.',$object);
                    $t_object = $file."_thumb.".$ext;
                    $res = $this->oss->upload_file_by_content($this->bucket,$t_object,[
                             'content' => $thumb,
                             'length' => strlen($thumb),
                             \ALIOSS::OSS_CONTENT_TYPE=>$filetype,

                    ]);


                    if($res->isOK()){
                        $this->thumb_img = $this->domain.$t_object;
                    }
               
               }else{
                   $this->thumb_img = false; //无缩略图
               }
               
            }
            
            
            return $this->domain.$object;
        }
        else {
            var_dump($response);
            return false;
        }
    }
    
    /**
     * 根据流的elf信息获取文件类型
     * @param hex $content
     * @return string
     */
    private function _fileType($content)
    {
        $bin = substr($content, 0, 2); //只读2字节
        $strInfo = @unpack("C2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        $fileType = '';
        switch ($typeCode)
        {
            case 7790:
                $fileType = 'exe';
                break;
            case 7784:
                $fileType = 'midi';
                break;
            case 8273:
                $fileType = 'webp';
                break;
            case 8297:
                $fileType = 'rar';
                break;
            case 8075:
                $fileType = 'zip';
                break;
            case 255216:
                $fileType = 'jpg';
                break;
            case 7173:
                $fileType = 'gif';
                break;
            case 6677:
                $fileType = 'bmp';
                break;
            case 13780:
                $fileType = 'png';
                break;
            default:
                $fileType = 'unknown: '.$typeCode;
        }
    
        //Fix
        if ($strInfo['chars1']=='-1' AND $strInfo['chars2']=='-40' ) return 'jpg';
        if ($strInfo['chars1']=='-119' AND $strInfo['chars2']=='80' ) return 'png';
    
        return $fileType;
    }

    /**
     * 继承实现父类方法
     * 格式化返回结果
     *
     * */
    protected function _format($response) {
        echo '|-----------------------Start---------------------------------------------------------------------------------------------------' . "\n";
        echo '|-Status:' . $response->status . "\n";
        echo '|-Body:' . "\n";
        echo $response->body . "\n";
        echo "|-Header:\n";
        print_r($response->header);
        echo '-----------------------End-----------------------------------------------------------------------------------------------------' . "\n\n";
        exit;
    }

    /**
     * 获取当前根目录名
     * @return string
     */
    public function getRoot() {
        return $this->root;
    }
    
    public function getUrl($object) {
        $object = $this->root . '/' . ltrim($object, '/'); //add root用于区分
        return $this->domain.$object;
    }

}
