<?php

interface PapayaTemplateSimpleAst {

  function accept(PapayaTemplateSimpleVisitor $visitor);
}
