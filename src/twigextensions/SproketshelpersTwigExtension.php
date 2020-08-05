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
use Twig_Filter_Method;
use Twig_Markup;
use DOMDocument;

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
class SproketshelpersTwigExtension extends \Twig_Extension
{
    // Public Methods
    // =========================================================================

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'Sproketshelpers';
    }

    /**
     * Returns an array of Twig filters, used in Twig templates via:
     *
     *      {{ 'something' | someFilter }}
     *
     * @return array
     */
    public function getFilters()
    {
        return [
          new \Twig\TwigFilter('emailObfuscate', [$this, 'emailObfuscate']),
          new \Twig\TwigFilter('get_type', [$this, 'twig_get_type'] )
        ];
    }

    public function getTests()
    {
      return [
        new \Twig\TwigTest('of_type', [$this, 'twig_of_type'])
      ];
    }


    public function twig_of_type($var, $typeTest=null, $className=null)
	{

		switch ($typeTest)
		{
			default:
				return false;
				break;

			case 'array':
				return is_array($var);
				break;

			case 'bool':
				return is_bool($var);
				break;

			case 'class':
				return is_object($var) === true && get_class($var) === $className;
				break;

			case 'float':
				return is_float($var);
				break;

			case 'int':
				return is_int($var);
				break;

			case 'numeric':
				return is_numeric($var);
				break;

			case 'object':
				return is_object($var);
				break;

			case 'scalar':
				return is_scalar($var);
				break;

			case 'string':
				return is_string($var);
				break;
		}
	}

	public function twig_get_type($var)
	{
		return gettype( $var );
	}


  protected function pregEmails($string)
  {

    return preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $string);

  }

  /**
  * Regex to find email addresses and replace them with full HTML links
  *
  * @param string $string
  * @return string
  *
  */
  public function emailObfuscate($string)
  {

    if (trim($string) == '') {
      return;
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

    return new Twig_Markup($string, Craft::$app->view->getTwig()->getCharset());
  }

  /**
  * Returns a rot13 encrypted string as well as a JavaScript decoder function.
  * @param string $inputString The string to encrypt
  * @return string An encoded javascript function
  */
  private function js_rot13_encode($string)
  {
    $rotated = str_replace('"','\"',str_rot13($string));
    $string = '<script type="text/javascript">
      /*<![CDATA[*/
      document.write("'.$rotated.'".replace(/[a-zA-Z]/g, function(c){return String.fromCharCode((c<="Z"?90:122)>=(c=c.charCodeAt(0)+13)?c:c-26);}));
      /*]]>*/
      </script>';

    return $string;
  }
}
