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
namespace Papaya\CMS\Plugin\Filter\Content;

use Papaya\Application;
use Papaya\BaseObject;
use Papaya\CMS\Plugin;
use Papaya\Utility;

class Group
  implements Application\Access, Plugin\Filter\Content, \IteratorAggregate {
  use Application\Access\Aggregation;

  /**
   * @var array
   */
  private $_filters = [];

  /**
   * @var BaseObject\Parameters
   */
  private $_options;

  /**
   * @var
   */
  private $_page;

  public function __construct($page) {
    Utility\Constraints::assertObject($page);
    $this->_page = $page;
    $this->_options = new BaseObject\Parameters([]);
  }

  /**
   * @return \Papaya\CMS\Output\Page
   */
  public function getPage() {
    return $this->_page;
  }

  /**
   * @param Plugin\Filter\Content|\base_plugin $filterPlugin
   */
  public function add($filterPlugin) {
    $this->_filters[\spl_object_hash($filterPlugin)] = $filterPlugin;
  }

  /**
   * @return \Traversable
   */
  public function getIterator(): \Traversable {
    return new \ArrayIterator($this->_filters);
  }

  /**
   * @param string $content
   * @param BaseObject\Parameters|null $options
   */
  public function prepare($content, BaseObject\Parameters $options = NULL) {
    $this->_options = NULL !== $options ? $options : new BaseObject\Parameters([]);
    foreach ($this as $filter) {
      if ($filter instanceof Plugin\Filter\Content) {
        $filter->prepare($content, $this->_options);
      } elseif (\method_exists($filter, 'prepareFilterData')) {
        if (\method_exists($filter, 'initialize')) {
          $bc = new \stdClass();
          $bc->parentObj = $this->getPage();
          $filter->initialize($bc);
        }
        $data = ['text' => $content];
        $filter->prepareFilterData($data, ['text']);
        if (\method_exists($filter, 'loadFilterData')) {
          $filter->loadFilterData($data);
        }
      }
    }
  }

  /**
   * @param string $content
   * @return string
   */
  public function applyTo($content) {
    $result = $content;
    foreach ($this as $filter) {
      if ($filter instanceof Plugin\Filter\Content) {
        $result = $filter->applyTo($result);
      } elseif (\method_exists($filter, 'applyFilterData')) {
        $result = Utility\Text\XML::repairEntities($filter->applyFilterData($result));
      }
    }
    return $result;
  }

  /**
   * @param \Papaya\XML\Element $parent
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    foreach ($this as $filter) {
      if ($filter instanceof Plugin\Filter\Content) {
        $parent->append($filter);
      } elseif (\method_exists($filter, 'getFilterData')) {
        $parent->appendXML(
          $filter->getFilterData(Utility\Arrays::ensure(\iterator_to_array($this->_options)))
        );
      }
    }
  }
}
