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
namespace Papaya\CMS\Administration\Phrases\Storage {

  use Papaya\Application;
  use Papaya\CMS\Content;
  use Papaya\CMS\Administration\Phrases;

  class Database
    implements Application\Access, Phrases\Storage {
    use Application\Access\Aggregation;

    /**
     * @var array
     */
    private $_cache = [];

    /**
     * @var array
     */
    private $_loadedGroups = [];

    /**
     * @var array
     */
    private $_errors = [];

    /**
     * @var Content\Phrases
     */
    private $_phrases;

    /**
     * @var Content\Phrase\Messages
     */
    private $_messages;

    /**
     * @var bool $_disabledOnError access to database will be disabled on error - store the state
     */
    private $_disabledOnError = FALSE;

    /**
     * @param string $identifier
     * @param string $group
     * @param int $languageId
     * @return string
     */
    public function get($identifier, $group, $languageId) {
      $identifier = (string)$identifier;
      $key = \strtolower($identifier);
      $phrase = NULL;
      if (!isset($this->_loadedGroups[$languageId][$group])) {
        $this->loadGroup($group, $languageId);
      }
      if ($this->_disabledOnError) {
        return (string)$identifier;
      }
      if (!isset($this->_cache[$languageId][$key])) {
        $phrase = $this->loadPhrase($key, $languageId);
        if (!$phrase->isLoaded()) {
          $this->log(
            \sprintf('Phrase not found: %s.', $identifier),
            $identifier,
            NULL,
            $group,
            $languageId
          );
          $phrase['translation'] = $identifier;
        } elseif (empty($phrase['translation'])) {
          $this->log(
            \sprintf('Phrase translation not found: %s.', $identifier),
            $identifier,
            $phrase['id'],
            $group,
            $languageId
          );
          $phrase['translation'] = $identifier;
        }
        $this->_cache[$languageId][$key] = \iterator_to_array($phrase);
      }
      if (!empty($this->_cache[$languageId][$key])) {
        if (
          NULL !== $phrase &&
          !isset($this->_cache[$languageId][$key]['GROUPS'][$group]) &&
          $phrase->isLoaded()
        ) {
          $phrase->addToGroup($group);
        }
        return (string)$this->_cache[$languageId][$key]['translation'];
      }
      return (string)$identifier;
    }

    private function loadGroup($group, $languageId) {
      $group = \strtolower(\trim($group));
      if ($this->_disabledOnError) {
        return;
      }
      $phrases = $this->phrases();
      $loaded = $phrases->load(
        [
          'group' => $group,
          'language_id' => $languageId
        ]
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
      } else {
        $this->_disabledOnError = TRUE;
      }
    }

    /**
     * @param string $key
     * @param int $languageId
     * @return Content\Phrase
     */
    public function loadPhrase($key, $languageId) {
      return $this->phrases()->getItem(
        [
          'identifier' => $key,
          'language_id' => $languageId
        ]
      );
    }

    /**
     * @param Content\Phrases|null $phrases
     * @return Content\Phrases
     */
    public function phrases(Content\Phrases $phrases = NULL) {
      if (NULL !== $phrases) {
        $this->_phrases = $phrases;
      } elseif (NULL === $this->_phrases) {
        $this->_phrases = new Content\Phrases();
        $this->_phrases->papaya($this->papaya());
      }
      return $this->_phrases;
    }

    /**
     * @param Content\Phrase\Messages $messages
     *
     * @return Content\Phrase\Messages
     */
    public function messages(Content\Phrase\Messages $messages = NULL) {
      if (NULL !== $messages) {
        $this->_messages = $messages;
      } elseif (NULL === $this->_messages) {
        $this->_messages = new Content\Phrase\Messages();
        $this->_messages->papaya($this->papaya());
      }
      return $this->_messages;
    }

    /**
     * @param string $message
     * @param string $phrase
     * @param int $phraseId
     * @param string $group
     * @param int $languageId
     */
    public function log($message, $phrase, $phraseId, $group, $languageId) {
      $phrase = (string)$phrase;
      if (
        !empty($phrase) &&
        !isset($this->_errors[$phrase]) &&
        isset($this->papaya()->options) &&
        $this->papaya()->options->get('PAPAYA_DEBUG_LANGUAGE_PHRASES', FALSE)
      ) {
        $this->_errors[$phrase] = TRUE;
        $this->messages()->add(
          [
            'text' => (string)$message,
            'phrase' => (string)$phrase,
            'phrase_id' => (int)$phraseId,
            'group' => (string)$group,
            'language_id' => (int)$languageId,
            'created' => \time()
          ]
        );
      }
    }
  }
}
