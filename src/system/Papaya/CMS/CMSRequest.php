<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\CMS {

  use Papaya\CMS\Content\View\Mode as ViewMode;
  use Papaya\Request;
  use Papaya\Request\ContentLanguage;
  use Papaya\Request\ContentMode;
  use Papaya\Request\Parser as RequestParser;

  /**
   * Class CMSRequest
   * @package Papaya\CMS
   *
   * @property Content\Language $language
   * @property Content\View\Mode $mode
   * @property-read int $pageId
   * @property-read int $categoryId
   * @property-read int $languageId
   * @property-read string $languageIdentifier
   * @property-read int $modeId
   * @property-read bool $isPreview
   * @property bool $isAdministration
   */
  class CMSRequest extends Request {

    private $_language;

    private $_mode;

    /**
     * @var bool|null
     */
    private $_isAdministration;

    /**
     * @var \Papaya\Request\Parser[]
     */
    private $_parsers;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name) {
      $name = \Papaya\Utility\Text\Identifier::toCamelCase($name);
      switch ($name) {
      case 'language' :
      case 'pageId' :
      case 'categoryId' :
      case 'languageId' :
      case 'languageIdentifier' :
      case 'mode' :
      case 'modeId' :
      case 'isPreview' :
      case 'isAdministration' :
        return TRUE;
      }
      return parent::__isset($name);
    }

    /**
     * Allow to read request data as properties
     *
     * @param string $name
     *
     * @return mixed
     * @throws \LogicException
     *
     */
    public function __get($name) {
      $name = \Papaya\Utility\Text\Identifier::toCamelCase($name);
      switch ($name) {
      case 'language' :
        return $this->language();
      case 'pageId' :
        return $this->getParameter(
          'page_id',
          $this->papaya()->options->get(CMSConfiguration::PAGEID_DEFAULT, 0),
          NULL,
          self::SOURCE_PATH
        );
      case 'categoryId' :
        return $this->getParameter(
          'category_id',
          0,
          NULL,
          self::SOURCE_PATH
        );
      case 'languageId' :
        return (int)$this->language->id;
      case 'languageIdentifier' :
        return $this->language->identifier;
      case 'mode' :
        return $this->mode();
      case 'modeId' :
        return $this->mode()->id;
      case 'isPreview' :
        return $this->getParameter(
          'preview', FALSE, NULL, self::SOURCE_PATH
        );
      case 'isAdministration' :
        return NULL !== $this->_isAdministration ? $this->_isAdministration : FALSE;
      }
      return parent::__get($name);
    }

    /**
     * Allow to set request sub objects as properties, block other changes
     *
     * @param string $name
     * @param mixed $value
     *
     * @throws \LogicException
     */
    public function __set($name, $value) {
      $name = \Papaya\Utility\Text\Identifier::toCamelCase($name);
      switch ($name) {
      case 'language' :
        $this->language($value);
        return;
      case 'mode' :
        $this->mode($value);
        return;
      case 'isAdministration' :
        $this->_isAdministration = (bool)$value;
        return;
      }
      parent::__set($name, $value);
    }

    /**
     * Getter/Setter for the request language
     *
     * @param ContentLanguage|NULL $language
     * @return ContentLanguage
     */
    public function language(ContentLanguage $language = NULL): ContentLanguage {
      if (NULL !== $language) {
        $this->_language = $language;
      } elseif (NULL === $this->_language) {
        $this->_language = new Content\Language();
        $this->_language->papaya($this->papaya());
        if ($identifier = $this->getParameter('language', '', NULL, self::SOURCE_PATH)) {
          $this->_language->activateLazyLoad(
            ['identifier' => $identifier]
          );
        } elseif ($id = $this->papaya()->options->get(CMSConfiguration::CONTENT_LANGUAGE, 0)) {
          $this->_language->activateLazyLoad(
            ['id' => $id]
          );
        }
      }
      return $this->_language;
    }

    /**
     * Getter/Setter for view mode object
     *
     * @param ContentMode|NULL $mode
     * @return ContentMode
     */
    public function mode(ContentMode $mode = NULL): ContentMode {
      if (NULL !== $mode) {
        $this->_mode = $mode;
      } elseif (NULL === $this->_mode) {
        $this->_mode = new ViewMode();
        $this->_mode->papaya($this->papaya());
        $extension = $this->getParameter(
          'output_mode', 'html', NULL, \Papaya\Request::SOURCE_PATH
        );
        if ('xml' === $extension) {
          $this->_mode->assign(
            [
              'id' => -1,
              'extension' => 'xml',
              'type' => 'page',
              'charset' => 'utf-8',
              'content_type' => 'application/xml'
            ]
          );
        } else {
          $this->_mode->activateLazyLoad(
            ['extension' => $extension]
          );
        }
      }
      return $this->_mode;
    }

    /**
     * Initialize request parsers if not already done and
     * return them.
     */
    public function getParsers() {
      if (empty($this->_parsers)) {
        /** @var RequestParser[] $parsers */
        $parsers = [
          new Request\Parser\File(),
          new Request\Parser\System(),
          new Request\Parser\Page(),
          new Request\Parser\Thumbnail(),
          new Request\Parser\Media(),
          new Request\Parser\Image(),
          new Request\Parser\Wrapper(),
          new Request\Parser\Start()
        ];
        foreach ($parsers as $parser) {
          $parser->papaya($this->papaya());
        }
        $this->_parsers = \Papaya\Utility\Arrays::merge(
          parent::getParsers(), $parsers
        );
      }
      return $this->_parsers;
    }
  }
}


