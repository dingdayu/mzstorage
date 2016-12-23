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

$referer = file_get_contents('referer');

include_once "vendor/autoload.php";
include_once "extend/mzstorage.php";
include_once 'extend/SaveToDB.php';

$mzstorage = new mzstorage();
$mzstorage->setUrl($referer);


$SaveToDB = new SaveToDB();

$startTime = strtotime(date('Y-m-d',strtotime('-30 day'))) . '000';
$endTime = strtotime(date('Y-m-d')) . '000';

$offset = 0;
$limit = 100;

do {

    $album = $mzstorage->get_listRange($startTime, $endTime, $limit, $offset);

    if($album['code'] == 200) {
        //var_dump($dir['value']);
        $SaveToDB->album($album['value']['file']);
        $count = count($album['value']['file']);
        $offset = $offset + $count;
        echo "相册拉取：{$offset}/{$album['value']['count']} 张" . PHP_EOL;

    } else {
        echo $album['message'] . PHP_EOL;
        echo "[ERROR] TOKEN 失效，请更新token！". PHP_EOL;
        exit();
    }

    sleep(3);
} while (!$album['value']['end']);

echo "[" . date('Y-m-d') . "] "."[" . date('Y-m-d',strtotime('-30 day')) . "] 更新完成！";
