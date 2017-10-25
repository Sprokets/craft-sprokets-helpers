<?php
/**
 * Sprokets Helpers plugin for Craft CMS
 *
 * Sprokets Helpers Variable
 *
 * --snip--
 * Craft allows plugins to provide their own template variables, accessible from the {{ craft }} global variable
 * (e.g. {{ craft.pluginName }}).
 *
 * https://craftcms.com/docs/plugins/variables
 * --snip--
 *
 * @author    Sprokets
 * @copyright Copyright (c) 2017 Sprokets
 * @link      http://sprokets.net
 * @package   SproketsHelpers
 * @since     1.0.0
 */

namespace Craft;

class SproketsHelpersVariable
{
  /**
   * For eventual use with asset manifest
   */

  public function getUiFilePath($file) {
    $manifestDirectory = craft()->path->getTempPath() . 'assetmanifest';
    $manifestPath = $manifestDirectory . '/assetmanifest.json';
    $expiresPath = $manifestDirectory . '/expires.txt';

    $manifest = null;

    $expired = true;

    if(is_file($expiresPath)) {
      $expiresTime = file_get_contents($expiresPath);
      $expired = time() - $expiresTime > 3600;
    }

    if(!is_file($manifestPath) || $expired) {
      if (!is_dir($manifestDirectory)) {
        mkdir($manifestDirectory);
      }
      $manifestContent = file_get_contents(craft()->config->get('assetmanifest'));
      file_put_contents($manifestPath, $manifestContent);
      $manifest = json_decode($manifestContent, true);
      file_put_contents($manifestDirectory . '/expires.txt', time() + 3600);
    }
    else {
      $manifest = json_decode(file_get_contents($manifestPath), true);
    }


      // json_decode(file_get_contents('http://assets.inst.com.s3.amazonaws.com/www.develop/build/asset-manifest.json'), true),

    return isset($manifest[$file])
    ?
    craft()->config->get('assetprefix') . $manifest[$file]
    :
    $file;
  }




}
