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


  public function getIdFromString($str) {
    return preg_replace('/\W+/','',strtolower(strip_tags($str)));

  }

  function getVimeoIdFromUrl($url)
  {
      if (preg_match('#(?:https?://)?(?:www.)?(?:player.)?vimeo.com/(?:[a-z]*/)*([0-9]{6,11})[?]?.*#', $url, $m)) {
          return $m[1];
      }
      return false;
  }

  function getVimeoInfo($url) {
    $imgid = $this->getVimeoIdFromUrl($url);

    if($imgid) {
      $vdata = unserialize(@file_get_contents("http://vimeo.com/api/v2/video/$imgid.php"));

      if($vdata[0]) {
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

  function getYoutubeInfo($url) {
    preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $url, $matches);

    if(isset($matches[1]) && $matches[1]) {
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

  public function getVideoInfo($url) {
    if(strpos($url, 'youtube') === false) {
      return $this->getVimeoInfo($url);
    }

    return $this->getYoutubeInfo($url);
  }

  function nl2p($string, $line_breaks = true, $xml = true) {

    $string = str_replace(array('<p>', '</p>', '<br>', '<br />'), '', $string);

    // It is conceivable that people might still want single line-breaks
    // without breaking into a new paragraph.
    if ($line_breaks == true) {
      return '<p>'.preg_replace(array("/([\n]{2,})/i", "/([^>])\n([^<])/i"), array("</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'), trim($string)).'</p>';
    }

    return '<p>'.preg_replace(
      array("/([\n]{2,})/i", "/([\r\n]{3,})/i","/([^>])\n([^<])/i"),
      array("</p>\n<p>", "</p>\n<p>", '$1<br'.($xml == true ? ' /' : '').'>$2'),
      trim($string)).'</p>';
  }

  public function productCount() {
    $criteria = craft()->elements->getCriteria('Commerce_Product');
    $criteria->limit = 10000;

    $allcrit = $criteria->find();
    return sizeof($allcrit);
  }

  public function purchasableBySku($sku)
  {
    return craft()->commerce_purchasables->getPurchasableBySku($sku);
  }

  public function getSizedImage($url, $size) {
    // $allowedSizes = array(60, 70, 100, 125, 161, 262, 320, 600);
    // $ext = strpos($url, '.jpg') !== false ? '.jpg' : (strpos($url, '.jpeg') !== false ? '.jpeg' : '') ;

    // if(in_array($size, $allowedSizes) && !empty($ext)) {
    //   return explode($ext, $url)[0] . "_{$size}{$ext}";
    // }
    return $url;
  }

  public function getUiFiles() {
    $manifestDirectory = Craft::$app->path->getTempPath() . 'assetmanifest';

    $assetDomain = getenv('ASSET_DOMAIN') ? getenv('ASSET_DOMAIN') : Craft::$app->config->general->assetDomain;

    $manifestPath = $manifestDirectory . '/assetmanifest.json';
    $expiresPath = $manifestDirectory . '/expires.txt';
    $uiheadPath = $manifestDirectory . '/uihead.html';
    $uibodyPath = $manifestDirectory . '/uibody.html';

    $auth = base64_encode("dott:chatham08");
    $context = stream_context_create(['http' => ['header' => "Authorization: Basic $auth"]]);

    $manifest = null;

    $expired = true;

    if(is_file($expiresPath)) {
      $expiresTime = file_get_contents($expiresPath);
      $expired = time() - $expiresTime > 30;
    }

    if(!is_file($manifestPath) || !is_file($uiheadPath) || !is_file($uibodyPath) || $expired) {
      if (!is_dir($manifestDirectory)) {
        mkdir($manifestDirectory);
      }
      $manifestContent = str_replace('"/', '"' . $assetDomain . '/', file_get_contents($assetDomain.'/asset-manifest.json',  false, $context));

      file_put_contents($manifestPath, $manifestContent);

      $uibodyContent = str_replace('"/', '"' . $assetDomain . '/', file_get_contents($assetDomain.'/static/html/uibody.html',  false, $context));
      file_put_contents($uibodyPath, $uibodyContent);

      $uiheadContent = str_replace('"/', '"' . $assetDomain . '/', file_get_contents($assetDomain.'/static/html/uihead.html',  false, $context));
      file_put_contents($uiheadPath, $uiheadContent);

      $manifest = json_decode($manifestContent, true);
      file_put_contents($manifestDirectory . '/expires.txt', time() + 30);
    }
    else {
      $manifest = json_decode(file_get_contents($manifestPath), true);
      $uiheadContent = file_get_contents($uiheadPath);
      $uibodyContent = file_get_contents($uibodyPath);
    }

    return array('manifest' => $manifest, 'uiheadContent' => $uiheadContent, 'uibodyContent' => $uibodyContent);
  }

  public function getUiHeadHtml() {
    $uifiles = self::getUiFiles();
    return $uifiles['uiheadContent'];
  }

  public function getUiBodyHtml() {
    $uifiles = self::getUiFiles();
    return $uifiles['uibodyContent'];
  }

  public function getUiFilePath($file) {


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
