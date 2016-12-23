<?php
require_once __DIR__ . '/vendor/autoload.php';

$referer = @file_get_contents ('referer');
$alioss_sigin = @file_get_contents ('.alioss_sigin');

include_once "vendor/autoload.php";
include_once "extend/mzstorage.php";
include_once 'extend/SaveToDB.php';
include_once 'extend/OSS.php';

$option = require ('./config.php');

$mzstorage = new mzstorage();
$SaveToDB = new SaveToDB();

$mzstorage->setUrl($referer);

$sigin = json_decode($alioss_sigin, true);
if(empty($sigin) || time() - $sigin['time'] > 3500) {
    echo "更新OSS签名！" . PHP_EOL;
    $sigin = $mzstorage->get_sig();

    if($sigin['code'] != 200) {
        echo $sigin['message'] . PHP_EOL;
        echo "请重新输入URL" . PHP_EOL;
        exit();
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

// 不支持多进程
if( true || !function_exists('pcntl_fork')) {
    do{
        // 从数据库取需要下载的文件链接
        $list = $SaveToDB->getAlbumList(['is_delted' => 0, 'local' => ''], 2);

        if(empty($list)){
            echo "所有照片已下载完毕！".PHP_EOL;
            exit();
        }
        foreach ($list as $key => $value) {
            //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
            $local = $option['DOWN_FILE'] .DIRECTORY_SEPARATOR.$value['url'];

            try {
                $signUrl = $OSS->signUrl($value['url']);
            } catch (Exception $exception) {
                echo $exception->getMessage() . PHP_EOL;
                exit();
            }
            $ret = $OSS->down(
                $signUrl,
                $local
            );
            if($ret) {
                $value['local'] = $value['url'];
                $SaveToDB->updateAlbum($value);
                echo "[SUCCESS] '{$value['url']}'" . PHP_EOL;
            } else {
                echo "[ERROR] '{$value['url']}'" . PHP_EOL;
                exit();
            }
        }
    } while($list);
    exit();
} else {
        // 从数据库取需要下载的文件链接
        $list = $SaveToDB->getAlbumList(['dirId' => 277, 'local' => ''], 4000);
        if(empty($list)){
            echo "所有图片下载完毕！";
            exit();
        }
        // 多进程
        foreach ($list as $value) {
            $pid = pcntl_fork();
            //父进程和子进程都会执行下面代码
            if ($pid == -1) {
                //错误处理：创建子进程失败时返回-1.
                die('could not fork');
            } else if ($pid) {
                //父进程会得到子进程号，所以这里是父进程执行的逻辑
                pcntl_wait($status); //等待子进程中断，防止子进程成为僵尸进程。
            } else {
                //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
                $value['local'] = 'down/'.$value['url'];
                try {
                    $signUrl = $OSS->signUrl($value['url']);
                } catch (Exception $exception) {
                    echo $exception->getMessage() . PHP_EOL;
                    exit();
                }
                $ret = $OSS->down(
                    $signUrl,
                    $value['local']
                );
                if($ret) {
                    $SaveToDB->updateAlbum($value);
                    echo "[SUCCESS] [{$pid}] '{$value['url']}'" . PHP_EOL;
                } else {
                    echo "[ERROR] [{$pid}] '{$value['url']}'" . PHP_EOL;
                }
                exit($pid);
            }
        }
}

