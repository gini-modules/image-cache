<?php

namespace Gini\Controller\CLI\ImageCache;

class App extends \Gini\Controller\CLI\CLITrait
{
    public function __index($params)
    {
        $this->actionHelp($params);
    }

    public function actionEdit()
    {
        $input = $this->getData([
            'client_id'=> [
                'title'=> 'Client ID'
            ]
        ]);
        $client_id = $input['client_id'];
        if (!$client_id) {
            return;
        }

        $file = APP_PATH . '/' . DATA_DIR . '/client/' . $client_id . '.yml';
        if (!file_exists($file)) {
            return $this->showError(sprintf('Client ID <%s> not exists!', $client_id));
        }

        $data = \yaml_parse_file($file);
        $this->show(vsprintf("Clint Secret: %s\nSizes: %s\nPaths: %s", [
            $data['secret'] ?: '-',
            $data['sizes'] ? "\n\t" . join("\n\t", $data['sizes']) : '-',
            $data['paths'] ? "\n\t" . join("\n\t", $data['paths']) : '-'
        ]));

        $this->show("\n" . str_repeat('=', 10) . "\n");

        $newData = $this->getData([
            'secret'=> [
                'title'=> 'New Client Secret',
                'default'=> $data['secret']
            ],
            'sizes'=> [
                'title'=> 'Accepted Sizes',
                'example'=> '[{$width}x{$height} | {$width}x | {$width}] | <ENTER> to finish',
                'isMulti'=> true
            ],
            'paths'=> [
                'title'=> 'Accepted Relative Paths',
                'example'=> '<ENTER> to finish',
                'isMulti'=> true
            ]
        ]);

        $bool = \yaml_emit_file($file, array_merge($data, $newData));
        if ($bool===true) {
            return $this->show('Done!');
        }
        $this->showError('Fail!');
    }

    public function actionRegister()
    {
        $data = $this->getData([
            'id'=> [
                'title'=> 'Client ID',
                'example'=> 'e0933e2cbc44ce1bc3dec13e0c285722'
            ],
            'secret'=> [
                'title'=> 'Client Secret',
                'example'=> 'd6bf74d82b7f1d60462cacd124a7b8c6'
            ],
            'sizes'=> [
                'title'=> 'Accepted Sizes',
                'example'=> '[{$width}x{$height} | {$width}x | {$width}] | <ENTER> to finish',
                'isMulti'=> true
            ],
            'paths'=> [
                'title'=> 'Accepted Relative Paths',
                'example'=> '<ENTER> to finish',
                'isMulti'=> true
            ]
        ]);

        if (empty($data['id'])) {
            return $this->showError('Please input Client ID!');
        }
        if (empty($data['secret'])) {
            return $this->showError('Please input Client Secret!');
        }

        $file = APP_PATH . '/' . DATA_DIR . '/client/' . $data['id'] . '.yml';

        if (file_exists($file)) {
            return $this->showError('Client ID exists!');
        }

        $bool = \yaml_emit_file($file, $data);
        if ($bool===true) {
            return $this->show('Done!');
        }
        $this->showError('Fail!');
    }

    /**
     * ImageCache app 帮助
     * @param $params
     * @return void
     */
    public function actionHelp($params)
    {
        echo "register app: gini imagecache app register\n";
        echo "edit app: gini imagecache app edit\n";
    }

}
