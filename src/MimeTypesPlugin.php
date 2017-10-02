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
use Exception;

/**
 * Class CountryPlugin.
 *
 * @since   0.1.0
 *
 * @package BrightNucleus\MimeTypes
 * @author  Alain Schlesser <alain.schlesser@gmail.com>
 */
final class MimeTypesPlugin implements PluginInterface, EventSubscriberInterface
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
        $io->write('Fetching new source version of the Apache HTTP Server MIME types database...');
        self::downloadFile($dataFilename . '.txt', MimeTypes::DATA_URL);

        $io->write('Generating PHP configuration file from MIME types source file...');
        try {
            $generator = new ConfigGenerator($dataFilename . '.txt');
            $config    = $generator->generate();
            self::writeConfigFile($dataFilename . '.php', $config);
        } catch (Exception $exception) {
            $io->writeError('Could not write PHP configuration file. Reason: ' . $exception->getMessage());
        }

        $io->write('Removing MIME types source file...');
        $filesystem->remove($dataFilename . '.txt');

        $io->write(
            sprintf(
                'The MIME types database has been updated (%1$s).',
                $dataFilename . '.php'
            )
        );
    }

    /**
     * Download a file from an URL.
     *
     * @since 0.1.0
     *
     * @param string $filename Filename of the file to download.
     * @param string $url      URL to download the file from.
     */
    private static function downloadFile($filename, $url)
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
     * Save the configuration to a file.
     *
     * @since 0.1.0
     *
     * @param string $filename Filename of the file to save.
     * @param string $data     Data to save to the file.
     */
    private static function writeConfigFile($filename, $data)
    {
        file_put_contents($filename, $data);
    }

    /**
     * Activate the plugin.
     *
     * @since 0.1.0
     *
     * @param Composer    $composer The main Composer object.
     * @param IOInterface $io       The i/o interface to use.
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // no action required
    }
}
