<?php

namespace Webnuvola\Laravel;

use Exception;

class Mix
{
	/**
	 * Public base path of versioned Mix files.
	 * @var strinng
	 */
	public static $publicPath;

    /**
     * Get the path to a versioned Mix file.
     *
     * @param  string  $path
     * @param  string  $manifestDirectory
     * @return string
     *
     * @throws \Exception
     */
    public static function mix($path, $manifestDirectory = '')
    {
    	if (! self::$publicPath) {
    		throw new Exception('You must assign `\Webnuvola\Laravel\Mix::$publicPath` static variable.');
    	}

        static $manifests = [];

        if (! self::startsWith($path, '/')) {
            $path = "/{$path}";
        }

        if ($manifestDirectory && ! self::startsWith($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }

        if (file_exists(self::public_path($manifestDirectory.'/hot'))) {
            $url = rtrim(file_get_contents(self::public_path($manifestDirectory.'/hot')));

            if (self::startsWith($url, ['http://', 'https://'])) {
                return self::after($url, ':').$path;
            }

            return "//localhost:8080{$path}";
        }

        $manifestPath = self::public_path($manifestDirectory.'/mix-manifest.json');

        if (! isset($manifests[$manifestPath])) {
            if (! file_exists($manifestPath)) {
                throw new Exception('The Mix manifest does not exist.');
            }

            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
        }

        $manifest = $manifests[$manifestPath];

        if (! isset($manifest[$path])) {
            throw new Exception("Unable to locate Mix file: {$path}.");
        }

        return $manifestDirectory.$manifest[$path];
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */
    protected static function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        return false;
    }
    /**
     * Return the remainder of a string after a given value.
     *
     * @param  string  $subject
     * @param  string  $search
     * @return string
     */
    protected static function after($subject, $search)
    {
        return $search === '' ? $subject : array_reverse(explode($search, $subject, 2))[0];
    }

    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    protected function public_path($path = '')
    {
    	$publicPath = rtrim(self::$publicPath, '/');
    	$path = '/' . ltrim($path, '/');
    	return $publicPath . $path;
    }
}
