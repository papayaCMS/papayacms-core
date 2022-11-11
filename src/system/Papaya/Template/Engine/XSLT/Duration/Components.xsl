<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="func php">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="MODULE_DURATION_COMPONENTS" select="'Duration/Components'"/>

  <func:function name="fn:years-from-duration">
    <xsl:param name="duration"/>
    <func:result select="php:function($CARICA_CALLBACK, $MODULE_DURATION_COMPONENTS, 'yearsFromDuration', string($duration))"/>
  </func:function>

  <func:function name="fn:months-from-duration">
    <xsl:param name="duration"/>
    <func:result select="php:function($CARICA_CALLBACK, $MODULE_DURATION_COMPONENTS, 'monthsFromDuration', string($duration))"/>
  </func:function>

  <func:function name="fn:days-from-duration">
    <xsl:param name="duration"/>
    <func:result select="php:function($CARICA_CALLBACK, $MODULE_DURATION_COMPONENTS, 'daysFromDuration', string($duration))"/>
  </func:function>

  <func:function name="fn:hours-from-duration">
    <xsl:param name="duration"/>
    <func:result select="php:function($CARICA_CALLBACK, $MODULE_DURATION_COMPONENTS, 'hoursFromDuration', string($duration))"/>
  </func:function>

  <func:function name="fn:minutes-from-duration">
    <xsl:param name="duration"/>
    <func:result select="php:function($CARICA_CALLBACK, $MODULE_DURATION_COMPONENTS, 'minutesFromDuration', string($duration))"/>
  </func:function>

  <func:function name="fn:seconds-from-duration">
    <xsl:param name="duration"/>
    <func:result select="php:function($CARICA_CALLBACK, $MODULE_DURATION_COMPONENTS, 'secondsFromDuration', string($duration))"/>
  </func:function>

</xsl:stylesheet>
