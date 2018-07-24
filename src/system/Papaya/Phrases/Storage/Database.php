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

use Papaya\Content\Phrase\Messages;

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

class PapayaPhrasesStorageDatabase
  extends \PapayaObject
  implements \PapayaPhrasesStorage {

  private $_cache = array();
  private $_loadedGroups = array();
  private $_errors = array();

  /**
   * @var PapayaContentPhrases
   */
  private $_phrases = NULL;

  /**
   * @var Messages
   */
  private $_messages;

  public function get($identifier, $group, $languageId) {
    (string)$identifier = $identifier;
    $key = strtolower($identifier);
    if (!isset($this->_loadedGroups[$languageId][$group])) {
      $this->loadGroup($group, $languageId);
    }
    if (!isset($this->_cache[$languageId][$key])) {
      $phrase = $this->loadPhrase($key, $languageId);
      if (!$phrase->isLoaded()) {
        $this->log(
          sprintf('Phrase not found: %s.', $identifier),
          $identifier,
          NULL,
          $group,
          $languageId
        );
        $phrase['translation'] = $identifier;
      } elseif (empty($phrase['translation'])) {
        $this->log(
          sprintf('Phrase translation not found: %s.', $identifier),
          $identifier,
          $phrase['id'],
          $group,
          $languageId
        );
        $phrase['translation'] = $identifier;
      }
      $this->_cache[$languageId][$key] = iterator_to_array($phrase);
    }
    if (!empty($this->_cache[$languageId][$key])) {
      if (!isset($this->_cache[$languageId][$key]['GROUPS'][$group])) {
        if (isset($phrase) && $phrase->isLoaded()) {
          $phrase->addToGroup($group);
        }
      }
      return (string)$this->_cache[$languageId][$key]['translation'];
    }
    return (string)$identifier;
  }

  private function loadGroup($group, $languageId) {
    $group = strtolower(trim($group));
    $phrases = $this->phrases();
    $loaded = $phrases->load(
      array(
        'group' => $group,
        'language_id' => $languageId
      )
    );
    if ($loaded) {
      $this->_loadedGroups[$languageId][$group] = TRUE;
      foreach ($phrases as $phraseId => $phrase) {
        $key = $phrase['identifier'];
        if (!isset($this->_cache[$key])) {
          $this->_cache[$languageId][$key] = $phrase;
        }
        $this->_cache[$languageId][$key]['GROUPS'][$group] = TRUE;
      }
    }
  }

  public function loadPhrase($key, $languageId) {
    return $this->phrases()->getItem(
      array(
        'identifier' => $key,
        'language_id' => $languageId
      )
    );
  }

  public function phrases(\PapayaContentPhrases $phrases = NULL) {
    if (isset($phrases)) {
      $this->_phrases = $phrases;
    } elseif (NULL === $this->_phrases) {
      $this->_phrases = new \PapayaContentPhrases();
      $this->_phrases->papaya($this->papaya());
    }
    return $this->_phrases;
  }

  /**
   * @param \Papaya\Content\Phrase\Messages $messages
   * @return \Papaya\Content\Phrase\Messages
   */
  public function messages(\Papaya\Content\Phrase\Messages $messages = NULL) {
    if (isset($messages)) {
      $this->_messages = $messages;
    } elseif (NULL === $this->_messages) {
      $this->_messages = new \Papaya\Content\Phrase\Messages();
      $this->_messages->papaya($this->papaya());
    }
    return $this->_messages;
  }

  public function log($message, $phrase, $phraseId, $group, $languageId) {
    $phrase = (string)$phrase;
    if (
      !empty($phrase) &&
      !isset($this->_errors[$phrase]) &&
      $this->papaya()->options['PAPAYA_DEBUG_LANGUAGE_PHRASES']
    ) {
      $this->_errors[$phrase] = TRUE;
      $this->messages()->add(
        array(
          'text' => (string)$message,
          'phrase' => (string)$phrase,
          'phrase_id' => (int)$phraseId,
          'group' => (string)$group,
          'language_id' => (int)$languageId,
          'created' => time()
        )
      );
    }
  }

}
