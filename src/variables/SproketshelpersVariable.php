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
      $vdata = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$imgid.php"));

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

    if($matches[1]) {
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
}
