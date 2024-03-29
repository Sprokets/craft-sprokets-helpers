<?php

/**
 * sprokets-helpers plugin for Craft CMS 3.x
 *
 * Misc helpers
 *
 * @link      https://sprokets.net
 * @copyright Copyright (c) 2018 sprokets
 */

namespace sprokets\sproketshelpers\assetbundles\discloseassets;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;
use yii\base\InvalidArgumentException;

/**
 * SproketshelpersAsset AssetBundle
 *
 * AssetBundle represents a collection of asset files, such as CSS, JS, images.
 *
 * Each asset bundle has a unique name that globally identifies it among all asset bundles used in an application.
 * The name is the [fully qualified class name](http://php.net/manual/en/language.namespaces.rules.php)
 * of the class representing it.
 *
 * An asset bundle can depend on other asset bundles. When registering an asset bundle
 * with a view, all its dependent asset bundles will be automatically registered.
 *
 * http://www.yiiframework.com/doc-2.0/guide-structure-assets.html
 *
 * @author    sprokets
 * @package   Sproketshelpers
 * @since     2.0.0
 */
class DiscloseAssetsBundle extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * 
     * @return void 
     * @throws InvalidArgumentException 
     */
    public function init(): void
    {
        // define the path that your publishable resources live
        $this->sourcePath = "@sprokets/sproketshelpers/assetbundles/discloseassets/dist";

        // define the dependencies
        $this->depends = [
            CpAsset::class,
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'js/discloseassets.js',
        ];

        parent::init();
    }
}
