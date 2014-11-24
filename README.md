image-cache
============

## 配置 aria2 Server

* 启动docker container的命令
```sh
docker pull pihizi/aria2
docker run --name pihizi-aria2 \
    -v /dev/log:/dev/log \
    -v /YOUR/ARIA2/CONFIG/DIR:/etc/aria2 \
    -v /YOUR/SHARED/DIR:/data/aria2/download \
    -p 6800:6800 \
    -d pihizi/aria2
```

* 名词解释

    /YOUR/ARIA2/CONFIG/DIR: aria2.conf 所属目录

    /YOUR/SHARED/DIR: aria2 下载文件的存放目录

* aria2.conf 实例

``` aria2.conf
# The directory to store the downloaded file
dir=/tmp/aria2-images

# Specify a port number for JSON-RPC/XML-RPC server to listen to. Possible Values: 1024 -65535 Default: 6800
rpc-listen-port=6800

#Listen incoming JSON-RPC/XML-RPC requests on all network interfaces. If false is given, listen only on local loopback interface. Default: false
rpc-listen-all=true

# Enable JSON-RPC/XML-RPC server. Default: false
enable-rpc=true

on-download-complete=/etc/aria2/download-complete.sh
```


## 配置 Nginx Server

```nginx
...
server {
    #listen      8827;
    server_name image-cache.docker.local;

    index index.php index.html index.htm;

    # $root 为image-cache的图片存储目录。
    # nginx在该目录查找图片，如果存在直接返回该图片。如果没有，则交由PHP处理
    # 注意：该目录应该与aria2 server的下载目录为同一个目录。在docker run时挂载同一个目标目录
    set $root /data/images;

    location / {
        root $root;
        # images folder
        try_files $uri /index.php$request_uri;
    }

    location ~* \.php {
        # web folder
        root /data/gini-modules/image-cache/web;

        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        # NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini

        fastcgi_pass 127.0.0.1:9000;
        #fastcgi_pass unix:/var/run/php5-fpm.sock;
        fastcgi_index index.php;

        include fastcgi_params;

        fastcgi_param IMAGE_CACHE_ROOT_PATH $root;
        fastcgi_param GINI_MODULE_BASE_PATH /data/gini-modules;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;

    }

}
...
```

## image-cache server 配置`raw/config/image-cache.yml`

```yml
---
cache_dir: ${IMAGE_CACHE_ROOT_PATH}
curl: 
    proxy: proxy_url
    timeout: 5
aria2:
    server: http://ARIA2-SERVER-URL:6800/jsonrpc
...
```

## 向image-cache server 注册app

    生成CLIENT_ID和CLIENT_SECRET，以及相关的配置信息

```shell
gini image-cache app register
gini image-cache app edit
```

## image-cache client 

    需要调用image-server服务的app，需要添加image-cache依赖，并配置响应的yml

* 配置`raw/config/app.yml`

```yml
---
image_cache:
    server: http://IMAGE-CACHE-SERVER-URL:80
    client_id: CLIENTID
    client_secret: CLIENTSECRET
...
```

* app生成image url

```PHP
# $size = 2x | 70x70 | 70 | null
public static function makeUrl($url, $size=null, $path=null, $format='png')

\Gini\ImageCache::makeUrl(...);
```

## image-cache server 提供了rpc远程调用, 方法如下:
* rpc->authorize($client_id, $client_secret);
* rpc->delete(/\*REAL_URL\*/$url);

