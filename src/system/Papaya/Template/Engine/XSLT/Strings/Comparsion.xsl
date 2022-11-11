<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="func php">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_STRINGS_COMPARSION" select="'Strings/Comparsion'"/>

  <func:function name="fn:compare">
    <xsl:param name="a"/>
    <xsl:param name="b"/>
    <xsl:param name="collation" select="''"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_STRINGS_COMPARSION, 'compare', string($a), string($b), string($collation))"/>
  </func:function>

  <func:function name="fn:codepoint-equal">
    <xsl:param name="a"/>
    <xsl:param name="b"/>
    <xsl:variable name="collation">http://www.w3.org/2005/xpath-functions/collation/codepoint</xsl:variable>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_STRINGS_COMPARSION, 'compare', string($a), string($b), $collation)"/>
  </func:function>

  <func:function name="fn:collation-key">
    <xsl:param name="key"/>
    <xsl:param name="collation" select="''"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_STRINGS_COMPARSION, 'collationKey', string($key), string($collation))"/>
  </func:function>

  <func:function name="fn:contains-token">
    <xsl:param name="value"/>
    <xsl:param name="token"/>
    <xsl:param name="collation" select="''"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_STRINGS_COMPARSION, 'containsToken', string($value), string($token), string($collation))"/>
  </func:function>


  <func:function name="fn:default-collation">
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_STRINGS_COMPARSION, 'defaultCollation')"/>
  </func:function>

</xsl:stylesheet>
