<?php
/**
* @file Index.php
* @brief 图片处理
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-10-20
 */

namespace Gini\Controller\CGI;

class Index extends \Gini\Controller\CGI
{

    private function _show($file)
    {
        $type = \Gini\ImageCache\File::getContentType($file);
        $content = \Gini\ImageCache\File::getContent($file);
        \Gini\IoC::construct('\Gini\CGI\Response\Image', $content, $type)->output();
    }

    private function _getFile($req_file, $url, $client_id)
    {
        if (!$req_file || !$url || !$client_id) return;

        // client_id和client_secret如何存储？
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
        if (!\Gini\ImageCache\File::fetch($url, $raw_file)) return;

        if ($raw_file===$req_file) {
            return $raw_file;
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
            if (!\Gini\ImageCache\File::resize($raw_file, $req_file, $req_width)) {
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

        if (!$file) return $this->_show404();

        $this->_show($file);

    }

}
