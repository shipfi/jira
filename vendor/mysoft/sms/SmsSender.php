<?php

namespace mysoft\sms;

/**
 * 短信发送接口
 * @author sunfx
 */
interface SmsSender {
/**
 * 发送短信
 * 短信平台带反骚扰监控系统，不允许短时间内向同一手机号码发送过多短信。
 * 默认设置为20分钟内不允许向同一号码发送超过2条相同内容的短信；不同内容的限制为5条。
 * @param string $receiveMobileTel 接收的手机号码
 * @param string $message 短信内容
 * @param string $actionMark 功能点标识,标识具体的功能点,可以为空
 */
    public function send($receiveMobileTel, $message, $actionMark='');
}
