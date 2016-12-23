<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://dingxiaoyu.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed dingdayu.
// +----------------------------------------------------------------------
// | Author: dingdayu 614422099@qq.com
// +----------------------------------------------------------------------
// | DATE: 2016/12/18 15:15
// +----------------------------------------------------------------------
// | Explain: 请在这里填写说明
// +----------------------------------------------------------------------
$token = file_get_contents('token');

include_once "vendor/autoload.php";
include_once "extend/mzstorage.php";
include_once 'extend/SaveToDB.php';

$mzstorage = new mzstorage();
$mzstorage->setUrl($token);


$SaveToDB = new SaveToDB();


// 选择相册
if(empty($argv[1])) {
    echo "请选择相册：" . PHP_EOL;
    $list = $SaveToDB->getDirList();
    if(empty($list)) {
        echo "[暂无相册] 请先 'php dir.php' 同步相册列表！".PHP_EOL;
        exit();
    }
    echo "-------------------" . PHP_EOL;
    echo " id\t相册名称".PHP_EOL;
    echo "-------------------" . PHP_EOL;
    foreach ($list as $value) {
        echo " {$value['dir_id']}\t{$value['dirName']}".PHP_EOL;
    }
    echo "-------------------" . PHP_EOL;
    echo "请输入: 'php album.php 277' 进行采集" . PHP_EOL;
    exit();
}

$dirID = $argv[1];
$offset = 0;
$limit = 48;


$dirInfo = $SaveToDB->getDir($dirID);
if(empty($dirInfo)) {
    echo "[ERROR] 相册id输入错误，请重新输入！";
    exit();
}

do {

    $album = $mzstorage->get_album_list($dirID, $offset, $limit);

    if($album['code'] == 200) {
        //var_dump($dir['value']);
        $SaveToDB->album($album['value']['file']);
        $count = count($album['value']['file']);
        $offset = $offset + $count;
        echo "相册拉取：{$offset}/{$album['value']['count']} 张" . PHP_EOL;

    } else {
        echo $album['message'] . PHP_EOL;
        echo "[ERROR] TOKEN 失效，请更新token！". PHP_EOL;
        echo "> 请获取携带token的url并复制到token中！";
        exit();
    }

    sleep(3);
} while (!$album['value']['end']);

echo "{$dirInfo['dirName']} 更新完成！";

