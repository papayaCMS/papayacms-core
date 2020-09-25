<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Media\Thumbnail {

  use Papaya\Graphics\BoundingRectangle;

  interface Calculation {

    const MODE_FIX = 'abs';
    const MODE_CONTAIN = 'max';
    const MODE_CONTAIN_PADDED = 'maxfill';
    const MODE_COVER = 'min';
    const MODE_COVER_CROPPED = 'mincrop';

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return array [width, height]
     */
    public function getTargetSize();

    /**
     * @return BoundingRectangle
     */
    public function getSource();

    /**
     * @return BoundingRectangle
     */
    public function getDestination();

  }
}
