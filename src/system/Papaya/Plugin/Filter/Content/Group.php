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

class PapayaPluginFilterContentGroup
  extends \Papaya\Application\BaseObject
  implements \PapayaPluginFilterContent, \IteratorAggregate {

  private $_filters = array();

  /**
   * @var \Papaya\BaseObject\Parameters
   */
  private $_options;
  private $_page = NULL;

  public function __construct($page) {
    \PapayaUtilConstraints::assertObject($page);
    $this->_page = $page;
    $this->_options = new \Papaya\BaseObject\Parameters([]);
  }

  /**
   * @return \PapayaUiContentPage
   */
  public function getPage() {
    return $this->_page;
  }

  public function add($filterPlugin) {
    $this->_filters[spl_object_hash($filterPlugin)] = $filterPlugin;
  }

  public function getIterator() {
    return new \ArrayIterator($this->_filters);
  }

  public function prepare($content, \Papaya\BaseObject\Parameters $options = NULL) {
    $this->_options = isset($options) ? $options : new \Papaya\BaseObject\Parameters([]);
    foreach ($this as $filter) {
      if ($filter instanceof \PapayaPluginFilterContent) {
        $filter->prepare($content, $this->_options);
      } elseif (method_exists($filter, 'prepareFilterData')) {
        if (method_exists($filter, 'initialize')) {
          $bc = new \stdClass();
          $bc->parentObj = $this->getPage();
          $filter->initialize($bc);
        }
        $data = array('text' => $content);
        $filter->prepareFilterData($data, array('text'));
        if (method_exists($filter, 'loadFilterData')) {
          $filter->loadFilterData($data);
        }
      }
    }
  }

  public function applyTo($content) {
    $result = $content;
    foreach ($this as $filter) {
      if ($filter instanceof \PapayaPluginFilterContent) {
        $result = $filter->applyTo($result);
      } elseif (method_exists($filter, 'applyFilterData')) {
        $result = \PapayaUtilStringXml::repairEntities($filter->applyFilterData($result));
      }
    }
    return $result;
  }

  public function appendTo(\PapayaXmlElement $parent) {
    foreach ($this as $filter) {
      if ($filter instanceof \PapayaPluginFilterContent) {
        $parent->append($filter);
      } elseif (method_exists($filter, 'getFilterData')) {
        $parent->appendXml(
          $filter->getFilterData(\PapayaUtilArray::ensure(iterator_to_array($this->_options)))
        );
      }
    }
  }
}
