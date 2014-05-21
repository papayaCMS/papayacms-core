<?php

class PapayaDatabaseConditionRoot extends PapayaDatabaseConditionGroup {

  /**
   * @param string $method
   * @param array $arguments
   * @return PapayaDatabaseConditionElement
   * @throws LogicException
   */
  public function __call($method, $arguments) {
    if (count($this) > 0) {
      throw new LogicException(
        sprintf(
          '"%s" can only contain a single condition use logicalAnd() or logicalOr().',
          get_class($this)
        )
      );
    }
    return parent::__call($method, $arguments);
  }

  /**
   * @param bool $silent
   * @return string
   */
  public function getSql($silent = FALSE) {
    /** @var PapayaDatabaseConditionElement $condition  */
    foreach ($this as $condition) {
      return $condition->getSql($silent);
    }
    return '';
  }
}
