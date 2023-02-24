<?php

/**
 * sprokets-helpers plugin for Craft CMS 3.x
 *
 * Misc helpers
 *
 * @link      https://sprokets.net
 * @copyright Copyright (c) 2018 sprokets
 */

namespace sprokets\sproketshelpers\variables;

use sprokets\sproketshelpers\Sproketshelpers;

use Craft;

/**
 * sprokets-helpers Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.sproketshelpers }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    sprokets
 * @package   Sproketshelpers
 * @since     2.0.0
 */
class SproketshelpersVariable
{
  // Public Methods
  // =========================================================================

  /**
   * 
   * @param string $str 
   * @return string|string[]|null 
   */
  public function getIdFromString(string $str)
  {
    return preg_replace('/\W+/', '', strtolower(strip_tags($str)));
  }

  /**
   * 
   * @param string $url 
   * @return string|false 
   */
  function getVimeoIdFromUrl(string $url)
  {
    if (preg_match('#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[a-z]*/)*([0-9]{6,11})[?]?.*#', $url, $m)) {
      return $m[1];
    }
    return false;
  }

  /**
   * 
   * @param string $url 
   * @return array<string, mixed>|null 
   */
  function getVimeoInfo(string $url)
  {
    $imgid = $this->getVimeoIdFromUrl($url);

    if ($imgid) {
      $vdata = unserialize(@file_get_contents("http://vimeo.com/api/v2/video/$imgid.php"));

      if ($vdata[0]) {
        return array(
          'id' => $imgid,
          'type' => 'vimeo',
          'embed' => '<iframe src="https://player.vimeo.com/video/' . $imgid . '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
          'hqthumbnail' => $vdata[0]['thumbnail_large'],
          'thumbnail' => $vdata[0]['thumbnail_medium'],
        );
      }
    }

    return null;
  }

  /**
   * 
   * @param string $url 
   * @return string[]|null 
   */
  function getYoutubeInfo(string $url)
  {
    preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);

    if (isset($matches[1]) && $matches[1]) {
      $id = $matches[1];

      return array(
        'id' => $id,
        'type' => 'youtube',
        'embed' => '<iframe src="https://www.youtube.com/embed/' . $id . '?rel=0&showinfo=0&color=white&iv_load_policy=3" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>',
        'hqthumbnail' => 'https://i1.ytimg.com/vi/' . $id . '/hqdefault.jpg',
        'thumbnail' => 'https://i1.ytimg.com/vi/' . $id . '/default.jpg'
      );
    }

