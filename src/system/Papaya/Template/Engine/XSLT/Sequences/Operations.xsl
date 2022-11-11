<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="func php">

  <xsl:import href="xpath-functions://MapsAndArrays/Arrays"/>

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_SEQUENCES_OPERATIONS" select="'Sequences/Operations'"/>

  <func:function name="fn:empty">
    <xsl:param name="input"/>
    <func:result select="array:size($input) = 0"/>
  </func:function>

  <func:function name="fn:head">
    <xsl:param name="input"/>
    <func:result select="array:head($input)"/>
  </func:function>

  <func:function name="fn:tail">
    <xsl:param name="input"/>
    <func:result select="array:tail($input)"/>
  </func:function>

  <func:function name="fn:reverse">
    <xsl:param name="input"/>
    <func:result select="array:reverse($input)"/>
  </func:function>

  <func:function name="fn:remove">
    <xsl:param name="input"/>
    <xsl:param name="position"/>
    <func:result select="array:remove($input, $position)"/>
  </func:function>

  <func:function name="fn:subsequence">
    <xsl:param name="input"/>
    <xsl:param name="position"/>
    <xsl:param name="length" select="0"/>
    <func:result select="array:subarray($input, $position, $length)"/>
  </func:function>

  <func:function name="fn:insert-before">
    <xsl:param name="input"/>
    <xsl:param name="position"/>
    <xsl:param name="i1"/>
    <xsl:param name="i2" select="/.."/>
    <xsl:param name="i3" select="/.."/>
    <xsl:param name="i4" select="/.."/>
    <xsl:param name="i5" select="/.."/>
    <xsl:param name="i6" select="/.."/>
    <xsl:param name="i7" select="/.."/>
    <xsl:param name="i8" select="/.."/>
    <xsl:param name="i9" select="/.."/>
    <xsl:param name="i10" select="/.."/>
    <func:result select="array:insert-before($input, $position, $i1, $i2, $i3, $i4, $i5, $i6, $i7, $i8, $i9, $i10)"/>
  </func:function>

</xsl:stylesheet>
