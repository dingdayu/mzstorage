<?php

$token = file_get_contents('token');

include_once 'vendor/autoload.php';
include_once 'extend/mzstorage.php';
include_once 'extend/SaveToDB.php';

$mzstorage = new mzstorage();
$mzstorage->setUrl($token);
getDir($mzstorage);

function getDir(mzstorage $mzstorage)
{
    try {
        $dir = $mzstorage->getDirList();
        if ($dir['code'] === 200) {
            $saveToDB = new SaveToDB();
            showDir($dir['value']['dir']);
            $saveToDB->dir($dir['value']['dir']);
            echo "相册更新成功！\r\n";
            // TODO::循环下页
        } else {
            $mzstorage->tipUpdateToken($dir['message']);
            getDir($mzstorage);
        }
    } catch (Exception $exception) {
        if ($exception instanceof PDOException) {
            echo '数据库链接失败，请检查链接！'.PHP_EOL;
            exit();
        }
        echo '遇到未知：'.$exception->getMessage();
        exit();
    }
}

function showDir($list = [])
{
    if (empty($list)) {
        echo "[暂无相册] 请先 'php dir.php' 同步相册列表！".PHP_EOL;
    }
    echo '-------------------'.PHP_EOL;
    echo " id\t相册名称".PHP_EOL;
    echo '-------------------'.PHP_EOL;
    foreach ($list as $value) {
        echo " {$value['dirName']}\t{$value['fileNum']}张".PHP_EOL;
    }
    echo '-------------------'.PHP_EOL;
    echo "请输入: 'php album.php 277' 进行采集".PHP_EOL;
}
