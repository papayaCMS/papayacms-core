<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="func php">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_ERRORS" select="'Errors'"/>

  <func:function name="fn:error">
    <xsl:param name="uri">http://www.w3.org/2005/xqt-errors#FOER0000</xsl:param>
    <xsl:param name="description">
      <xsl:if test="$uri = 'http://www.w3.org/2005/xqt-errors#FOER0000'">
        <xsl:text>Unidentified error</xsl:text>
      </xsl:if>
    </xsl:param>
    <xsl:param name="context" select="/.."/>
    <func:result
      select="php:function($CARICA_CALLBACK, $CARICA_ERRORS, 'error', string($uri), string($description), $context)"/>
  </func:function>

</xsl:stylesheet>
