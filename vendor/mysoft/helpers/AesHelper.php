<?php

namespace mysoft\helpers;

use Yii;

/**
 * 特别针对微助手 AES-128 加解密算法处理类
 *
 * @author 雷鸣 <leim01@mysoft.com.cn>
 */
class AesHelper {

    /**
     * 加密
     *
     * @param string $plaintext  明文
     * @param string $key 密钥（可选）
     * @return string 密文
     *
     * @author 雷鸣 <leim01@mysoft.com.cn>
     */
    public static function encrypt($plaintext, $key = 'Mysoft95938') {
        $plaintext = trim($plaintext);
        if ($plaintext == '') {
            throw new \InvalidArgumentException('加密字符串不能为空');
        }
        $plaintext = static::addPKCS7Padding($plaintext);
        if (!extension_loaded('mcrypt')) {
            throw new \Exception(Yii::t('yii', 'AesEncrypt requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
        }
        // 将密钥转换为16位hash值
        $hashstr = md5($key, true);
        $hash = static::toAsciiBytes($hashstr);
        // 混淆
        $newhash = array();
        static::copyArray($hash, 0, $newhash, 0, 16);
        static::copyArray($hash, 0, $newhash, 15, 16);
        $newhash[] = 0;
        // 获取实际加密key       
        $realkey = static::toAsciiString($newhash);
        // 解密
        return base64_encode(
                mcrypt_encrypt(
                        MCRYPT_RIJNDAEL_128, $realkey, $plaintext, MCRYPT_MODE_ECB, mcrypt_create_iv(
                                mcrypt_get_iv_size(
                                        MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB
                                ), MCRYPT_DEV_URANDOM
                        )
                )
        );
    }

    /**
     * 将字符串转换为 ascii 编码的 byte 数组
     *
     * @param string $str 字符串
     * @return array byte 数组
     *
     * @author 雷鸣 <leim01@mysoft.com.cn>
     */
    public static function toAsciiBytes($str) {
        $bytes = array();
        for ($i = 0; $i < strlen($str); $i++) {
            $bytes[] = ord($str[$i]);
        }
        return $bytes;
    }

    /**
     * 将 byte 数组转换为 ascii 编码的字符串
     *
     * @param array $bytes
     * @return string
     *
     * @author 雷鸣 <leim01@mysoft.com.cn>
     */
    public static function toAsciiString($bytes) {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }

    /**
     * 按需复制数组
     *
     * @param array $srcArray 源数组
     * @param int $srcIndex 源数组开始索引
     * @param array $destArray 目标数组（引用类型）
     * @param int $destIndex 目标数组开始索引
     * @param int $length 要复制长度
     * 
     * @author 雷鸣 <leim01@mysoft.com.cn>
     */
    public static function copyArray($srcArray, $srcIndex, &$destArray, $destIndex, $length) {
        for ($i = $srcIndex, $j = $destIndex; $i < $length; $i++, $j++) {
            $destArray[$j] = $srcArray[$i];
        }
    }

    /**
     * 填充算法
     * @param string $source
     * @return string
     */
    public static function addPKCS7Padding($source){
        $source = trim($source);
        $block = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }

    /**
     * 解密
     *
     * @param string $ciphertext base64密文
     * @param string $key 密钥（可选）
     * @return string 明文
     *
     * @author 雷鸣 <leim01@mysoft.com.cn>
     */
    public static function decrypt($ciphertext, $key = 'Mysoft95938') {
        if ($ciphertext == '') {
            throw new \InvalidArgumentException('解密字符串不能为空');
        }
        if (!extension_loaded('mcrypt')) {
            throw new \Exception(Yii::t('yii', 'AesEncrypt requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
        }

        // 将密钥转换为16位hash值
        $hashstr = md5($key, true);
        $hash = static::toAsciiBytes($hashstr);
        // 混淆
        $newhash = array();
        static::copyArray($hash, 0, $newhash, 0, 16);
        static::copyArray($hash, 0, $newhash, 15, 16);
        $newhash[] = 0;
        // 获取实际加密key
        $realkey = static::toAsciiString($newhash);
        // 解密
        return rtrim(
                mcrypt_decrypt(
                        MCRYPT_RIJNDAEL_128, $realkey, base64_decode($ciphertext), MCRYPT_MODE_ECB, mcrypt_create_iv(
                                mcrypt_get_iv_size(
                                        MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB
                                ), MCRYPT_DEV_URANDOM
                        )
                ), "\x00..\x1F" /* 去除特殊符号 */
        );
    }

}
