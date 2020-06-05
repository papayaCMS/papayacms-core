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
namespace Papaya\Administration {

  use Papaya\Administration\UI as AdministrationUI;
  use Papaya\Application\BaseObject as ApplicationBaseObject;
  use Papaya\Request;
  use Papaya\Request\Parameters;
  use Papaya\Template;
  use Papaya\Template\XSLT as XSLTTemplate;
  use Papaya\UI\Menu;
  use Papaya\UI\Toolbar;

  /**
   * Abstract superclass for an administration page.
   *
   * The administration page has three parts (content, navigation, information). The parts are executed
   * one after another with the same parameters. Changes to the parameters of one part are assigned
   * to the next.
   *
   * Here is an composed toolbar sets for each element.
   *
   * @package Papaya-Library
   * @subpackage Administration
   */
  abstract class Page extends ApplicationBaseObject {
    /**
     * @var string|null
     */
    private $_moduleId;

    /**
     * @var AdministrationUI
     */
    private $_ui;
    /**
     * @var XSLTTemplate
     */
    private $_template;

    /**
     * @var Page\Parts
     */
    private $_parts;

    /**
     * @var Toolbar
     */
    private $_toolbar;

    /**
     * @var string
     */
    protected $_parameterGroup = '';

    /**
     * Create page object and store administration UI for later use
     *
     * @param AdministrationUI|Template $ui
     * @param null|string $moduleId
     */
    public function __construct($ui, $moduleId = NULL) {
      if ($ui instanceof Template) {
        // BC, allow old calls
        $this->_template = $ui;
      } elseif ($ui instanceof AdministrationUI) {
        $this->_ui = $ui;
      } else {
        throw new \InvalidArgumentException(
          sprintf(
            'Argument should be a "%s" and can be a "%s" for old code.',
            AdministrationUI::class,
            Template::class
          )
        );
      }
      $this->_moduleId = $moduleId;
    }

    /**
     * @return AdministrationUI|null
     */
    public function getUI() {
      return $this->_ui;
    }


    /**
     * @return Template
     */
    public function getTemplate() {
      if (NULL === $this->_template && ($ui = $this->getUI())) {
        $this->_template = $ui->template();
      }
      return $this->_template;
    }

    /**
     * @return null|string
     */
    public function getModuleId() {
      return $this->_moduleId;
    }

    /**
     * This method needs to be overloaded to create the content part of the page
     * If an valid part is returned, it will be used first.
     *
     * @return Page\Part|false
     */
    protected function createContent() {
      return FALSE;
    }

    /**
     * This method needs to be overloaded to create the navigation part of the page.
     * If an valid part is returned, it will be used after the content part.
     *
     * @return Page\Part|false
     */
    protected function createNavigation() {
      return FALSE;
    }

    /**
     * This method needs to be overloaded to create the content part of the page.
     * If an valid part is returned, it will be used last.
     *
     * @return Page\Part|false
     */
    protected function createInformation() {
      return FALSE;
    }

    /**
     * Make module executable (like a function)
     */
    public function __invoke() {
      $this->execute();
    }

    /**
     * Execute the module and add the xml to the layout object
     */
    public function execute() {
      if (!$this->validateAccess()) {
        $this->papaya()->messages->displayError('Access forbidden.');
        return;
      }
      $parts = $this->parts();
      $restoreParameters = ('get' === $this->papaya()->request->getMethod()) && !empty($this->_parameterGroup);
      if ($restoreParameters && $parts->parameters()->isEmpty()) {
        $value = $this->papaya()->session->getValue($this->getSessionParametersName());
        $parts->parameters()->merge(\is_array($value) ? $value : []);
        $this->papaya()->request->setParameters(
          Request::SOURCE_QUERY,
          $this->papaya()->request->getParameters(Request::SOURCE_QUERY)->set(
            $this->_parameterGroup, \is_array($value) ? $value : []
          )
        );
      }
      foreach ($parts as $name => $part) {
        if (
          $part instanceof Page\Part &&
          ($xml = $part->getXML())
        ) {
          $this->getTemplate()->add($xml, $this->parts()->getTarget($name));
        }
      }
      if ($restoreParameters) {
        $this->storeParameters($parts->parameters());
      }
      $this->parts()->toolbar()->toolbar($this->toolbar());
      $this->getTemplate()->addMenu($this->parts()->toolbar()->getXML());
    }

    private function getSessionParametersName() {
      return [\get_class($this), 'parameters', $this->_parameterGroup];
    }

    public function storeParameters(Parameters $parameters = NULL) {
      $this->papaya()->session->setValue(
        $this->getSessionParametersName(), $parameters->toArray()
      );
    }

    /**
     * Getter/Setter for the parts list
     *
     * @param Page\Parts $parts
     *
     * @return Page\Parts
     */
    public function parts(Page\Parts $parts = NULL) {
      if ($parts) {
        $this->_parts = $parts;
      } elseif (NULL === $this->_parts) {
        $this->_parts = new Page\Parts($this);
        $this->_parts->papaya($this->papaya());
        if (!empty($this->_parameterGroup)) {
          $this->_parts->parameterGroup($this->_parameterGroup);
        }
      }
      return $this->_parts;
    }

    /**
     * A method called by the parts list, if an part is needed and not already existing in the list.
     *
     * It calls different protected methods that can be overload to create the part. If it returns
     * FALSE the part is ignored.
     *
     * @param string $name
     *
     * @return false|Page\Part
     */
    public function createPart($name) {
      switch ($name) {
      case Page\Parts::PART_CONTENT :
        return $this->createContent();
      case Page\Parts::PART_NAVIGATION :
        return $this->createNavigation();
      case Page\Parts::PART_INFORMATION :
        return $this->createInformation();
      }
      return FALSE;
    }

    /**
     * Getter/Setter for the action toolbar. The parts append buttons to sets the sets are
     * appended to the toolbar.
     *
     * @param Toolbar $toolbar
     *
     * @return Toolbar
     */
    public function toolbar(Toolbar $toolbar = NULL) {
      if ($toolbar) {
        $this->_toolbar = $toolbar;
      } elseif (NULL === $this->_toolbar) {
        $this->_toolbar = new Menu();
        $this->_toolbar->papaya($this->papaya());
        $this->_toolbar->identifier = 'edit';
      }
      return $this->_toolbar;
    }

    /**
     * @return bool
     */
    public function validateAccess() {
      return TRUE;
    }
  }
}
