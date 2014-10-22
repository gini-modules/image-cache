image-server
============

## Nginx的配置

```nginx
...
server {
    #listen      8827;
    server_name image-server.docker.local;

    index index.php index.html index.htm;

    location / {
        # images folder
        root /tmp/images;
        try_files $uri /index.php$request_uri;
    }

    location ~* \.php {
        # web folder
        root /data/gini-modules/image-server/web;

        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        # NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini

        fastcgi_pass 127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;

        include fastcgi_params;

        fastcgi_param GINI_MODULE_BASE_PATH /data/gini-modules;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;

    }

}
...
```

## 注册app
```shell
gini imageserver app register
```

## 配置图片缓存目录`raw/config/app.yml`

```yml
---
root_dir: /tmp/images
curl_proxy: proxy-url
...
```

## rpc远程调用

* rpc->authorize($client_id, $client_secret);
* rpc->delete(/\*REAL_URL\*/$url);

## app生成image url的方法

```PHP
$url = REAL_URL;
$secret = CLIENT_SECRET;
$cid = CLIENT_ID;

$filename = md5(crypt($url, $secret));
$eURL = urlencode($url);
$eCID = urlencode($cid);

// 原图
$url = "http://image-server.gapper.com/{$filename}.png?url={$eURL}&client_id={$eCID}";
// 指定宽高
$url = "http://image-server.gapper.com/{$filename}@{$width}x{$height}.png?url={$eURL}&client_id={$eCID}";
// 缩放倍数
$url = "http://image-server.gapper.com/{$filename}@{$times}x.png?url={$eURL}&client_id={$eCID}";
// 仅指定宽度，高度自适应
$url = "http://image-server.gapper.com/{$filename}@{$width}.png?url={$eURL}&client_id={$eCID}";
```
