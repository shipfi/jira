<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\message;


use mysoft\helpers\ErrorCodeHelper;

class MessageSender {

    public $error=[];
    public function send($msg,$platform){
        if(!empty($this->error)){
            return $this->error;
        }
        $openid_list = explode('|', $msg['touser']);
        foreach ($openid_list as $openid) {
            $msg_format = $this->_formatSendMsg($msg, $openid,$platform);
            //发送

        }

    }

    /**
     * 校验消息格式
     * @param $msg 消息体
     * @return array
     */
    public function verifyMsg($msg) {
        //必填字段校验
        if (empty($msg)) {
            return $this->set_error(ErrorCodeHelper::ERRJSON, 'JSON格式解析失败');
        }
        $rules = [
            ["tenantid",ErrorCodeHelper::INVALID_TENANTID],
            ["appcode",ErrorCodeHelper::MISS_AGENTID],
            ["touser",ErrorCodeHelper::MISS_USERID],
            ["msgtype",ErrorCodeHelper::MISS_MSGTYPE],
        ];

        foreach ($rules as $rule) {
            if (empty($msg[$rule[0]])) {
                return $this->set_error($rule[1], $rule[0].'节点格式不正确');
            }
        }
        $msgtype = $msg['msgtype'];
        if (empty($msg[$msgtype])) {
            return $this->set_error(ErrorCodeHelper::MISS_MSGBODY, $msgtype.'节点格式不正确');
        }

        return $this->set_error(ErrorCodeHelper::OK, 'ok');
    }

    /**
     * 格式化需要保存的消息体
     * @param $msginfo 消息体
     * @param $openid 发送人
     * @param $platform 平台
     * @return array
     */
    private function _formatSendMsg($msginfo, $openid, $platform) {
        $send_msg = [
            'biz_id' => !isset($msginfo['biz_id']) ? null : $msginfo['biz_id'],
            'tenant_id' => $msginfo['tenantid'],
            'appcode' => $msginfo['appcode'],
            'openid' => $openid,
            'msgtype' => $msginfo['msgtype'],
            'content' => $this->_formatUrl($msginfo, $platform),
            'status' => 1,
            'safe' => !isset($msginfo['safe']) ? null : $msginfo['safe'],
            'sendto' => $platform,
        ];

        if (!empty($msginfo['mark'])) {
            $send_msg['mark'] = json_encode($msginfo['mark'], JSON_UNESCAPED_UNICODE);
            ;
        }

        if (isset($msginfo['senddate'])) {
            if (!empty($msginfo['senddate'])) {
                $send_msg['send_time'] = $msginfo['senddate'];
            }
        }

        return $send_msg;
    }

    private function _formatUrl($msginfo,$platform)
    {
        switch ($msginfo['msgtype'])
        {
            case 'news':
                //找到url字段，检查值中是否有__from，有则替换，无则增加
                //存在才不替换
                if (isset($msginfo[$msginfo['msgtype']]['articles'][0]['url'])) {
                    $url = $msginfo[$msginfo['msgtype']]['articles'][0]['url'];
                    $msginfo[$msginfo['msgtype']]['articles'][0]['url'] = $this->_replaceUrl($url, '__from', $platform);
                }
                break;
            case 'oa':
                //找到message+url字段，检查值中是否有__from，有则替换，无则增加
                if (isset($msginfo[$msginfo['msgtype']]['message_url'])) {
                    $url = $msginfo[$msginfo['msgtype']]['message_url'];
                    $msginfo[$msginfo['msgtype']]['message_url'] = $this->_replaceUrl($url, '__from', $platform);
                }
                break;
            default :
                //不操作
                break;
        }
        return json_encode($msginfo[$msginfo['msgtype']], JSON_UNESCAPED_UNICODE);
    }

    /**
     *
     * @param url $url 要替换的url
     * @param string $str   要替换的参数
     * @param string $value 参数对应的值
     * @return type
     */
    private function _replaceUrl($url, $str, $value) {
        if (!empty($url)) {
            $urlarray = parse_url($url);
            if (!isset($urlarray['query'])) {
                //不存在url参数
                $url = $url . '?' . $str . '=' . $value;
                return $url;
            } else {
                //判断有没有要替换的参数$str，没有直接加在最末，有就替换值
                //查找有没有__from参数
                if (strchr($url, $str)) {
                    $querystr = $urlarray['query'];
                    parse_str($querystr, $queryarray);
                    $oldreplacestr = $str . '=' . $queryarray[$str];
                    $replacestr = $str . '=' . $value;
                    return str_replace($oldreplacestr, $replacestr, $url);
                } else {
                    $url = $url . '&' . $str . '=' . $value;
                    return $url;
                }
            }
        }
        return $url;
    }

    private function set_error($errcode, $errmsg) {
        $this->error = [
            'errcode' => $errcode,
            'errmsg' => $errmsg
        ];
        return $this->error;
    }
}
