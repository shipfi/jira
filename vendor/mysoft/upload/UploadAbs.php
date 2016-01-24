<?php

/**
 * mysoft extension for Yii framework
 * 上传组件，抽象类定义
 * ```
 * 'components'=>[
 *  
 *     'upload'=>[
 *         'class' => 'mysoft\upload\xxx',  //定义启用哪种上传组件
 *         'rootDirName'=>'xxxxx',         //设置根路径,不设置则默认为空
 *         'debug'=>true                   //调试状态，开启则组件里所有调试代信息会出现，默认关闭
 *     ]
 * 
 * ]
 * 
 * 关于调试也可以根据全局是否调试状态开启
 * if(YII_DEBUG) $config['components']['upload']['debug'] = true;
 * 
 * 
 * @author yangzhen
 *
 */

namespace mysoft\upload;

use yii\base\Component;
use yii\base\NotSupportedException;

abstract class UploadAbs extends Component {

    /**
     * 是否调试信息,组件配置参数使用
     * @access public
     * @var bool $debug,默认false
     */
    public $debug = false;

    /**
     * 新增定义根目录名，组件配置使用
     * @var string
     */
    public $rootDirName = '';

    /**
     * 允许的扩展类型
     * @var array|string 
     */
    public $allowExtension = ['jpg', 'png', 'gif'];
    

    /**
     * 是否同步生成缩略图
     * @var bool|string 
     */
    protected $use_thumb_type = false;


    /**
     * 缩略图类型方法需要参数
     * @var type 
     */
    protected $use_thumb_params;


    /**
     * 压缩比例
     * @var float 
     */
    public $percent = 0;
        

    /**
     * 缩略图地址
     * @var string 
     */
    protected  $thumb_img = '';

    
    /**
     * 检查当前文件扩展是否允许
     * @param string  $curExt
     * @return boolean
     */
    protected function checkExt($curExt) {

        $allow = [];
        $extExt = strtolower($curExt);

        if (is_string($this->allowExtension)) {

            if ($this->allowExtension == '*') {
                return true;
            }

            $allow = explode('|',$this->allowExtension);
        } else {

            $allow = $this->allowExtension;
        }

        if (in_array($extExt, $allow)) {
            return true;
        } else {
            return false;
        }
    }

    
    /**
     * 使用缩略图
     * 
     * 
     * 
     * @param string  $type 缩略类型
     * @params mixed  $params
     * @return \mysoft\upload\UploadAbs
     */
    public function useThumb($type='percent',$params=0.2)
    {

       $this->use_thumb_type   = $type; 
       $this->use_thumb_params = $params; 
       return $this;    
    }
    
    
    /** 
     * 获取缩略图地址
     * 
     * upload完毕后才能获取到当前上传的缩略图地址
     * 
     * $upload = \Yii::$app->upload;
     * 
     * $type   缩略图的处理类型，具体查看mysoft\image\ImageAbs
     * $params 对应处理方式需要的参数 
     * 
     * $upload->useThumb($type,$params)->uploadByForm(xxxxx) ; 
     * OR 
     * $upload->useThumb($type,$params)->uploadFile(xxxxx,xxxx) ;
     * 
     * 
     * $thumb_url = $upload->getThumbImg();//这里就可以获取到缩略图 
     * 
     * $thumb_url = ''    上传缩略图失败导致
     * 
     * $thumb_url = false 没有缩略图
     * 
     * 
     * @return string|bool
     * 
     */
    public function getThumbImg()
    {
        return $this->thumb_img;
    }
    
    /**
     * 通过表单个文件上传,指定object相对路径，文件名为随机md5 
     * @param  string  $object_dir
     * @return string
     * @throws \Exception
     */
    public function uploadByForm($object_dir) {

        if (!isset($_FILES['__upfile__'])) {
            throw new \Exception('上传的文件对象不存在!');
        }
        $file = $_FILES['__upfile__'];
        $file_name = $file['name'];

        if (empty($file_name)) {
            throw new \Exception('上传文件为空');
        }



        $size = $file['size'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (!$this->checkExt($ext)) {
            throw new \Exception('不允许的上传文件类型');
        }


        $filename = md5($file_name . '#' . date("YmdHis") . '_' . rand(10000, 99999)) . '.' . $ext;

        $object = rtrim($object_dir, '/') . '/' . $filename;
        $source = $file['tmp_name'];
                       
        return $this->uploadFile($source, $object);
    }

    
    //暂未实现
    public function uploadByStream($streamcontent,$path)
    {
        throw NotSupportedException('uploadByStream has not supported yet');
    }
    
    
    /**
     * 上传文件抽象,指定源文件和目标文件
     * @access public
     * @param string $source 上传源
     * @param string $object 目标地址
     * @return string  上传成功的地址，empty则表示失败
     */
    abstract public function uploadFile($source, $object);

    /**
     * 获取当期父级根目录名
     * @access public
     * @return string
     */
    abstract public function getRoot();

    /**
     * 打印响应信息
     * @param mixed $response
     */
    protected function _format($response) {
        //TODO：
    }

    /**
     * 是否开启调试模式
     * @param string $debug
     * @return $this
     */
    public function debug($debug = true) {
        $this->debug = $debug;
        return $this;
    }

}
