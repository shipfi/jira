<?php
namespace mysoft\wx\msg;
use mysoft\wx\msg\WxMsg;

class WxMsgNews extends WxMsg {
	
	/**
	 * @desc array(array("title"=>title,"desc"=>desc,"pic"=>pic,"url"=>url));
	 * @var array
	 */
	private $items;
	
	/* 
	 * @see WxMsg::renderReplyXml()
	 */
	public function renderReplyXml() {
		$tpl = "<xml>"
				 ."<ToUserName><![CDATA[{$this->ToUserName}]]></ToUserName>"
				 ."<FromUserName><![CDATA[{$this->FromUserName}]]></FromUserName>"
				 ."<CreateTime>".time()."</CreateTime>"
				 ."<MsgType><![CDATA[news]]></MsgType>"
				 ."<ArticleCount>".count($this->items)."</ArticleCount>"
				 ."<Articles>"
				 .$this->make_news_items($this->items)
				 ."</Articles>"
				 ."</xml>";
		return $tpl;
	}
	
 	private function make_news_items($items) {
    	$tpl = "<item>"
 			    ."<Title><![CDATA[%s]]></Title>"
 				."<Description><![CDATA[%s]]></Description>"
 				."<PicUrl><![CDATA[%s]]></PicUrl>"
 				."<Url><![CDATA[%s]]></Url>"
 				."</item>";
 		$res = "";
 		for($i =0 ;$i<count($items); $i++) {
 			if( isset($items[$i]['title']) && isset($items[$i]['desc']) &&
 				isset($items[$i]['pic']) && isset($items[$i]['url'])) {
 					$res .= sprintf($tpl,$items[$i]['title'],$items[$i]['desc'],$items[$i]['pic'],$items[$i]['url']);
 				}
 		}
 		return $res;	
    }
	/**
	 * @return the $items
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * @param array $items
	 */
	public function setItems($items) {
		$this->items = $items;
	}
	/**
	 * @desc items[] = make_item();
	 * @param unknown_type $title
	 * @param unknown_type $desc
	 * @param unknown_type $pic
	 * @param unknown_type $url
	 */
	public function make_item($title,$desc,$pic,$url) {
		return array("title"=>$title,"desc"=>$desc,"pic"=>$pic,"url"=>$url);
	}

	/* (non-PHPdoc)
	 * @see WxMsg::renderSendXml()
	 */
	public function renderSendXml() {

	}

}