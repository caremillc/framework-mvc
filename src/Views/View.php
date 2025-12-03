<?php
namespace Careminate\Views;

class View
{
    protected static $cacheDir;

    public static function cache()
    {
        static::$cacheDir = config('view.cache_path');
        // var_dump(static::$cacheDir);
        if (! is_dir(static::$cacheDir)) {
            mkdir(static::$cacheDir, 0755, true);
        }
    }
    /**
     * Render the view and return the content as a string.
     *
     * @param string $view
     * @param array|null $data
     * @return string
     */
    public static function make(string $view, ?array $data = [])
    {
        $viewPath = str_replace('.', '/', $view);
        // dd($viewPath);
        $viewFile = config('view.path') . '/' . $viewPath . '.flint.php';
        //dd($viewFile);
        if (! file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
        }

        if (config('view.cache')) {
            static::cache();
            $cacheFile = static::getCacheFilePath($viewPath);

            if (static::isCacheValid($cacheFile, $viewFile)) {
                include $cacheFile;
            } else {
                $output = static::generateViewOutput($view, $data);
                file_put_contents($cacheFile, $output);
                echo $output;
            }
        } else {
            ob_start();
            extract($data ?? []);
            include $viewFile;
            return ob_get_clean();
        }
    }

    protected static function getCacheFilePath($view)
    {
        return static::$cacheDir . '/view_' . md5(config('view.path')) . '_' . $view . '.cache.php';
    }

    protected static function isCacheValid($cacheFile, $viewFile)
    {
        // If cache file doesn't exist, it's not valid
        if (! file_exists($cacheFile)) {
            return false;
        }

        // If view file is newer than cache file, invalidate cache
        return filemtime($viewFile) <= filemtime($cacheFile);
    }

    protected static function generateViewOutput($view, $data)
    {
        $view = str_replace('.', '/', $view);
        $path = config('view.path') . '/' . $view . '.flint.php';

        if (! file_exists($path)) {
            throw new \Exception("View file not found: {$path}");
        }

        ob_start();
        extract($data ?? []);
        include $path;
        return ob_get_clean();
    }

}
