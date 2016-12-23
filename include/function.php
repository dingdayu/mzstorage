<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://dingxiaoyu.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed dingdayu.
// +----------------------------------------------------------------------
// | Author: dingdayu 614422099@qq.com
// +----------------------------------------------------------------------
// | DATE: 2016/12/18 01:25
// +----------------------------------------------------------------------
// | Explain: 请在这里填写说明
// +----------------------------------------------------------------------

function convertUrlQuery($query)
{
    $queryParts = explode('&', $query);
    $params = array();
    foreach ($queryParts as $param) {
        $item = explode('=', $param);
        $params[$item[0]] = $item[1];
    }
    return $params;
}

function curl($url = '', $method = 'get', $data = [], $heard = [], $referer = '')
{
    $ch = curl_init();
    if ($method == 'get') {
        $url = $url . '?' . http_build_query($data);
    } else {
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    if ($heard) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $heard);
    }

    if ($referer) {
        curl_setopt($ch, CURLOPT_REFERER, $referer);
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $output = curl_exec($ch);

    if ($output == false) {
        throw new Exception('CURL ERROR: ' . curl_error($ch));
    }
    curl_close($ch);
    return $output;
}