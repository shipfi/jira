<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace mysoft\base;

/**
 * DAL集成测试基类
 * table断言API （https://phpunit.de/manual/current/zh_cn/database.html)
 * @author tianl
 */
abstract class DalUnitTestBase extends \PHPUnit_Extensions_Database_TestCase {

    private $orgcode;

    public function __construct($orgcode) {
        $this->orgcode = $orgcode;
    }

    // 只实例化 pdo 一次，供测试的清理和装载基境使用
    static private $pdo = null;
    // 对于每个测试，只实例化 PHPUnit_Extensions_Database_DB_IDatabaseConnection 一次
    private $conn = null;

    final public function getConnection() {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = DB($this->orgcode)->pdo;
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $this->orgcode);
        }

        return $this->conn;
    }

}
