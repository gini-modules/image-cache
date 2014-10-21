image-server
============

## Nginx的配置

```nginx
...
location / {
    try_files $uri /index.php$request_uri;
}
location ~* ^/images/.*\.php$ {
    deny all;
}
location ~* \.php {
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
    # NOTE: You should have "cgi.fix_pathinfo = 0;" in php.ini

    fastcgi_pass 127.0.0.1:9000;
    fastcgi_index index.php;

    include fastcgi_params;

    fastcgi_param GINI_MODULE_BASE_PATH ******;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
}
...
```

## app生成image url的方法

```PHP
$url = REAL_URL;
$secret = CLIENT_SECRET;
$cid = CLIENT_ID;

$filename = md5(crypt($url, $secret));
$eURL = urlencode($url);
$eCID = urlencode($cid);

$url = "http://image-server.gapper.com/{$filename}.png?url={$eURL}&client_id={$eCID}";
```
