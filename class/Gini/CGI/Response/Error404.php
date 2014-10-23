<?php

/**
* @file IMAGE.php
* @brief Response Image
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-10-21
 */
namespace Gini\CGI\Response;

class Error404
{
    private $_content;

    public function output()
    {
        header("HTTP/1.1 404 Not Found");
    }

    public function content()
    {
        return $this->_content;
    }

}
