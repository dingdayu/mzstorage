<?php

// +----------------------------------------------------------------------
// | JIANKE [ WWW.XYSER.COM ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://dingxiaoyu.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( dingdayu @ JIANKE )
// +----------------------------------------------------------------------
// | Author: dingdayu 614422099@qq.com
// +----------------------------------------------------------------------
// | DATE: 16/9/5 20:53
// +----------------------------------------------------------------------
// | Explain: 请在这里填写说明
// +----------------------------------------------------------------------

date_default_timezone_set('PRC');
include_once 'Log.php';

class Mysql
{
    private $host = '';
    private $port = 3306;
    private $socket = '';
    private $username = '';
    private $passwd = '312422';

    private $dbname = '';

    private $handle;
    private $log;

    public function __construct($options = [])
    {
        if (!class_exists('PDO')) {
            throw new Exception('not found PDO');
        }
        $this->host = empty($options['host']) ? $this->host : $options['host'];
        $this->port = empty($options['port']) ? $this->port : $options['port'];
        $this->username = empty($options['username']) ? $this->username : $options['username'];
        $this->passwd = empty($options['passwd']) ? $this->passwd : $options['passwd'];
        $this->dbname = empty($options['dbname']) ? $this->dbname : $options['dbname'];

        $dns = "mysql:host={$this->host};dbname={$this->dbname}";
        if (!empty($this->port)) {
            $dns .= ';port='.$this->port;
        } elseif (!empty($$this->socket)) {
            $dns .= ';unix_socket='.$this->socket;
        }

        try {
            $this->handle = new PDO(
                $dns,
                $this->username,
                $this->passwd
            );
        } catch (PDOException $e) {
//            echo "** 数据库链接错误，请检查配置！".PHP_EOL;
//            exit();
            throw $e;
        }

        $this->handle->exec('set names utf8mb4');
        $this->log = Log::getInstance();
    }

    /**
     * 通过单例获取实例化.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param array $options
     *
     * @return mixed
     */
    public static function getInstance($options = [])
    {
        if (!$options) {
            $options = require dirname(__FILE__).'/../config.php';
            $options = $options['DB'];
        }
        static $mysql_instance = array();
        $guid = md5(serialize($options));
        if (!isset($mysql_instance[$guid])) {
            $mysql_instance[$guid] = new self($options);
        }

        return $mysql_instance[$guid];
    }

    /**
     * 获取数据库操作指针.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @return PDO
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * 增加一条数据.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param string $sql
     *
     * @return bool|int|string
     */
    public function insert($sql = '')
    {
        if (!empty($sql)) {
            $res = $this->handle->exec($sql);
            if ($res !== 0) {
                return $this->handle->lastInsertId();
            }

            return $res;
        }

        return false;
    }

    /**
     * 更新数据.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param string $sql
     *
     * @return bool|int
     */
    public function update($sql = '')
    {
        if (!empty($sql)) {
            $res = $this->handle->exec($sql);

            return $res;
        }

        return false;
    }

    /**
     * 执行删除.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param string $sql
     *
     * @return bool|int
     */
    public function del($sql = '')
    {
        if (!empty($sql)) {
            $res = $this->handle->exec($sql);

            return $res;
        }

        return false;
    }

    /**
     * 执行sql.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param string $sql
     *
     * @return bool|int
     */
    public function exec($sql = '')
    {
        if (!empty($sql)) {
            $this->saveLog($sql);
            $res = $this->handle->exec($sql);

            return $res;
        }

        return false;
    }

    /**
     * 记录日志.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param string $sql
     */
    public function saveLog($sql = '')
    {
        $file = dirname(__FILE__).'/log/sql_'.date('y_m_d').'.log';
        $this->log->write($sql, $file);
    }

    /**
     * 执行查询.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param string $sql
     *
     * @return array|bool|void
     */
    public function query($sql = '')
    {
        if (empty($sql)) {
            return;
        }
        $this->saveLog($sql);
        $ret = $this->handle->query($sql);
        if (false === $ret) {
            return false;
        }
        $res = $ret->fetchAll(\PDO::FETCH_ASSOC);
        //$res = count($res) == 1 ? $res[0] : $res;
        return $res;
    }
}
