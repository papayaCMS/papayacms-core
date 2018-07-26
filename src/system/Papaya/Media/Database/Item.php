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
 * Representing a media database item
 *
 * @property string $mediaId
 * @property integer $versionId
 * @property string $name
 * @property string $mimeType
 *
 * @package Papaya-Library
 * @subpackage Media-Database
 */
class PapayaMediaDatabaseItem {

  /**
  * database access object
  * @var \Papaya\Database\Access
  */
  private $_databaseAccessObject = NULL;

  /**
  * Media storage service
  * @var \PapayaMediaStorageService
  */
  private $_storage = NULL;

  /**
  * Media item id
  * @var string
  */
  private $_mediaId = '';

  /**
  * Media item version id
  * @var integer
  */
  private $_versionId = 0;

  /**
  * Media item attributes
  * @var array
  */
  private $_attributes = array(
    'name' => '',
    'mimeType' => '',
  );

  /**
   * Constructor - define id and storage service
   *
   * @param \PapayaMediaStorageService $storage
   * @return \PapayaMediaDatabaseItem
   */
  public function __construct(\PapayaMediaStorageService $storage) {
    $this->_storage = $storage;
  }

  /**
   * Magic function, read dynamic properties
   *
   * @param string $name
   * @throws \BadMethodCallException
   * @return mixed
   */
  public function __get($name) {
    switch ($name) {
    case 'mediaId' :
      return $this->_mediaId;
    case 'versionId' :
      return $this->_versionId;
    }
    if (isset($this->_attributes[$name])) {
      return $this->_attributes[$name];
    } else {
      throw new \BadMethodCallException(
        sprintf(
          'Invalid attribute "%s:$%s."',
          __CLASS__,
          $name
        )
      );
    }
  }

  /**
   * Magic function, set dynamic properties
   *
   * @param string $name
   * @param mixed $value
   * @throws \BadMethodCallException
   * @return mixed
   */
  public function __set($name, $value) {
    switch ($name) {
    case 'name' :
      $this->_setName($value);
      break;
    case 'mediaId' :
      $this->_setMediaId($value);
      break;
    case 'versionId' :
      $this->_setVersionId($value);
      break;
    case 'mimeType' :
      $this->_setAttributeTrimString($name, $value);
      break;
    default :
      throw new \BadMethodCallException(
        sprintf(
          'Invalid attribute "%s:$%s."',
          __CLASS__,
          $name
        )
      );
    }
  }

  /**
  * Get database access object (implicit create)
  * @return \PapayaMediaDatabaseItemRecord
  */
  public function getDatabaseAccessObject() {
    if (!($this->_databaseAccessObject instanceof \PapayaMediaDatabaseItemRecord)) {
      $this->_databaseAccessObject = new \PapayaMediaDatabaseItemRecord();
    }
    return $this->_databaseAccessObject;
  }

  /**
  * Set database access object
  * @param \PapayaMediaDatabaseItemRecord $databaseAccessObject
  * @return void
  */
  public function setDatabaseAccessObject(\PapayaMediaDatabaseItemRecord $databaseAccessObject) {
    $this->_databaseAccessObject = $databaseAccessObject;
  }

  /**
   * Load media item data from database
   *
   * @param string $mediaId
   * @param integer $versionId
   * @throws \InvalidArgumentException
   * @return boolean
   */
  public function load($mediaId, $versionId = NULL) {
    $databaseAccess = $this->getDatabaseAccessObject();
    if ($databaseAccess->load($mediaId, $versionId)) {
      $this->_setMediaId($mediaId);
      $this->_setVersionId(
        empty($versionId) ? $databaseAccess['current_version_id'] : $versionId
      );
      $this->_setName($databaseAccess['file_name']);
      $this->mimeType = $databaseAccess['mimetype'];
    } else {
      throw new \InvalidArgumentException(
        sprintf(
          'Media item id "%s" version "%d" does not exist.',
          $mediaId,
          $versionId
        )
      );
    }
    return TRUE;
  }

  /**
  * Return url to media file if availiable
  * @return NULL|string
  */
  public function getUrl() {
    $identifier = $this->_mediaId.'v'.$this->versionId;
    $url = $this->_storage->getUrl('files', $identifier, $this->mimeType);
    return $url;
  }

  /**
   * Set media id
   *
   * @param string $value
   * @throws \BadMethodCallException
   */
  protected function _setMediaId($value) {
    if (preg_match('(^[a-fA-F\d]{32}$)', $value)) {
      $this->_mediaId = $value;
    } else {
      throw new \BadMethodCallException(
        sprintf(
          'Invalid attribute value for %s:$mediaId: "%s"',
          __CLASS__,
          $value
        )
      );
    }
  }

  /**
   * Set media id
   *
   * @param string $value
   * @throws \BadMethodCallException
   */
  protected function _setVersionId($value) {
    if ($value > 0) {
      $this->_versionId = (int)$value;
    } else {
      throw new \BadMethodCallException(
        sprintf(
          'Invalid attribute value for %s:$versionId: "%s"',
          __CLASS__,
          $value
        )
      );
    }
  }

  /**
  * Set name attribute
  * @param string $value
  * @return void
  */
  protected function _setName($value) {
    $this->_setAttributeTrimString('name', $value);
  }

  /**
   * Set an attribute to a string value that is neither empty
   * nor can be trimmed to the empty string
   *
   * @param string $attribute
   * @param string $value
   * @throws \BadMethodCallException
   */
  protected function _setAttributeTrimString($attribute, $value) {
    if (!empty($value) && trim($value != '')) {
      $this->_attributes[$attribute] = (string)$value;
    } else {
      throw new \BadMethodCallException(
        sprintf(
          'Invalid attribute value for %s:$%s: "%s"',
          __CLASS__,
          $attribute,
          $value
        )
      );
    }
  }

}


