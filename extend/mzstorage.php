<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://dingxiaoyu.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed dingdayu.
// +----------------------------------------------------------------------
// | Author: dingdayu 614422099@qq.com
// +----------------------------------------------------------------------
// | DATE: 2016/12/18 01:27
// +----------------------------------------------------------------------
// | Explain: 请在这里填写说明
// +----------------------------------------------------------------------

class mzstorage
{
    public $token;

    public function setUrl($url = '')
    {
        if (empty($url)) {
            echo 'token url 不可为空！'.PHP_EOL;
            echo '> 请获取携带token的url并复制到token中！'.PHP_EOL;
            exit();
            //throw new Exception("token url not empty！");
        }

        return $this->getToken(trim($url));
    }

    /**
     * 从token文件中获取token内容.
     *
     * @param string $url [description]
     *
     * @return string token内容
     */
    public function getToken($url = '')
    {
        if (empty($url)) {
            return null;
        }
        if (strpos($url, 'http') === false) {
            $this->token = $url;

            return $this->token;
        }
        $urlArr = parse_url($url);
        $query = $this->convertUrlQuery($urlArr['query']);
        $this->token = $query['token'];

        return $this->token;
    }

    /**
     * 提示用户更新token.
     *
     * @param string $msg 附加提示语
     */
    public function tipUpdateToken($msg = '')
    {
        if (!empty($msg)) {
            echo '[MSG] '.$msg.PHP_EOL;
        }
        echo '[ERROR] TOKEN 失效，请更新token！'.PHP_EOL;
        echo "\t > 刷新flyme云服务的相册页面，复制获取token的js方法到Console窗口下获取token，并更新到token文件中。".PHP_EOL;
        fwrite(STDOUT, '[INPUT] 请输入新的Token:');
        $this->token = trim(fgets(STDIN));
        fwrite(STDOUT, '[NOTICE] Token 已更新。');
    }

    /**
     * 转换url到数组.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param $query
     *
     * @return array
     */
    private function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }

        return $params;
    }

    /**
     * 获取签名.
     *
     * code => 401 用户验证失败
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @return mixed
     */
    public function getSig()
    {
        $url = 'https://mzstorage.meizu.com/file/get_sig';
        $output = $this->curl($url, 'post', ['type' => 2, 'token' => $this->token]);
        //var_dump(json_decode($output, true));
        return json_decode($output, true);
    }

    /**
     * 执行网络请求
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param string $url
     * @param string $method
     * @param array  $data
     *
     * @return mixed
     *
     * @throws Exception
     */
    private function curl($url = '', $method = 'get', $data = [])
    {
        $ch = curl_init();
        if ($method === 'get') {
            $url = $url.'?'.http_build_query($data);
        } else {
            // post数据
            curl_setopt($ch, CURLOPT_POST, 1);
            // post的变量
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);

        if ($output === false) {
            throw new Exception('CURL ERROR: '.curl_error($ch));
        }
        curl_close($ch);

        return $output;
    }

    /**
     * 获取相册列表.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param int $dirId  相册id
     * @param int $offset 偏移量  offset 为 page*limit
     * @param int $limit  每页记录数
     *
     * @return mixed
     */
    public function getAlbumList($dirId = 0, $offset = 0, $limit = 48)
    {
        $url = 'https://mzstorage.meizu.com/album/list';
        //偏移量  offset 为 page*limit
        $output = $this->curl(
            $url,
            'post',
            ['dirId' => $dirId, 'limit' => $limit, 'offset' => $offset, 'order' => 1, 'token' => $this->token]
        );
        //var_dump(json_decode($output, true));
        return json_decode($output, true);
    }

    /**
     * 获取图册列表（相册内容）.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param int $offset 偏移量  offset 为 page*limit
     * @param int $limit  每页记录数
     *
     * @return mixed
     */
    public function getDirList($offset = 0, $limit = 30)
    {
        $url = 'https://mzstorage.meizu.com/album/dir/list';
        //偏移量  offset 为 page*limit
        $output = $this->curl(
            $url,
            'post',
            ['limit' => $limit, 'offset' => $offset, 'order' => 1, 'token' => $this->token]
        );
        //var_dump(json_decode($output, true));
        return json_decode($output, true);
    }

    /**
     * 获取时间轴列表.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @return mixed
     */
    public function getGroup()
    {
        $url = 'https://mzstorage.meizu.com/album/group';
        //偏移量  offset 为 page*limit
        $output = $this->curl(
            $url,
            'post',
            ['order' => 1, 'token' => $this->token]
        );
        //var_dump(json_decode($output, true));
        return json_decode($output, true);
    }

    /**
     * 获取时间轴.
     *
     * @author: dingdayu(614422099@qq.com)
     *
     * @param int $startTime
     * @param int $endTime
     * @param int $limit
     * @param int $offset
     *
     * @return mixed
     */
    public function getListRange($startTime = 0, $endTime = 0, $limit = 100, $offset = 0)
    {
        $url = 'https://mzstorage.meizu.com/album/listRange';
        //偏移量  offset 为 page*limit
        $output = $this->curl(
            $url,
            'post',
            ['startTime' => $startTime, 'endTime' => $endTime, 'limit' => $limit, 'offset' => $offset, 'order' => 1, 'token' => $this->token]
        );
        //var_dump(json_decode($output, true));
        return json_decode($output, true);
    }
}
