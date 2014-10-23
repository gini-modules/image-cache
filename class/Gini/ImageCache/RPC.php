<?php

namespace Gini\ImageCache;

class RPC
{
    private $_sess_key = 'imageserver.rpc.current_client';
    public static function setCurrentClient($client_id)
    {
        $_SESSION[$_sess_key] = $client_id;
        return true;
    }

    public static function getCurrentClient()
    {
        return $_SESSION[$_sess_key];
    }
}
