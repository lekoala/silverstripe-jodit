<?php

namespace LeKoala\Jodit;

use Exception;
use SilverStripe\i18n\i18n;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;
use SilverStripe\i18n\i18nEntityProvider;
use SilverStripe\Core\Manifest\ModuleResource;
use SilverStripe\Forms\HTMLEditor\HTMLEditorConfig;
use SilverStripe\Core\Manifest\ModuleResourceLoader;

/**
 * Default configuration for HtmlEditor specific to Jodit
 */
class JoditEditorConfig extends HTMLEditorConfig implements i18nEntityProvider
{
    /**
     * Location of module relative to BASE_DIR. This must contain the following dirs
     * Supports vendor/module:path
     *
     * @config
     * @var string
     */
    private static $base_dir = 'lekoala/silverstripe-jodit';

    /**
     * Jodit JS settings
     *
     * @link https://xdsoft.net/jodit/docs/classes/config.Config.html
     * @var array
     */
    protected $settings = [
        'hidePoweredByJodit' => true,
        'toolbarAdaptive' => false,
        "showCharsCounter" => false,
        "showWordsCounter" => false,
        "showXPathInStatusbar" => false,
        "uploader" => [
            "insertImageAsBase64URI" => true
        ],
    ];

    /**
     * Holder list of enabled plugins
     *
     * @var array
     */
    protected $disabledPlugins = [
        'powered-by-jodit'
    ];

    /**
     * Holder list of enabled plugins
     *
     * @var array
     */
    protected $removeButtons = [];

    /**
     * Holder list of enabled plugins
     *
     * @var array
     */
    protected $buttons = [
        "bold", "italic", "underline", "strikethrough", "eraser", "|",
        "ul", "ol", "|", "link", "paragraph", "classSpan", "image", "video", "hr", "table",
    ];

    /**
     * Theme name (can be "dark")
     *
     * @var string
     */
    protected $theme = 'default';

    /**
     * @param string $key
     * @return mixed
     */
    public function getOption($key)
    {
        if (isset($this->settings[$key])) {
            return $this->settings[$key];
        }
        return null;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setOption($key, $value)
    {
        $this->settings[$key] = $value;
        return $this;
    }

    /**
     * @param array $options
     * @return self
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            $this->settings[$key] = $value;
        }
        return $this;
    }

    /**
     * Get the theme
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set the theme name
     *
     * @param string $theme
     * @return $this
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Get all settings
     *
     * @return array
     */
    protected function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return [
            'data-editor' => 'jodit',
            'data-config' => json_encode($this->getConfig()),
        ];
    }

    /**
     * Generate the JavaScript that will set Jodit's configuration:
     * - Parse all configurations into JSON objects to be used in JavaScript
     * - Includes Jodit and configurations using the {@link Requirements} system
     *
     * @link https://xdsoft.net/jodit/play.html
     * @return array
     */
    protected function getConfig()
    {
        $settings = $this->getSettings();

        $settings['i18n'] = self::get_jodit_lang();
        $settings['theme'] = $this->getTheme();
        if (!empty($this->disabledPlugins)) {
            $settings['disablePlugins'] = implode(",", $this->disabledPlugins);
        }
        if (!empty($this->removeButtons)) {
            $settings['removeButtons'] = implode(",", $this->removeButtons);
        }
        if (!empty($this->buttons)) {
            $settings['buttons'] = implode(",", $this->buttons);
        }

        return $settings;
    }

    public function init()
    {
        HTMLEditorConfig::set_active(new JoditEditorConfig);
    }

    public function getConfigSchemaData()
    {
        $data = parent::getConfigSchemaData();
        return $data;
    }

    /**
     * Get the current Jodit language
     *
     * @return string Language
     */
    public static function get_jodit_lang()
    {
        $lang = static::config()->get('jodit_lang');
        $locale = i18n::get_locale();
        if (isset($lang[$locale])) {
            return $lang[$locale];
        }
        return 'en';
    }

    /**
     * Returns the full filesystem path to Jodit resources (which could be different from the original Jodit
     * location in the module).
     *
     * Path will be absolute.
     *
     * @return string
     * @throws Exception
     */
    public function getJoditResourcePath()
    {
        $resource = $this->getJoditResource();
        if ($resource instanceof ModuleResource) {
            return $resource->getPath();
        }
        return Director::baseFolder() . '/' . $resource;
    }

    /**
     * Get front-end url to Jodit resources
     *
     * @return string
     * @throws Exception
     */
    public function getJoditResourceURL()
    {
        $resource = $this->getJoditResource();
        if ($resource instanceof ModuleResource) {
            return $resource->getURL();
        }
        return $resource;
    }

    /**
     * Get resource root for Jodit, either as a string or ModuleResource instance
     * Path will be relative to BASE_PATH if string.
     *
     * @return ModuleResource|string
     * @throws Exception
     */
    public function getJoditResource()
    {
        $configDir = static::config()->get('base_dir');
        if ($configDir) {
            return ModuleResourceLoader::singleton()->resolveResource($configDir);
        }
        throw new Exception(sprintf(
            'If the silverstripe/admin module is not installed you must set the Jodit path in %s.base_dir',
            __CLASS__
        ));
    }

    /**
     * Sets the upload folder name used by the insert media dialog
     *
     * @param string $folderName
     * @return $this
     */
    public function setFolderName(string $folderName): self
    {
        $folder = Folder::find_or_make($folderName);
        $folderID = $folder ? $folder->ID : null;
        $this->setOption('upload_folder_id', $folderID);
        return $this;
    }

    public function provideI18nEntities()
    {
        $entities = [
            self::class . '.PIXEL_WIDTH' => '{width} pixels',
        ];
        foreach (self::config()->get('image_size_presets') as $preset) {
            if (empty($preset['i18n']) || empty($preset['text'])) {
                continue;
            }
            $entities[$preset['i18n']] = $preset['text'];
        }

        return $entities;
    }
}
