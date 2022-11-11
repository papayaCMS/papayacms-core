<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="func php">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_NUMERIC_FORMATTING" select="'Numeric/Formatting'"/>

  <func:function name="fn:format-integer">
    <xsl:param name="input"/>
    <xsl:param name="picture" select="''"/>
    <xsl:param name="language" select="''"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_NUMERIC_FORMATTING, 'formatInteger', number($input), string($picture), string($language))"/>
  </func:function>

</xsl:stylesheet>
