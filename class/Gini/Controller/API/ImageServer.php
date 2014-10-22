<?php
/**
* @file ImageServer.php
* @brief RPC
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-10-21
 */

namespace Gini\Controller\API;

class ImageServer extends \Gini\Controller\API
{

    public function actionAuthorize($client_id, $client_secret)
    {
        if (!\Gini\ImageServer\Client::check($client_id, $client_secret)) return false;
        return \Gini\ImageServer\RPC::setCurrentClient($client_id)===true ? true : false;
    }

    public function actionDelete($url)
    {
        $client_id = \Gini\ImageServer\RPC::getCurrentClient();
        if (!$client_id) return;
        $client_secret = \Gini\ImageServer\Client::getSecret($client_id);
        if (!$client_secret) return;
        $filename = md5(crypt($url, $client_secret));
        return \Gini\ImageServer\File::globDelete($filename);
    }

} // END class
