<?php
/**
* Build teaser list xml from a list of pages.
*
* @copyright 2013 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui-Content
* @version $Id: Teasers.php 39721 2014-04-07 13:13:23Z weinert $
*/

/**
* Build teaser list xml from a list of pages.
*
* @package Papaya-Library
* @subpackage Ui-Content
*/
class PapayaUiContentTeasers extends PapayaUiControl {

  private $_pages = NULL;
  private $_reference = NULL;

  /**
  * thumbnail width
  *
  * @var integer
  */
  private $_width = 0;

  /**
  * thumbnail height
  *
  * @var integer
  */
  private $_height = 0;

  /**
  * thumbnail resize mode (abs, max, min, mincrop)
  *
  * @var integer
  */
  private $_resizeMode = 'max';

  /**
   * Create list, store pages and optional thumbnail configuration
   *
   * @param PapayaContentPages $pages
   * @param integer $width
   * @param integer $height
   * @param string $resizeMode
   */
  public function __construct(
    PapayaContentPages $pages, $width = 0, $height = 0, $resizeMode = 'mincrop'
  ) {
    $this->pages($pages);
    $this->_width = $width;
    $this->_height = $height;
    $this->_resizeMode = $resizeMode;
  }

  /**
   * Getter/Setter for the pages subobject
   *
   * @param PapayaContentPages $pages
   * @return PapayaContentPages
   */
  public function pages(PapayaContentPages $pages = NULL) {
    if (isset($pages)) {
      $this->_pages = $pages;
    }
    return $this->_pages;
  }

  /**
   * Getter/Setter for the template reference subobject used to generate links to the subpages
   *
   * @param PapayaUiReferencePage $reference
   * @return PapayaUiReferencePage
   */
  public function reference(PapayaUiReferencePage $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    } elseif (NULL == $this->_reference) {
      $this->_reference = new PapayaUiReferencePage();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }

  /**
   * Fetch teasers from plugins and append them to parent xml element. Append thumnbails
   * if configuration was provided.
   *
   * @see PapayaXmlAppendable::appendTo()
   */
  public function appendTo(PapayaXmlElement $parent) {
    $teasers = $parent->appendElement('teasers');
    foreach ($this->pages() as $record) {
      $this->appendTeaser($teasers, $record);
    }
    $this->appendThumbnails($teasers);
  }

  /**
   * Instanciate plugin and fetch the teaser from it.
   *
   * @param PapayaXmlElement $parent
   * @param array $record
   */
  private function appendTeaser(PapayaXmlElement $parent, $record) {
    if (!empty($record['module_guid'])) {
      $page = new PapayaUiContentPage(
        $record['id'], $record['language_id'], $this->pages()->isPublic()
      );
      $page->page()->assign($record);
      $page->translation()->assign($record);
      $plugin = $this->papaya()->plugins->get($record['module_guid'], $page, $record['content']);
      if ($plugin) {
        $reference = clone $this->reference();
        $reference->setPageId($record['id'], TRUE);
        $teaser = $parent->appendElement(
          'teaser',
          array(
            'page-id' => $record['id'],
            'plugin-guid' => $record['module_guid'],
            'plugin' => get_class($plugin),
            'href' => $reference->getRelative(),
            'published' => PapayaUtilDate::timestampToString(
              (isset($record['published']) && $record['published'] > 0)
                ? $record['published'] : $record['modified']
            ),
            'created' => PapayaUtilDate::timestampToString($record['created'])
          )
        );
        if ($plugin instanceof PapayaPluginQuoteable) {
          $plugin->appendQuoteTo($teaser);
        } elseif ($plugin instanceof base_content &&
                  method_exists($plugin, 'getParsedTeaser')) {
          $teaser->appendXml((string)$plugin->getParsedTeaser());
        }
        /** @var PapayaXmlDocument $document */
        $document = $teaser->ownerDocument;
        if ($document->xpath()->evaluate('count(node())', $teaser) == 0) {
          $teaser->parentNode->removeChild($teaser);
        }
      }
    }
  }

  /**
   * Append thumnbail xml for the generated teasers
   *
   * @param PapayaXmlElement $parent
   */
  private function appendThumbnails(PapayaXmlElement $parent) {
    if ($this->_width > 0 || $this->_height > 0) {
      $thumbnails = new PapayaUiContentTeaserImages(
        $parent, $this->_width, $this->_height, $this->_resizeMode
      );
      $thumbnails->papaya($this->papaya());
      $parent->append($thumbnails);
    }
  }
}
