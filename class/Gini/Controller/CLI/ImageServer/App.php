<?php

namespace Gini\Controller\CLI\ImageServer;

class App extends \Gini\Controller\CLI\CLITrait
{
    public function __index($params)
    {
        $this->actionHelp($params);
    }

    public function actionRegister()
    {
        $data = $this->getData([
            'client_id'=> [
                'title'=> 'Client ID',
                'example'=> 'e0933e2cbc44ce1bc3dec13e0c285722'
            ],
            'client_secret'=> [
                'title'=> 'Client Secret',
                'example'=> 'd6bf74d82b7f1d60462cacd124a7b8c6'
            ]
        ]);

        if (empty($data['client_id'])) {
            return $this->showError('Please input Client ID!');
        }
        if (empty($data['client_secret'])) {
            return $this->showError('Please input Client Secret!');
        }

        $file = APP_PATH . '/' . DATA_DIR . '/client/' . $data['client_id'] . '.yml';

        $yml = ['secret'=> $data['client_secret']];
        $bool = \yaml_emit_file($file, $yml);
        if ($bool===true) {
            $this->show('Done!');
            return;
        }
        $this->showError('Fail!');
    }

    /**
     * ImageServer app 帮助
     * @param $params
     * @return void
     */
    public function actionHelp($params)
    {
        echo "register app: gini imageserver app register\n";
    }

}
