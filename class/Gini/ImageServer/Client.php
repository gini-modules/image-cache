<?php
/**
* @file Client.php
* @brief 管理client_id client_secret的验证
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-10-21
 */

namespace Gini\ImageCache;

class Client
{
    public static function check($client_id, $client_secret)
    {
        $secret = self::getSecret($client_id);
        return $secret===$client_secret;
    }

    public static function getSecret($client_id)
    {
        $file = APP_PATH . '/' . DATA_DIR . '/client/' . $client_id . '.yml';
        if (!file_exists($file)) return;
        $config = (array) \yaml_parse_file($file);
        return $config['secret'];
    }
}
