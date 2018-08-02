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

namespace Papaya\Administration\Theme\Editor\Changes;
use Papaya\Content\Structure;

/**
 * Dialog command that allows to edit the dynamic values on on page, the groups are field groups
 *
 * @package Papaya-Library
 * @subpackage Administration
 */
class Dialog
  extends \Papaya\Ui\Control\Command\Dialog\Database\Record {

  /**
   * @var \Papaya\Content\Structure\Page
   */
  private $_themePage;
  /**
   * @var \Papaya\Theme\Handler
   */
  private $_themeHandler;
  /**
   * @var \Papaya\Ui\Dialog\Field\Factory
   */
  private $_fieldFactory;
  /**
   * @var \Papaya\Cache\Service
   */
  private $_cacheService;

  /**
   * Create dialog and add fields for the dynamic values defined by the current theme values page
   *
   * @see \Papaya\Ui\Control\Command\Dialog::createDialog()
   * @return \PapayaUiDialog
   */
  public function createDialog() {
    $setId = $this->parameters()->get('set_id', 0);
    if ($setId > 0) {
      $this->record()->load($setId);
    }
    $dialog = new \Papaya\Ui\Dialog\Database\Save($this->record());
    if ($page = $this->themePage()) {
      $dialog->caption = new \PapayaUiStringTranslated('Dynamic Values: %s', array($page->title));
      $dialog->options->topButtons = TRUE;
      $dialog->parameterGroup($this->parameterGroup());
      $dialog->parameters($this->parameters());
      $dialog->hiddenFields()->merge(
        array(
          'cmd' => 'values_edit',
          'theme' => $this->parameters()->get('theme', ''),
          'set_id' => $setId,
          'page_identifier' => $this->parameters()->get('page_identifier', '')
        )
      );
      /** @var Structure\Group $group */
      foreach ($page->groups() as $group) {
        $fieldset = new \PapayaUiDialogFieldGroup($group->title);
        /** @var Structure\Value $value */
        foreach ($group->values() as $value) {
          try {
            $options = new \Papaya\Ui\Dialog\Field\Factory\Options(
              array(
                'name' => 'values/'.$value->getIdentifier(),
                'caption' => $value->title,
                'default' => $value->default,
                'parameters' => $value->fieldParameters
              )
            );
            $fieldset->fields[] = $field = $this->fieldFactory()->getField(
              $value->fieldType, $options
            );
            $field->setHint($value->hint);
          } catch (\Papaya\Ui\Dialog\Field\Factory\Exception $e) {
            $fieldset->fields[] = new \PapayaUiDialogFieldMessage(
              \Papaya\Message::SEVERITY_ERROR, $e->getMessage()
            );
          }
        }
        $dialog->fields[] = $fieldset;
      }
      if (0 === \count($dialog->fields)) {
        $dialog->fields[] = new \PapayaUiDialogFieldMessage(
          \Papaya\Message::SEVERITY_ERROR,
          new \PapayaUiStringTranslated('Invalid value definition!')
        );
      } else {
        $dialog->buttons[] = new \Papaya\Ui\Dialog\Button\Submit(new \PapayaUiStringTranslated('Save'));
        $this->callbacks()->onExecuteSuccessful = array($this, 'callbackSaveValues');
        $this->callbacks()->onExecuteFailed = array($this, 'callbackShowError');
      }
    } else {
      $dialog->caption = new \PapayaUiStringTranslated('Error');
      if (0 === \count($dialog->fields)) {
        $dialog->fields[] = new \PapayaUiDialogFieldMessage(
          \Papaya\Message::SEVERITY_ERROR,
          new \PapayaUiStringTranslated('Theme page not found!')
        );
      }
    }
    return $dialog;
  }

  /**
   * Show success message and trigger cache delete
   */
  public function callbackSaveValues() {
    $this->papaya()->messages->dispatch(
      new \Papaya\Message\Display\Translated(
        \Papaya\Message::SEVERITY_INFO,
        'Values saved.'
      )
    );
    if ($cache = $this->cache()) {
      $cache->delete('theme', $this->parameters()->get('theme', ''));
    }
  }

  /**
   * Save data from dialog
   *
   * @param object $context
   * @param \PapayaUiDialog $dialog
   */
  public function callbackShowError($context, $dialog) {
    $this->papaya()->messages->dispatch(
      new \Papaya\Message\Display\Translated(
        \Papaya\Message::SEVERITY_ERROR,
        'Invalid input. Please check the field(s) "%s".',
        array(implode(', ', $dialog->errors()->getSourceCaptions()))
      )
    );
  }

  /**
   * Theme definition page to access the group and value definition of the selected page
   *
   * @param Structure\Page $themePage
   * @return Structure\Page
   */
  public function themePage(Structure\Page $themePage = NULL) {
    if (NULL !== $themePage) {
      $this->_themePage = $themePage;
    } elseif (NULL === $this->_themePage) {
      $this->_themePage = $this
        ->themeHandler()
        ->getDefinition($this->parameters()->get('theme', ''))
        ->getPage($this->parameters()->get('page_identifier', ''));
    }
    return $this->_themePage;
  }

  /**
   * The theme handler is an helper object to get general information about the
   * themes of the current installation
   *
   * @param \Papaya\Theme\Handler $themeHandler
   * @return \Papaya\Theme\Handler
   */
  public function themeHandler(\Papaya\Theme\Handler $themeHandler = NULL) {
    if (NULL !== $themeHandler) {
      $this->_themeHandler = $themeHandler;
    } elseif (NULL === $this->_themeHandler) {
      $this->_themeHandler = new \Papaya\Theme\Handler();
      $this->_themeHandler->papaya($this->papaya());
    }
    return $this->_themeHandler;
  }

  /**
   * The dialog field factory creates field for the given field types using profile classes/objects
   * defined by the field type name.
   *
   * @param \Papaya\Ui\Dialog\Field\Factory $factory
   * @return \Papaya\Ui\Dialog\Field\Factory
   */
  public function fieldFactory(\Papaya\Ui\Dialog\Field\Factory $factory = NULL) {
    if (NULL !== $factory) {
      $this->_fieldFactory = $factory;
    } elseif (NULL === $this->_fieldFactory) {
      $this->_fieldFactory = new \Papaya\Ui\Dialog\Field\Factory();
    }
    return $this->_fieldFactory;
  }

  /**
   * Access to the theme cache service - to reset the cache after changes.
   *
   * @param \Papaya\Cache\Service $service
   * @return \Papaya\Cache\Service
   */
  public function cache(\Papaya\Cache\Service $service = NULL) {
    if (NULL !== $service) {
      $this->_cacheService = $service;
    } elseif (NULL === $this->_cacheService) {
      /** @noinspection PhpParamsInspection */
      $this->_cacheService = \Papaya\Cache::getService($this->papaya()->options);
    }
    return $this->_cacheService;
  }
}
