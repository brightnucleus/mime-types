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

use RuntimeException;

/**
 * Class ConfigGenerator.
 *
 * @since   0.1.2
 *
 * @package BrightNucleus\MimeTypes
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class ConfigGenerator
{
    /**
     * Source data file to generate the config from.
     *
     * @since 0.1.2
     *
     * @var string
     */
    private $txtFile;

    /**
     * Instantiate a ConfigGenerator object.
     *
     * @since 0.1.2
     *
     * @param string $txtFile Source data file to generate the config from.
     *
     * @throws RuntimeException If the source file does not exist or is not readable.
     */
    public function __construct($txtFile)
    {
        if (! file_exists($txtFile) && is_readable($txtFile)) {
            throw new RuntimeException(
                sprintf(
                    'Data file does not exist or is not readable: %s',
                    $txtFile
                )
            );
        }

        $this->txtFile = $txtFile;
    }

    /**
     * Generate a PHP configuration file from the TXT data file.
     *
     * @since 0.1.2
     *
     * @param string $phpFile Path to the PHP file.
     */
    public function generate($phpFile)
    {
        $lines = file($this->txtFile);
        $lines = array_filter($lines, array($this, 'filterComments'));
        $lines = array_map(array($this, 'splitLines'), $lines);

        $data = '<?php' . PHP_EOL;
        $data .= '/* DO NOT EDIT! This file has been automatically generated. Run composer update to fetch a new version. */' . PHP_EOL;
        $data .= 'return array(' . PHP_EOL;
        $data .= '   \'mime-types\' => array(' . PHP_EOL;
        foreach ($this->getMimeTypes($lines) as $mimeType => $extensions) {
            $data .= '      \'' . addslashes($mimeType) . '\' => ' . $this->renderArray($extensions) . ',' . PHP_EOL;
        }
        $data .= '   ),' . PHP_EOL;
        $data .= '   \'extensions\' => array(' . PHP_EOL;
        foreach ($this->getExtensions($lines) as $extension => $mimeTypes) {
            $data .= '      \'' . addslashes($extension) . '\' => ' . $this->renderArray($mimeTypes) . ',' . PHP_EOL;
        }
        $data .= '   ),' . PHP_EOL;
        $data .= ');' . PHP_EOL;
        file_put_contents($phpFile, $data);
    }

    /**
     * Render an array as "array( <data> )" PHP code.
     *
     * @since 0.1.2
     *
     * @param array $data Array to render.
     *
     * @return string PHP code representing the provided array.
     */
    private function renderArray($data)
    {
        $elements = array();

        if (! is_array($data)) {
            var_export($data, true);
        }

        foreach ($data as $key => $value) {
            $elements[] = is_string($key)
                ? $key . ' => \'' . addslashes($value) . '\''
                : '\'' . addslashes($value) . '\'';
        }

        return 'array( ' . implode(', ', $elements) . ' )';
    }

    /**
     * Get the MIME-type-based array from the lines of data.
     *
     * @since 0.1.2
     *
     * @param array $lines Lines of data.
     *
     * @return array MIME-type-based array.
     */
    private function getMimeTypes($lines)
    {
        $result = array();

        foreach ($lines as $line) {
            $result[$line[0]] = explode(' ', trim($line[1]));
        }

        ksort($result);

        return $result;
    }

    /**
     * Get the extension-based array from the lines of data.
     *
     * @since 0.1.2
     *
     * @param array $lines Lines of data.
     *
     * @return array Extension-based array.
     */
    private function getExtensions($lines)
    {
        $result = array();

        foreach ($lines as $line) {
            foreach (explode(' ', trim($line[1])) as $extension) {
                $mimeTypes          = array_key_exists($extension, $result) ? $result[$extension] : array();
                $mimeTypes[]        = $line[0];
                $result[$extension] = $mimeTypes;
            }
        }

        ksort($result);

        return $result;
    }

    /**
     * Filter function to eliminate comments from the MIE types source file.
     *
     * @since 0.1.2
     *
     * @param string $line Line to filter.
     *
     * @return bool Whether the line should be kept or not.
     */
    private function filterComments($line)
    {
        return 0 !== strpos($line, '#');
    }

    /**
     * Mapping function to split a line into two parts: MIME type and extensions.
     *
     * @since 0.1.2
     *
     * @param string $line Line to split.
     *
     * @return array Array of MIME type and extensions parts.
     */
    private function splitLines($line)
    {
        $parts = explode("\t", $line);
        return array(array_shift($parts), array_pop($parts));
    }
}
