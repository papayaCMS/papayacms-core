<?php
/**
* This object loads the defined domains for a papaya installation.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Content
* @version $Id: Domains.php 39695 2014-03-26 14:21:02Z weinert $
*/

/**
* This object loads the defined domains for a papaya installation.
*
* @package Papaya-Library
* @subpackage Content
*/
class PapayaContentDomains extends PapayaDatabaseRecords {

  /**
  * Map field names to more convinient property names
  *
  * @var array(string=>string)
  */
  protected $_fields = array(
    'id' => 'domain_id',
    'host' => 'domain_hostname',
    'scheme' => 'domain_protocol',
    'language_id' => 'domain_language_id',
    'group_id' => 'domaingroup_id',
    'mode' => 'domain_mode',
    'data' => 'domain_data'
  );

  /**
  * Table containing domain informations
  *
  * @var string
  */
  protected $_tableName = PapayaContentTables::DOMAINS;

  protected $_identifierProperties = array('id');
}