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

namespace Papaya\Plugin\Filter\Content;

class Group
  extends \Papaya\Application\BaseObject
  implements \Papaya\Plugin\Filter\Content, \IteratorAggregate {

  private $_filters = array();

  /**
   * @var \Papaya\BaseObject\Parameters
   */
  private $_options;
  private $_page = NULL;

  public function __construct($page) {
    \Papaya\Utility\Constraints::assertObject($page);
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
      if ($filter instanceof \Papaya\Plugin\Filter\Content) {
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
      if ($filter instanceof \Papaya\Plugin\Filter\Content) {
        $result = $filter->applyTo($result);
      } elseif (method_exists($filter, 'applyFilterData')) {
        $result = \Papaya\Utility\Text\Xml::repairEntities($filter->applyFilterData($result));
      }
    }
    return $result;
  }

  public function appendTo(\Papaya\Xml\Element $parent) {
    foreach ($this as $filter) {
      if ($filter instanceof \Papaya\Plugin\Filter\Content) {
        $parent->append($filter);
      } elseif (method_exists($filter, 'getFilterData')) {
        $parent->appendXml(
          $filter->getFilterData(\Papaya\Utility\Arrays::ensure(iterator_to_array($this->_options)))
        );
      }
    }
  }
}
