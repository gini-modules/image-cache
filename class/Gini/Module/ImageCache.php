<?php

namespace Gini\Module;

class ImageCache
{
    public static function setup()
    {
    }

    public static function diagnose()
    {
        // check image-cache: authorize
        $keys = array_keys(\Gini\Core::$MODULE_INFO);
        if ('image-cache'!==end($keys)) {
            $conf = \Gini\Config::get('app.image_cache');
            try {
                $rpc = \Gini\IoC::construct('\Gini\RPC', rtrim($conf['server'], '/').'/api');
                if (!$rpc->imagecache->authorize($conf['client_id'], $conf['client_secret'])) {
                    return ['Please check your image_cache rpc config in app.yml!'];
                }
            }
            catch (\Exception $e) {
                return ['Please check your image_cache rpc config in app.yml!'];
            }
        }
    }
}
