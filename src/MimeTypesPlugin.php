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

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;

/**
 * Class CountryPlugin.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\MimeTypes
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
class MimeTypesPlugin implements PluginInterface, EventSubscriberInterface
{

    /**
     * Get the event subscriber configuration for this plugin.
     *
     * @since 0.1.0
     *
     * @return array<string,string> The events to listen to, and their associated handlers.
     */
    public static function getSubscribedEvents()
    {
        return array(
            ScriptEvents::POST_INSTALL_CMD => 'update',
            ScriptEvents::POST_UPDATE_CMD  => 'update',
        );
    }

    /**
     * Update the stored database.
     *
     * @since 0.1.0
     *
     * @param Event $event The event that has called the update method.
     */
    public static function update(Event $event)
    {
        $dataFilename = MimeTypes::getLocation();
        $filesystem   = new Filesystem();

        $filesystem->ensureDirectoryExists(dirname($dataFilename));

        $io = $event->getIO();
        $io->write('Fetching new source version of the Apache HTTP Server MIME types database...', true);
        self::downloadFile($dataFilename . '.txt', MimeTypes::DATA_URL);

        $io->write('Generating PHP configuration file from MIME types source file...', true);
        self::generateConfig($dataFilename . '.txt', $dataFilename . '.php');

        $io->write('Removing MIME types source file...', true);
        $filesystem->remove($dataFilename . '.txt');

        $io->write(
            sprintf(
                'The MIME types database has been updated (%1$s).',
                $dataFilename . '.php'
            ),
            true
        );
    }

    /**
     * Download a file from an URL.
     *
     * @since 0.1.0
     *
     * @param string $filename Filename of the file to download.
     */
    protected static function downloadFile($filename, $url)
    {
        $fileHandle = fopen($filename, 'w');
        $options    = array(
            CURLOPT_FILE    => $fileHandle,
            CURLOPT_TIMEOUT => 600,
            CURLOPT_URL     => $url,
        );

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        curl_exec($curl);
        curl_close($curl);
    }

    /**
     * Generate a PHP configuration file from the TXT data file.
     *
     * @since 0.1.0
     *
     * @param string $txtFile Path to the TXT file.
     * @param string $phpFile Path to the PHP file.
     */
    protected static function generateConfig($txtFile, $phpFile)
    {
        $lines = file($txtFile);
        $lines = array_filter($lines, __CLASS__ . '::filterComments');
        $lines = array_map(__CLASS__ . '::splitLines', $lines);

        $data = '<?php' . PHP_EOL;
        $data .= '/* DO NOT EDIT! This file has been automatically generated. Run composer update to fetch a new version. */' . PHP_EOL;
        $data .= 'return array(' . PHP_EOL;
        $data .= '   \'mime-types\' => array(' . PHP_EOL;
        foreach (self::getMimeTypes($lines) as $mimeType => $extensions) {
            $data .= '      \'' . addslashes($mimeType) . '\' => ' . self::renderArray($extensions) . ',' . PHP_EOL;
        }
        $data .= '   ),' . PHP_EOL;
        $data .= '   \'extensions\' => array(' . PHP_EOL;
        foreach (self::getExtensions($lines) as $extension => $mimeTypes) {
            $data .= '      \'' . addslashes($extension) . '\' => ' . self::renderArray($mimeTypes) . ',' . PHP_EOL;
        }
        $data .= '   ),' . PHP_EOL;
        $data .= ');' . PHP_EOL;
        file_put_contents($phpFile, $data);
    }

    /**
     * Render an array as "array( <data> )" PHP code.
     *
     * @since 0.1.0
     *
     * @param array $data Array to render.
     *
     * @return string PHP code representing the provided array.
     */
    protected static function renderArray($data)
    {
        $elements = array();

        if (! is_array($data)) {
            var_dump($data);
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
     * @since 0.1.0
     *
     * @param array $lines Lines of data.
     *
     * @return array MIME-type-based array.
     */
    protected static function getMimeTypes($lines)
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
     * @since 0.1.0
     *
     * @param array $lines Lines of data.
     *
     * @return array Extension-based array.
     */
    protected static function getExtensions($lines)
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
     * @since 0.1.0
     *
     * @param string $line Line to filter.
     *
     * @return bool Whether the line should be kept or not.
     */
    protected static function filterComments($line)
    {
        return 0 !== strpos($line, '#');
    }

    /**
     * Mapping function to split a line into two parts: MIME type and extensions.
     *
     * @since 0.1.0
     *
     * @param string $line Line to split.
     *
     * @return array Array of MIME type and extensions parts.
     */
    protected static function splitLines($line)
    {
        $parts = explode("\t", $line);
        return array(array_shift($parts), array_pop($parts));
    }

    /**
     * Activate the plugin.
     *
     * @since 0.1.3
     *
     * @param Composer    $composer The main Composer object.
     * @param IOInterface $io       The i/o interface to use.
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // no action required
    }
}
