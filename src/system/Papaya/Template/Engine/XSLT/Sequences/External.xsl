<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="func php">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_SEQUENCES_EXTERNAL" select="'Sequences/External'"/>

  <func:function name="fn:doc">
    <xsl:param name="href"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_SEQUENCES_EXTERNAL, 'doc', string($href))"/>
  </func:function>

  <func:function name="fn:unparsed-text">
    <xsl:param name="href"/>
    <xsl:param name="encoding" select="''"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_SEQUENCES_EXTERNAL, 'unparsedText', string($href), string($encoding))"/>
  </func:function>

  <func:function name="fn:unparsed-text-lines">
    <xsl:param name="href"/>
    <xsl:param name="encoding" select="''"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_SEQUENCES_EXTERNAL, 'unparsedTextLines', string($href), string($encoding))"/>
  </func:function>

</xsl:stylesheet>
