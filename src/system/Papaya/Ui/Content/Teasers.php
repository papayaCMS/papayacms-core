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
* Build teaser list xml from a list of pages.
*
* @package Papaya-Library
* @subpackage Ui-Content
*/
class PapayaUiContentTeasers extends PapayaUiControl {

  private $_pages;
  private $_reference;

  /**
   * thumbnail width
   *
   * @var integer
   */
  private $_width;

  /**
   * thumbnail height
   *
   * @var integer
   */
  private $_height;

  /**
   * thumbnail resize mode (abs, max, min, mincrop)
   *
   * @var integer
   */
  private $_resizeMode;

  /**
   * @var \PapayaContentViews
   */
  private $_viewConfigurations;

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
    if (NULL !== $pages) {
      $this->_pages = $pages;
    }
    return $this->_pages;
  }

  /**
   * Getter/Setter for the view configurations
   *
   * @param \PapayaContentViewConfigurations $viewConfigurations
   * @return \PapayaContentViewConfigurations
   */
  public function viewConfigurations(\PapayaContentViewConfigurations $viewConfigurations = NULL) {
    if (NULL !== $viewConfigurations) {
      $this->_viewConfigurations = $viewConfigurations;
    } elseif (NULL === $this->_viewConfigurations) {
      $this->_viewConfigurations = new \PapayaContentViewConfigurations();
      $this->_viewConfigurations->papaya($this->papaya());
      $viewIds = iterator_to_array(
        new \PapayaIteratorArrayMapper($this->pages(), 'view_id'), FALSE
      );
      $this->_viewConfigurations->activateLazyLoad(
        [
          'id' => $viewIds,
          'mode_id' => $this->papaya()->request->modeId,
          'type' => \PapayaContentViewConfigurations::TYPE_OUTPUT
        ]
      );
    }
    return $this->_viewConfigurations;
  }

  /**
   * Getter/Setter for the template reference subobject used to generate links to the pages
   *
   * @param PapayaUiReferencePage $reference
   * @return PapayaUiReferencePage
   */
  public function reference(PapayaUiReferencePage $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new PapayaUiReferencePage();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }

  /**
   * Fetch teasers from plugins and append them to parent xml element. Append thumbnails
   * if configuration was provided.
   *
   * @see PapayaXmlAppendable::appendTo()
   * @param \PapayaXmlElement $parent
   */
  public function appendTo(PapayaXmlElement $parent) {
    $teasers = $parent->appendElement('teasers');
    foreach ($this->pages() as $record) {
      $this->appendTeaser($teasers, $record);
    }
    $this->appendThumbnails($teasers);
  }

  /**
   * Instantiate plugin and fetch the teaser from it.
   *
   * @param PapayaXmlElement $parent
   * @param array $pageData
   */
  private function appendTeaser(PapayaXmlElement $parent, array $pageData) {
    if (!empty($pageData['module_guid'])) {
      $page = new PapayaUiContentPage(
        $pageData['id'], $pageData['language_id'], $this->pages()->isPublic()
      );
      $page->papaya($this->papaya());
      $page->assign($pageData);
      if (NULL !== $pageData['viewmode_id']) {
        $viewData = $this->viewConfigurations()->offsetGet(
          [$pageData['view_id'], $pageData['viewmode_id'], \PapayaContentViewConfigurations::TYPE_OUTPUT]
        );
      } else {
        $viewData = ['id' => -1, 'mode_id' => -1, 'type' => \PapayaContentViewConfigurations::TYPE_OUTPUT];
      }
      $page->appendQuoteTo($parent, [], $viewData);
    }
  }

  /**
   * Append thumbnail xml for the generated teasers
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
