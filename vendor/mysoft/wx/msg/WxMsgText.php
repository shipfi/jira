<?php
namespace mysoft\wx\msg;
use mysoft\wx\msg\WxMsg;

class WxMsgText extends WxMsg {
	
	private $content;
	
	/**
	 * @desc
	 * @param $content 消息内容
	 * @param $isSend 是否构造发送格式消息
	 */
	public function __construct() {
		$array = func_get_args();
		if(count($array) > 0) {
			$this->content = func_get_arg(0);
		}
		
		if(count($array) > 1) {
			$this->isSend = func_get_arg(1);
		}
		
		$this->MsgType = "text";
	}
	
	/* 
	 * @see WxMsg::renderReplyXml()
	 */
	public function renderReplyXml() {
		$textTpl = "<xml>"
						."<ToUserName><![CDATA[%s]]></ToUserName>"
						."<FromUserName><![CDATA[%s]]></FromUserName>"
						."<CreateTime>%s</CreateTime>"
						."<MsgType><![CDATA[text]]></MsgType>"
						."<Content><![CDATA[%s]]></Content>"
						."<FuncFlag>%d</FuncFlag>"
						."</xml>";
		$this->CreateTime = $this->CreateTime == null ?time ():$this->CreateTime;
		return sprintf($textTpl,$this->ToUserName,
								$this->FromUserName,
								$this->CreateTime,
								$this->content,$this->FuncFlag);
		
	}
	
	/* 
	 * @see WxMsg::renderSendXml()
	 */
	public function renderSendXml() {
		$textTpl = "<xml>"
						."<ToUserName><![CDATA[%s]]></ToUserName>"
						."<FromUserName><![CDATA[%s]]></FromUserName>"
						."<CreateTime>%s</CreateTime>"
						."<MsgType><![CDATA[text]]></MsgType>"
						."<Content><![CDATA[%s]]></Content>"
						."<MsgId>%s</MsgId>"
						."</xml>";
		$this->CreateTime = $this->CreateTime == null ?time ():$this->CreateTime;
		return sprintf($textTpl,$this->ToUserName,
								$this->FromUserName,
								$this->CreateTime,
								$this->content,$this->MsgId);
		
	}
	
	/**
	 * @return the $content
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param field_type $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}


	
}