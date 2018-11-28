<?php
/**
* @file ImageCache.php
* @brief 图片处理
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-10-20
 */

namespace Gini\Controller\CGI;

class ImageCache extends \Gini\Controller\CGI
{

    private function _showLoading()
    {
        $form = $this->form('get');

        $file = APP_PATH . '/web/assets/img/loading.gif';
        $type = 'image/gif';
        $content = file_get_contents($file);

        header("HTTP/1.1 404 Not Found");

        header("Cache-Control: no-cache");

        \Gini\IoC::construct('\Gini\CGI\Response\Image', $content, $type)->output();
    }

    private function _show($file)
    {
        $type = \Gini\ImageCache\File::getContentType($file);
        $content = \Gini\ImageCache\File::getContent($file);
        \Gini\IoC::construct('\Gini\CGI\Response\Image', $content, $type)->output();
    }

    // Intervention 支持 GD 和 Imagick
    // 目前我们是用GD
    private static function _needGDCheck()
    {
        return true;
    }

    // https://stackoverflow.com/questions/45174672/imagecreatefrompng-and-imagecreatefromstring-causes-to-unrecoverable-fatal-err
    // GD 库提供的方法有bug，需要用这种蹩脚的方式去处理，也是没有办法. 升级PHP版本的话，是个大成本的事情
    private static function _isGDFile($file)
    {
        $root = \Gini\Config::get('image-cache.cache_dir');
        $file = rtrim($root, '/') . '/' . ltrim($file, '/');
        $mime = strtolower(finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file));
        switch ($mime) {
        case 'image/png':
        case 'image/x-png':
            $method = 'imagecreatefrompng';
            break;
        case 'image/jpg':
        case 'image/jpeg':
        case 'image/pjpeg':
            $method = 'imagecreatefromjpeg';
            break;
        case 'image/gif':
            $method = 'imagecreatefromgif';
            break;
        default:
            return false;
        }
        $output = `php -r "echo $method('$file');" 2>&1`;
        if (empty($output)) {
            return false;
        }
        return true;
    }

    private function _getFile($req_file, $url, $client_id)
    {
        if (!$req_file || !$url || !$client_id) return;

        $config = \Gini\ImageCache\Client::getInfo($client_id);
        if (!$config) return;
        $client_secret = $config['secret'];
        $allowed_sizes = (array)$config['sizes'];
        $allowed_paths = (array)$config['paths'];
        if (!$client_secret) return;

        // hash@{w}*{h}.png
        // hash@2x.png
        // hash.png
        // 
        $pattern = '/^((?:[a-z0-9]+\/)+)?([a-z0-9]+)(\@(?:(?:(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?))|(?:(\d+(?:\.\d+)?)x)|(\d+(?:\.\d+)?)))?(\.png|jpg|jpeg|gif|ico|swf)$/';

        if (!preg_match($pattern, $req_file, $matches)) return;

        $req_path = $matches[1];
        $req_hash = $matches[2];
        $req_size = $matches[3];
        $req_width = $matches[4] ?: $matches[7];
        $req_height = $matches[5];
        $req_times = $matches[6];
        $req_ext = $matches[8];

        if ($req_size && !in_array(substr($req_size, 1), $allowed_sizes)) return;
        if ($req_path && !in_array(substr($req_path, 0, -1), $allowed_paths)) return;

        //$raw_file = md5(crypt($url, $client_secret));
        $hash = \Gini\ImageCache\File::hash($url, $client_secret);
        if ($hash!==$req_hash) return;

        $raw_file = $req_path . $req_hash . $req_ext;

        if (!\Gini\ImageCache\File::has($raw_file)) {
            // 使用aria2进行下载
            // if (!\Gini\ImageCache\File::fetch($url, $raw_file)) return;
            // return $raw_file;
            $config = (array) \Gini\Config::get('image-cache.aria2');
            $client = new \Aria2Client\Client($config['server'] ?: 'http://127.0.0.1:6800/jsonrpc');
            $client->addURL($url, $raw_file);
            return;
        }

        if ($raw_file===$req_file) {
            return $raw_file;
        }

        if (self::_needGDCheck() && !self::_isGDFile($raw_file)) {
            \Gini\ImageCache\File::globDelete($hash);
            return;
        }

        // 2x3
        if ($req_width && $req_height) {
            if (!\Gini\ImageCache\File::resize($raw_file, $req_file, $req_width, $req_height)) {
                return;
            }
        }
        // 2x
        else if ($req_times) {
            if (!\Gini\ImageCache\File::scale($raw_file, $req_file, $req_times)) {
                return;
            }
        }
        // 2
        else if ($req_width) {
            if (!\Gini\ImageCache\File::resize($raw_file, $req_file, $req_width, $req_width)) {
                return;
            }
        }

        return $req_file;

    }

    private function _show404()
    {
        \Gini\IoC::construct('\Gini\CGI\Response\Error404')->output();
    }

    public function __index()
    {
        $path_info = substr($_SERVER['PATH_INFO'], 1);
        $form = $this->form();
        $url = $form['url'];
        $client_id = $form['client_id'];

        $file = $this->_getFile($path_info, $url, $client_id);

        if (!$file) return $this->_showLoading();

        $this->_show($file);

    }

}
