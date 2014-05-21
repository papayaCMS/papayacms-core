<?php
/**
* paging buttons for listviews and other uses
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Administration
* @version $Id: papaya_paging_buttons.php 39734 2014-04-08 19:01:37Z weinert $
*/


/**
* paging buttons for listviews and other uses
* @package Papaya
* @subpackage Administration
*/
class papaya_paging_buttons {

  /**
  * Get pages navigation
  *
  * @param base_object $aOwner
  * @param array $baseParams basic link params
  * @param integer $offset current offset
  * @param integer $step offset step size
  * @param integer $max offset maximum
  * @param integer $groupCount page link count
  * @param string $paramName offset param name
  * @param string $buttonAlign button alignment left|right|default
  * @return string
  */
  public static function getPagingButtons(
    $aOwner,
    $baseParams,
    $offset,
    $step,
    $max,
    $groupCount = 9,
    $paramName = 'offset',
    $buttonAlign = NULL
  ) {
    // this makes sure the for loop terminals eventually
    $step = ($step > 0) ? $step : 10;
    if (is_object($aOwner) && is_subclass_of($aOwner, 'base_object')) {
      if ($max > $step) {
        switch ($buttonAlign) {
        case 'left' :
          $positionTag = 'left';
          break;
        case 'right' :
          $positionTag = 'right';
          break;
        default :
          $positionTag = 'buttons';
          break;
        }

        $pageCount = ceil($max / $step);
        $currentPage = ceil($offset / $step);

        $result = '<'.papaya_strings::escapeHTMLChars($positionTag).'>';
        if ($currentPage > 0) {
          $i = ($currentPage - 1) * $step;
          $params = $baseParams;
          $params[$paramName] = $i;
          $result .= sprintf(
            '<button hint="%s" glyph="%s" href="%s"/>'.LF,
            papaya_strings::escapeHTMLChars($aOwner->_gt('Previous page')),
            papaya_strings::escapeHTMLChars($aOwner->papaya()->images['actions-go-previous']),
            papaya_strings::escapeHTMLChars($aOwner->getLink($params))
          );
        } else {
          $result .= sprintf(
            '<button glyph="%s" />'.LF,
            papaya_strings::escapeHTMLChars(
              $aOwner->papaya()->images['status-go-previous-disabled']
            )
          );
        }

        if ($pageCount > $groupCount) {
          $plusMinus = floor($groupCount / 2);
          $pageMin = ceil(($offset - ($step * ($plusMinus))) / $step);
          $pageMax = ceil(($offset + ($step * ($plusMinus))) / $step);
          if ($pageMin < 0) {
            $pageMin = 0;
          }
          if ($pageMin == 0) {
            $pageMax = $groupCount;
          } elseif ($pageMax >= $pageCount) {
            $pageMax = $pageCount;
            $pageMin = $pageCount - $groupCount;
          }
          for ($x = $pageMin; $x < $pageMax; $x++) {
            $i = $x * $step;
            $down = ($i == $offset)? ' down="down"' : '';
            $params = $baseParams;
            $params[$paramName] = $i;
            $result .= sprintf(
              '<button title="%s" href="%s"%s/>'.LF,
              papaya_strings::escapeHTMLChars($x + 1),
              papaya_strings::escapeHTMLChars($aOwner->getLink($params)),
              $down
            );
          }
        } else {
          for ($i = 0, $x = 1; $i < $max; $i += $step, $x++) {
            $down = ($i == $offset)? ' down="down"' : '';
            $params = $baseParams;
            $params[$paramName] = $i;
            $result .= sprintf(
              '<button title="%s" href="%s"%s/>'.LF,
              papaya_strings::escapeHTMLChars($x),
              papaya_strings::escapeHTMLChars($aOwner->getLink($params)),
              $down
            );
          }
        }
        if ($currentPage < $pageCount - 1) {
          $i = ($currentPage + 1) * $step;
          $params = $baseParams;
          $params[$paramName] = $i;
          $result .= sprintf(
            '<button hint="%s" glyph="%s" href="%s"/>'.LF,
            papaya_strings::escapeHTMLChars($aOwner->_gt('Next page')),
            papaya_strings::escapeHTMLChars($aOwner->papaya()->images['actions-go-next']),
            papaya_strings::escapeHTMLChars($aOwner->getLink($params))
          );
        } else {
          $result .= sprintf(
            '<button glyph="%s" />'.LF,
            papaya_strings::escapeHTMLChars($aOwner->papaya()->images['status-go-next-disabled'])
          );
        }
        $result .= '</'.papaya_strings::escapeHTMLChars($positionTag).'>';
        return $result;
      }
    }
    return '';
  }


  /**
  * Get pages navigation from array
  *
  * @param base_object $aOwner
  * @param array $baseParams basic link params
  * @param array $pageValues links (value => caption)
  * @param mixed $currentValue current link value
  * @param string $paramName offset param name
  * @param string $buttonAlign button alignment left|right|default
  * @return string
  */
  public static function getButtons(
    $aOwner, $baseParams, $pageValues, $currentValue, $paramName = 'limit', $buttonAlign = NULL
  ) {
    if (is_object($aOwner) && is_subclass_of($aOwner, 'base_object')) {
      if (is_array($pageValues) && count($pageValues) > 0) {
        switch ($buttonAlign) {
        case 'left' :
          $positionTag = 'left';
          break;
        case 'right' :
          $positionTag = 'right';
          break;
        default :
          $positionTag = 'buttons';
          break;
        }

        $result = '<'.papaya_strings::escapeHTMLChars($positionTag).'>';
        foreach ($pageValues as $pageValue => $buttonData) {
          $params = $baseParams;
          $params[$paramName] = $pageValue;
          $down = ($pageValue == $currentValue)? ' down="down"' : '';
          if (is_array($buttonData)) {
            $result .= sprintf(
              '<button hint="%s" glyph="%s" href="%s"%s/>'.LF,
              papaya_strings::escapeHTMLChars($buttonData[0]),
              papaya_strings::escapeHTMLChars($buttonData[1]),
              papaya_strings::escapeHTMLChars($aOwner->getLink($params)),
              $down
            );
          } else {
            $result .= sprintf(
              '<button title="%s" href="%s"%s/>'.LF,
              papaya_strings::escapeHTMLChars($buttonData),
              papaya_strings::escapeHTMLChars($aOwner->getLink($params)),
              $down
            );
          }
        }
        $result .= '</'.papaya_strings::escapeHTMLChars($positionTag).'>';
        return $result;
      }
    }
    return '';
  }

}


