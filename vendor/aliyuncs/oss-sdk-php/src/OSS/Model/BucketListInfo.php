<?php

namespace OSS\Model;

/**
 * Class BucketListInfo
 *
 * ListBuckets接口返回的数据类型
 *
 * @package OSS\Model
 */
class BucketListInfo
{
    /**
     * BucketInfo信息列表
     *
     * @var array
     */
    private $bucketList = array();

    /**
     * BucketListInfo constructor.
     * @param array $bucketList
     */
    public function __construct(array $bucketList)
    {
        $this->bucketList = $bucketList;
    }

    /**
     * 得到BucketInfo列表
     *
     * @return BucketInfo[]
     */
    public function getBucketList()
    {
        return $this->bucketList;
    }
}