<?php
/**
 * SEOmatic plugin for Craft CMS 3.x
 *
 * A turnkey SEO implementation for Craft CMS that is comprehensive, powerful,
 * and flexible
 *
 * @link      https://nystudio107.com
 * @copyright Copyright (c) 2017 nystudio107
 */

namespace nystudio107\seomatic\variables;

use craft\helpers\UrlHelper;
use nystudio107\seomatic\Seomatic;
use nystudio107\seomatic\helpers\Environment as EnvironmentHelper;
use nystudio107\seomatic\models\MetaGlobalVars;
use nystudio107\seomatic\models\MetaSiteVars;
use nystudio107\seomatic\models\MetaSitemapVars;
use nystudio107\seomatic\models\Settings;
use nystudio107\seomatic\services\Helper;
use nystudio107\seomatic\services\JsonLd;
use nystudio107\seomatic\services\Link;
use nystudio107\seomatic\services\Script;
use nystudio107\seomatic\services\Tag;
use nystudio107\seomatic\services\Title;
use nystudio107\seomatic\services\MetaContainers;
use nystudio107\seomatic\services\MetaBundles;
use nystudio107\seomatic\variables\ManifestVariable as Manifest;

use Craft;

use yii\di\ServiceLocator;

/**
 * Seomatic defines the `seomatic` global template variable.
 *
 * @property Helper     helper
 * @property JsonLd     jsonLd
 * @property Link       link
 * @property Script     script
 * @property Tag        tag
 * @property Title      title
 * @property Manifest   manifest
 *
 * @author    nystudio107
 * @package   Seomatic
 * @since     3.0.0
 */
class SeomaticVariable extends ServiceLocator
{
    // Properties
    // =========================================================================

    /**
     * @var MetaGlobalVars
     */
    public $meta;

    /**
     * @var MetaSiteVars
     */
    public $site;

    /**
     * @var MetaSitemapVars
     */
    public $sitemap;

    /**
     * @var Settings
     */
    public $config;

    /**
     * @var MetaContainers
     */
    public $containers;

    /**
     * @var MetaBundles
     */
    public $bundles;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        /** @noinspection PhpDeprecationInspection */
        $components = [
            'helper' => Helper::class,
            'jsonLd' => JsonLd::class,
            'link' => Link::class,
            'script' => Script::class,
            'tag' => Tag::class,
            'title' => Title::class,
            'manifest' => Manifest::class
        ];

        $config['components'] = $components;

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $replaceVars = Seomatic::$loadingContainers || Seomatic::$previewingMetaContainers;
        if ($this->meta === null || $replaceVars) {
            $this->meta = Seomatic::$plugin->metaContainers->metaGlobalVars;
        }
        if ($this->site === null || $replaceVars) {
            $this->site = Seomatic::$plugin->metaContainers->metaSiteVars;
        }
        if ($this->sitemap === null || $replaceVars) {
            $this->sitemap = Seomatic::$plugin->metaContainers->metaSitemapVars;
        }
        // Set the config settings, parsing the environment if its a frontend request
        $configSettings = Seomatic::$settings;
        if (!$replaceVars && !Craft::$app->getRequest()->getIsCpRequest()) {
            $configSettings->environment = Seomatic::$environment;
        }
        $this->config = $configSettings;
    }

    /**
     * Get the plugin's name
     *
     * @return null|string
     */
    public function getPluginName()
    {
        return Seomatic::$plugin->name;
    }

    /**
     * Get all of the meta bundles
     *
     * @param bool $allSites
     *
     * @return array
     */
    public function getContentMetaBundles(bool $allSites = true): array
    {
        return Seomatic::$plugin->metaBundles->getContentMetaBundles($allSites);
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return Seomatic::$environment;
    }

    /**
     * @return string
     */
    public function getEnvironmentMessage()
    {
        $result = '';
        /** @var Settings $settings */
        $settings = Seomatic::$plugin->getSettings();
        $settingsEnv = $settings->environment;
        $env = Seomatic::$environment;
        $settingsUrl = UrlHelper::cpUrl('seomatic/plugin');
        if (Seomatic::$devMode) {
            return Craft::t('seomatic', 'The `{settingsEnv}` [SEOmatic Environment]({settingsUrl}) setting has been overriden to `{env}`, because the `devMode` config setting is enabled. Tracking scripts are disabled, and the `robots` tag is set to `none` to prevent search engine indexing.',
                ['env' => $env, 'settingsEnv' => $settingsEnv, 'settingsUrl' => $settingsUrl]
            );
        }
        $envVar = getenv('ENVIRONMENT');
        if (!empty($envVar)) {
            $env = EnvironmentHelper::determineEnvironment();
            switch ($env) {
                case EnvironmentHelper::SEOMATIC_STAGING_ENV:
                    $additionalMessage = 'The `robots` tag is set to `none` to prevent search engine indexing.';
                    break;
                case EnvironmentHelper::SEOMATIC_PRODUCTION_ENV:
                    $additionalMessage = 'Tracking scripts are disabled, and the `robots` tag is set to `none` to prevent search engine indexing.';
                    break;
                default:
                    $additionalMessage = '';
                    break;
            }
            if ($settings->environment !== $env) {
                return Craft::t('seomatic', 'The `{settingsEnv}` [SEOmatic Environment]({settingsUrl}) setting has been overriden to `{env}`, because the `.env` setting `ENVIRONMENT` is set to `{envVar}`. {additionalMessage}',
                    ['env' => $env, 'settingsEnv' => $settingsEnv, 'settingsUrl' => $settingsUrl, 'envVar' => $envVar, 'additionalMessage' => $additionalMessage]
                );
            }
        }

        return $result;
    }
}
