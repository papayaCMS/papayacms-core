<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:func="http://exslt.org/functions"
  xmlns:exslt_math="http://exslt.org/math"
  xmlns:php="http://php.net/xsl"
  xmlns:math="http://www.w3.org/2005/xpath-functions/math"
  extension-element-prefixes="func php exslt_math">

  <xsl:variable name="CARICA_CALLBACK" select="'Carica\XpathFunctions\XSLTProcessor::handleFunctionCall'"/>
  <xsl:variable name="CARICA_NUMERIC_MATH_NUMERIC_MATH" select="'Numeric/Math'"/>

  <func:function name="math:pi">
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_NUMERIC_MATH_NUMERIC_MATH, 'pi')"/>
  </func:function>

  <func:function name="math:exp">
    <xsl:param name="argument"/>
    <func:result select="exslt_math:exp($argument)"/>
  </func:function>

  <func:function name="math:exp10">
    <xsl:param name="argument"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_NUMERIC_MATH_NUMERIC_MATH, 'exp10', number($argument))"/>
  </func:function>

  <func:function name="math:log">
    <xsl:param name="argument"/>
    <func:result select="exslt_math:log($argument)"/>
  </func:function>

  <func:function name="math:log10">
    <xsl:param name="argument"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_NUMERIC_MATH_NUMERIC_MATH, 'log10', number($argument))"/>
  </func:function>

  <func:function name="math:pow">
    <xsl:param name="base"/>
    <xsl:param name="exponent"/>
    <func:result select="exslt_math:power($base, $exponent)"/>
  </func:function>

  <func:function name="math:sqrt">
    <xsl:param name="input"/>
    <func:result select="php:function($CARICA_CALLBACK, $CARICA_NUMERIC_MATH_NUMERIC_MATH, 'sqrt', number($input))"/>
  </func:function>

  <func:function name="math:sin">
    <xsl:param name="input"/>
    <func:result select="exslt_math:sin($input)"/>
  </func:function>

  <func:function name="math:cos">
    <xsl:param name="input"/>
    <func:result select="exslt_math:cos($input)"/>
  </func:function>

  <func:function name="math:tan">
    <xsl:param name="input"/>
    <func:result select="exslt_math:tan($input)"/>
  </func:function>

  <func:function name="math:asin">
    <xsl:param name="input"/>
    <func:result select="exslt_math:asin($input)"/>
  </func:function>

  <func:function name="math:acos">
    <xsl:param name="input"/>
    <func:result select="exslt_math:acos($input)"/>
  </func:function>

  <func:function name="math:atan">
    <xsl:param name="input"/>
    <func:result select="exslt_math:atan($input)"/>
  </func:function>

  <func:function name="math:atan2">
    <xsl:param name="y"/>
    <xsl:param name="x"/>
    <func:result select="exslt_math:atan2($y,$x)"/>
  </func:function>

</xsl:stylesheet>
