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
* error level information
* @var integer
*/
define('PAPAYA_FEED_ERROR_INFO', 1);
/**
* error level warning
* @var integer
*/
define('PAPAYA_FEED_ERROR_WARNING', 2);
/**
* error level fatal error
* @var integer
*/
define('PAPAYA_FEED_ERROR_FATAL', 3);

/**
* error code required element
* @var integer
*/
define('PAPAYA_FEED_ERRORCODE_REQUIRED', 1);
/**
* error code wrong format
* @var integer
*/
define('PAPAYA_FEED_ERRORCODE_FORMAT', 2);
/**
* error code duplicate for unique element
* @var integer
*/
define('PAPAYA_FEED_ERRORCODE_DUPLICATE', 3);

/**
* Validator for an Atom 1.0 feed
*
* @package Papaya-Library
* @subpackage XML-Feed
*/
class papaya_atom_validate {

  /**
  * error list
  * @var array
  */
  var $_errors = array();
  /**
  * feed object
  * @var papaya_atom_feed
  */
  var $_feed = NULL;

  /**
   * @var array
   */
  private $_ids = array();

  /**
   * @var boolean
   */
  private $_feedHasAuthor = FALSE;

  /**
   * @var boolean
   */
  private $_allEntriesHaveAuthors = FALSE;

  /**
  * create validator object and attach feed
  * @param $feed
  */
  function __construct(papaya_atom_feed $feed) {
    $this->_feed = $feed;
  }

  /**
  * add error to list
  * @param integer $level
  * @param integer $code
  * @param string $element
  * @param string $index
  * @param string $property
  * @return void
  */
  function _addError($level, $code, $element, $index, $property) {
    if (!(isset($this->_errors) && is_array($this->_errors))) {
      $this->_errors = array();
    }
    $this->_errors[] = array(
      'level' => $level,
      'code' => $code,
      'element' => $element,
      'index' => $index,
      'property' => $property
    );
  }

  /**
  * get errors in html format
  * @return string
  */
  function getErrorsHTML() {
    if (isset($this->_errors) && is_array($this->_errors) && count($this->_errors) > 0) {
      $result = '<ul>';
      foreach ($this->_errors as $error) {
        switch ($error['level']) {
        case PAPAYA_FEED_ERROR_INFO :
          $errorLevel = 'INFORMATION';
          break;
        case PAPAYA_FEED_ERROR_WARNING :
          $errorLevel = 'WARNING';
          break;
        case PAPAYA_FEED_ERROR_FATAL :
          $errorLevel = 'FATAL ERROR';
          break;
        default :
          $errorLevel = 'UNKNOWN';
          break;
        }
        switch ($error['code']) {
        case PAPAYA_FEED_ERRORCODE_REQUIRED :
          $errorMsg = '%1$s: Missing "%4$s" for "%2$s#%3$s".';
          break;
        case PAPAYA_FEED_ERRORCODE_FORMAT :
          $errorMsg = '%1$s: Invalid data in "%4$s" for "%2$s#%3$s".';
          break;
        case PAPAYA_FEED_ERRORCODE_DUPLICATE :
          $errorMsg = '%1$s: Duplicate "%$4s" for "%$2s#%$3s".';
          break;
        default :
          $errorMsg = '%1$s: Error in "%$4s" for "%$2s#%$3s".';
          break;
        }
        $result .= '<li>';
        $result .= sprintf(
          $errorMsg,
          $errorLevel,
          htmlspecialchars($error['element']),
          htmlspecialchars($error['index']),
          htmlspecialchars($error['property'])
        );
        $result .= '</li>';
      }
      $result .= '</ul>';
      return $result;
    }
    return '';
  }

