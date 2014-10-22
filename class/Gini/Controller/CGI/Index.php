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
        $type = \Gini\ImageServer\File::getContentType($file);
        $content = \Gini\ImageServer\File::getContent($file);
        \Gini\IoC::construct('\Gini\CGI\Response\Image', $content, $type)->output();
    }

    private function _get_file($req_file, $url, $client_id)
    {
        if (!$req_file || !$url || !$client_id) return;

        // client_id和client_secret如何存储？
        $client_secret = \Gini\ImageServer\Client::getSecret($client_id);
        if (!$client_secret) return;

        // hash@{w}*{h}.png
        // hash@2x.png
        // hash.png
        // 
        $pattern = '/^([a-z0-9]+)(\@(?:(?:(\d+(?:\.\d+)?)x(\d+(?:\.\d+)?))|(?:(\d+(?:\.\d+)?)x)|(\d+(?:\.\d+)?)))?(\.png|jpg|jpeg|gif|ico|swf)$/';

        $raw_file = md5(crypt($url, $client_secret));

        if (!preg_match($pattern, $req_file, $matches)) return;

        if ($raw_file!==$matches[1]) return;

        $raw_file = $raw_file . $matches[7];
        if (!\Gini\ImageServer\File::fetch($url, $raw_file)) return;

        $file = $req_file;
        if ($file!==$raw_file) {
            // 2x3
            if ($matches[3] && $matches[4]) {
                if (!\Gini\ImageServer\File::resize($raw_file, $file, $matches[3], $matches[4])) {
                    return;
                }
            }
            // 2x
            else if ($matches[5]) {
                if (!\Gini\ImageServer\File::scale($raw_file, $file, $matches[5])) {
                    return;
                }
            }
            // 2
            else if ($matches[6]) {
                if (!\Gini\ImageServer\File::resize($raw_file, $file, $matches[6])) {
                    return;
                }
            }
        }

        return $file;

    }

    private function _show_nothing()
    {
        \Gini\IoC::construct('\Gini\CGI\Response\Nothing')->output();
    }

    public function __index()
    {
        $path_info = substr($_SERVER['PATH_INFO'], 1);
        $form = $this->form();
        $url = $form['url'];
        $client_id = $form['client_id'];
        $file = $this->_get_file($path_info, $url, $client_id);

        if (!$file) return $this->_show_nothing();

        $this->_show($file);

    }

}
