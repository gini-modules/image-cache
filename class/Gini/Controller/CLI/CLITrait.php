<?php

namespace Gini\Controller\CLI;

abstract class CLITrait extends \Gini\Controller\CLI
{
    private function _getData($v)
    {
        $tmpTitle = $v['title'];
        $tmpEG = $v['example'] ? " (e.g {$v['example']})" : '';
        $tmpDefault = $v['default'] ? "\n\tdefault value is {$v['default']}\n" : '';
        $tmpData = readline($tmpTitle . $tmpEG . $tmpDefault . ': ');
        if (isset($v['default']) && !$tmpData) {
            $tmpData = $v['default'];
        }

        if (isset($tmpData) && $tmpData!=='') {
            return $tmpData;
        }

        if (!$v['optional']) {
            return $this->_getData($v);
        }
    }

    protected function getData($data)
    {
        $result = [];
        foreach ($data as $k => $v) {
            if (!!$v['isMulti']) {
                $tmpResult = [];
                while (true) {
                    $tmpData = $this->_getData($v);
                    if (empty($tmpData)) {
                        break;
                    }
                    array_push($tmpResult, $tmpData);
                }
            }
            else {
                $tmpResult = $this->_getData($v);
            }
            if (!empty($tmpResult)) $result[$k] = $tmpResult;
        }

        return $result;
    }

    protected function surround($string)
    {
        return "\e[31m" . $string . "\e[0m";
    }

    protected function show($msg)
    {
        echo $msg . "\n";
    }

    protected function showError($msg)
    {
        $this->show($this->surround($msg));
    }
}
