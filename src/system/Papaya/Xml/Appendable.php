<?php

interface PapayaXmlAppendable {

  /**
  * Create dom node structure of the given object and append it to the given xml
  * element node.
  *
  * @param PapayaXmlElement $parent
  */
  function appendTo(PapayaXmlElement $parent);
}