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

use Papaya\Content\Link\Types as LinkTypes;
use Papaya\Iterator\Filter;

/**
* Linktypes basic object
* @package Papaya
* @subpackage Core
*/
class base_linktypes extends base_db {

  /**
   * @var LinkTypes
   */
  private $_linkTypes;

  public function linkTypes(LinkTypes $linkTypes = NULL) {
    if (NULL !== $linkTypes) {
      $this->_linkTypes = $linkTypes;
    } elseif (NULL === $this->_linkTypes) {
      $this->_linkTypes = new LinkTypes();
      $this->_linkTypes->papaya($this->papaya());
      $this->_linkTypes->activateLazyLoad();
    }
    return $this->_linkTypes;
  }

  private function mapPropertiesToFields(array $values) {
    return [
      'linktype_id' => $values['id'],
      'linktype_name' => $values['name'],
      'linktype_is_visible' => $values['is_visible'],
      'linktype_class' => $values['class'],
      'linktype_target' => $values['target'],
      'linktype_is_popup' => $values['is_popup'],
      'linktype_popup_config' => $values['popup_options']
    ];
  }

  /**
  * loads link types
  *
  * @param boolean $minimal loads only id and name if set to TRUE, default FALSE
  * @param mixed $linkTypeIds single ID or array of linktype Ids
  * @return array $linkTypes fills this->linkTypes and returns it
  */
  public function loadLinkTypes($minimal = FALSE, $linkTypeIds = NULL) {
    $result = new \Papaya\Iterator\Callback(
      is_array($linkTypeIds)
        ? new Filter\Callback(
          $this->linkTypes(),
          static function(array $linkType) use ($linkTypeIds) { return in_array($linkType['id'], $linkTypeIds); }
        )
        : $this->linkTypes(),
      $minimal
        ? static function(array $linkType) { return $linkType['name']; }
        : static function(array $linkType) { return $this->mapPropertiesToFields($linkType); }
    );
    return iterator_to_array($result);
  }

  /**
   * loads linktypes with additional data
   *
   * @param int|array $linkTypeIds single ID or array of linktype Ids or NULL
   * @param bool $forceLoading
   * @return array $result linktypes with additional data (popup config).
   */
  public function getCompleteLinkTypes($linkTypeIds = NULL, $forceLoading = FALSE) {
    if ($forceLoading) {
      $this->linkTypes()->load();
    }
    return $this->loadLinkTypes($linkTypeIds);
  }

  /**
  * wrapper for base_linktypes::getCompleteLinkTypes()
  */
  public function getLinkType($linkTypeId, $forceLoading = FALSE) {
    return $this->getCompleteLinkTypes($linkTypeId, $forceLoading);
  }

  /**
  * fetches linktypes and their visibility
  *
  * @return array $result array(linktype_id => visibility)
  */
  public function getLinkTypesVisibility() {
    $result = new \Papaya\Iterator\Callback(
      $this->linkTypes(),
      static function(array $linkType) { return $linkType['is_visible']; }
    );
    return iterator_to_array($result);
  }

  /**
  * loads link types by visibility
  *
  * @param integer $visible which status of visibility to load, defaults to 1
  * @param boolean $minimal whether to load additional data, defaults to FALSE
  * @return array $linkTypes link types of requested visibility
  */
  function getLinkTypesByVisibility($visible = 1, $minimal = FALSE) {
    $result = new \Papaya\Iterator\Callback(
      new Filter\Callback(
        $this->linkTypes(),
        static function(array $linkType) use ($visible) { return $visible === (bool)$linkType['is_visible']; }
      ),
      $minimal
        ? static function(array $linkType) { return $linkType['name']; }
        : static function(array $linkType) { return $this->mapPropertiesToFields($linkType); }
    );
    return iterator_to_array($result);
  }

}

