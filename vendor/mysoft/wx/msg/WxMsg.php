<?php
namespace mysoft\wx\msg;

abstract class WxMsg {
	
	protected  $ToUserName;

	protected  $FromUserName;
	
	protected  $CreateTime;
	
	protected  $MsgType;
	
	protected  $FuncFlag = 0;
	
	protected  $MsgId = "1234567890123456";
	
	protected  $isSend = 0; //是被动回复消息，还是发送消息
	
	/**
	 * @return the $ToUserName
	 */
	public function getToUserName() {
		return $this->ToUserName;
	}

	/**
	 * @return the $FromUserName
	 */
	public function getFromUserName() {
		return $this->FromUserName;
	}

	/**
	 * @return the $CreateTime
	 */
	public function getCreateTime() {
		return $this->CreateTime;
	}

	/**
	 * @return the $MsgType
	 */
	public function getMsgType() {
		return $this->MsgType;
	}

	/**
	 * @param field_type $ToUserName
	 */
	public function setToUserName($ToUserName) {
		$this->ToUserName = $ToUserName;
	}

	/**
	 * @param field_type $FromUserName
	 */
	public function setFromUserName($FromUserName) {
		$this->FromUserName = $FromUserName;
	}

	/**
	 * @param field_type $CreateTime
	 */
	public function setCreateTime($CreateTime) {
		$this->CreateTime = $CreateTime;
	}

	/**
	 * @param field_type $MsgType
	 */
	public function setMsgType($MsgType) {
		$this->MsgType = $MsgType;
	}

	public function renderXml() {
		if($this->isSend) {
			return $this->renderSendXml();
		}
		else return $this->renderReplyXml();
	}
	
	public abstract function renderReplyXml(); //被动回复消息
	public abstract function renderSendXml(); //主动推送消息（xml）
}