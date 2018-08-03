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

namespace Papaya\Administration\Community\Users\Roster;

/**
 * Surfer list navigation. A administration interface control, that allows to navigate to a
 * surfer id. Can be used if an administration interface needs to attach data to the surfer.
 *
 * @package Papaya-Library
 * @subpackage Administration
 *
 * @property integer $usersPerPage
 * @property integer $pagingButtonsLimit
 */
class Dialog extends \Papaya\Ui\Dialog {

  private $_listview;
  private $_paging;
  private $_reference;

  private $_users;

  private $_parameterNames = array(
    'user' => 'user_id',
    'page' => 'page',
    'filter' => 'filter',
    'reset' => 'filter-reset'
  );
  protected $_usersPerPage = 20;
  protected $_pagingButtonsLimit = 5;

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'caption' => array('caption', 'caption'),
    'image' => array('_image', '_image'),
    'fields' => array('fields', 'fields'),
    'buttons' => array('buttons', 'buttons'),
    'hiddenFields' => array('hiddenFields', 'hiddenFields'),
    'hiddenValues' => array('hiddenValues', 'hiddenValues'),
    'data' => array('data', 'data'),
    'options' => array('options', 'options'),
    'description' => array('description', 'description'),
    'usersPerPage' => array('_usersPerPage', '_usersPerPage'),
    'pagingButtonsLimit' => array('_pagingButtonsLimit', '_pagingButtonsLimit')
  );

  /**
   * Set options and create dialog fields
   */
  public function prepare() {
    $this->caption = new \PapayaUiStringTranslated('Users');
    $this->options->dialogWidth = \Papaya\Ui\Dialog\Options::SIZE_SMALL;
    $this->options->captionStyle = \Papaya\Ui\Dialog\Options::CAPTION_NONE;
    $this->options->useToken = FALSE;
    $this->options->useConfirmation = FALSE;
    $this->parameterMethod(self::METHOD_MIXED_GET);
    $this->fields[] = $field = new \Papaya\Ui\Dialog\Field\Input(
      new \PapayaUiStringTranslated('Search'),
      $this->_parameterNames['filter']
    );
    $this->fields[] = $buttons = new \Papaya\Ui\Dialog\Field\Buttons();
    $buttons->buttons[] = new \Papaya\Ui\Dialog\Button\Submit(
      new \PapayaUiStringTranslated('Filter'),
      \Papaya\Ui\Dialog\Button::ALIGN_RIGHT
    );
    $buttons->buttons[] = new \Papaya\Ui\Dialog\Button\NamedSubmit(
      new \PapayaUiStringTranslated('Reset'),
      $this->_parameterNames['reset'],
      TRUE,
      \Papaya\Ui\Dialog\Button::ALIGN_LEFT
    );
    $this->fields[] = $field = new \Papaya\Ui\Dialog\Field\Listview($listview = $this->listview());
    $listview->toolbars()->bottomRight->elements[] = $this->paging();
  }

  /**
   * Execute the dialog and load the community user records.
   *
   * @return boolean
   */
  public function execute() {
    if ($result = parent::execute()) {
      if ($this->data()->get($this->_parameterNames['reset'], FALSE)) {
        $this->data()->remove($this->_parameterNames['filter']);
        $this->parameters()->remove($this->_parameterNames['filter']);
      }
      $filter = $this->data()->get($this->_parameterNames['filter']);
      $page = $this->parameters()->get($this->_parameterNames['page'], 1);
      $this->users()->load(
        array('filter' => $filter),
        $this->_usersPerPage,
        $page * $this->_usersPerPage - $this->_usersPerPage
      );
      $this->paging()->reference()->getParameters()->merge(
        array(
          $this->parameterGroup() => array(
            $this->_parameterNames['filter'] => $filter
          )
        )
      );
      $this->paging()->itemsCount = (int)$this->users()->absCount();
      $this->paging()->currentPage = $this->parameters()->get('page');
    }
    return $result;
  }

  /**
   * Getter/Setter for the community user records object
   *
   * @param \Papaya\Content\Community\Users $users
   * @return \Papaya\Content\Community\Users
   */
  public function users(\Papaya\Content\Community\Users $users = NULL) {
    if (NULL !== $users) {
      $this->_users = $users;
    } elseif (NULL === $this->_users) {
      $this->_users = new \Papaya\Content\Community\Users();
      $this->_users->papaya($this->papaya());
    }
    return $this->_users;
  }

  /**
   * Getter/Setter for the listview subobject, it displays a chunk of the users list
   *
   * @param \Papaya\Ui\Listview $listview
   * @return \Papaya\Ui\Listview
   */
  public function listview(\Papaya\Ui\Listview $listview = NULL) {
    if (NULL !== $listview) {
      $this->_listview = $listview;
    } elseif (NULL === $this->_listview) {
      $this->_listview = new \Papaya\Ui\Listview();
      $this->_listview->papaya($this->papaya());
      $this->_listview->parameterGroup($this->parameterGroup());
      $this->_listview->parameters($this->parameters());
      $this->_listview->reference(clone $this->reference());
      $this->_listview->builder(
        $builder = new \Papaya\Ui\Listview\Items\Builder($this->users())
      );
      $builder->callbacks()->onCreateItem = array($this, 'createUserItem');
    }
    return $this->_listview;
  }

  /**
   * Create a listview item for a community user record.
   *
   * @param object $context
   * @param \Papaya\Ui\Listview\Items $items
   * @param array $user
   */
  public function createUserItem($context, $items, $user) {
    $items[] = new \Papaya\Ui\Listview\Item(
      'items-user',
      empty($user['caption']) ? $user['email'] : $user['caption'],
      array(
        $this->_parameterNames['user'] => $user['id'],
        $this->_parameterNames['filter'] => $this->data()->get('filter'),
        $this->_parameterNames['page'] => $this->paging()->currentPage
      ),
      $this->parameters()->get($this->_parameterNames['user']) === $user['id']
    );
  }

  /**
   * Getter/Setter for the paging subobject, allows to navigate between the user list chunks
   *
   * @param \PapayaUiToolbarPaging $paging
   * @return \PapayaUiToolbarPaging
   */
  public function paging(\PapayaUiToolbarPaging $paging = NULL) {
    if (NULL !== $paging) {
      $this->_paging = $paging;
    } elseif (NULL === $this->_paging) {
      $this->_paging = new \PapayaUiToolbarPaging(
        array($this->parameterGroup(), $this->_parameterNames['page']), 1
      );
      $this->_paging->papaya($this->papaya());
      $this->_paging->reference(clone $this->reference());
      $this->_paging->buttonLimit = $this->_pagingButtonsLimit;
      $this->_paging->itemsPerPage = $this->_usersPerPage;
    }
    return $this->_paging;
  }

  /**
   * The basic reference object used by the subobjects to create urls.
   *
   * @param \Papaya\Ui\Reference $reference
   * @return \Papaya\Ui\Reference
   */
  public function reference(\Papaya\Ui\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new \Papaya\Ui\Reference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }

  /**
   * Define a parameter mapping - allows to define the detail parameter names used
   * for links and form fields.
   *
   * @param string $identifier
   * @param string $name
   * @throws \InvalidArgumentException
   */
  public function setParameterNameMapping($identifier, $name) {
    if (!isset($this->_parameterNames[$identifier])) {
      throw new \InvalidArgumentException(
        sprintf('Unknown parameter identifier "%s".', $identifier)
      );
    }
    \Papaya\Utility\Constraints::assertNotEmpty($name);
    $this->_parameterNames[$identifier] = $name;
  }
}
