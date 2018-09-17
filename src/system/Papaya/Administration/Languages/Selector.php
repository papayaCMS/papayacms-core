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

namespace Papaya\Administration\Languages;

use \Papaya\Content;
use \Papaya\UI;
use \Papaya\XML;

/**
 * Language switch administration control, allows to access the current content language and
 * append links for available content languages.
 *
 * The object will be available in the application registry, because the content language
 * informations are needed in different administration controls.
 *
 * @package Papaya-Library
 * @subpackage Administration
 *
 * @property-read int $id currently selected language id (internal autoincrement)
 * @property-read string $identifier identifier used in urls (en, de, ...)
 * @property-read string $code language code (en-US, de-DE, ...)
 * @property-read string $image current language image including path
 * @property-read string $title current language title
 */
class Selector extends UI\Control\Interactive {

  /**
   * Internal property for language list
   *
   * @var Content\Languages
   */
  private $_languages;

  /**
   * Internal property for current language
   *
   * @var Content\Language
   */
  private $_current;

  /**
   * Getter/Setter for a content languages record list.
   *
   * @param Content\Languages $languages
   * @return Content\Languages
   */
  public function languages(Content\Languages $languages = NULL) {
    if (NULL !== $languages) {
      $this->_languages = $languages;
    } elseif (NULL === $this->_languages) {
      $this->_languages = new Content\Languages();
    }
    return $this->_languages;
  }

  /**
   * Map some properties from the current language for easier access.
   *
   * @param string $name
   * @throws \LogicException
   * @return mixed|string
   */
  public function __get($name) {
    switch ($name) {
      case 'id' :
      case 'code' :
      case 'identifier' :
      case 'title' :
        return $this->getCurrent()->$name;
      case 'image' :
        $image = $this->getCurrent()->image;
        return empty($image) ? '' : './pics/language/'.$image;
    }
    throw new \LogicException(
      \sprintf(
        'Can not find property %s::$%s', \get_class($this), $name
      )
    );
  }

  /**
   * Get the currently selected content language. If no language is found, a default language
   * object is initialized.
   *
   * @return Content\Language
   */
  public function getCurrent() {
    $this->prepare();
    return $this->_current;
  }

  /**
   * Appends a <links> element with references for the different content languages.
   *
   * @param XML\Element $parent
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $current = $this->getCurrent();
    $links = $parent->appendElement(
      'links',
      array('title' => new UI\Text\Translated('Content Language'))
    );
    foreach ($this->languages() as $id => $language) {
      $reference = new UI\Reference();
      $reference->papaya($this->papaya());
      $reference->setParameters(array('language_select' => $id), 'lngsel');
      $link = $links->appendElement(
        'link',
        array(
          'href' => $reference->getRelative(),
          'title' => $language['title'],
          'image' => $language['image']
        )
      );
      if ((string)$current->id === (string)$id) {
        $link->setAttribute('selected', 'selected');
      }
    }
    return $links;
  }

  /**
   * Load content languages and determine current language. The method looks for
   * a request parameter, a session value, the user interface language, the default content language
   * and the default interface language.
   *
   * If none of these are found a default language object containing data for English ist created.
   */
  private function prepare() {
    $application = $this->papaya();
    $languages = NULL;
    if (NULL === $this->_current) {
      $languages = $this->languages();
      $languages->loadByUsage(Content\Languages::FILTER_IS_CONTENT);
      if ($id = $this->parameters()->get('lngsel[language_select]')) {
        $this->_current = $languages->getLanguage($id);
      } elseif ($id = $application->session->values()->get(array($this, 'CONTENT_LANGUAGE'))) {
        $this->_current = $languages->getLanguage($id);
      } elseif (isset($application->administrationUser->options['PAPAYA_CONTENT_LANGUAGE'])) {
        $this->_current = $languages->getLanguage(
          $application->administrationUser->options['PAPAYA_CONTENT_LANGUAGE']
        );
      } elseif ($id = $application->options->get('PAPAYA_CONTENT_LANGUAGE')) {
        $this->_current = $languages->getLanguage($id);
      } elseif ($code = $application->options->get('PAPAYA_UI_LANGUAGE')) {
        $this->_current = $languages->getLanguageByCode($code);
      }
    }
    if (NULL === $this->_current && NULL !== $languages) {
      if ($language = $languages->getDefault()) {
        $this->_current = $language;
      } else {
        $this->_current = $this->getDefault();
      }
    } else {
      $application->session->values()->set(array($this, 'CONTENT_LANGUAGE'), $this->_current->id);
    }
  }

  /**
   * Create and return a language object with default (English) data.
   *
   * @return Content\Language
   */
  private function getDefault() {
    $result = new Content\Language();
    $result->assign(
      array(
        'id' => 1,
        'identifier' => 'en',
        'code' => 'en-US',
        'title' => 'English',
        'image' => 'us.gif',
        'is_content' => 1,
        'is_interface' => 1
      )
    );
    return $result;
  }
}
