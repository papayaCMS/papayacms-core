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

namespace Papaya\URL\Transformer;
/**
 * Papaya URL Transformer Cleanup, parses a url, removes "./", "../" and "//" from it.
 *
 * @package Papaya-Library
 * @subpackage URL
 */
class Cleanup {

  /**
   * Remove relative paths from the url
   *
   * @param string $target url to transform
   * @return string
   */
  public function transform($target) {
    $result = '';
    if ($url = parse_url($target)) {
      $url['path'] = empty($url['path']) ? '/' : $this->_calculateRealPath($url['path']);
      if (isset($url['host'])) {
        $result .= empty($url['scheme']) ? 'http://' : $url['scheme'].'://';
        if (isset($url['user'])) {
          $result .= $url['user'];
          if (isset($url['pass'])) {
            $result .= ':'.$url['pass'];
          }
          $result .= '@';
        }
        $result .= $url['host'];
        if (isset($url['port'])) {
          $result .= ':'.$url['port'];
        }
      }
      $result .= \Papaya\Utility\Arrays::get($url, 'path', '');
      if (isset($url['query'])) {
        $result .= '?'.$url['query'];
      }
      if (isset($url['fragment'])) {
        $result .= '#'.$url['fragment'];
      }
    }
    return $result;
  }

  /**
   * This method calculates /../ occurrences and removes // and /./ occurrences from a path
   *
   * @param string $path
   * @return string
   */
  protected function _calculateRealPath($path) {
    // in order to keep leading/trailing slashes, remember them
    $leadingSlash = (0 === strpos($path, '/'));
    $trailingSlash = ('/' === substr($path, -1));

    $pathElements = explode('/', $path);
    $outputElements = array();
    foreach ($pathElements as $element) {
      if ('..' === $element) {
        if (count($outputElements) > 0) {
          // going one level up, we drop the last valid folder element
          array_pop($outputElements);
        }
      } elseif ('.' !== $element && '' !== $element) {
        // ignoring same folder and empty elements, adding valid folders to output
        $outputElements[] = $element;
      }
    }

    $result = $leadingSlash ? '/' : '';
    $result .= implode('/', $outputElements);
    if ('/' !== $result && $trailingSlash) {
      $result .= '/';
    }

    return $result;
  }
}
