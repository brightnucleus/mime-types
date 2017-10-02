<?php
/**
 * Automatically updated MIME types database, using the Apache HTTP Server configuration.
 *
 * @package   BrightNucleus\MimeTypes
 * @author    Alain Schlesser <alain.schlesser@gmail.com>
 * @license   MIT
 * @link      https://www.brightnucleus.com/
 * @copyright 2017 Alain Schlesser, Bright Nucleus
 */

namespace BrightNucleus\MimeTypes;

/**
 * Class MimeTypes.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\MimeTypes
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class MimeTypes
{

    const DATA_URL = 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
    const DATA_FOLDER = 'data';
    const DATA_FILENAME = 'mime-types';
    const DATA_KEY_EXTENSIONS = 'extensions';
    const DATA_KEY_MIME_TYPES = 'mime-types';

    /**
     * The MIME types data that is queried.
     *
     * @var array
     *
     * @since 0.1.0
     */
    private static $data;

    /**
     * Get the location of the database file.
     *
     * @since 0.1.0
     *
     * @param bool $array Optional. Whether to return the location as an array. Defaults to false.
     *
     * @return string|array Either a string, containing the absolute path to the file, or an array with the location
     *                      split up into two keys named 'folder' and 'filename'
     */
    public static function getLocation($array = false)
    {
        $folder   = dirname(__DIR__) . '/' . self::DATA_FOLDER;
        $filepath = $folder . '/' . self::DATA_FILENAME;
        if (! $array) {
            return $filepath;
        }

        return array(
            'folder' => $folder,
            'file'   => self::DATA_FILENAME,
        );
    }

    /**
     * Get the list of allowed MIME types for a given extension.
     *
     * @since 0.1.0
     *
     * @param string $extension File extension to query the allowed mime types for.
     * @param mixed  $fallback  Optional. Fallback value to use if the extension was not found. Defaults to false.
     *
     * @return array Array of MIME types that are allowed for the provided extension.
     */
    public static function getTypesForExtension($extension, $fallback = false)
    {
        $data = self::getData();

        if (! array_key_exists($extension, $data[self::DATA_KEY_EXTENSIONS])) {
            return $fallback;
        }

        return $data[self::DATA_KEY_EXTENSIONS][$extension];
    }

    /**
     * Get the list of extensions allowed for a given MIME type.
     *
     * @since 0.1.0
     *
     * @param string $mimeType MIME type to query for.
     * @param mixed  $fallback Optional. Fallback value to use if the extension was not found. Defaults to false.
     *
     * @return array Array of extensions that are allowed for the provided MIME type.
     */
    public static function getExtensionsForType($mimeType, $fallback = false)
    {
        $data = self::getData();

        if (! array_key_exists($mimeType, $data[self::DATA_KEY_MIME_TYPES])) {
            return $fallback;
        }

        return $data[self::DATA_KEY_MIME_TYPES][$mimeType];
    }

    /**
     * Get the MIME types data.
     *
     * Initializes the data first as needed.
     *
     * @since 0.1.0
     */
    private static function getData()
    {
        if (! self::$data) {
            $filepath   = self::getLocation() . '.php';
            self::$data = include $filepath;
        }

        return self::$data;
    }
}
