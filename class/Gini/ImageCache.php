<?php
/**
* @file File.php
* @brief 图片处理
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-10-21
 */

namespace Gini;

class ImageCache
{
    public static function makeURL($url, $size=null, $path=null, $format='png')
    {
        $config = (array)\Gini\Config::get('image-cache');
        if (empty($config)) return $url;

        $server = $config['server'];
        if (empty($server)) return $url;

        $client_id = $config['client_id'];
        $client_secret = $config['client_secret'];

        if (!$client_id || !$client_secret) return $url;

        $hash = hash_hmac('md5', $url, $client_secret);

        $result = vsprintf('%s%s%s%s.%s?url=%s&client_id=%s', [
            rtrim($server, '/') . '/',
            $path ? rtrim($path, '/') . '/' : '',
            $hash,
            $size ? '@' . $size : '',
            $format,
            urlencode($url),
            urlencode($client_id)
        ]);

        return $result;
    }
}
