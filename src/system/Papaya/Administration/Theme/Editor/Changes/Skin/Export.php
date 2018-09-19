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
namespace Papaya\Administration\Theme\Editor\Changes\Skin;

use Papaya\Content;
use Papaya\Theme;
use Papaya\UI;
use Papaya\XML;

/**
 * Import theme skin values from an uploaded file
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Export
  extends UI\Control\Command {
  /**
   * @var Content\Theme\Skin
   */
  private $_themeSet;

  /**
   * @var Theme\Handler
   */
  private $_themeHandler;

  /**
   * @param Content\Theme\Skin $themeSet
   * @param Theme\Handler $themeHandler
   */
  public function __construct(Content\Theme\Skin $themeSet, Theme\Handler $themeHandler) {
    $this->_themeSet = $themeSet;
    $this->_themeHandler = $themeHandler;
  }

  /**
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    $this->_themeSet->load($this->parameters()->get('skin_id', 0));
    $themeName = $this->_themeSet['theme'];
    $response = $this->papaya()->response;
    $response->setStatus(200);
    $response->sendHeader(
      \sprintf(
        'Content-Disposition: attachment; filename="%s.xml"',
        \str_replace(
          ['\\', '"'],
          ['\\\\', '\\"'],
          $themeName.' '.$this->_themeSet['title']
        )
      )
    );
    $response->setContentType('application/octet-stream');
    $response->content(
      new \Papaya\Response\Content\Text(
        $this
          ->_themeSet
          ->getValuesXML(
            $this->_themeHandler->getDefinition($themeName)
          )
          ->saveXML()
      )
    );
    $response->send();
    $response->end();
  }
}
