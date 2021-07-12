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
* Load / Save page (n-dimensional)
*
* @package Papaya
* @subpackage Core
*/
class papaya_topic extends base_topic_edit {

  /**
   * @param null $id
   */
  public function initialize($id = NULL) {
    if (NULL === $id) {
      $id = empty($_REQUEST['p_id']) ? 0 : (int)$_REQUEST['p_id'];
    }
    parent::initialize($id);

  }

  /**
   * @return string
   */
  public function getXML() {
    return '';
  }

  /**
  * Check publish period
  *
  * @param integer $topicId
  * @access public
  * @return boolean TRUE
  */
  function checkPublishPeriod($topicId) {
    return TRUE;
  }

  /**
  * Load output
  *
  * @param integer $topicId
  * @param integer $lngIdent
  * @param integer $versionTime optional, default value 0
  * @access public
  * @return boolean
  */
  function loadOutput($topicId, $lngIdent, $versionTime = 0) {
    if (is_integer($lngIdent) && $lngIdent > 0) {
      $lngId = (int)$lngIdent;
    } elseif (!($lngId = $this->languageIdentToId($lngIdent))) {
      $lngId = 0;
    }
    if (parent::loadOutput($topicId, $lngId)) {
      if ($versionTime > 0) {
        if ($this->loadVersion(0, $topicId, $versionTime)) {
          //load selected language
          $contentLoaded = $this->loadVersionTranslatedData(
            $this->savedVersion['version_id'], $lngId
          );
          //load topic main language
          if ((!$contentLoaded) && $lngId <>
              $this->savedVersion['topic_mainlanguage'] &&
              $this->savedVersion['topic_mainlanguage'] > 0) {
            $contentLoaded = $this->loadVersionTranslatedData(
              $this->savedVersion['version_id'],
              $this->savedVersion['topic_mainlanguage']
            );
          }
          //load default content language
          if ((!$contentLoaded) && defined('PAPAYA_CONTENT_LANGUAGE') &&
              PAPAYA_CONTENT_LANGUAGE > 0) {
            $contentLoaded = $this->loadVersionTranslatedData(
              $this->savedVersion['version_id'], PAPAYA_CONTENT_LANGUAGE
            );
          }
          if ($contentLoaded) {
            $this->topic = $this->savedVersion;
            return TRUE;
          } else {
            return FALSE;
          }
        } else {
          return FALSE;
        }
      }
      $contentLoaded = isset($this->topic['TRANSLATION']);
      //try to load topic default language
      if ((!$contentLoaded) && $lngId <> $this->topic['topic_mainlanguage'] &&
          $this->topic['topic_mainlanguage'] > 0) {
        $contentLoaded = $this->loadTranslatedData($topicId, $this->topic['topic_mainlanguage']);
      }
      //try to load system default language
      if ((!$contentLoaded) && defined('PAPAYA_CONTENT_LANGUAGE') &&
          PAPAYA_CONTENT_LANGUAGE > 0) {
        $contentLoaded = $this->loadTranslatedData($topicId, PAPAYA_CONTENT_LANGUAGE);
      }
      if ($contentLoaded) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Non public version should not be cached
  * @return integer
  */
  function getExpires() {
    return 0;
  }

  /**
   * Do not check file names in preview mode - no url fixation here
   *
   * @param string $currentFileName file name part of the current url
   * @param string $outputMode
   * @return FALSE|string
   */
  function checkURLFileName($currentFileName, $outputMode) {
    return FALSE;
  }
}
