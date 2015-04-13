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
            $conf = \Gini\Config::get('image-cache');
            try {
                $rpc = \Gini\IoC::construct('\Gini\RPC', rtrim($conf['server'], '/').'/api');
                if (!$rpc->imagecache->authorize($conf['client_id'], $conf['client_secret'])) {
                    return ['Please check your rpc config in image-cache.yml!'];
                }
                $sizes = $conf['sizes'];
                if (is_array($sizes) && !$rpc->imagecache->hasSizes($sizes)) {
                    return ['image-cache: not all image sizes are registered!'];
                }
                $paths = $conf['paths'];
                if (is_array($paths) && !$rpc->imagecache->hasPaths($paths)) {
                    return ['image-cache not all image paths are registered!'];
                }
            }
            catch (\Exception $e) {
                return ['Please check your rpc config in image-cache.yml!'];
            }
        }
    }
}
