image-cache
============

## Nginx的配置

```nginx
...
server {
    #listen      8827;
    server_name image-cache.docker.local;

    index index.php index.html index.htm;

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

## image-cache server 注册app
```shell
gini image-cache app register
gini image-cache app edit
```

## image-cache server 配置`raw/config/image-cache.yml`

```yml
---
cache_dir: /tmp/images
curl: 
    proxy: proxy_url
    timeout: 5
aria2:
    server: http://127.0.0.1:6800/jsonrpc
...
```

## image-cache client 配置`raw/config/app.yml`

```yml
---
image_cache:
    server: http://127.0.0.1:80
    client_id: CLIENTID
    client_secret: CLIENTSECRET
...
```


## rpc远程调用

* rpc->authorize($client_id, $client_secret);
* rpc->delete(/\*REAL_URL\*/$url);

## app生成image url的方法

* app.yml
```app.yml
image_cache:
    server: http://image-cache.gapper.in/:8080
    client_id: ***
    client_secret: ***
```

* php code

```PHP
# $size = 2x | 70x70 | 70 | null
public static function makeUrl($url, $size=null, $path=null, $format='png')

\Gini\ImageCache::makeUrl(...);
```
