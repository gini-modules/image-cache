<?php
/**
* @file Index.php
* @brief 图片处理
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-10-20
 */

namespace Gini\Controller\CGI;

use \Intervention\Image\ImageManagerStatic as Image;

class Index extends \Gini\Controller\CGI
{

    private function _show($file)
    {
        $finfo = finfo_open(FILEINFO_MIME);
        $info = finfo_file($finfo, $file);
        finfo_close($finfo);
        $type = substr($info, 0, strpos($info, ';'));
        $content = file_get_contents($file);
        \Gini\IoC::construct('\Gini\CGI\Response\Image', $content, $type)->output();
    }

    private function _get_secret($client_id)
    {
        $file = APP_PATH . '/' . DATA_DIR . '/client/' . $client_id . '.yml';
        if (!file_exists($file)) return;
        $content = file_get_contents($file);
        $config = (array) \yaml_parse($content);
        $secret = $config['secret'];
        return $secret;
    }

    private function _get_file($path_info, $url, $client_id)
    {
        if (!$path_info || !$url || !$client_id) return;

        // hash@{w}*{h}.png
        // hash@2x.png
        // hash.png
        $pattern = '/^\/([a-z0-9]+)(\@(?:(?:(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?))|(?:(\d+(?:\.\d+)?)x)))?(\.png|jpg|jpeg|gif|ico|swf)$/';

        // client_id和client_secret如何存储？
        $client_secret = $this->_get_secret($client_id);
        if (!$client_secret) return;

        $raw_file = md5(crypt($url, $client_secret));

        if (!preg_match($pattern, $path_info, $matches)) return;

        if ($raw_file!==$matches[1]) return;

        $root = \Gini\Config::get('app.root_dir');

        $raw_file = $root . '/' . $raw_file . $matches[6];
        if (!file_exists($raw_file)) {
            if (!$this->_fetch($url, $raw_file)) return;
        }

        $file = $root . '/' . $matches[1] . $matches[2] . $matches[6];
        if ($file!==$raw_file) {
            $image = Image::make($raw_file);
            $raw_width = $image->width();
            $raw_height = $image->height();
            // 2x3
            if ($matches[3] && $matches[4]) {
                $image = $image->resize($matches[3], $matches[4]);
            }
            // 2x
            else if ($matches[5]) {
                $image = $image->resize($raw_width * $matches[5], $raw_height * $matches[5]);
            }
            if (!$image->save($file)) return;
        }

        return $file;

    }

    private function _fetch($url, $file) 
    {
        $ch = curl_init();
        $handler = fopen($file, 'w');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILE, $handler);
        $content = curl_exec($ch);
        $hasError = curl_errno($ch);
        curl_close($ch);
        fclose($handler);
        if (!$hasError) {
            return true;
        }
    }

    private function _show_nothing()
    {
        \Gini\IoC::construct('\Gini\CGI\Response\Nothing')->output();
    }

    public function __index()
    {
        $path_info = $_SERVER['PATH_INFO'];
        $form = $this->form();
        $url = $form['url'];
        $client_id = $form['client_id'];
        $file = $this->_get_file($_SERVER['PATH_INFO'], $url, $client_id);

        if (!$file) return $this->_show_nothing();

        $this->_show($file);

    }

}
