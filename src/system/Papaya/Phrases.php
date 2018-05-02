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
 * Phrase bases translations. If a phrase is not yet translated the phrase is returned and used.
 *
 * @package Papaya-Library
 * @subpackage Phrases
 *
 * @property PapayaPhrasesGroups groups
 *
 */
class PapayaPhrases extends PapayaObject {

  /**
   * @var PapayaPhrasesGroups
   */
  private $_groups;

  /**
   * @var PapayaPhrasesStorage
   */
  private $_storage;

  /**
   * @var PapayaContentLanguage
   */
  private $_language;

  private $_defaultGroup;

  public function __construct(PapayaPhrasesStorage $storage, PapayaContentLanguage $language) {
    $this->_storage = $storage;
    $this->_language = $language;
  }

  /**
   * @return PapayaPhrasesStorage
   */
  public function getStorage() {
    return $this->_storage;
  }

  /**
   * @return PapayaContentLanguage
   */
  public function getLanguage() {
    return $this->_language;
  }

  /**
   * @param PapayaContentLanguage $language
   */
  public function setLanguage(PapayaContentLanguage $language) {
    $this->_language = $language;
  }

  /**
   * @param string $name
   * @return mixed
   */
  public function __get($name) {
    switch($name) {
    case 'groups' :
      return $this->groups();
    }
    return $this->$name;
  }

  /**
   * @param string $name
   * @return bool
   */
  public function __isset($name) {
    switch($name) {
      case 'groups' :
        return TRUE;
    }
    return FALSE;
  }

  /**
   * @param string $name
   * @param mixed $value
   */
  public function __set($name, $value) {
    switch($name) {
    case 'groups' :
      $this->groups($value);
    }
    $this->$name = $value;
  }

  /**
   * A list of phrase groups. This is a little syntax sugar so that you don't have to
   * provide the group name in each phrase request, but can just store the group object.
   *
   * $group = $phrases->groups()->get('GROUP_NAME');
   * $phrase = $group->get('PHRASE');
   *
   * @param PapayaPhrasesGroups $groups
   * @return PapayaPhrasesGroups
   */
  public function groups(PapayaPhrasesGroups $groups = NULL) {
    if (NULL !== $groups) {
      $this->_groups = $groups;
    } elseif (NULL === $this->_groups) {
      $this->_groups = new PapayaPhrasesGroups($this);
    }
    return $this->_groups;
  }

  /**
   * Getter/Setter for the default phrase group
   *
   * @param string $name
   * @return string
   */
  public function defaultGroup($name = NULL) {
    if (NULL !== $name) {
      $this->_defaultGroup = $name;
    }
    if (NULL === $this->_defaultGroup) {
      $fileNamePattern = '#^(([^\?]*)/)?([^?]+)(\.\d+)(\.(php|html))(\?.*)?#i';
      $pathNamePattern = '#^(([^\?]*)/)?([^?]+)(\?.*)?#';
      /** @var PapayaUrl $url */
      $url = $this->papaya()->request->getUrl();
      $requestUri = $url->getPath();
      $result = '';
      if (preg_match($fileNamePattern, $requestUri, $regs)) {
        $result = basename($regs[3].$regs[5]);
      } elseif (preg_match($pathNamePattern, $requestUri, $regs)) {
        $result = basename($regs[3]);
      } elseif (isset($_SERVER['SCRIPT_FILENAME'])) {
        $result = basename($_SERVER['SCRIPT_FILENAME']);
      }
      $this->_defaultGroup = empty($result) ? '#default' : $result;
    }
    return $this->_defaultGroup;
  }

  /**
   * @param string $groupName
   * @return string
   */
  private function getGroupName($groupName = NULL) {
    if (NULL === $groupName) {
      $groupName = $this->defaultGroup();
    }
    return $groupName;
  }

  /**
   * Get a PapayaUiStringTranslated instance for a phrase. This object encaspulates
   * a string that will be translated. If the $arguments are provided it the translated
   * string can contain placeholders usable with sprintf().
   *
   * If no group name is provided, the default group will be used.
   *
   * @param string $phrase
   * @param array $arguments
   * @param string|NULL $groupName
   * @return PapayaUiStringTranslated
   */
  public function get($phrase, array $arguments = array(), $groupName = NULL) {
    return $this->groups()->get($this->getGroupName($groupName))->get($phrase, $arguments);
  }

  /**
   * Get a PapayaUiStringTranslatedList instance for a list of phrases.
   *
   * @param array|Traversable $phrases
   * @param array $groupName
   * @return PapayaUiStringTranslatedList
   */
  public function getList($phrases, $groupName = NULL) {
    return $this->groups()->get($this->getGroupName($groupName))->getList($phrases);
  }

  /**
   * Fetch an actual translated string from the storage.
   *
   * @param string $phrase
   * @param string $groupName
   * @return string
   */
  public function getText($phrase, $groupName = NULL) {
    return $this->_storage->get($phrase, $this->getGroupName($groupName), $this->_language->id);
  }

  /**
   * Fetch an actual translated string from the storage.
   *
   * This method is only implemented for backwards compatibility
   *
   * @deprecated
   * @param string $phrase
   * @param array $values
   * @param string $groupName
   * @return string
   */
  public function getTextFmt($phrase, array $values = array(), $groupName = NULL) {
    $result = new PapayaUiString(
      $this->_storage->get($phrase, $this->getGroupName($groupName), $this->_language->id), $values
    );
    return (string)$result;
  }
}
