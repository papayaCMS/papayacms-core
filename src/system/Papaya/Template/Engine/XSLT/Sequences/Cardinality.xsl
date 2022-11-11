<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  xmlns:array="http://www.w3.org/2005/xpath-functions/array"
  extension-element-prefixes="func array php">

  <xsl:import href="xpath-functions://MapsAndArrays/Arrays"/>

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_SEQUENCES_CARDINALITY" select="'Sequences/Cardinality'"/>

  <func:function name="fn:zero-or-one">
    <xsl:param name="input"/>
    <xsl:variable name="size" select="array:size($input)"/>
    <func:result select="$size = 0 or $size = 1"/>
  </func:function>

  <func:function name="fn:one-or-more">
    <xsl:param name="input"/>
    <xsl:variable name="size" select="array:size($input)"/>
    <func:result select="$size &gt; 0"/>
  </func:function>

  <func:function name="fn:exactly-one">
    <xsl:param name="input"/>
    <xsl:variable name="size" select="array:size($input)"/>
    <func:result select="$size = 1"/>
  </func:function>

</xsl:stylesheet>
