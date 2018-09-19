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

use Papaya\Content;
use Papaya\UI;
use Papaya\Utility;

/**
 * Surfer list navigation. A administration interface control, that allows to navigate to a
 * surfer id. Can be used if an administration interface needs to attach data to the surfer.
 *
 * @package Papaya-Library
 * @subpackage Administration
 *
 * @property int $usersPerPage
 * @property int $pagingButtonsLimit
 */
class Dialog extends UI\Dialog {
  private $_listview;

  private $_paging;

  private $_reference;

  private $_users;

  private $_parameterNames = [
    'user' => 'user_id',
    'page' => 'page',
    'filter' => 'filter',
    'reset' => 'filter-reset'
  ];

  protected $_usersPerPage = 20;

  protected $_pagingButtonsLimit = 5;

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'caption' => ['caption', 'caption'],
    'image' => ['_image', '_image'],
    'fields' => ['fields', 'fields'],
    'buttons' => ['buttons', 'buttons'],
    'hiddenFields' => ['hiddenFields', 'hiddenFields'],
    'hiddenValues' => ['hiddenValues', 'hiddenValues'],
    'data' => ['data', 'data'],
    'options' => ['options', 'options'],
    'description' => ['description', 'description'],
    'usersPerPage' => ['_usersPerPage', '_usersPerPage'],
    'pagingButtonsLimit' => ['_pagingButtonsLimit', '_pagingButtonsLimit']
  ];

  /**
   * Set options and create dialog fields
   */
  public function prepare() {
    $this->caption = new UI\Text\Translated('Users');
    $this->options->dialogWidth = UI\Dialog\Options::SIZE_SMALL;
    $this->options->captionStyle = UI\Dialog\Options::CAPTION_NONE;
    $this->options->useToken = FALSE;
    $this->options->useConfirmation = FALSE;
    $this->parameterMethod(self::METHOD_MIXED_GET);
    $this->fields[] = $field = new UI\Dialog\Field\Input(
      new UI\Text\Translated('Search'),
      $this->_parameterNames['filter']
    );
    $this->fields[] = $buttons = new UI\Dialog\Field\Buttons();
    $buttons->buttons[] = new UI\Dialog\Button\Submit(
      new UI\Text\Translated('Filter'),
      UI\Dialog\Button::ALIGN_RIGHT
    );
    $buttons->buttons[] = new UI\Dialog\Button\NamedSubmit(
      new UI\Text\Translated('Reset'),
      $this->_parameterNames['reset'],
      TRUE,
      UI\Dialog\Button::ALIGN_LEFT
    );
    $this->fields[] = $field = new UI\Dialog\Field\ListView($listview = $this->listview());
    $listview->toolbars()->bottomRight->elements[] = $this->paging();
  }

  /**
   * Execute the dialog and load the community user records.
   *
   * @return bool
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
        ['filter' => $filter],
        $this->_usersPerPage,
        $page * $this->_usersPerPage - $this->_usersPerPage
      );
      $this->paging()->reference()->getParameters()->merge(
        [
          $this->parameterGroup() => [
            $this->_parameterNames['filter'] => $filter
          ]
        ]
      );
      $this->paging()->itemsCount = (int)$this->users()->absCount();
      $this->paging()->currentPage = $this->parameters()->get('page');
    }
    return $result;
  }

  /**
   * Getter/Setter for the community user records object
   *
   * @param Content\Community\Users $users
   *
   * @return Content\Community\Users
   */
  public function users(Content\Community\Users $users = NULL) {
    if (NULL !== $users) {
      $this->_users = $users;
    } elseif (NULL === $this->_users) {
      $this->_users = new Content\Community\Users();
      $this->_users->papaya($this->papaya());
    }
    return $this->_users;
  }

  /**
   * Getter/Setter for the listview subobject, it displays a chunk of the users list
   *
   * @param UI\ListView $listview
   *
   * @return UI\ListView
   */
  public function listview(UI\ListView $listview = NULL) {
    if (NULL !== $listview) {
      $this->_listview = $listview;
    } elseif (NULL === $this->_listview) {
      $this->_listview = new UI\ListView();
      $this->_listview->papaya($this->papaya());
      $this->_listview->parameterGroup($this->parameterGroup());
      $this->_listview->parameters($this->parameters());
      $this->_listview->reference(clone $this->reference());
      $this->_listview->builder(
        $builder = new UI\ListView\Items\Builder($this->users())
      );
      $builder->callbacks()->onCreateItem = function(
        /* @noinspection PhpUnusedParameterInspection */
        $context, UI\ListView\Items $items, array $user
      ) {
        $items[] = new UI\ListView\Item(
          'items-user',
          empty($user['caption']) ? $user['email'] : $user['caption'],
          [
            $this->_parameterNames['user'] => $user['id'],
            $this->_parameterNames['filter'] => $this->data()->get('filter'),
            $this->_parameterNames['page'] => $this->paging()->currentPage
          ],
          $this->parameters()->get($this->_parameterNames['user']) === $user['id']
        );
      };
    }
    return $this->_listview;
  }

  /**
   * Getter/Setter for the paging subobject, allows to navigate between the user list chunks
   *
   * @param UI\Toolbar\Paging $paging
   *
   * @return UI\Toolbar\Paging
   */
  public function paging(UI\Toolbar\Paging $paging = NULL) {
    if (NULL !== $paging) {
      $this->_paging = $paging;
    } elseif (NULL === $this->_paging) {
      $this->_paging = new UI\Toolbar\Paging(
        [$this->parameterGroup(), $this->_parameterNames['page']], 1
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
   * @param UI\Reference $reference
   *
   * @return UI\Reference
   */
  public function reference(UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new UI\Reference();
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
   *
   * @throws \InvalidArgumentException
   */
  public function setParameterNameMapping($identifier, $name) {
    if (!isset($this->_parameterNames[$identifier])) {
      throw new \InvalidArgumentException(
        \sprintf('Unknown parameter identifier "%s".', $identifier)
      );
    }
    Utility\Constraints::assertNotEmpty($name);
    $this->_parameterNames[$identifier] = $name;
  }
}
