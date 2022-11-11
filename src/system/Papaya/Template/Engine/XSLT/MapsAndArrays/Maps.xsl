<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:exsl="http://exslt.org/common"
  xmlns:array="http://www.w3.org/2005/xpath-functions/array"
  xmlns:map="http://www.w3.org/2005/xpath-functions/map"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="exsl func array map php">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_MAPS_AND_ARRAYS_MAPS" select="'MapsAndArrays/Maps'"/>

  <func:function name="map:map">
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
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_MAPS_AND_ARRAYS_MAPS, 'createMap', $i1, $i2, $i3, $i4, $i5, $i6, $i7, $i8, $i9, $i10)"/>
  </func:function>

  <func:function name="map:entry">
    <xsl:param name="key"/>
    <xsl:param name="item"/>
    <xsl:variable name="result">
      <xsl:variable name="type" select="exsl:object-type($item)"/>
      <xsl:choose xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:when test="$type = 'RTF'">
          <xsl:copy-of select="map:entry($key, exsl:node-set($value)/*[1])"/>
        </xsl:when>
        <xsl:when test="$type = 'number'">
          <number key="{$key}">
            <xsl:value-of select="$item"/>
          </number>
        </xsl:when>
        <xsl:when test="$type = 'boolean'">
          <boolean key="{$key}">
            <xsl:value-of select="$item"/>
          </boolean>
        </xsl:when>
        <xsl:when test="$type = 'null'">
          <null key="{$key}"/>
        </xsl:when>
        <xsl:when test="$type = 'node-set' and contains('array map string number boolean null', local-name($item))">
          <xsl:element name="{local-name()}" namespace="http://www.w3.org/2005/xpath-functions">
            <xsl:attribute name="key"><xsl:value-of select="$key"/></xsl:attribute>
            <xsl:copy-of select="./node()"/>
          </xsl:element>
        </xsl:when>
        <xsl:otherwise>
          <string key="{$key}">
            <xsl:value-of select="$item"/>
          </string>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:*"/>
  </func:function>

  <func:function name="map:merge">
    <xsl:param name="maps"/>
    <xsl:param name="options" select="/.."/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_MAPS_AND_ARRAYS_MAPS, 'merge', $maps, $options)"/>
  </func:function>

  <func:function name="map:map-from-nodeset">
    <xsl:param name="input"/>
    <!-- if first child has no ancestor element (document, fragment, ...) use first child -->
    <func:result select="($input|$input/*[count(./ancestor::*) = 0])[local-name() = 'map'][1]"/>
  </func:function>

  <func:function name="map:size">
    <xsl:param name="input"/>
    <func:result select="count(map:map-from-nodeset($input)/*)"/>
  </func:function>

  <func:function name="map:keys">
    <xsl:param name="input"/>
    <xsl:variable name="map" select="map:map-from-nodeset($input)"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$map/*[@key]/@key">
          <string><xsl:value-of select="."/></string>
        </xsl:for-each>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="map:contains">
    <xsl:param name="input"/>
    <xsl:param name="key"/>
    <xsl:variable name="map" select="map:map-from-nodeset($input)"/>
    <func:result select="count($map/*[@key = string($key)]) &gt; 0"/>
  </func:function>

  <func:function name="map:get">
    <xsl:param name="input"/>
    <xsl:param name="key"/>
    <xsl:variable name="map" select="map:map-from-nodeset($input)"/>
    <func:result select="$map/*[@key = string($key)]"/>
  </func:function>

  <func:function name="map:find">
    <xsl:param name="input"/>
    <xsl:param name="key"/>
    <xsl:variable name="map" select="map:map-from-nodeset($input)"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$map//*[@key = string($key)]">
          <xsl:element name="{local-name()}" namespace="http://www.w3.org/2005/xpath-functions">
            <xsl:copy-of select="./node()"/>
          </xsl:element>
        </xsl:for-each>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="map:put">
    <xsl:param name="input"/>
    <xsl:param name="key"/>
    <xsl:param name="value"/>
    <xsl:variable name="map" select="map:map-from-nodeset($input)"/>
    <xsl:variable name="result">
      <map xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="map:remove($map, $key)/*">
          <xsl:copy-of select="."/>
        </xsl:for-each>
        <xsl:copy-of select="map:entry($key, $value)"/>
      </map>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:map"/>
  </func:function>

  <func:function name="map:remove">
    <xsl:param name="input"/>
    <xsl:param name="key"/>
    <xsl:variable name="map" select="map:map-from-nodeset($input)"/>
    <xsl:variable name="result">
      <map xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$map/*">
          <xsl:if test="string(@key) != string($key)">
            <xsl:copy-of select="."/>
          </xsl:if>
        </xsl:for-each>
      </map>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:map"/>
  </func:function>

</xsl:stylesheet>