  /**
  * validate attached feed
  * @return void
  */
  function validate() {
    $this->_errors = array();
    $this->_ids = array();
    $this->_feedHasAuthor = FALSE;
    $this->_allEntriesHaveAuthors = TRUE;
    $feedId = $this->_feed->id->get();
    if (empty($feedId)) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_REQUIRED, 'feed', 0, 'id'
      );
    }
    if (empty($this->_feed->updated)) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_REQUIRED, 'feed', 0, 'updated'
      );
    } elseif ($this->_feed->updated <= 0) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_FORMAT, 'feed', 0, 'updated'
      );
    }
    if ($this->_feed->title->isEmpty()) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_REQUIRED, 'feed', 0, 'title'
      );
    }
    $elementCount = $this->_feed->authors->count();
    if ($elementCount > 0) {
      for ($idx = 0; $idx < $elementCount; $idx++) {
        /** @var papaya_atom_person $author */
        $author = $this->_feed->authors->item($idx);
        if ($this->_validatePerson($author, $idx, 'feed::author')) {
          $this->_feedHasAuthor = TRUE;
        }
      }
    }
    $elementCount = $this->_feed->contributors->count();
    if ($elementCount > 0) {
      for ($idx = 0; $idx < $elementCount; $idx++) {
        /** @var papaya_atom_person $contributor */
        $contributor = $this->_feed->contributors->item($idx);
        $this->_validatePerson($contributor, $idx, 'feed::contributor');
      }
    }
    $elementCount = $this->_feed->entries->count();
    if ($elementCount > 0) {
      for ($idx = 0; $idx < $elementCount; $idx++) {
        /** @var papaya_atom_entry $entry */
        $entry = $this->_feed->entries->item($idx);
        $this->_validateEntry($entry, $idx);
      }
    }

    if (!($this->_feedHasAuthor || $this->_allEntriesHaveAuthors)) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_REQUIRED, 'feed', 0, 'author'
      );
    }
  }

  /**
  * validate ffed entry
  * @param papaya_atom_entry $entry
  * @param integer $idx
  * @return void
  */
  function _validateEntry($entry, $idx) {
    $entryId = $entry->id->get();
    if (empty($entryId)) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_REQUIRED, 'entry', $idx, 'id'
      );
    } elseif (isset($this->_ids[$entryId]) && $entry->updated != $this->_ids[$entryId]) {
      $this->_addError(
        PAPAYA_FEED_ERROR_WARNING, PAPAYA_FEED_ERRORCODE_DUPLICATE, 'entry', $idx, 'id'
      );
    } elseif (isset($this->_ids[$this->_feed->id->get()])) {
      $this->_addError(
        PAPAYA_FEED_ERROR_WARNING, PAPAYA_FEED_ERRORCODE_DUPLICATE, 'entry', $idx, 'id'
      );
    }
    if (empty($entry->updated)) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_REQUIRED, 'entry', $idx, 'updated'
      );
      $this->_ids[$entryId] = 0;
    } elseif ($entry->updated <= 0) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_FORMAT, 'entry', $idx, 'updated'
      );
      $this->_ids[$entryId] = 0;
    } else {
      $this->_ids[$entryId] = $entry->updated;
    }
    if ($entry->title->isEmpty()) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_REQUIRED, 'entry', $idx, 'title'
      );
    }
    $entryHasAuthor = FALSE;
    $elementCount = $entry->authors->count();
    if ($elementCount > 0) {
      for ($idx = 0; $idx < $elementCount; $idx++) {
        /** @var papaya_atom_person $author */
        $author = $entry->authors->item($idx);
        if ($this->_validatePerson($author, $idx, 'feed::author')) {
          $entryHasAuthor = TRUE;
        }
      }
    }
    if (!$entryHasAuthor) {
      $this->_allEntriesHaveAuthors = FALSE;
      if ($this->_feedHasAuthor) {
        $this->_addError(
          PAPAYA_FEED_ERROR_WARNING, PAPAYA_FEED_ERRORCODE_REQUIRED, 'entry', $idx, 'author'
        );
      }
    }
    $elementCount = $entry->contributors->count();
    if ($elementCount > 0) {
      for ($idx = 0; $idx < $elementCount; $idx++) {
        /** @var papaya_atom_person $contributor */
        $contributor = $entry->contributors->item($idx);
        $this->_validatePerson($contributor, $idx, 'feed::contributor');
      }
    }
  }

  /**
  * validate person object
  * @param papaya_atom_person $person
  * @param integer $idx
  * @param string $elementType
  * @return boolean
  */
  function _validatePerson($person, $idx, $elementType) {
    $result = TRUE;
    if (empty($person->name)) {
      $this->_addError(
        PAPAYA_FEED_ERROR_FATAL, PAPAYA_FEED_ERRORCODE_REQUIRED, $elementType, $idx, 'name'
      );
      $result = FALSE;
    }
    if (!\Papaya\Filter\Factory::isEmail($person->email, FALSE)) {
      $this->_addError(
        PAPAYA_FEED_ERROR_WARNING, PAPAYA_FEED_ERRORCODE_FORMAT, $elementType, $idx, 'email'
      );
      $result = FALSE;
    }
    if (!\Papaya\Filter\Factory::isUrl($person->uri, FALSE)) {
      $this->_addError(
        PAPAYA_FEED_ERROR_WARNING, PAPAYA_FEED_ERRORCODE_FORMAT, $elementType, $idx, 'uri'
      );
      $result = FALSE;
    }
    return $result;
  }
}
