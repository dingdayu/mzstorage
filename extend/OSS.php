<?php

// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://dingxiaoyu.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed dingdayu.
// +----------------------------------------------------------------------
// | Author: dingdayu 614422099@qq.com
// +----------------------------------------------------------------------
// | DATE: 2016/12/19 17:26
// +----------------------------------------------------------------------
// | Explain: 请在这里填写说明
// +----------------------------------------------------------------------

class OSS {
    private $accessKeyId;
    private $accessKeySecret;
    private $endpoint;
    private $securityToken;
    private $bucket;

    private $ossClient;

    private $timeout = 300;

    /**
     * OSS constructor.
     *
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $endpoint
     * @param $securityToken
     * @param $bucket
     */
    public function __construct($accessKeyId, $accessKeySecret, $endpoint, $securityToken, $bucket)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->endpoint = $endpoint;
        $this->securityToken = $securityToken;
        $this->bucket = $bucket;

        if($this->accessKeyId && $this->accessKeySecret && $this->endpoint && $this->securityToken) {
            $this->ossClient = new \OSS\OssClient(
                $accessKeyId,
                $accessKeySecret,
                $endpoint,
                false,
                $securityToken
            );
        }
    }

    /**
     * 获取对应object签名后的url.
     *
     * @author: dingdayu(614422099@qq.com)
     * @param string $object
     *
     * @return string|void
     */
    public function signUrl($object = '')
    {
        try {
            $signedUrl = $this->ossClient->signUrl($this->bucket, $object, $this->timeout);
        } catch (OssException $e) {
            printf(__FUNCTION__ . ": FAILED\n");
            printf($e->getMessage() . "\n");
            return;
        }
        return $signedUrl;
    }

    /**
     * @param mixed $accessKeyId
     */
    public function setAccessKeyId($accessKeyId)
    {
        $this->accessKeyId = $accessKeyId;
    }

    /**
     * @param mixed $accessKeySecret
     */
    public function setAccessKeySecret($accessKeySecret)
    {
        $this->accessKeySecret = $accessKeySecret;
    }

    /**
     * @param mixed $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param mixed $securityToken
     */
    public function setSecurityToken($securityToken)
    {
        $this->securityToken = $securityToken;
    }

    /**
     * @param mixed $bucket
     */
    public function setBucket($bucket)
    {
        $this->bucket = $bucket;
    }

    /**
     * 根据签名链接下载文件，并保存到本地.
     *
     * @author: dingdayu(614422099@qq.com)
     * @param string $signedUrl
     * @param string $path
     *
     * @return bool
     */
    public function down($signedUrl = '', $path = '')
    {
        if(empty($signedUrl)) {
            //echo "[ERROR] {$path}";
            return false;
        }
        try {
            $content = $this->curl($signedUrl, 'get');
        } catch (\Exception $exception) {
            //echo "$path " . $exception->getMessage() . PHP_EOL;
            return false;
        }

        // 写入文件
        $ret = $this->write($path, $content);
        return $ret;
    }

    /**
     * curl
     *
     * @author: dingdayu(614422099@qq.com)
     * @param string $url
     * @param string $method
     * @param array $data
     * @param array $heard
     * @param string $referer
     * @param string $cookies
     * @return mixed
     * @throws Exception
     */
    private function curl($url = '', $method = 'get', $data = [],
                          $heard = [], $referer = '', $cookies = '')
    {
        $ch = curl_init();
        if ($method == 'get' && !empty($data)) {
            $url = $url . '?' . http_build_query($data);
        } elseif ($method == 'post' &&!empty($data)) {
            // post数据
            curl_setopt($ch, CURLOPT_POST, 1);
            // post的变量
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } elseif ($method == 'post') {
            // post数据
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if (!empty($referer)) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        if (!empty($heard)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $heard);
        }

        if(!empty($cookies)) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies); //保存cookie
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies); //读取cookie
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpCode != 200) {
            throw new Exception('HTTP_CODE ERROR: ' . $httpCode);
        }

        if ($output == false) {
            throw new Exception('CURL ERROR: ' . curl_error($ch));
        }
        curl_close($ch);

        return $output;
    }

    /**
     * 写入文件.
     *
     * @author: dingdayu(614422099@qq.com)
     * @param string $path
     * @param string $content
     *
     * @return bool
     */
    public function write($path = '', $content = '')
    {
        return (is_dir(dirname($path)) || $this->mkdir($path)) && file_put_contents($path, $content);
    }

    /**
     * 判断文件夹是否存在，并创建.
     *
     * @author: dingdayu(614422099@qq.com)
     * @param string $path
     *
     * @return bool
     */
    public function mkdir($path = '')
    {

        return is_dir(dirname($path)) || mkdir(dirname($path), 0777, true);
    }

    /**
     * 读取文件并保存到本地
     * 理论上支持较大文件
     *
     * 支持的协议：
     * @url：http://php.net/manual/zh/wrappers.php
     *
     * @author: dingdayu(614422099@qq.com)
     * @param string $signedUrl
     * @param string $path
     * @return bool
     */
    public function fdown($signedUrl = '', $path = '')
    {
        if(empty($signedUrl)) {
            //echo "[ERROR] {$path}";
            return false;
        }
        try {

            is_dir(dirname($path)) || $this->mkdir($path);

            $fileUrl = fopen($signedUrl, "rb");
            if ($fileUrl) {
                // 获取文件大小
                $filesize = -1;
                $headers = get_headers($signedUrl, 1);
                if (!array_key_exists("Content-Length", $headers)) {
                    $filesize = 0;
                }else {
                    $filesize = $headers["Content-Length"];
                }

                //不是所有的文件都会先返回大小的，
                //有些动态页面不先返回总大小，这样就无法计算进度了

                if ($filesize != -1) {
                    $echo = "SIZE: " . $filesize;
                }

                $fileSave = fopen($path, "wb");
                $downlen = 0;
                if ($fileSave)
                    while (!feof($fileUrl)) {
                        $data = fread($fileUrl, 1024 * 8);    //默认获取8K
                        $downlen += strlen($data);    // 累计已经下载的字节数
                        fwrite($fileSave, $data, 1024 * 8);

                        if($filesize != -1 && $filesize != 0) {
                            printf("当前进度: [%-50s] %d%% 占用：%dM/%dM\r",
                                str_repeat('#', $downlen/$filesize*100/2),
                                $downlen/$filesize*100,
                                ($downlen/1024/1024),
                                ($filesize/1024/1024)
                            );
                        } else {
                            printf("文件总量: %s 已下载：%dM \r", $filesize, $downlen/1024);
                        }

                        //ob_flush();
                        //flush();
                    }
            }

        } catch (\Error | \Exception $exception) {
            // Throwable 支持PHP7
            //echo "$path " . $exception->getMessage() . PHP_EOL;
            return false;
        } finally {
            fclose($fileUrl);
            fclose($fileSave);
        }

        return true;
    }
}