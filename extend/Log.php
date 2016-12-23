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
// | DATE: 16/9/6 00:04
// +----------------------------------------------------------------------
// | Explain: 请在这里填写说明
// +----------------------------------------------------------------------

class Log
{
    protected $config = array(
        'log_time_format' => ' c ',
        'log_file_size'   => 2097152,
        'log_path'        => '',
    );

    // 实例化并传入参数
    public function __construct($config = [])
    {
        $this->config['log_path'] = dirname(__FILE__) . '/';
        $this->config = array_merge($this->config, $config);
    }

    public static function getInstance()
    {
        static $log_instance = null;
        if (!isset($log_instance)) {
            $log_instance = new Log();
        }
        return $log_instance;
    }

    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination  写入目标
     * @return void
     */
    public function write($log, $destination = '')
    {
        $now = date($this->config['log_time_format']);
        if (empty($destination)) {
            $destination = $this->config['log_path'] . date('y_m_d') . '.log';
        }
        // 自动创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor($this->config['log_file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . '/' . time() . '-' . basename($destination));
        }
        error_log("[{$now}] " . "\r\n{$log}\r\n", 3, $destination);
    }
}