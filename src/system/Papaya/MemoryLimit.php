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
namespace Papaya {

  use Papaya\Application\Access as ApplicationAccess;

  class MemoryLimit implements ApplicationAccess {

    use ApplicationAccess\Aggregation;

    private static $_fallback = 8388608;
    private static $_configuration;
    private static $_suhoshin;

    public function increase($requiredMemory) {
      $existingLimit = $this->getLimit();
      if ($existingLimit > 0) {
        $requiredLimit = $requiredMemory + $this->getCurrentUsage();
        if ($requiredLimit > $existingLimit) {
          ini_set('memory_limit', $requiredLimit);
          $existingLimit = \Papaya\Utility\Bytes::fromString(ini_get('memory_limit'));
          return ($requiredLimit >= $existingLimit);
        }
      }
      return TRUE;
    }

    public function reset() {
      if (self::$_configuration) {
        ini_set('memory_limit', self::$_configuration);
      }
    }

    public function getCurrentUsage() {
      return memory_get_usage();
    }

    public function getLimit() {
      $configuration = $this->getConfiguredLimit();
      $suhoshin = $this->getSuhoshinLimit();
      if ($configuration > 0 && $suhoshin > 0) {
        return min($configuration, $suhoshin);
      }
      if ($suhoshin > 0) {
        return $suhoshin;
      }
      return $configuration;
    }

    public function getConfiguredLimit() {
      if (NULL !== self::$_configuration) {
        return self::$_configuration;
      }
      if ($memoryLimit = @ini_get('memory_limit')) {
        self::$_configuration = \Papaya\Utility\Bytes::fromString($memoryLimit);
      } else {
        self::$_configuration = self::$_fallback;
      }
      return self::$_configuration;
    }

    public function getSuhoshinLimit() {
      if (NULL !== self::$_suhoshin) {
        return self::$_suhoshin;
      }
      self::$_suhoshin = -1;
      if (extension_loaded('suhosin')) {
        if ($suhosinMemoryLimit = @ini_get('suhosin.memory_limit')) {
          self::$_suhoshin = \Papaya\Utility\Bytes::fromString($suhosinMemoryLimit);
        }
      }
      return self::$_suhoshin;
    }
  }
}


