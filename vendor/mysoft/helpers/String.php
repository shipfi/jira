<?php

namespace mysoft\helpers;

/**
 * 生成uuid的助手类
 * 
 * @see \mysoft\helpers\String::uuid();
 * @author yangzhen
 *
 */
class String {

    /**
     * 生成UUID 单机使用
     * @access public
     * @return string
     */
    public static function uuid() {
        list($usec, $sec) = explode(" ", microtime(false));
        $usec = (string) ($usec * 10000000);
        $timestamp = bcadd(bcadd(bcmul($sec, "10000000"), (string) $usec), "621355968000000000");
        $ticks = bcdiv($timestamp, 10000);
        $maxUint = 4294967295;
        $high = bcdiv($ticks, $maxUint) + 0;
        $low = bcmod($ticks, $maxUint) - $high;
        $highBit = (pack("N*", $high));
        $lowBit = (pack("N*", $low));
        $guid = str_pad(dechex(ord($highBit[2])), 2, "0", STR_PAD_LEFT) . str_pad(dechex(ord($highBit[3])), 2, "0", STR_PAD_LEFT) . str_pad(dechex(ord($lowBit[0])), 2, "0", STR_PAD_LEFT) . str_pad(dechex(ord($lowBit[1])), 2, "0", STR_PAD_LEFT) . "-" . str_pad(dechex(ord($lowBit[2])), 2, "0", STR_PAD_LEFT) . str_pad(dechex(ord($lowBit[3])), 2, "0", STR_PAD_LEFT) . "-";
        $chars = "abcdef0123456789";
        for ($i = 0; $i < 4; $i++) {
            $guid .= $chars[mt_rand(0, 15)];
        }
        $guid .="-";
        for ($i = 0; $i < 4; $i++) {
            $guid .= $chars[mt_rand(0, 15)];
        }
        $guid .="-";
        for ($i = 0; $i < 12; $i++) {
            $guid .= $chars[mt_rand(0, 15)];
        }

        return $guid;
    }

    /**
     * 检查字符串是否是UTF8编码
     * @param string $string 字符串
     * @return Boolean
     */
    public static function isUtf8($str) {
        $c = 0;
        $b = 0;
        $bits = 0;
        $len = strlen($str);
        for ($i = 0; $i < $len; $i++) {
            $c = ord($str[$i]);
            if ($c > 128) {
                if (($c >= 254))
                    return false;
                elseif ($c >= 252)
                    $bits = 6;
                elseif ($c >= 248)
                    $bits = 5;
                elseif ($c >= 240)
                    $bits = 4;
                elseif ($c >= 224)
                    $bits = 3;
                elseif ($c >= 192)
                    $bits = 2;
                else
                    return false;
                if (($i + $bits) > $len)
                    return false;
                while ($bits > 1) {
                    $i++;
                    $b = ord($str[$i]);
                    if ($b < 128 || $b > 191)
                        return false;
                    $bits--;
                }
            }
        }
        return true;
    }

