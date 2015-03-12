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
 * Class FileMimesGenerator
 * @package BespokeSupport\Mime
 */
class FileMimesGenerator
{
	/**
	 * @static
	 * @param \Composer\Script\Event $event
	 */
	public static function composerGenerate($event)
	{
		$event->getIO()->write('Generating BespokeSupport\Mime\FileMimes class');

        $file = dirname(__FILE__).'/../resources/mimes.csv';

        self::fetch($file);

		self::generate($file);
	}

    public static function fetch($file)
    {
        $url = 'http://www.freeformatter.com/mime-types-list.html';

        // no need to download again if csv present
        if (file_exists($file)) {
            return;
        }

        $filePointerCsv = fopen($file, 'w');
        $fileHtml = dirname(dirname(__FILE__)).'/resources/mimes.html';

        if (!file_exists($fileHtml)) {
            $contents = file_get_contents($url);
            file_put_contents($fileHtml, $contents);
        } else {
            $contents = file_get_contents($fileHtml);
        }

        $contents = preg_replace('/(.*)<tbody>/is', '', $contents);

        $contents = preg_replace('/<\/tbody>.*/is', '', $contents);

        try {
            $xml = new \DOMDocument();

            @$xml->loadHTML($contents);

            $trs = $xml->getElementsByTagName('tr');

            for ($i = 0; $i < $trs->length; $i++) {
                $tr = $trs->item($i);

                $tds = $tr->childNodes;

                $nodeName = $tds->item(0);
                $nodeMime = $tds->item(2);
                $nodeFile = $tds->item(4);

                $extText = ($nodeFile->textContent == 'N/A') ? null : str_replace('.', '', $nodeFile->textContent);

                $extensions = explode(',',$extText);

                $extension = (count($extensions)) ? trim(array_pop($extensions)) : $extText;

                fputcsv($filePointerCsv,
                    array(
                        $nodeMime->textContent,
                        $nodeName->textContent,
                        $extension
                    ),
                    ',',
                    '"'
                );
            }
        } catch (\Exception $e) {

        }

        fclose($filePointerCsv);
    }

	public static function generate($file)
	{
        $filePointer = fopen($file, 'r');

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

		file_put_contents(dirname(__FILE__).'/FileMimes.php', $class);
	}

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


	private static function getFooter()
	{
		return <<< EOF

}

EOF;
	}


	private static function getPropertyMimesHeader()
	{
		return <<< EOF

	/**
	 * @var array
	 */
	protected \$mimes = array(

EOF;
	}

	private static function getPropertyMimesFooter()
	{
		return <<< EOF
	);
EOF;

	}

	private static function getPropertyNamesHeader()
	{
		return <<< EOF

	/**
	 * @var array
	 */
	protected \$names = array(

EOF;
	}


	private static function getPropertyNamesFooter()
	{
		return <<< EOF
	);
EOF;

	}


	private static function getFunctions()
	{
		return <<< EOB
	/**
	 * @param null|string \$mime
	 * @return string
	 */
	public function getExtensionFromMime(\$mime = null)
	{
		if (\$mime && array_key_exists(\$mime, \$this->mimes)) {
			return \$this->mimes[\$mime];
		}

		return '';
	}

	/**
	 * @param null \$mime
	 * @return string
	 */
	public function getNameFromMime(\$mime = null)
	{
		if (\$mime && array_key_exists(\$mime, \$this->names)) {
			return \$this->names[\$mime];
		}

		return '';
	}

	/**
	 * @return array
	 */
	public function getMimes()
	{
		return \$this->mimes;
	}

	/**
	 * @return array
	 */
	public function getMimeNames()
	{
		return \$this->names;
	}
EOB;

	}
}
