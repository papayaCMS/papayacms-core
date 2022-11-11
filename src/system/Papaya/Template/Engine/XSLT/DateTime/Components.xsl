<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="func php">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_DATETIME_COMPONENTS" select="'DateTime/Components'"/>

  <func:function name="fn:dateTime">
    <xsl:param name="date"/>
    <xsl:param name="time"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'dateTime', string($date), string($time))"/>
  </func:function>

  <func:function name="fn:year-from-dateTime">
    <xsl:param name="dateTime"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'yearFromDateTime', string($dateTime))"/>
  </func:function>

  <func:function name="fn:month-from-dateTime">
    <xsl:param name="dateTime"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'monthFromDateTime', string($dateTime))"/>
  </func:function>

  <func:function name="fn:day-from-dateTime">
    <xsl:param name="dateTime"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'dayFromDateTime', string($dateTime))"/>
  </func:function>

  <func:function name="fn:hours-from-dateTime">
    <xsl:param name="dateTime"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'hoursFromDateTime', string($dateTime))"/>
  </func:function>

  <func:function name="fn:minutes-from-dateTime">
    <xsl:param name="dateTime"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'minutesFromDateTime', string($dateTime))"/>
  </func:function>

  <func:function name="fn:seconds-from-dateTime">
    <xsl:param name="dateTime"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'secondsFromDateTime', string($dateTime))"/>
  </func:function>

  <func:function name="fn:timezone-from-dateTime">
    <xsl:param name="dateTime"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'timezoneFromDateTime', string($dateTime))"/>
  </func:function>

  <func:function name="fn:year-from-date">
    <xsl:param name="date"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'yearFromDate', string($date))"/>
  </func:function>

  <func:function name="fn:month-from-date">
    <xsl:param name="date"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'monthFromDate', string($date))"/>
  </func:function>

  <func:function name="fn:day-from-date">
    <xsl:param name="date"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'dayFromDate', string($date))"/>
  </func:function>

  <func:function name="fn:hours-from-time">
    <xsl:param name="time"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'hoursFromTime', string($time))"/>
  </func:function>

  <func:function name="fn:minutes-from-time">
    <xsl:param name="time"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'minutesFromTime', string($time))"/>
  </func:function>

  <func:function name="fn:seconds-from-time">
    <xsl:param name="time"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'secondsFromTime', string($time))"/>
  </func:function>

  <func:function name="fn:timezone-from-time">
    <xsl:param name="time"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_DATETIME_COMPONENTS, 'timezoneFromTime', string($time))"/>
  </func:function>

</xsl:stylesheet>
