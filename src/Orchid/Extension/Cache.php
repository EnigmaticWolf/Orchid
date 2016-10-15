<?php

namespace AEngine\Orchid\Extension;

use AEngine\Orchid\App;
use RecursiveDirectoryIterator;

class Cache
{
    /**
     * Writes data to a temporary file
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $expire
     *
     * @return int|false
     */
    public static function write($key, $value, $expire = -1)
    {
        $data = [
            'expire' => ($expire >= 0) ? (is_string($expire) ? strtotime($expire) : time() + $expire) : $expire,
            'value'  => serialize($value),
        ];

        return file_put_contents(static::getCacheFilePath($key), serialize($data));
    }

    /**
     * Returns the path and the name of the temporary file
     *
     * @param string $key
     *
     * @return string
     */
    protected static function getCacheFilePath($key)
    {
        $app = App::getInstance();

        // directory is the default repository
        $path = $app->getBaseDir() . '/storage/cache/';

        if (($dir = (string)$app->path('cache:')) !== false) {
            $path = $dir;
        }

        return $path . md5($app->getSecret() . ':' . $key) . '.cache';
    }

    /**
     * Reads data from the temporary file
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function read($key, $default = null)
    {
        $file = static::getCacheFilePath($key);

        if (file_exists($file)) {
            $data = unserialize(file_get_contents($file));

            if (($data['expire'] > time()) || $data['expire'] < 0) {
                return unserialize($data['value']);
            }

            unlink($file);
        }

        return $default;
    }

    /**
     * Removes the temporary file
     *
     * @param string $key
     *
     * @return bool
     */
    public static function delete($key)
    {
        $file = static::getCacheFilePath($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }

    /**
     * Delete all temporary files
     *
     * @return bool
     */
    public static function flush()
    {
        $app = App::getInstance();

        // directory is the default repository
        $path = $app->getBaseDir() . '/storage/cache';

        if (($dir = (string)$app->path('cache:')) !== false) {
            $path = $dir;
        }

        $iterator = new RecursiveDirectoryIterator($path . '/');

        /** @var RecursiveDirectoryIterator $item */
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $file = realpath($item->getPathname());

                if (pathinfo($file)['extension'] == 'cache') {
                    unlink($file);
                }
            }
        }

        return true;
    }
}
