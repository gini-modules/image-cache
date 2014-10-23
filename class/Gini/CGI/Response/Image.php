<?php

/**
* @file IMAGE.php
* @brief Response Image
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-10-21
 */
namespace Gini\CGI\Response;

class Image
{
    private $_content;
    private $_type;

    public function __construct($content, $type=null)
    {
        $this->_content = $content;
        $this->_type = $type;
    }

    public function output()
    {
        switch ($this->_type) {
            case 'image/png':
            default:
                $type = 'image/png';
        }
        header("Content-Type: {$type}");
        file_put_contents('php://output', (string) $this->_content);
    }

    public function content()
    {
        return $this->_content;
    }

}
