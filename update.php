<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://dingxiaoyu.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed dingdayu.
// +----------------------------------------------------------------------
// | Author: dingdayu 614422099@qq.com
// +----------------------------------------------------------------------
// | DATE: 2016/12/21 10:05
// +----------------------------------------------------------------------
// | Explain: 请在这里填写说明
// +----------------------------------------------------------------------

$token = file_get_contents('token');

include_once 'vendor/autoload.php';
include_once 'extend/mzstorage.php';
include_once 'extend/SaveToDB.php';

$mzstorage = new mzstorage();
$mzstorage->setUrl($token);

$saveToDB = new SaveToDB();

$startTime = strtotime(date('Y-m-d', strtotime('-30 day')));
$endTime = strtotime(date('Y-m-d'));

$offset = 0;
$limit = 100;

do {
    $album = $mzstorage->getListRange($startTime.'000', $endTime.'000', $limit, $offset);

    if ($album['code'] === 200) {
        //var_dump($dir['value']);
        $saveToDB->album($album['value']['file']);
        $count = count($album['value']['file']);
        $offset = $offset + $count;
        echo "相册拉取：{$offset}/{$album['value']['count']} 张".PHP_EOL;
        sleep(1);
    } else {
        $mzstorage->tipUpdateToken($album['message']);
    }
} while (!$album['value']['end']);

echo '['.date('Y-m-d', $startTime).'] '.'['.date('Y-m-d', $endTime).'] 更新完成！';
