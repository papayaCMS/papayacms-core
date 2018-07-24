<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

/**
* output filter
*
* @package Papaya
* @subpackage Modules
*/
class base_outputfilter extends base_plugin {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array();

  /**
  * default template subdirectory
  * @var string
  */
  var $templatePath = '';

  /**
  * Error message
  * @var string
  */
  var $errorMessage = '';

  /**
  * Error status code (optional, default 500)
  * @var string
  */
  var $errorStatus = 404;

  /**
   * Parse page
   *
   * @param $topic
   * @param $layout
   * @access public
   * @return string ''
   */
  function parsePage($topic, $layout) {
    return '';
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
    return '';
  }

  /**
  * parse some xml data
  *
  * @param \Papaya\Template $layout
  * @access public
  * @return string
  */
  function parseXML($layout) {
    return '';
  }

  /**
  * Check configuration
  *
  * @param boolean $page optional, default value TRUE
  * @access public
  * @return boolean FALSE
  */
  function checkConfiguration($page = TRUE) {
    return FALSE;
  }

  /**
  * Get dialog
  *
  * @see base_dialog:getDialogXML
  * @access public
  * @return string
  */
  function getDialog($dialogTitlePrefix = '', $dialogIcon = '') {
    $this->initializeDialog();
    $this->dialog->dialogTitle = $this->_gt('Edit filter properties');
    $this->dialog->dialogDoubleButtons = FALSE;
    return $this->dialog->getDialogXML();
  }

  /**
  * callback from form to get the defined template path for file listing
  *
  * @access public
  * @return string
  */
  function getTemplatePath() {
    $templateHandler = new PapayaTemplateXsltHandler();
    return $templateHandler->getLocalPath().$this->templatePath.'/';
  }
}
