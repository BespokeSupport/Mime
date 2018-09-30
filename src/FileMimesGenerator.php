<?php
/**
 * Class for converting Mime Types to extensions
 * PHP Version 5.3
 * Based on work by http://www.freeformatter.com/mime-types-list.html#mime-types-list.
 *
 * @author   Richard Seymour <web@bespoke.support>
 * @license  MIT
 *
 * @link     https://github.com/BespokeSupport/Mime
 */

namespace BespokeSupport\Mime;

use DOMDocument;
use Exception;

/**
 * Class FileMimesGenerator.
 */
class FileMimesGenerator
{
    /**
     * @static
     *
     * @param \Composer\Script\Event $event
     */
    public static function composerGenerate($event = null)
    {
        if ($event) {
            $event->getIO()->write('Generating BespokeSupport\Mime\FileMimes class');
        }

        $file = __DIR__ . '/../resources/mimes.csv';

        self::fetch($file);

        self::generate($file);
    }

    /**
     * Convert HTML page to CSV then create Mime listings.
     *
     * @param $file
     */
    public static function fetch($file)
    {
        $url = 'http://www.freeformatter.com/mime-types-list.html';

        // no need to download again if csv present
        if (file_exists($file)) {
            return;
        }

        $filePointerCsv = fopen($file, 'wb');
        $fileHtml = \dirname(__FILE__, 2) . '/resources/mimes.html';

        if (!file_exists($fileHtml)) {
            $contents = file_get_contents($url);
            file_put_contents($fileHtml, $contents);
        } else {
            $contents = file_get_contents($fileHtml);
        }

        $contents = preg_replace('/(.*)<tbody>/is', '', $contents);

        $contents = preg_replace('/<\/tbody>.*/is', '', $contents);

        try {
            $xml = new DOMDocument();

            @$xml->loadHTML($contents);

            $trs = $xml->getElementsByTagName('tr');

            for ($i = 0; $i < $trs->length; $i++) {
                $tr = $trs->item($i);

                $tds = $tr->childNodes;

                $nodeName = $tds->item(0);
                $nodeMime = $tds->item(2);
                $nodeFile = $tds->item(4);

                $extText = ($nodeFile->textContent === 'N/A') ? null : str_replace('.', '', $nodeFile->textContent);

                $extensions = explode(',', $extText);

                $extension = \count($extensions) ? trim(array_pop($extensions)) : $extText;

                fputcsv($filePointerCsv,
                    [
                        $nodeMime->textContent,
                        $nodeName->textContent,
                        $extension,
                    ],
                    ',',
                    '"'
                );
            }
        } catch (Exception $e) {
        }

        fclose($filePointerCsv);
    }

    /**
     * Generate FileMimes class.
     *
     * @param $file
     */
    public static function generate($file)
    {
        $filePointer = fopen($file, 'rb');

        $names = '';
        $extensions = '';

        $header = true;
        while (($row = fgetcsv($filePointer))) {
            if ($header) {
                $header = false;
                continue;
            }

            $mime = trim($row[0]);
            $name = trim(addslashes($row[1]));
            $extension = trim($row[2]);

            $extensions .= "\t\t'$mime' => '$extension',\n";
            $names .= "\t\t'$mime' => '$name',\n";
        }

        $class = '';
        $class .= self::getHeader();
        $class .= self::getFunctions();
        $class .= self::getPropertyMimesHeader();
        $class .= $extensions;
        $class .= self::getPropertyMimesFooter();
        $class .= self::getPropertyNamesHeader();
        $class .= $names;
        $class .= self::getPropertyNamesFooter();
        $class .= self::getFooter();

        file_put_contents(__DIR__ . '/FileMimes.php', $class);
    }

    /**
     * @return string
     */
    private static function getHeader()
    {
        return <<< EOF
<?php
/**
 * Class for converting Mime Types to extensions
 * PHP Version 5.3
 * Based on work by http://www.freeformatter.com/mime-types-list.html#mime-types-list
 *
 * @author   Richard Seymour <web@bespoke.support>
 * @license  MIT
 * @link     https://github.com/BespokeSupport/Mime
 */

namespace BespokeSupport\Mime;

/**
 * Class FileMimes
 * @package BespokeSupport\Mime
 */
class FileMimes
{

EOF;
    }

    /**
     * @return string
     */
    private static function getFooter()
    {
        return <<< 'EOF'

}

EOF;
    }

    /**
     * @return string
     */
    private static function getPropertyMimesHeader()
    {
        return <<< 'EOF'

    /**
     * @var array
     */
    protected $mimes = array(

EOF;
    }

    /**
     * @return string
     */
    private static function getPropertyMimesFooter()
    {
        return <<< 'EOF'
    );
EOF;
    }

    /**
     * @return string
     */
    private static function getPropertyNamesHeader()
    {
        return <<< 'EOF'

    /**
     * @var array
     */
    protected $names = array(

EOF;
    }

    /**
     * @return string
     */
    private static function getPropertyNamesFooter()
    {
        return <<< 'EOF'
    );
EOF;
    }

    /**
     * @return string
     */
    private static function getFunctions()
    {
        return <<< 'EOB'
    /**
     * @return string
     */
    public static function read($file)
    {
        $fInfoClass = new \finfo(FILEINFO_MIME_TYPE | FILEINFO_PRESERVE_ATIME);
        return $fInfoClass->buffer(file_get_contents((string)$file));
    }

    /**
     * @return string
     */
    public static function extension($file)
    {
        $spl = new \SplFileInfo($file);
        return $spl->isFile() ? $spl->getExtension() : null;
    }

    /**
     * @param null|string $mime
     * @return string
     */
    public function getExtensionFromMime($mime = null)
    {
        if ($mime && array_key_exists($mime, $this->mimes)) {
            return $this->mimes[$mime];
        }

        return '';
    }

    /**
     * @param null $mime
     * @return string
     */
    public function getNameFromMime($mime = null)
    {
        if ($mime && array_key_exists($mime, $this->names)) {
            return $this->names[$mime];
        }

        return '';
    }

    /**
     * @param null $extension
     * @return string|null
     */
    public function getMimeFromExtension($extension = null)
    {
        if ($extension && ($extension = array_search($extension, $this->mimes))) {
            return $extension;
        }
        return null;
    }

    /**
     * @return array
     */
    public function getMimes()
    {
        return $this->mimes;
    }

    /**
     * @return array
     */
    public function getMimeNames()
    {
        return $this->names;
    }
EOB;
    }
}
