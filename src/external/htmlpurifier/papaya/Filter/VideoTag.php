<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

/**
 * load parent filter class
 */
require_once HTMLPURIFIER_INCLUDE_PATH.'HTMLPurifier/Filter.php';
require_once PAPAYA_INCLUDE_PATH.'system/sys_base_db.php';

/**
 * HTML purifier filter for papaya:media tags
 *
 * @package Papaya
 * @subpackage Validation
 */
class HTMLPurifier_Filter_VideoTag extends HTMLPurifier_Filter {
  var $name = 'papaya frontend video tag';
  var $defaultHeight = '300';
  var $defaultWidth = '400';
  var $useSWFObject = FALSE;

  function __construct($useSWFObject = FALSE) {
    $this->useSWFObject = $useSWFObject;
  }

  function HTMLPurifier_Filter_VideoTag($useSWFObject = FALSE) {
    $this->__construct($useSWFObject);
  }

  /**
   * pre filter: do nothing
   *
   * @param string $html
   * @param object $config
   * @param object &$context
   * @return string
   */
  function preFilter($html, $config, &$context) {
    return $html;
  }

  /**
   * post filter: replace <video>-Tags with object codes (with swfobject or with object/embed)
   *
   * @param string $html
   * @param object $config
   * @param object &$context
   * @return string = $html
   */
  function postFilter($html, $config, &$context) {
    $flashCode = '<object type="application/x-shockwave-flash" width="%3$s" height="%2$s" data="%1$s">'.
          '<param name="movie" value="%1$s" />'.
          '<param name="wmode" value="transparent" />'.
          '<param name="allowScriptAccess" value="sameDomain" />'.
          '<param name="quality" value="best" />'.
          '<param name="bgcolor" value="#FFFFFF" />'.
          '<param name="FlashVars" value="%4$s" />'.
        '</object>';

    // possible swfcode
    $swfObjCode = '<div id="%4$s">This video requires Javascript to be enabled, and requires the '.
                  '<a href="http://www.adobe.com/go/getflashplayer">Adobe Flash Player.</a></div>'.
  '<script type="text/javascript">
      /*<![CDATA[*/
      var flashvars = {playerMode: "embedded"};
      var params = {movie: "%1$s", wmode: "transparent", allowScriptAccess: "sameDomain", quality: "best", bgcolor: "#ffffff", FlashVars: "playerMode=embedded"};
      var attributes = {};
      swfobject.embedSWF("%1$s", "%4$s", "%3$s", "%2$s", "9.0.0", "expressInstall.swf", flashvars, params, attributes);
      /*]]>*/
    </script>';

    $pattern = '~<(video)(\s*([^>]*)|)>([^<]*)</\1>~i';
    $pattern2 = '~([a-z]*)="([^"]*)"~i';

    $replaces = array();
    if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
      foreach ($matches as $match) {
        if (isset($match[3]) && isset($match[4]) && strlen($match[4]) > 0 && !isset($replaces[$match[0]])) {
          $url = trim($match[4]);
          if (strlen($match[3]) > 0 && preg_match_all($pattern2, $match[3], $matches2, PREG_SET_ORDER)) {
            foreach ($matches2 as $match2) {
              if ($match2 && is_array($match2)) {
                if (isset($match2[1]) && $match2[1] == 'width') {
                  $width = $match2[2];
                } else if (isset($match2[1]) && $match2[1] == 'height') {
                  $height = $match2[2];
                } else if (isset($match2[1]) && $match2[1] == 'dataurl') {
                  $dataURL = $match2[2];
                } else if (isset($match2[1]) && $match2[1] == 'flashvars') {
                  $flashvars = $match2[2];
                }
              }
            }
          }
          if ($this->useSWFObject) {
            $replaces[$match[0]] = sprintf($swfObjCode, $url,
              (isset($height) ? $height : $height = $this->defaultHeight),
              (isset($width)  ? $width  : $width  = $this->defaultWidth),
              md5($url));
              // TODO dataurl, flashvars
          } else {
            // Get & Build FlashParams if need (fall back to data url)
            if (!isset($flashvars)) {
              $flashvars = '';
              if (isset($dataURL)) {
                $flashvars = 'dataurl='.$dataURL;
              } else {
                $flashvars = 'playerMode=embedded';
              }
            }
            // Use template and build object tag
            $replaces[$match[0]] = sprintf($flashCode, $url,
              (isset($height) ? $height : $height = $this->defaultHeight),
              (isset($width)  ? $width  : $width  = $this->defaultWidth),
              $flashvars);
          }

          unset($width);
          unset($height);
          unset($dataURL);
          unset($flashvars);
        }
      }
    }

    if (count($replaces) > 0) {
      return str_replace(array_keys($replaces), array_values($replaces), $html);
    }
    return $html;
  }
}
?>