    return null;
  }

  /**
   * 
   * @param string $url 
   * @return array<string, mixed>|null 
   */
  public function getVideoInfo(string $url)
  {
    if (strpos($url, 'youtube') === false) {
      return $this->getVimeoInfo($url);
    }

    return $this->getYoutubeInfo($url);
  }

  /**
   * 
   * @param string $string 
   * @param bool $line_breaks 
   * @param bool $xml 
   * @return string 
   */
  function nl2p(string $string, bool $line_breaks = true, bool $xml = true): string
  {

    $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

    // It is conceivable that people might still want single line-breaks
    // without breaking into a new paragraph.
    if ($line_breaks == true) {
      return '<p>' . preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '$1<br' . ($xml == true ? ' /' : '') . '>$2'), trim($string)) . '</p>';
    }

    return '<p>' . preg_replace(
      array("/([\n]{2,})/i", "/([\r\n]{3,})/i", "/([^>])\n([^<])/i"),
      array("</p>\n<p>", "</p>\n<p>", '$1<br' . ($xml == true ? ' /' : '') . '>$2'),
      trim($string)
    ) . '</p>';
  }

  /**
   * @return int
   */
  public function productCount()
  {
    $count = \craft\elements\Entry::find()->section('Commerce_Product')->count();
    return $count;
  }

  /**
   * 
   * @param string $sku 
   * @return mixed 
   */
  public function purchasableBySku(string $sku)
  {
    return \Craft::$app->commerce_purchasables->getPurchasableBySku($sku);
  }

  /**
   * 
   * @param string $url 
   * @param mixed $size 
   * @return string  
   */
  public function getSizedImage(string $url, $size): string
  {
    // $allowedSizes = array(60, 70, 100, 125, 161, 262, 320, 600);
    // $ext = strpos($url, '.jpg') !== false ? '.jpg' : (strpos($url, '.jpeg') !== false ? '.jpeg' : '') ;

    // if(in_array($size, $allowedSizes) && !empty($ext)) {
    //   return explode($ext, $url)[0] . "_{$size}{$ext}";
    // }
    return $url;
  }

  /**
   * 
   * @return string 
   */
  public function getNonce(): string
  {
    return Sproketshelpers::$plugin::$nonce;
  }

  /**
   * 
   * @return string 
   */
  public function getNonceAttribute()
  {
    if (Sproketshelpers::$plugin::$nonce) {
      return 'nonce="' . Sproketshelpers::$plugin::$nonce . '"';
    } else {
      return '';
    }
  }

  /**
   * 
   * @param bool $useNonce 
   * @return array<string, mixed> 
   */
  public function getUiFiles($useNonce = false)
  {
    $manifestDirectory = Craft::$app->path->getTempPath() . '/assetmanifest';

    $assetDomain = getenv('ASSET_DOMAIN') ? getenv('ASSET_DOMAIN') : Craft::$app->config->general->assetDomain;

    $manifestPath = $manifestDirectory . '/assetmanifest.json';
    $expiresPath = $manifestDirectory . '/expires.txt';
    $uiheadPath = $manifestDirectory . '/uihead.html';
    $uibodyPath = $manifestDirectory . '/uibody.html';

    $auth = base64_encode("dott:chatham08");
    $context = stream_context_create(['http' => ['header' => "Authorization: Basic $auth"]]);

    $manifest = null;

    $expired = true;

    if (is_file($expiresPath)) {
      $expiresTime = file_get_contents($expiresPath);
      $expired = time() - $expiresTime > 30;
    }

    if (!is_file($manifestPath) || !is_file($uiheadPath) || !is_file($uibodyPath) || $expired) {
      if (!is_dir($manifestDirectory)) {
        mkdir($manifestDirectory);
      }
      $manifestContent = str_replace('"/', '"' . $assetDomain . '/', file_get_contents($assetDomain . '/asset-manifest.json',  false, $context));

      file_put_contents($manifestPath, $manifestContent);

      $uibodyContent = str_replace('"/', '"' . $assetDomain . '/', file_get_contents($assetDomain . '/static/html/uibody.html',  false, $context));
      file_put_contents($uibodyPath, $uibodyContent);

      $uiheadContent = str_replace('"/', '"' . $assetDomain . '/', file_get_contents($assetDomain . '/static/html/uihead.html',  false, $context));
      file_put_contents($uiheadPath, $uiheadContent);



      $manifest = json_decode($manifestContent, true);


      file_put_contents($manifestDirectory . '/expires.txt', time() + 30);
    } else {
      $manifest = json_decode(file_get_contents($manifestPath), true);

      $uiheadContent = file_get_contents($uiheadPath);
      $uibodyContent = file_get_contents($uibodyPath);
    }

    if ($useNonce && Sproketshelpers::$plugin::$nonce) {
      $uiheadContent = str_replace('<script', '<script nonce="' . Sproketshelpers::$plugin::$nonce . '"', $uiheadContent);
      $uiheadContent = str_replace('<style', '<style nonce="' . Sproketshelpers::$plugin::$nonce . '"', $uiheadContent);
      $uiheadContent = str_replace('<link', '<link nonce="' . Sproketshelpers::$plugin::$nonce . '"', $uiheadContent);
      $uibodyContent = str_replace('<script', '<script nonce="' . Sproketshelpers::$plugin::$nonce . '"', $uibodyContent);
      $uibodyContent = str_replace('<style', '<style nonce="' . Sproketshelpers::$plugin::$nonce . '"', $uibodyContent);
      $uibodyContent = str_replace('<link', '<link nonce="' . Sproketshelpers::$plugin::$nonce . '"', $uibodyContent);
    }

    return array('manifest' => $manifest, 'uiheadContent' => $uiheadContent, 'uibodyContent' => $uibodyContent);
  }

  /**
   * 
   * @param bool $useNonce 
   * @return mixed 
   */
  public function getUiHeadHtml(bool $useNonce = false)
  {
    $uifiles = self::getUiFiles($useNonce);
    return $uifiles['uiheadContent'];
  }

  /**
   * 
   * @param bool $useNonce 
   * @return mixed 
   */
  public function getUiBodyHtml(bool $useNonce = false)
  {
    $uifiles = self::getUiFiles($useNonce);
    return $uifiles['uibodyContent'];
  }

  /**
   * 
   * @param string $file 
   * @return mixed 
   */
  public function getUiFilePath(string $file)
  {


    $uifiles = self::getUiFiles();
    $manifest = $uifiles['manifest'];
    // json_decode(file_get_contents('http://assets.ralstoninst.com.s3.amazonaws.com/www.develop/build/asset-manifest.json'), true),

    return isset($manifest[$file])
      ?
      $manifest[$file]
      :
      $file;
  }
}
