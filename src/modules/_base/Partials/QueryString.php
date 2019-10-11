<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Modules\Core\Partials {

  use Papaya\Plugin\Editable\Content as EditableContent;
  use Papaya\UI\Dialog;

  interface QueryString {

    const QUERYSTRING_MODE_IGNORE = 0;
    const QUERYSTRING_MODE_APPEND = 1;
    const QUERYSTRING_MODE_TEMPLATE = 2;

    const FIELD_QUERYSTRING_MODE = 'target-querystring-mode';
    const FIELD_QUERYSTRING_TEMPLATE = 'target-querystring-template';

    const _QUERYSTRING_DEFAULTS = [
      self::FIELD_QUERYSTRING_MODE => 0,
      self::FIELD_QUERYSTRING_TEMPLATE => ''
    ];


    /**
     * @param Dialog $dialog
     * @param EditableContent $content
     * @return void
     */
    public function appendQueryStringFieldsToDialog(Dialog $dialog, EditableContent $content);
  }
}


