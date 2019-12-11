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
  use Papaya\UI\Content\Teasers\Factory as PageTeaserFactory;
  use Papaya\UI\Dialog;

  interface Teasers {

    const FIELD_TEASERS_ORDER = 'teasers-order';
    const FIELD_TEASERS_LIMIT = 'teasers-limit';
    const FIELD_TEASERS_IMAGE_RESIZE = 'teasers-image-resize-mode';
    const FIELD_TEASERS_IMAGE_WIDTH = 'teasers-image-width';
    const FIELD_TEASERS_IMAGE_HEIGHT = 'teasers-image-height';

    const _TEASERS_DEFAULTS = [
      self::FIELD_TEASERS_ORDER => PageTeaserFactory::ORDER_POSITION_ASCENDING,
      self::FIELD_TEASERS_LIMIT => 10,
      self::FIELD_TEASERS_IMAGE_RESIZE => 'max',
      self::FIELD_TEASERS_IMAGE_WIDTH => 0,
      self::FIELD_TEASERS_IMAGE_HEIGHT => 0
    ];

    /**
     * @param Dialog $dialog
     * @param EditableContent $content
     * @return void
     */
    public function appendTeasersFieldsToDialog(Dialog $dialog, EditableContent $content);

    /**
     * @return array
     */
    public function getTeasersDefaultContent();

    /**
     * @param PageTeaserFactory|NULL $teasers
     * @return PageTeaserFactory
     */
    public function teaserFactory(PageTeaserFactory $teasers = NULL);
  }
}


