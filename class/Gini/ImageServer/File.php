<?php
/**
* @file File.php
* @brief 图片处理
* @author Hongjie Zhu
* @version 0.1.0
* @date 2014-10-21
 */

namespace Gini\ImageServer;

use \Intervention\Image\ImageManagerStatic as Image;

class File
{

    private static function _get_real_path($file)
    {
        $root = \Gini\Config::get('app.root_dir');
        $file = rtrim($root, '/') . '/' . $file;
        return $file;
    }

    public static function globDelete($file)
    {
        $file = self::_get_real_path($file);
        $pattern = $file . '*';
        foreach (glob($pattern) as $f) {
            \Gini\File::delete($f);
        }
        return $pattern;
    }

    public static function fetch($url, $file, $delete_if_exists=false)
    {

        $raw_file = $file;
        $file = self::_get_real_path($file);

        if (file_exists($file)) {
            if (!$delete_if_exists) {
                return true;
            }
        }

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
        return false;
    }

    public static function resize($from, $to, $width, $height=null)
    {
        $from = self::_get_real_path($from);
        $to = self::_get_real_path($to);
        $image = Image::make($from);
        $image->resize($width, $height);
        if (!$image->save($to)) return false;
        return true;
    }

    public static function scale($from, $to, $times)
    {
        $file = self::_get_real_path($from);

        $image = Image::make($file);
        $raw_width = $image->width();
        $raw_height = $image->height();
        $width = $raw_width * $times;
        $height = $raw_height * $times;
        return self::resize($from, $to, $width, $height);
    }

    public static function getContentType($file)
    {
        $file = self::_get_real_path($file);

        $finfo = finfo_open(FILEINFO_MIME);
        $info = finfo_file($finfo, $file);
        finfo_close($finfo);
        $type = substr($info, 0, strpos($info, ';'));
        return $type;
    }

    public static function getContent($file)
    {
        $file = self::_get_real_path($file);

        $content = file_get_contents($file);
        return $content;
    }
}
