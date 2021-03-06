<?php

require_once __DIR__.'/vendor/autoload.php';

$token = @file_get_contents('token');
$alioss_sigin = @file_get_contents('.alioss_sigin');

include_once 'vendor/autoload.php';
include_once 'extend/mzstorage.php';
include_once 'extend/SaveToDB.php';
include_once 'extend/OSS.php';

$option = require './config.php';

$mzstorage = new mzstorage();
$saveToDB = new SaveToDB();

$mzstorage->setUrl($token);

$sigin = json_decode($alioss_sigin, true);
if (empty($sigin) || time() - $sigin['time'] > 3500) {
    echo '更新OSS签名！'.PHP_EOL;
    $sigin = $mzstorage->getSig();

    if ($sigin['code'] !== 200) {
        // 提示更新token
        $mzstorage->tipUpdateToken($sigin['message']);
    }

    // 将签名放入缓存文件
    $sigin['time'] = time();
    file_put_contents('.alioss_sigin', json_encode($sigin));
}

$OSS = new OSS(
    $sigin['value']['accessKeyId'],
    $sigin['value']['accessKeySecret'],
    $sigin['value']['endpoint'],
    $sigin['value']['securityToken'],
    $sigin['value']['bucket']
    );

// 从数据库取需要下载的文件链接
$list = $saveToDB->getAlbumList(['is_delted' => 0, 'local' => ''], 1000);

while ($list) {
    foreach ($list as $key => $value) {
        //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
        if ('dir' === $option['DOWN_FILE_TYPE']) {
            $local = $option['DOWN_FILE'].DIRECTORY_SEPARATOR.$value['dirName'].DIRECTORY_SEPARATOR.$value['fileName'];
        } else {
            $local = $option['DOWN_FILE'].DIRECTORY_SEPARATOR.$value['url'];
        }

        try {
            $signUrl = $OSS->signUrl($value['url']);
        } catch (Exception $exception) {
            echo $exception->getMessage().PHP_EOL;
            exit();
        }
        $ret = $OSS->fdown(
            $signUrl,
            $local
        );
        if ($ret) {
            $value['local'] = $value['url'];
            $saveToDB->updateAlbum($value);
            echo "[SUCCESS] '{$value['url']}'".PHP_EOL;
        } else {
            echo "[ERROR] '{$value['url']}'".PHP_EOL;
            exit();
        }
    }
    $list = $saveToDB->getAlbumList(['is_delted' => 0, 'local' => ''], 1000);
}

if (empty($list)) {
    echo '所有照片已下载完毕！'.PHP_EOL;
    exit();
}
exit();
