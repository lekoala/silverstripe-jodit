<?php

namespace LeKoala\Jodit;

use SilverStripe\View\Requirements;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class JoditEditor extends HTMLEditorField
{
    /**
     * @config
     */
    private static bool $enable_requirements = true;

    /**
     * @config
     */
    private static bool $use_cdn = false;

    /**
     * @config
     */
    private static string $version = '3.18.9';

    /**
     * @param string $fieldName
     * @param string|null|bool $title
     * @param mixed $value
     * @param JoditEditorConfig $config
     */
    public function __construct($name, $title = null, $value = null, $config = null)
    {
        parent::__construct($name, $title, $value);

        if (!$config) {
            $this->editorConfig = new JoditEditorConfig;
        }
    }

    public static function requirements(): void
    {
        $use_cdn = self::config()->use_cdn;
        $version = self::config()->version;

        if ($use_cdn) {
            $baseDir = "https://cdn.jsdelivr.net/npm/jodit@$version/build";
        } else {
            $asset = ModuleResourceLoader::resourceURL('lekoala/silverstripe-jodit:client/cdn/jodit.es2018.min.js');
            $baseDir = dirname($asset);
        }

        Requirements::javascript("$baseDir/jodit.es2018.min.js");
        Requirements::javascript('lekoala/silverstripe-jodit:client/JoditField.js');
        Requirements::css("$baseDir/jodit.es2018.min.css");
    }

    public function Field($properties = [])
    {
        if (self::config()->enable_requirements) {
            self::requirements();
        }
        $this->getEditorConfig()->init();
        return parent::Field($properties);
    }
}
