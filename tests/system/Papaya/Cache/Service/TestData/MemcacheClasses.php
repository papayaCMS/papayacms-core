<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2021 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace {

  if (!class_exists('Memcache', FALSE)) {

    /** @noinspection PhpUndefinedClassInspection */

    class Memcache {
      public function addServer() {
      }

      public function flush() {
      }

      public function get() {
      }

      public function set() {
      }

      public function replace() {
      }
    }
  }

  if (!class_exists('Memcached', FALSE)) {

    /** @noinspection PhpUndefinedClassInspection */

    class Memcached {
      public function addServer() {
      }

      public function flush() {
      }

      public function get() {
      }

      public function set() {
      }

      public function replace() {
      }
    }
  }
}


