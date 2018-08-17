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
 * Papaya URL Transformer, transforms a absolute url to a relative url depending on conditional url
 *
 * @package Papaya-Library
 * @subpackage URL
 */
class Relative {

  /**
   * Transforms a absolute url to a relative url.
   *
   * @param \Papaya\URL $currentURL current url
   * @param \Papaya\URL $targetURL url to transform
   * @return string
   */
  public function transform($currentURL, $targetURL) {
    if (
      '' !== (string)$targetURL->getHost() &&
      $targetURL->getScheme() === (string)$currentURL->getScheme() &&
      $targetURL->getHost() === (string)$currentURL->getHost() &&
      $this->_comparePorts($targetURL->getPort(), $currentURL->getPort())
    ) {
      if (
        '' === (string)$targetURL->getUser() ||
        (string)$targetURL->getUser() === (string)$currentURL->getUser()
      ) {
        $path = $this->getRelativePath(
          $currentURL->getPath(),
          $targetURL->getPath()
        );
        if ('' !== (string)$targetURL->getQuery()) {
          $path .= '?'.$targetURL->getQuery();
        }
        if ('' !== (string)$targetURL->getFragment()) {
          $path .= '#'.$targetURL->getFragment();
        }
        return $path;
      }
    }
    return NULL;
  }

  /**
   * Compare two port and return TRUE if equal
   *
   * @param string $portOne
   * @param string $portTwo
   * @return boolean
   */
  private function _comparePorts($portOne, $portTwo) {
    if ($portOne == $portTwo ||
      ($portOne == '80' && empty($portTwo)) ||
      ($portTwo == '80' && empty($portOne))) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  /**
   * Get relative path from condition to target.
   *
   * @param string $currentPath
   * @param string $targetPath
   * @return string
   */
  public function getRelativePath($currentPath, $targetPath) {
    $parts = explode('/', $currentPath);
    array_pop($parts);
    $partCount = count($parts);
    $strippedPart = '';
    for ($i = 1; $i < $partCount; ++$i) {
      $part = $parts[$i];
      if (0 === strpos($targetPath.'/', $strippedPart.'/'.$part.'/')) {
        $strippedPart .= '/'.$part;
      } else {
        break;
      }
    }
    $result = '';
    if ($partCount - $i > 0) {
      $result = str_repeat('../', $partCount - $i);
    }
    $result .= substr($targetPath, strlen($strippedPart) + 1);
    if ($result == '') {
      return './';
    }
    return $result;
  }
}
