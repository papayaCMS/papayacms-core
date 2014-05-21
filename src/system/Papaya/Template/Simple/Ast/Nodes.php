<?php

class PapayaTemplateSimpleAstNodes
  extends PapayaObjectList
  implements PapayaTemplateSimpleAst {

  public function __construct(array $nodes = array()) {
    parent::__construct('PapayaTemplateSimpleAstNode');
    foreach ($nodes as $node) {
      $this[] = $node;
    }
  }

  /**
   * Tell the nodes about the visitor.
   *
   * @param PapayaTemplateSimpleVisitor $visitor
   */
  public function accept(PapayaTemplateSimpleVisitor $visitor) {
    /** @var PapayaTemplateSimpleAst $node */
    foreach ($this as $node) {
      $node->accept($visitor);
    }
  }
}