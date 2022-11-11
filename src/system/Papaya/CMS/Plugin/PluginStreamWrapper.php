<?php
declare(strict_types=1);

namespace Papaya\CMS\Plugin {

  class PluginStreamWrapper {

    /**
     * @var Loader[]
     */
    private static $_loaders = [];
    private $_stream;

    /**
     * Register the stream wrapper for the given protocol, if it is not registered yet.
     *
     * @param string $protocol
     * @param string $path
     */
    public static function register(string $protocol, Loader $pluginLoader): void {
      if (!in_array($protocol, stream_get_wrappers(), TRUE)) {
        self::$_loaders[$protocol] = $pluginLoader;
        stream_wrapper_register($protocol, __CLASS__);
      }
    }

    public function url_stat(
      /** @noinspection PhpUnusedParameterInspection */
      string $path, int $flags
    ): array {
      return [];
    }

    /**
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string $opened_path
     * @return bool
     */
    public function stream_open(
      /** @noinspection PhpUnusedParameterInspection */
      string $path, string $mode, int $options, &$opened_path
    ): bool {
      [$protocol, $fileWithModule] = \explode(':', $path);
      [$moduleID, $file] = \explode(':', $path);
      if (
        isset(self::$_loaders[$protocol]) &&
        preg_match('(^[a-z\d_./-]$)Di')
      ) {
        $loader = self::$_loaders[$protocol];
        $modulePath = $loader->getPluginFilesPath();
        if ($modulePath) {
          $this->_stream = fopen($modulePath.DIRECTORY_SEPARATOR.$file, 'rb');
        }
        return is_resource($this->_stream);
      }
      return FALSE;
    }

    /**
     * @param int $count
     * @return bool|string
     */
    public function stream_read(int $count) {
      return fread($this->_stream, $count);
    }

    /**
     * @param string $data
     * @return bool|int
     */
    public function stream_write(string $data) {
      return fwrite($this->_stream, $data);
    }

    public function stream_eof(): bool {
      return feof($this->_stream);
    }

    public function stream_seek(int $offset, int $whence): int {
      return fseek($this->_stream, $offset, $whence);
    }

    public function __destruct() {
      if (is_resource($this->_stream)) {
        fclose($this->_stream);
      }
    }
  }
}
