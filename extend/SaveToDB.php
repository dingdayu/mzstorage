<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://dingxiaoyu.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed dingdayu.
// +----------------------------------------------------------------------
// | Author: dingdayu 614422099@qq.com
// +----------------------------------------------------------------------
// | DATE: 2016/12/18 01:59
// +----------------------------------------------------------------------
// | Explain: 请在这里填写说明
// +----------------------------------------------------------------------

include_once 'Mysql.php';

class SaveToDB
{
    public function __construct()
    {
    }

    /**
     * 更新相册目录.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param array $dir
     */
    public function dir($dir = [])
    {
        foreach ($dir as $key => $item) {
            $album = $this->getDir($item['id']);
            if (empty($album)) {
                // 添加
                $this->addDir($item);
            } else {
                // 更新
                $this->updateDir($item);
            }
        }
    }

    /**
     * 获取一个相册.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param int $dir_id
     *
     * @return mixed
     */
    public function getDir($dir_id = 0)
    {
        $sql = "select * from `dy_mz_dir` WHERE `dir_id` = '{$dir_id}' AND `is_delted` = 0";
        $ret = Mysql::getInstance()->query($sql);

        return $ret;
    }

    /**
     * 添加一个相册.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param $item
     *
     * @return mixed
     */
    private function addDir($item = [])
    {
        $date_time = date('Y-m-d H:i:s');
        $sql = "insert into `dy_mz_dir` (`dir_id`, `dirName`, `fileNum`, `icon`, `sqlNow`, `modifyTime`, `createTime`, `userId`, `totalSize`, `status`, `create_time`, `update_time`) values ('{$item['id']}', '{$item['dirName']}', '{$item['fileNum']}', '{$item['icon']}', '{$item['sqlNow']}', '{$item['modifyTime']}', '{$item['createTime']}', '{$item['userId']}', '{$item['totalSize']}', '{$item['status']}', '{$date_time}', '{$date_time}')";

        return Mysql::getInstance()->insert($sql);
    }

    /**
     * 更新一个相册.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param array $item
     *
     * @return mixed
     */
    private function updateDir($item = [])
    {
        $date_time = date('Y-m-d H:i:s');
        $sql = "update `dy_mz_dir` set `dirName`='{$item['dirName']}', `fileNum`='{$item['fileNum']}', `icon`='{$item['icon']}', `sqlNow`='{$item['sqlNow']}', `modifyTime`='{$item['modifyTime']}', `createTime`='{$item['createTime']}', `userId`='{$item['userId']}', `totalSize`='{$item['totalSize']}', `status`='{$item['status']}', `update_time`='{$date_time}' where `dir_id`='{$item['id']}' ";

        return Mysql::getInstance()->update($sql);
    }

    /**
     * 更新相册图片.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param array $album
     */
    public function album($album = [])
    {
        foreach ($album as $key => $item) {
            $album = $this->getAlbum($item['id']);
            if (empty($album)) {
                // 添加
                $this->addAlbum($item);
            } else {
                // 更新
                $item['album_id'] = $item['id'];
                unset($item['id']);
                $this->updateAlbum($item);
            }
        }
    }

    /**
     * 获取一个相册.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param int $album_id
     *
     * @return mixed
     */
    private function getAlbum($album_id = 0)
    {
        $sql = "select * from `dy_mz_album` WHERE `album_id` = '{$album_id}' AND `is_delted` = 0";
        $ret = Mysql::getInstance()->query($sql);

        return $ret;
    }

    /**
     * 添加一个相册.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param $item
     *
     * @return mixed
     */
    private function addAlbum($item = [])
    {
        $date_time = date('Y-m-d H:i:s');
        $item['isVideo'] = (int) $item['isVideo'];
        $sql = <<<SQL
INSERT INTO `dy_mz_album`(
    `album_id` ,
	`dirId` ,
	`dirName` ,
	`fileName` ,
	`url` ,
	`size` ,
	`shootTime` ,
	`height` ,
	`width` ,
	`md5` ,
	`groupId` ,
	`groupDirId` ,
	`uid` ,
	`userId`,
	`thumb256` ,
	`thumb1024` ,
	`isVideo` ,
	`tags` ,
	`remainTrashTime` ,
	`sqlNow` ,
	`createTime` ,
	`modifyTime` ,
	`status` ,
	`create_time` ,
	`update_time` ,
	`is_delted`
)
VALUES
	(
		'{$item['id']}' ,
		'{$item['dirId']}' ,
		'{$item['dirName']}' ,
		'{$item['fileName']}' ,
		'{$item['url']}' ,
		'{$item['size']}' ,
		'{$item['shootTime']}' ,
		'{$item['height']}' ,
		'{$item['width']}' ,
		'{$item['md5']}' ,
		'{$item['groupId']}' ,
		'{$item['groupDirId']}' ,
		'{$item['uid']}' ,
		'{$item['userId']}' ,
		'{$item['thumb256']}' ,
		'{$item['thumb1024']}' ,
		'{$item['isVideo']}' ,
		'{$item['tags']}' ,
		'{$item['remainTrashTime']}' ,
		'{$item['sqlNow']}' ,
		'{$item['createTime']}' ,
		'{$item['modifyTime']}' ,
		'{$item['status']}' ,
		'{$date_time}' ,
		'{$date_time}' ,
		'0'
	)
SQL;

        return Mysql::getInstance()->insert($sql);
    }

    /**
     * 更新一个相册.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param array $item
     *
     * @return mixed
     */
    public function updateAlbum($item = [])
    {
        $date_time = date('Y-m-d H:i:s');

        $item['update_time'] = $date_time;
        unset($item['id']);

        $whereSet = '';

        if (!empty($item)) {
            foreach ($item as $key => $value) {
                if ($key !== 'album_id') {
                    $whereSet .= "`{$key}` = '{$value}' ,";
                }
            }
        }
        $whereSet = trim($whereSet, ',');

        $sql = "UPDATE `dy_mz_album` SET {$whereSet} WHERE `album_id` = '{$item['album_id']}'";

        return Mysql::getInstance()->update($sql);
    }

    public function getAlbumList($where = [], $limit = 0)
    {
        $whereStr = '';
        $limitStr = '';
        if (!empty($where)) {
            foreach ($where as $key => $value) {
                if (!empty($whereStr)) {
                    $whereStr .= ' AND ';
                }
                // 支持<> 不等写法
                if (is_array($value)) {
                    $whereStr .= "`{$key}` {$value[0]} '$value[1]'";
                } else {
                    $whereStr .= "`{$key}` = '$value'";
                }
            }
        }

        if (!empty($limit)) {
            $limitStr = 'LIMIT '.$limit;
        }

        if (empty($whereStr)) {
            $sql = "select * from `dy_mz_album` WHERE `is_delted` = 0 {$limitStr}";
        } else {
            $sql = "select * from `dy_mz_album` WHERE `is_delted` = 0 AND {$whereStr} {$limitStr}";
        }

        try {
            $ret = Mysql::getInstance()->query($sql);
        } catch (Exception $exception) {
            echo $sql.PHP_EOL;
            echo $exception->getMessage().PHP_EOL;
            exit();

            return [];
        }

        return $ret;
    }

    /**
     * 获取相册目录列表.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @return mixed
     */
    public function getDirList()
    {
        $sql = 'select * from `dy_mz_dir` WHERE `is_delted` = 0';
        $ret = Mysql::getInstance()->query($sql);

        return $ret;
    }
}
