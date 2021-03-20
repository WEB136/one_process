<?php

namespace web136\one_process;

use InvalidArgumentException;
use LogicException;

class UniqueProcess
{

    /**
     * @var string
     */
    protected $pidFile;

    /**
     * UniqueProcess constructor.
     * @param string $pidFile
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function __construct($pidFile = '')
    {
        $pidFile = trim(strval($pidFile));
        $this->checkPidFile($pidFile);
        $this->pidFile = $pidFile;
    }

    /**
     * @param string $pidFile
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    public function checkPidFile($pidFile = '')
    {
        if (empty($pidFile)) {
            throw new InvalidArgumentException('Параметр $pidFile обязательный');
        }

        $pidFileDir = dirname($pidFile);

        if (!file_exists($pidFileDir)) {
            throw new LogicException('Директории ' . $pidFileDir . ' не существует');
        }

        if (!is_writeable($pidFileDir)) {
            throw new LogicException("Директория {$pidFileDir} недоступна для записи");
        }

        if (!is_readable($pidFileDir)) {
            throw new LogicException("Директория {$pidFileDir} недоступна для чтения");
        }
    }

    /**
     * Пытается пометить процесс активным. В случае неудачи вернет false
     * @return bool
     */
    public function markProcessStarted(): bool
    {
        if (!file_exists($this->pidFile)) {
            // Процесса не было. Создаем новый файл
            return $this->createPidFile();
        }
        elseif (self::isProcessActive($this->getPidFromFile())) {
            // Процесс активен. Не стоит ничего делать
            return false;
        }
        else {
            // Есть файл с pid но сам pid не активен.
            $this->deletePidFile();

            return $this->createPidFile();
        }
    }

    /**
     * Пытается создать $pidFile и записать в него pid текущего процесса. Если файл существует, вернет false
     * @return bool
     */
    protected function createPidFile(): bool
    {
        if (!file_exists($this->pidFile)) {
            return false !== file_put_contents($this->pidFile, self::getMyPid());
        }
        else {
            return false;
        }
    }

    /**
     * Возвращает pid текущего процесса
     * @return false|int
     */
    public static function getMyPid()
    {
        return getmypid();
    }

    /**
     * Проверяет активен ли процесс с pid $pid
     * @param $pid
     *
     * @return bool
     */
    public static function isProcessActive($pid): bool
    {
        $pid = intval($pid);

        if ($pid < 1) {
            return false;
        }
        else {
            return posix_kill($pid, 0);
        }
    }

    /**
     * Возвращает pid хранящийся в pidFile
     * @return false|string
     */
    public function getPidFromFile()
    {
        if (file_exists($this->pidFile)) {
            return file_get_contents($this->pidFile);
        }
        else {
            return false;
        }
    }

    /**
     * Удаляет pidFile
     */
    protected function deletePidFile()
    {
        unlink($this->pidFile);
    }

    /**
     * Помечает процесс завершенным
     */
    public function markProcessEnded()
    {
        $this->deletePidFile();
    }

}