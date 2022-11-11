<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="func php">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_CONTEXT" select="'Context'"/>

  <func:function name="fn:current-dateTime">
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_CONTEXT, 'currentDateTime')"/>
  </func:function>

  <func:function name="fn:current-date">
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_CONTEXT, 'currentDate')"/>
  </func:function>

  <func:function name="fn:current-time">
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_CONTEXT, 'currentTime')"/>
  </func:function>

  <func:function name="fn:implicit-timezone">
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_CONTEXT, 'implicitTimezone')"/>
  </func:function>

  <func:function name="fn:default-collation">
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_CONTEXT, 'defaultCollation')"/>
  </func:function>

  <func:function name="fn:default-language">
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_CONTEXT, 'defaultLanguage')"/>
  </func:function>

</xsl:stylesheet>