    // 自动转换字符集 支持数组转换
    public static function autoCharset($string, $from = 'gbk', $to = 'utf-8') {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($string) || (is_scalar($string) && !is_string($string))) {
            //如果编码相同或者非字符串标量则不转换
            return $string;
        }
        if (is_string($string)) {
            if (function_exists('mb_convert_encoding')) {
                return mb_convert_encoding($string, $to, $from);
            } elseif (function_exists('iconv')) {
                return iconv($from, $to, $string);
            } else {
                return $string;
            }
        } elseif (is_array($string)) {
            foreach ($string as $key => $val) {
                $_key = self::autoCharset($key, $from, $to);
                $string[$_key] = self::autoCharset($val, $from, $to);
                if ($key != $_key)
                    unset($string[$key]);
            }
            return $string;
        }
        else {
            return $string;
        }
    }

    public static function jsonEncode($value) {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 将汉字转换为拼音
     * @param string $zh_cn 将汉字转换为拼音
     * @author gongz <gongz@mysoft.com.cn>
     * @return string 返回拼音值
     */
    static public function PinYin($zh_cn) {
        if (function_exists('transliterator_transliterate')) {
            $pinyin = transliterator_transliterate('Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();', $zh_cn);
            return str_replace(' ', '', $pinyin);
        }
        $_data_key = 'a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha|chai|chan|chang|chao|che|chen|cheng|chi'
                . '|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui'
                . '|dun|duo|e|en|er|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui|gun|guo|ha|hai|han|hang|hao|he'
                . '|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng'
                . '|kong|kou|ku|kua|kuai|kuan|kuang|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue|lun|luo|ma'
                . '|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu'
                . '|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran'
                . '|rang|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|she|shen|sheng|shi|shou|shu|shua|shuai|shuan'
                . '|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei'
                . '|wen|weng|wo|wu|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you|yu|yuan|yue|yun|za|zai|zan|zang'
                . '|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo';
        $_dataValue = '-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990|-19986|-19982|-19976|-19805|-19784'
                . '|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281'
                . '|-19275|-19270|-19263|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003|-18996|-18977|-18961|-18952'
                . '|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239'
                . '|-18237|-18231|-18220|-18211|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922|-17759|-17752|-17733'
                . '|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-1746|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915'
                . '|-16733|-16708|-16706|-16689|-16664|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407|-16403|-16401'
                . '|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707'
                . '|-15701|-15681|-15667|-15661|-15659|-15652|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369|-15363'
                . '|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933'
                . '|-14930|-14929|-14928|-14926|-14922|-14921|-14914|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645'
                . '|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149|-14145|-14140|-14137|-14135|-14125|-14123|-14122'
                . '|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831'
                . '|-13658|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340|-13329|-13326|-13318|-13147|-13138|-13120'
                . '|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597'
                . '|-12594|-12585|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847|-11831|-11798|-11781|-11604|-11589'
                . '|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815'
                . '|-10800|-10790|-10780|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274|-10270|-10262|-10260|-10256'
                . '|-10254';
        $_data = array_combine(explode('|', $_data_key), explode('|', $_dataValue));
        arsort($_data);
        $zh_cn = iconv('utf-8', 'gbk', $zh_cn);
        $_res = [];
        for ($i = 0; $i < strlen($zh_cn);) {
            //属于 gbk 编码范围的
            if (ord($zh_cn{$i}) >= 0x81 && ord($zh_cn) <= 0xfe && $i + 1 < strlen($zh_cn) ) {
                $_res[] = self:: _Pinyin(ord($zh_cn{$i})*256 + ord($zh_cn{$i+1}) - 65536, $_data);
                $i += 2; //一个gbk两字节
            }
            else {
                //属于ascii的部分，用ascii值显示，其他的，用_代替
                $_res[] = ord($zh_cn{$i}) >= 0x20 && ord($zh_cn{$i}) <= 0x7f ?$zh_cn{$i}:'_';
                $i ++;
            }
        }
        
        return implode('',$_res);
    }

    private static function _Pinyin($_num, $_data) {
        if ($_num < -20319 || $_num > -10247) {
            return '_';
        } 
        else {
            $k = '_';
            foreach ($_data as $k => $v) {
                if ($v <= $_num)
                    break;
            }
            return $k;
        }
    }
    
    /**
     * 检测字符串编码（注意：存在误判的可能性，降低误判的几率的唯一方式是给出尽可能多的样本$line）
     * 检测原理：对给定的字符串的每一个字节进行判断，如果误差与gb18030在指定误差内，则判定为gb18030；与utf-8在指定误差范围内，则判定为utf-8；否则判定为utf-16
     * @param string $line
     * @return string 中文字符集，返回gb18030（兼容gbk,gb2312,ascii）；西文字符集，返回utf-8（兼容ascii）；其他，返回utf-16（双字节unicode）
     * @author fangl
     */
    static function detect_charset($line,$seq=['GB2312','BIG-5','EUC-TW','GB18030','UTF-8','ASCII']) {
        $ret = mb_detect_encoding($line,$seq,true);
        if($ret) {
            return $ret;
        }
        else return 'utf-16';
//         if(self::detect_big5($line)) {
//             return 'big5';
//         }
//         else if(self::detect_gb18030($line)) {
//             return 'gb18030';
//         }
//         else if(self::detect_utf8($line)) {
//             return 'utf-8';
//         }
//         else return 'utf-16';
    }
    
    /**
     * 兼容ascii，gbk gb2312，识别字符串是否是gb18030标准的中文编码
     * @param string $line
     * @return boolean
     * @author fangl
     */
    static function detect_gb18030($line) {
        $gbbyte = 0; //识别出gb字节数
        for($i=0;$i+3<strlen($line);) {
            if(ord($line{$i}) >= 0 && ord($line{$i}) <= 0x7f) {
                $gbbyte ++; //识别一个单字节 ascii
                $i++;
            }
            else if( ord($line{$i}) >= 0x81 && ord($line{$i}) <= 0xfe &&
            (ord($line{$i+1}) >= 0x40 && ord($line{$i+1}) <= 0x7e ||
             ord($line{$i+1}) >= 0x80 && ord($line{$i+1}) <= 0xfe) ) {
                $gbbyte += 2; //识别一个双字节gb18030（gbk）
                $i += 2;
            }
            else if( ord($line{$i}) >= 0x81 && ord($line{$i}) <= 0xfe &&
            ord($line{$i+2}) >= 0x81 && ord($line{$i+2}) <= 0xfe &&
            ord($line{$i+1}) >= 0x30 && ord($line{$i+1}) <= 0x39 &&
            ord($line{$i+3}) >= 0x30 && ord($line{$i+3}) <= 0x39) {
                $gbbyte += 4; //识别一个4字节gb18030（扩展）
                $i += 4;
            }
            else $i++; //未识别gb18030字节
        }
        return abs($gbbyte - strlen($line)) <= 4; //误差在4字节之内
    }
    
    //高字节从A1到F9，低字节从40到7E，A1到FE。
    static function detect_big5($line) {
        $gbbyte = 0; //识别出big5字节数
        for($i=0;$i+1<strlen($line);) {
            if(ord($line{$i}) >= 0 && ord($line{$i}) <= 0x7f) {
                $gbbyte ++; //识别一个单字节 ascii
                $i++;
            }
            else if( ord($line{$i}) >= 0xa1 && ord($line{$i}) <= 0xf9 &&
            (ord($line{$i+1}) >= 0x40 && ord($line{$i+1}) <= 0x7e ||
            ord($line{$i+1}) >= 0xa1 && ord($line{$i+1}) <= 0xfe) ) {
                $gbbyte += 2; //识别一个big5
                $i += 2;
            }
            else $i++; //未识别big5字节
        }
        return abs($gbbyte - strlen($line)) <= 2; //误差在2字节之内
    }
    
    
    /**
     * 识别字符串是否是utf-8编码，同样兼容ascii
     * @param string $line
     * @return boolean
     * @author fangl
     */
    static function detect_utf8($line) {
        $utfbyte = 0; //识别出utf-8字节数
        for($i=0;$i+2<strlen($line);) {
            //单字节时，编码范围为：0x00 - 0x7f
            if(ord($line{$i}) >= 0 && ord($line{$i}) <= 0x7f) {
                $utfbyte ++; //识别一个单字节utf-8（ascii）
                $i++;
            }
            //双字节时，编码范围为：高字节 0xc0 - 0xcf 低字节 0x80 - 0xbf
            else if(ord($line{$i}) >= 0xc0 && ord($line{$i}) <= 0xcf
            && ord($line{$i+1}) >= 0x80 && ord($line{$i+1}) <= 0xbf) {
                $utfbyte += 2; //识别一个双字节utf-8
                $i += 2;
            }
            //三字节时，编码范围为：高字节 0xe0 - 0xef 中低字节 0x80 - 0xbf
            else if(ord($line{$i}) >= 0xe0 && ord($line{$i}) <= 0xef
            && ord($line{$i+1}) >= 0x80 && ord($line{$i+1}) <= 0xbf
            && ord($line{$i+2}) >= 0x80 && ord($line{$i+2}) <= 0xbf) {
                $utfbyte += 3; //识别一个三字节utf-8
                $i += 3;
            }
            else $i++; //未识别utf-8字节
        }
        return abs($utfbyte - strlen($line)) <= 3; //误差在3字节之内的，则识别为utf-8编码
    }

    /**
     * 返回指定中文名的随机表示颜色
     * @param $cName
     */
    static  function getRandColor($cName){
        //定义颜色数组
        $color = ["#67cac9", "#5ec9f6", "#ff953f", "#e9c068","#f65e5e", "#e085e3", "#6b77e7", "#947df4", "#a8ca6c"];
        $py = self::PinYin($cName);
        $lenth = strlen($py);
        $sum = 0;
        for($i=0;$i<$lenth;$i++){
            $sum = $sum + ord($py[$i]);
        }
        //求余数
        $rd = ($sum) % 9;
        return $color[$rd];
    }

    /**
     * 判断字符串是否为json格式
     * @param $string
     * @return bool
     */
    static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
