<?php
namespace mysoft\user;

interface IAccount {
    /**
     * 获取账号信息
     * @param $tenantId 租户id
     * @param cropId
     * @return mixed
     */
    public static function getAccount($tenantId,$cropId);
}