<?php
namespace mysoft\user;

use mysoft\pubservice\Conf;

/**
 * Class WzsAccount 微助手账号
 * @package mysoft\user
 */
class WzsAccount implements IAccount
{
    /**
     * 获取微助手openid
     * @param $cropId 企业ID
     * @param $cropId 微助手设备ID
     * @author 骆兵
     */
    public static function getAccount($tenantId, $cropId)
    {
        $url = Conf::getConfig('api_site') . '/api/qy-auth2/get-wzs-openid' . '?' . http_build_query(['tenant_id' => $tenantId, 'corp_id' => $cropId]);
        $curl = new \mysoft\http\Curl();
        return $curl->get($url."&rand=".rand(1,99999));
    }
}