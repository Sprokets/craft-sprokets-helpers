<?php

/**
 * test plugin for Craft CMS 3.x
 *
 * test
 *
 * @link      https://dtott.com
 * @copyright Copyright (c) 2019 dan
 */

namespace sprokets\sproketshelpers\twigextensions;

use sprokets\sproketshelpers\Sproketshelpers;

use Craft;
use Twig_Extension;
use Twig\Filter\Method;
use Twig\Markup;
use DOMDocument;
use Twig\TwigTest;

/**
 * Twig can be extended in many ways; you can add extra tags, filters, tests, operators,
 * global variables, and functions. You can even extend the parser itself with
 * node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 *
 * @author    dan
 * @package   Test
 * @since     1.0.0
 */
class SproketshelpersTwigExtension extends \Twig\Extension\AbstractExtension
{
  // Public Methods
  // =========================================================================

  /**
   * Returns the name of the extension.
   *
   * @return string The extension name
   */
  public function getName(): string
  {
    return 'Sproketshelpers';
  }

  /**
   * Returns an array of Twig filters, used in Twig templates via:
   *
   *      {{ 'something' | someFilter }}
   *
   * @return \Twig\TwigFilter[]
   */
  public function getFilters(): array
  {
    return [
      new \Twig\TwigFilter('emailObfuscate', [$this, 'emailObfuscate']),
      new \Twig\TwigFilter('get_type', [$this, 'twig_get_type'])
    ];
  }

  /**
   * 
   * @return TwigTest[] 
   */
  public function getTests(): array
  {
    return [
      new \Twig\TwigTest('of_type', [$this, 'twig_of_type'])
    ];
  }


  /**
   * 
   * @param mixed $var 
   * @param mixed $typeTest 
   * @param mixed $className 
   * @return bool 
   */
  public function twig_of_type($var, $typeTest = null, $className = null): bool
  {

    switch ($typeTest) {
      case 'array':
        return is_array($var);

      case 'bool':
        return is_bool($var);

      case 'class':
        return is_object($var) === true && get_class($var) === $className;

      case 'float':
        return is_float($var);

      case 'int':
        return is_int($var);

      case 'numeric':
        return is_numeric($var);

      case 'object':
        return is_object($var);

      case 'scalar':
        return is_scalar($var);

      case 'string':
        return is_string($var);

      default:
        return false;
    }
  }

  /**
   * 
   * @param mixed $var 
   * @return string 
   */
  public function twig_get_type($var): string
  {
    return gettype($var);
  }

  /**
   * 
   * @param string $string 
   * @return string|string[]|null 
   */
  protected function pregEmails(string $string)
  {

    return preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $string);
  }

  /**
   *
   * @param string $string
   * @return null|\Twig\Markup 
   */
  public function emailObfuscate(string $string): ?\Twig\Markup
  {

    if (trim($string) == '') {
      return null;
    }

    $string = $this->pregEmails($string);


    // Start the dom object
    $dom = new DOMDocument();
    $dom->recover = true;
    $dom->substituteEntities = true;

    // Feed the content to the dom object
    libxml_use_internal_errors(true);
    $dom->loadHTML($string);
    libxml_use_internal_errors(false);

    // Check each link
    foreach ($dom->getElementsByTagName('a') as $anchor) {

      // Get the href
      $href = $anchor->getAttribute('href');

      // // Check if it's a mailto link
      if (substr($href, 0, 7) == 'mailto:') {

        $anchor = $dom->saveHTML($anchor);
        $encoded = $this->js_rot13_encode($anchor);
        $string = str_replace($anchor, $encoded, $string);
      }
    }

    return new \Twig\Markup($string, Craft::$app->view->getTwig()->getCharset());
  }

  /**
   * Returns a rot13 encrypted string as well as a JavaScript decoder function.
   * @param string $string 
   * @return string 
   */
  private function js_rot13_encode(string $string): string
  {
    $rotated = str_replace('"', '\"', str_rot13($string));
    $string = '<script type="text/javascript">
      /*<![CDATA[*/
      document.write("' . $rotated . '".replace(/[a-zA-Z]/g, function(c){return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);}));
      /*]]>*/
      </script>';

    return $string;
  }
}
