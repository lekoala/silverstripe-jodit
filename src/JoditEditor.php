<?php

namespace LeKoala\Jodit;

use LeKoala\ModularBehaviour\ModularBehaviour;
use SilverStripe\View\Requirements;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

class JoditEditor extends HTMLEditorField
{
    use ModularBehaviour;

    /**
     * @config
     */
    private static bool $default_lazy_init = true;

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
    private static string $version = '3.20';

    protected $lazyInit = true;

    /**
     * @param string $fieldName
     * @param string|null|bool $title
     * @param mixed $value
     * @param JoditEditorConfig $config
     */
    public function __construct($name, $title = null, $value = null, $config = null)
    {
        parent::__construct($name, $title, $value);
        $this->setLazyInit(self::config()->default_lazy_init ?? true);

        if (!$config) {
            $this->editorConfig = new JoditEditorConfig;
        }
    }

    /**
     * Get the value of lazyInit
     */
    public function getLazyInit(): bool
    {
        return $this->lazyInit;
    }

    /**
     * Set the value of lazyInit
     */
    public function setLazyInit(bool $lazyInit): self
    {
        $this->lazyInit = $lazyInit;
        return $this;
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
        // Requirements::javascript('lekoala/silverstripe-jodit:client/JoditField.js');
        Requirements::css("$baseDir/jodit.es2018.min.css");
    }

    public function JsonOptions(): string
    {
        return $this->getEditorConfig()->getAttributes()['data-config'];
    }

    public function getModularConfigName()
    {
        return str_replace('-', '_', $this->ID()) . '_config';
    }

    public function getModularConfig()
    {
        $JsonOptions = $this->JsonOptions();
        $configName = $this->getModularConfigName();
        $script = "var $configName = $JsonOptions";
        return $script;
    }

    public function getModularName()
    {
        return 'Jodit.make';
    }

    public function Field($properties = [])
    {
        if (self::config()->enable_requirements) {
            self::requirements();
        }
        if ($this->lazyInit) {
            $this->setModularLazy($this->lazyInit);
        }
        $this->getEditorConfig()->init();
        return $this->getModularField($properties);
    }
}
