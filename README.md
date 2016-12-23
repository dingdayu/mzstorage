# mzstorage
魅族相册备份脚本

### 使用方法

1、 登录 https://cloud.flyme.cn/browser/main.jsp 点击 `云相册`

这个时候页面地址栏：`https://photos.flyme.cn/photo`

这个时候，刷新一下页面（F5）。地址栏会变成：`https://photos.flyme.cn/photo?token=******` (token后内容被省略)

2、 赋值地址栏URL，打开`referer`，清空内容，并粘贴！

`linux`或者`mac` 下，可直接在目录下，通过输出通道到`token`中：
```
echo "https://photos.flyme.cn/photo?token=******" > token
```

### 特别注意

1、目前由于`token`失效时间较快，且没实现通过`cookie`自动更新`token`，只能在提示失败后，再按照`使用方法`手动更新`token`

2、下载脚本，魅族相册存储在`阿里OSS（对象存储）`上，目前web上面是通过`STS授权`访问，所以，同样受制于token的失效，幸运的是在获得STS授权后，一个小时才会失效，也就是说，如果你的相册可以在一个小时内下载完毕，那么就不需要手动更新`token`。

3、 大部分错误都有对应的错误提示，并有处理指引，你可以根据提示，进行相关的操作。

### 命令提示

#### 拉取相册：
```
php dir.php
```

#### 拉取相册图片
1、 查看相册列表
```
php album.php
```
2、拉取对应的相册
```
php album.php 277

```
#### 下载图片到本地
```
php down.php
```