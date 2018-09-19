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
namespace Papaya\Utility\Text;

/**
 * Papaya Utilities - Papaya functions
 *
 * @package Papaya-Library
 * @subpackage Util
 *
 * @deprecated
 */
class Papaya {
  const PAPAYA_TAG_PATTERN = '(<(papaya|ndim):([a-z]\w+)\s?([^>]*)\/?>(<\/(\1):(\2)>)?)ims';

  const PAPAYA_INPUT_PATTERN = '(^([^.,]+(\.\w+)?)(,(\d+)(,(\d+)(,(\w+))?)?)?$)i';

  /**
   * Get papaya image tag <papaya:media...
   *
   * @param string $str this is the string the dialog type image(?)
   *                    contains like "32242...,max,200,300"
   * @param int $width optional, default value 0
   * @param int $height optional, default value 0
   * @param string $alt optional, default value ''
   * @param mixed $resize optional, default value NULL
   * @param string $subTitle
   *
   * @return string tag or ''
   */
  public static function getImageTag(
    $str, $width = 0, $height = 0, $alt = '', $resize = NULL, $subTitle = ''
  ) {
    if (\preg_match(self::PAPAYA_TAG_PATTERN, $str, $regs)) {
      return $regs[0];
    } elseif (\preg_match(self::PAPAYA_INPUT_PATTERN, $str, $regs)) {
      $result = '<papaya:media src="'.\Papaya\Utility\Text\XML::escape($regs[1]).'"';
      if ($width > 0) {
        $result .= ' width="'.(int)$width.'"';
      } elseif (isset($regs[4])) {
        $result .= ' width="'.(int)$regs[4].'"';
      }
      if ($height > 0) {
        $result .= ' height="'.(int)$height.'"';
      } elseif (isset($regs[6])) {
        $result .= ' height="'.(int)$regs[6].'"';
      }
      if (isset($resize)) {
        $result .= ' resize="'.\Papaya\Utility\Text\XML::escape($resize).'"';
      } elseif (isset($regs[8])) {
        $result .= ' resize="'.\Papaya\Utility\Text\XML::escape($regs[8]).'"';
      }
      if (isset($alt) && '' != \trim($alt)) {
        $result .= ' alt="'.\Papaya\Utility\Text\XML::escape($alt).'"';
      }
      if (!empty($subTitle)) {
        $result .= ' subtitle="'.\Papaya\Utility\Text\XML::escape($subTitle).'"';
      }
      return $result.'/>';
    }
    return '';
  }
}
