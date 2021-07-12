<?php
/**
* Base class of action box objects
*
* box plugins must be inherited from this superclass
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Modules
* @version $Id: base_actionbox.php 39636 2014-03-19 18:27:18Z weinert $
*/

/**
* Base class of action box objects
*
* box plugins must be inherited from this superclass
*
* @package Papaya
* @subpackage Modules
*/
class base_actionbox extends base_plugin {

  /**
  * Css class
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'x-large';

  /**
  * box record database id
  * @var integer $boxId
  */
  var $boxId = 0;

  /**
  * language id
  * @var integer $languageId
  */
  var $languageId = 0;

  /**
  * Is the output cacheable?
  * @see base_actionbox::getCacheId()
  * @var boolean $cacheable
  */
  var $cacheable = TRUE;

  /**
  * more detailed cache dependencies
  *
  * querystring = depends on current $_SERVER['QUERY_STRING'],
  * page = depends on current page/topic id
  * surfer = depends on current surfer id (guest for invalid surfers)
  *
  * @see base_actionbox::getCacheId()
  * @var array $cacheDependency
  */
  var $cacheDependency = array(
    'querystring' => FALSE,
    'page' => FALSE,
    'surfer' => FALSE
  );

  /**
  * Return page XML
  *
  * @access public
  * @return string
  */
  function getParsedData() {
    return '';
  }

  /**
  * Execution of commands. When you implement your own class, you should override
  * this function if you need to execute commands this class receives via POST and
  * GET. The commands need to be tested for plausibility.
  *
  * @access public
  * @return boolean FALSE
  */
  function execute() {
    return FALSE;
  }

  /**
  * Get output cache identifier
  *
  * @see base_boxeslinks::getBoxCacheId()
  *
  * @param string $additionalCacheString a suffix to the caclulated cache string,
  *                                      can be used for inheritance
  * @access public
  * @return mixed Cache Id or FALSE
  */
  function getCacheId($additionalCacheString = '') {
    if ($this->cacheable) {
      $cacheString = get_class($this);
      if (is_array($this->cacheDependency)) {
        if (isset($this->cacheDependency['querystring']) &&
            $this->cacheDependency['querystring'] &&
            !empty($_SERVER['QUERY_STRING'])) {
          $cacheString .= '_query#'.$_SERVER['QUERY_STRING'];
        }
        if (isset($this->cacheDependency['page']) &&
            $this->cacheDependency['page']) {
          $cacheString .= '_page#'.$this->parentObj->topicId;
        }
        if (isset($this->cacheDependency['surfer']) &&
            $this->cacheDependency['surfer']) {
          $surfer = $this->papaya()->surfer;
          if ($surfer->isValid) {
            $surferId = (string)$surfer->surferId;
          } else {
            $surferId = 'guest';
          }
          $cacheString .= '_surfer#'.$surferId;
        }
      }
      return md5($cacheString.$additionalCacheString);
    } else {
      return FALSE;
    }
  }

  /**
  * Delete output cache of current box
  *
  * @access public
  */
  function deleteCache() {
    $directory = $this->papaya()->options['PAPAYA_PATH_CACHE'];
    if ($directory && ($dh = opendir($directory))) {
      while ($file = readdir($dh)) {
        $match = preg_match(
          '(^\.box_\d+_'.(int)$this->boxId.'(_[a-f\d]{32})+(\.\w+)?$)i', $file
        );
        if ($match) {
          unlink($directory.$file);
        }
      }
      closedir($dh);
    }
  }
}
