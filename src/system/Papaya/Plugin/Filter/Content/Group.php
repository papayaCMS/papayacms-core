<?php

class PapayaPluginFilterContentGroup
  extends PapayaObject
  implements PapayaPluginFilterContent, IteratorAggregate {

  private $_filters = array();
  private $_options = array();
  private $_page = NULL;

  public function __construct($page) {
    PapayaUtilConstraints::assertObject($page);
    $this->_page = $page;
  }

  /**
   * @return PapayaUiContentPage
   */
  public function getPage() {
    return $this->_page;
  }

  public function add($filterPlugin) {
    $this->_filters[spl_object_hash($filterPlugin)] = $filterPlugin;
  }

  public function getIterator() {
    return new ArrayIterator($this->_filters);
  }

  public function prepare($content, $options = array()) {
    $this->_options = $options;
    foreach ($this as $filter) {
      if ($filter instanceof PapayaPluginFilterContent) {
        $filter->prepare($content);
      } elseif (method_exists($filter, 'prepareFilterData')) {
        if (method_exists($filter, 'initialize')) {
          $bc = new stdClass();
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
      if ($filter instanceof PapayaPluginFilterContent) {
        $result = $filter->applyTo($result);
      } elseif (method_exists($filter, 'applyFilterData')) {
        $result = PapayaUtilStringXml::repairEntities($filter->applyFilterData($result));
      }
    }
    return $result;
  }

  public function appendTo(PapayaXmlElement $parent) {
    foreach ($this as $filter) {
      if ($filter instanceof PapayaPluginFilterContent) {
        $parent->append($filter);
      } elseif (method_exists($filter, 'getFilterData')) {
        $parent->appendXml(
          $filter->getFilterData(PapayaUtilArray::ensure($this->_options))
        );
      }
    }
  }
}
