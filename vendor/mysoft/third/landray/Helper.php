<?php
namespace mysoft\third\landray;

class Helper {
    
    /**
     * 生成ltpatoken的算法
     * @param string $username
     * @param string $secret base64_encoded
     * @param number $timeout
     * @return string
     */
    static function encode_sso($username,$secret,$timeout = 7200) {
        $strcode = chr(0).chr(1).chr(2).chr(3); //0123开头
        $strcode .= base_convert(time(),10,16);             //当前时间
        $strcode .= base_convert(time()+$timeout,10,16);    //过期时间
        $strcode .= $username;                  //username
        $strcode .= sha1($strcode.base64_decode($secret),true);
        return base64_encode($strcode);
    }
    
    /**
     * 获取ltpatoken的算法
     * @param string $strCode
     * @param string $secret base64_encoded
     * @return string|boolean
     */
    static function decode_sso($strCode,$secret) {
        $strCode = base64_decode($strCode);
        $headerCode = chr(0).chr(1).chr(2).chr(3);
        $ctime = hexdec(substr($strCode, 4,8));
        $etime = hexdec(substr($strCode, 12,8));
        $username = substr($strCode,20,strlen($strCode)-40);
        $curTime = time();
    
        if( $ctime<=$curTime && $etime> $curTime ) {
            $strDeSha = self::byteToString(substr($strCode,strlen($strCode)-20,20));
            $strEnSha = sha1(substr($strCode,0,strlen($strCode)-20).base64_decode($secret));
            if($strDeSha==$strEnSha) {
                return $username;
            }
        }
        return false; //时间戳过期，或者完整性失效
    }
    
    /**
     * 将字符串转换为字符串，主要用于sha1算法生成的序列进行ascii化
     * @param string $strByte
     * @return Ambigous <string, string>
     */
    static function byteToString($strByte) {
        $b = array_map('ord', str_split($strByte, 1));
        $str = "";
        foreach ($b as $c) {
            if($c>=16) {
                $str .= dechex($c);
            }
            else {
                $str .= "0".dechex($c);
            }
        }
        return $str;
    }
}

