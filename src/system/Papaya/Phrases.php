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

namespace Papaya;

use Papaya\BaseObject\DeclaredProperties;
use Papaya\BaseObject\Interfaces\Properties;
use Papaya\Phrases\Groups as PhraseGroups;
use Papaya\Phrases\Storage as PhraseStorage;
use Papaya\UI\Text\Translated as TranslatedText;
use Papaya\UI\Text\Translated\Collection as TranslatedTextsCollection;

/**
 * Phrase bases translations. If a phrase is not yet translated the phrase is returned and used.
 *
 * @package Papaya-Library
 * @subpackage Phrases
 *
 * @property PhraseGroups $groups
 * @property PhraseStorage $storage
 * @property Content\Language $language
 */
class Phrases implements Application\Access, Properties\Declared {
  use Application\Access\Aggregation;
  use DeclaredProperties;

  /**
   * @var PhraseGroups
   */
  private $_groups;

  /**
   * @var PhraseStorage
   */
  private $_storage;

  /**
   * @var Content\Language
   */
  private $_language;

  private $_defaultGroup;

  public function __construct(Phrases\Storage $storage, Content\Language $language) {
    $this->_storage = $storage;
    $this->setLanguage($language);
  }

  /**
   * @return PhraseStorage
   */
  public function getStorage() {
    return $this->_storage;
  }

  /**
   * @return Content\Language
   */
  public function getLanguage() {
    return $this->_language;
  }

  /**
   * @param Content\Language $language
   */
  public function setLanguage(Content\Language $language) {
    $this->_language = $language;
  }

  /**
   * A list of phrase groups. This is a little syntax sugar so that you don't have to
   * provide the group name in each phrase request, but can just store the group object.
   *
   * $group = $phrases->groups()->get('GROUP_NAME');
   * $phrase = $group->get('PHRASE');
   *
   * @param PhraseGroups $groups
   *
   * @return PhraseGroups
   */
  public function groups(Phrases\Groups $groups = NULL) {
    if (NULL !== $groups) {
      $this->_groups = $groups;
    } elseif (NULL === $this->_groups) {
      $this->_groups = new Phrases\Groups($this);
    }
    return $this->_groups;
  }

  /**
   * Getter/Setter for the default phrase group
   *
   * @param string $name
   *
   * @return string
   */
  public function defaultGroup($name = NULL) {
    if (NULL !== $name) {
      $this->_defaultGroup = $name;
    }
    if (NULL === $this->_defaultGroup) {
      $pathNamePattern = '(^(([^\?]*)/)?([^?]+)(\?.*)?)';
      /** @var URL $url */
      $url = $this->papaya()->request->getURL();
      $requestUri = $url->getPath();
      $result = '';
      if (\preg_match($pathNamePattern, $requestUri, $regs)) {
        $result = \basename($regs[3]);
      }
      if (empty($result) && isset($_SERVER['SCRIPT_FILENAME'])) {
        $result = \basename($_SERVER['SCRIPT_FILENAME']);
      }
      $this->_defaultGroup = empty($result) ? '#default' : $result;
    }
    return $this->_defaultGroup;
  }

  /**
   * @param string $groupName
   *
   * @return string
   */
  private function getGroupName($groupName = NULL) {
    if (NULL === $groupName) {
      $groupName = $this->defaultGroup();
    }
    return $groupName;
  }

  /**
   * Get a \Papaya\UI\Text\Translated instance for a phrase. This object encaspulates
   * a string that will be translated. If the $arguments are provided it the translated
   * string can contain placeholders usable with sprintf().
   *
   * If no group name is provided, the default group will be used.
   *
   * @param string $phrase
   * @param array $arguments
   * @param string|null $groupName
   *
   * @return TranslatedText
   */
  public function get($phrase, array $arguments = [], $groupName = NULL) {
    return $this->groups()->get($this->getGroupName($groupName))->get($phrase, $arguments);
  }

  /**
   * Get a \Papaya\UI\Text\Translated\Collection instance for a list of phrases.
   *
   * @param array|\Traversable $phrases
   * @param array $groupName
   *
   * @return TranslatedTextsCollection
   */
  public function getList($phrases, $groupName = NULL) {
    return $this->groups()->get($this->getGroupName($groupName))->getList($phrases);
  }

  /**
   * Fetch an actual translated string from the storage.
   *
   * @param string $phrase
   * @param string $groupName
   *
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
   * @param string $phrase
   * @param array $values
   * @param string $groupName
   *
   * @return string
   * @deprecated
   *
   */
  public function getTextFmt($phrase, array $values = [], $groupName = NULL) {
    $result = new UI\Text(
      $this->_storage->get($phrase, $this->getGroupName($groupName), $this->_language->id), $values
    );
    return (string)$result;
  }

  /**
   * Allows to declare dynamic properties with optional getter/setter methods. The read and write
   * options can be methods or properties. If no write option is provided the property is read only.
   *
   * [
   *   'propertyName' => ['read', 'write']
   * ]
   *
   * @return array
   */
  public static function getPropertyDeclaration() {
    return [
      'storage' => ['getStorage'],
      'groups' => ['groups', 'groups'],
      'language' => ['getLanguage', 'setLanguage']
    ];
  }
}
