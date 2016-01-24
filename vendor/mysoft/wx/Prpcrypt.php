<?php
namespace mysoft\wx;

/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
    public static $block_size = 32;

    /**
     * 对需要加密的明文进行填充补位
     * @param $text 需要进行填充补位操作的明文
     * @return 补齐明文字符串
     */
    function encode($text)
    {
        $block_size = PKCS7Encoder::$block_size;
        $text_length = strlen($text);
        //计算需要填充的位数
        $amount_to_pad = PKCS7Encoder::$block_size - ($text_length % PKCS7Encoder::$block_size);
        if ($amount_to_pad == 0) {
            $amount_to_pad = PKCS7Encoder::block_size;
        }
        //获得补位所用的字符
        $pad_chr = chr($amount_to_pad);
        $tmp = "";
        for ($index = 0; $index < $amount_to_pad; $index++) {
            $tmp .= $pad_chr;
        }
        return $text . $tmp;
    }

    /**
     * 对解密后的明文进行补位删除
     * @param decrypted 解密后的明文
     * @return 删除填充补位后的明文
     */
    function decode($text)
    {

        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > PKCS7Encoder::$block_size) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }

}

class PrpcryptError {
    
    const RESULT_LENGHT_LESS_THAN_16 = -101;
    const CORPID_IS_NOT_VALID = -102;
    
    public $code;
    public $message;
    public $line;
    public $file;
    public $trace;
    
    public function __construct($code, $message, $line=__LINE__, $file=__FILE__, $trace=[]) {
        $this->code = $code;
        $this->message = $message;
        $this->line = $line;
        $this->file = $file;   
    }
}

/**
 * Prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt
{
    private $key;

    private $err;
    
    function __construct($aeskey)
    {
        $this->key = base64_decode($aeskey . "=");
        $this->err = new PrpcryptError(0, 'ok');
    }

    /**
     * 对明文进行加密
     * @param string $text 需要加密的明文
     * @return string 加密后的密文
     */
    public function encrypt($text, $corpid)
    {

        try {
            //获得16位随机字符串，填充到明文之前
            $random = $this->getRandomStr();
            $text = $random . pack("N", strlen($text)) . $text . $corpid;
            // 网络字节序
            $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv = substr($this->key, 0, 16);
            //使用自定义的填充方式对明文进行补位填充
            $pkc_encoder = new PKCS7Encoder;
            $text = $pkc_encoder->encode($text);
            mcrypt_generic_init($module, $this->key, $iv);
            //加密
            $encrypted = mcrypt_generic($module, $text);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);

            //print(base64_encode($encrypted));
            //使用BASE64对加密后的字符串进行编码
            return base64_encode($encrypted);
        } catch (Exception $e) {
            $this->err = new PrpcryptError($e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile(), $e->getTrace());
            return null;
        }
    }

    /**
     * @param string $encrypted 密文
     * @param string $corpid    
     * @param boolean $validCorpid 是否验证corpid，默认验证
     * @return NULL|string
     */
    public function decrypt($encrypted, $corpid, $validCorpid=true)
    {

        try {
            //使用BASE64对需要解密的字符串进行解码
            $ciphertext_dec = base64_decode($encrypted);
            $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
            $iv = substr($this->key, 0, 16);
            mcrypt_generic_init($module, $this->key, $iv);

            //解密
            $decrypted = mdecrypt_generic($module, $ciphertext_dec);
            mcrypt_generic_deinit($module);
            mcrypt_module_close($module);
        } catch (Exception $e) {
            $this->err = new PrpcryptError($e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile(), $e->getTrace());
            return null;
        }

        try {
            //去除补位字符
            $pkc_encoder = new PKCS7Encoder;
            $result = $pkc_encoder->decode($decrypted);
            //去除16位随机字符串,网络字节序和AppId
            if (strlen($result) < 16) {
                $this->err = new PrpcryptError(PrpcryptError::RESULT_LENGHT_LESS_THAN_16, 'result length is less than 16', __LINE__, __FILE__, debug_backtrace());
                return null;
            }
            $content = substr($result, 16, strlen($result));
            $len_list = unpack("N", substr($content, 0, 4));
            $xml_len = $len_list[1];
            $xml_content = substr($content, 4, $xml_len);
            $from_corpid = substr($content, $xml_len + 4);
        } catch (Exception $e) {
            $this->err = new PrpcryptError($e->getCode(), $e->getMessage(), $e->getLine(), $e->getFile(), $e->getTrace());
            return null;
        }
        
        if ($validCorpid && $from_corpid != $corpid) {
            $this->err = new PrpcryptError(PrpcryptError::CORPID_IS_NOT_VALID, "coprid given is :{$corpid}, but the from corpid is : {$from_corpid}", __LINE__, __FILE__, debug_backtrace());
            return null;
        }
        else return $xml_content;
    }


    /**
     * 随机生成16位字符串
     * @return string 生成的字符串
     */
    private function getRandomStr()
    {

        $str = "";
        $str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($str_pol) - 1;
        for ($i = 0; $i < 16; $i++) {
            $str .= $str_pol[mt_rand(0, $max)];
        }
        return $str;
    }
    
    public function isSuccess() {
        return $this->err->code == 0;
    }
    
    public function getErr() {
        return $this->err;
    }
    
    public function printErr() {
        \Yii::info($this->err->message,get_class());
        var_dump($this->err);
    }
}
