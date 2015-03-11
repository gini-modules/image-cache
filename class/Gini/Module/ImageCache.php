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
        if ('image-cache'!==end(array_keys(\Gini\Core::$MODULE_INFO))) {
            $conf = \Gini\Config::get('app.image_cache');
            $rpc = \Gini\IoC::construct('\Gini\RPC', $conf['server']);
            if (!$rpc->imagecache->authorize($conf['client_id'], $conf['client_secret'])) {
                return ['Please check your image_cache rpc config in app.yml!'];
            }
        }
    }
}
