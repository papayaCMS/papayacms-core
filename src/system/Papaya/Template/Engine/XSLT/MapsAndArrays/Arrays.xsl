<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:exsl="http://exslt.org/common"
  xmlns:array="http://www.w3.org/2005/xpath-functions/array"
  xmlns:php="http://php.net/xsl"
  xmlns:fn="http://www.w3.org/2005/xpath-functions"
  extension-element-prefixes="exsl func array php">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_MAPS_AND_ARRAYS_ARRAYS" select="'MapsAndArrays/Arrays'"/>

  <func:function name="array:array-from-nodeset">
    <xsl:param name="input"/>
    <!-- if first child has no ancestor element (document, fragment, ...) use first child -->
    <func:result select="($input|$input/*[count(./ancestor::*) = 0])[local-name() = 'array'][1]"/>
  </func:function>

  <func:function name="array:array">
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
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:call-template name="carica-output-item-as-xdm-array-element">
          <xsl:with-param name="item" select="$i1"/>
        </xsl:call-template>
        <xsl:if test="exsl:object-type($i2) != 'null'">
          <xsl:call-template name="carica-output-item-as-xdm-array-element">
            <xsl:with-param name="item" select="$i2"/>
          </xsl:call-template>
          <xsl:if test="exsl:object-type($i3) != 'null'">
            <xsl:call-template name="carica-output-item-as-xdm-array-element">
              <xsl:with-param name="item" select="$i3"/>
            </xsl:call-template>
            <xsl:if test="exsl:object-type($i4) != 'null'">
              <xsl:call-template name="carica-output-item-as-xdm-array-element">
                <xsl:with-param name="item" select="$i4"/>
              </xsl:call-template>
              <xsl:if test="exsl:object-type($i5) != 'null'">
                <xsl:call-template name="carica-output-item-as-xdm-array-element">
                  <xsl:with-param name="item" select="$i5"/>
                </xsl:call-template>
                <xsl:if test="exsl:object-type($i6) != 'null'">
                  <xsl:call-template name="carica-output-item-as-xdm-array-element">
                    <xsl:with-param name="item" select="$i6"/>
                  </xsl:call-template>
                  <xsl:if test="exsl:object-type($i7) != 'null'">
                    <xsl:call-template name="carica-output-item-as-xdm-array-element">
                      <xsl:with-param name="item" select="$i7"/>
                    </xsl:call-template>
                    <xsl:if test="exsl:object-type($i8) != 'null'">
                      <xsl:call-template name="carica-output-item-as-xdm-array-element">
                        <xsl:with-param name="item" select="$i8"/>
                      </xsl:call-template>
                      <xsl:if test="exsl:object-type($i9) != 'null'">
                        <xsl:call-template name="carica-output-item-as-xdm-array-element">
                          <xsl:with-param name="item" select="$i9"/>
                        </xsl:call-template>
                        <xsl:if test="exsl:object-type($i10) != 'null'">
                          <xsl:call-template name="carica-output-item-as-xdm-array-element">
                            <xsl:with-param name="item" select="$i10"/>
                          </xsl:call-template>
                        </xsl:if>
                      </xsl:if>
                    </xsl:if>
                  </xsl:if>
                </xsl:if>
              </xsl:if>
            </xsl:if>
          </xsl:if>
        </xsl:if>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="array:size">
    <xsl:param name="input"/>
    <func:result select="count(array:array-from-nodeset($input)/*)"/>
  </func:function>

  <func:function name="array:get">
    <xsl:param name="input"/>
    <xsl:param name="position"/>
    <xsl:variable name="array" select="array:array-from-nodeset($input)"/>
    <func:result select="$array/*[$position]"/>
  </func:function>

  <func:function name="array:put">
    <xsl:param name="input"/>
    <xsl:param name="position"/>
    <xsl:param name="member"/>
    <xsl:variable name="array" select="array:array-from-nodeset($input)"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$array/*">
          <xsl:choose>
            <xsl:when test="position() = $position">
              <xsl:call-template name="carica-output-item-as-xdm-array-element">
                <xsl:with-param name="item" select="$member"/>
              </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
              <xsl:copy-of select="."/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="array:append">
    <xsl:param name="input"/>
    <xsl:param name="member"/>
    <xsl:variable name="array" select="array:array-from-nodeset($input)"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:copy-of select="$array/*"/>
        <xsl:call-template name="carica-output-item-as-xdm-array-element">
          <xsl:with-param name="item" select="$member"/>
        </xsl:call-template>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="array:subarray">
    <xsl:param name="input"/>
    <xsl:param name="position"/>
    <xsl:param name="length" select="0"/>
    <xsl:variable name="array" select="array:array-from-nodeset($input)"/>
    <xsl:variable name="end" select="count($array/*) - $position + $length"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$array/*">
          <xsl:if test="position() &gt;= $position and ($length &lt; 1 or position() &lt;= $end)">
            <xsl:copy-of select="."/>
          </xsl:if>
        </xsl:for-each>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="array:remove">
    <xsl:param name="input"/>
    <xsl:param name="position"/>
    <xsl:variable name="array" select="array:array-from-nodeset($input)"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$array/*">
          <xsl:if test="position() != $position">
            <xsl:copy-of select="."/>
          </xsl:if>
        </xsl:for-each>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="array:insert-before">
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
    <xsl:variable name="array" select="array:array-from-nodeset($input)"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$array/*">
          <xsl:if test="position() = $position">
            <xsl:call-template name="carica-output-item-as-xdm-array-element">
              <xsl:with-param name="item" select="$i1"/>
            </xsl:call-template>
            <xsl:if test="exsl:object-type($i2) != 'null'">
              <xsl:call-template name="carica-output-item-as-xdm-array-element">
                <xsl:with-param name="item" select="$i2"/>
              </xsl:call-template>
              <xsl:if test="exsl:object-type($i3) != 'null'">
                <xsl:call-template name="carica-output-item-as-xdm-array-element">
                  <xsl:with-param name="item" select="$i3"/>
                </xsl:call-template>
                <xsl:if test="exsl:object-type($i4) != 'null'">
                  <xsl:call-template name="carica-output-item-as-xdm-array-element">
                    <xsl:with-param name="item" select="$i4"/>
                  </xsl:call-template>
                  <xsl:if test="exsl:object-type($i5) != 'null'">
                    <xsl:call-template name="carica-output-item-as-xdm-array-element">
                      <xsl:with-param name="item" select="$i5"/>
                    </xsl:call-template>
                    <xsl:if test="exsl:object-type($i6) != 'null'">
                      <xsl:call-template name="carica-output-item-as-xdm-array-element">
                        <xsl:with-param name="item" select="$i6"/>
                      </xsl:call-template>
                      <xsl:if test="exsl:object-type($i7) != 'null'">
                        <xsl:call-template name="carica-output-item-as-xdm-array-element">
                          <xsl:with-param name="item" select="$i7"/>
                        </xsl:call-template>
                        <xsl:if test="exsl:object-type($i8) != 'null'">
                          <xsl:call-template name="carica-output-item-as-xdm-array-element">
                            <xsl:with-param name="item" select="$i8"/>
                          </xsl:call-template>
                          <xsl:if test="exsl:object-type($i9) != 'null'">
                            <xsl:call-template name="carica-output-item-as-xdm-array-element">
                              <xsl:with-param name="item" select="$i9"/>
                            </xsl:call-template>
                            <xsl:if test="exsl:object-type($i10) != 'null'">
                              <xsl:call-template name="carica-output-item-as-xdm-array-element">
                                <xsl:with-param name="item" select="$i10"/>
                              </xsl:call-template>
                            </xsl:if>
                          </xsl:if>
                        </xsl:if>
                      </xsl:if>
                    </xsl:if>
                  </xsl:if>
                </xsl:if>
              </xsl:if>
            </xsl:if>
          </xsl:if>
          <xsl:copy-of select="."/>
        </xsl:for-each>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="array:head">
    <xsl:param name="input"/>
    <func:result select="array:get($input, 1)"/>
  </func:function>

  <func:function name="array:tail">
    <xsl:param name="input"/>
    <func:result select="array:subarray($input, 2)"/>
  </func:function>

  <func:function name="array:reverse">
    <xsl:param name="input"/>
    <xsl:variable name="array" select="array:array-from-nodeset($input)"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$array/*">
          <xsl:sort select="position()" data-type="number" order="descending"/>
          <xsl:copy-of select="."/>
        </xsl:for-each>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="array:join">
    <xsl:param name="a1"/>
    <xsl:param name="a2"/>
    <xsl:param name="a3" select="/.."/>
    <xsl:param name="a4" select="/.."/>
    <xsl:param name="a5" select="/.."/>
    <xsl:param name="a6" select="/.."/>
    <xsl:param name="a7" select="/.."/>
    <xsl:param name="a8" select="/.."/>
    <xsl:param name="a9" select="/.."/>
    <xsl:param name="a10" select="/.."/>
    <xsl:variable name="current" select="array:array-from-nodeset($a1)"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$current/*">
          <xsl:copy-of select="."/>
        </xsl:for-each>
        <xsl:if test="$a2 and exsl:object-type($a2) = 'node-set'">
          <xsl:for-each select="array:join($a2, $a3, $a4, $a5, $a6, $a7, $a8, $a9, $a10)/*">
            <xsl:copy-of select="."/>
          </xsl:for-each>
        </xsl:if>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <func:function name="array:flatten">
    <xsl:param name="input"/>
    <xsl:variable name="array" select="array:array-from-nodeset($input)"/>
    <xsl:variable name="result">
      <array xmlns="http://www.w3.org/2005/xpath-functions">
        <xsl:for-each select="$array/*">
          <xsl:choose>
            <xsl:when test="local-name()='array'">
              <xsl:for-each select="array:flatten(.)/*">
                <xsl:copy-of select="."/>
              </xsl:for-each>
            </xsl:when>
            <xsl:otherwise>
              <xsl:copy-of select="."/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:for-each>
      </array>
    </xsl:variable>
    <func:result select="exsl:node-set($result)/fn:array"/>
  </func:function>

  <xsl:template name="carica-output-item-as-xdm-array-element">
    <xsl:param name="item"/>
    <xsl:variable name="type" select="exsl:object-type($item)"/>
    <xsl:choose xmlns="http://www.w3.org/2005/xpath-functions">
      <xsl:when test="$type = 'RTF'">
        <xsl:call-template name="carica-output-item-as-xdm-array-element">
          <xsl:with-param name="item" select="exsl:node-set($item)"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="$type = 'number'">
        <number>
          <xsl:value-of select="$item"/>
        </number>
      </xsl:when>
      <xsl:when test="$type = 'boolean'">
        <boolean>
          <xsl:value-of select="$item"/>
        </boolean>
      </xsl:when>
      <xsl:when test="$type = 'null'">
        <null/>
      </xsl:when>
      <xsl:when test="$type = 'node-set' and contains('array map string number boolean null', local-name($item))">
        <xsl:for-each select="$item">
          <xsl:element name="{local-name()}" namespace="http://www.w3.org/2005/xpath-functions">
            <xsl:copy-of select="./node()"/>
          </xsl:element>
        </xsl:for-each>
      </xsl:when>
      <xsl:otherwise>
        <string>
          <xsl:value-of select="$item"/>
        </string>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>
