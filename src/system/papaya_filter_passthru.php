<?php
/**
* output filter that just return the xml/data
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Modules
* @version $Id: papaya_filter_passthru.php 39818 2014-05-13 13:15:13Z weinert $
*/
/**
* output filter
*
* @package Papaya
* @subpackage Modules
*/
class papaya_filter_passthru extends base_outputfilter {

  /**
  * Parse page
  *
  * @param base_topic $topic
  * @param PapayaTemplate $layout
  * @access public
  * @return string ''
  */
  function parsePage($topic, $layout) {
    return $layout->getXml();
  }

  /**
  * Parse box
  *
  * @param object base_topic $topic
  * @param array $box
  * @param string $xmlString
  * @access public
  * @return string ''
  */
  function parseBox($topic, $box, $xmlString) {
    return $xmlString;
  }

  /**
  * parse some xml data
  *
  * @param PapayaTemplate $layout
  * @access public
  * @return string
  */
  function parseXML($layout) {
    return $layout->getXml();
  }

  /**
  * Check configuration
  *
  * @param boolean $page optional, default value TRUE
  * @access public
  * @return boolean FALSE
  */
  function checkConfiguration($page = TRUE) {
    return TRUE;
  }
}