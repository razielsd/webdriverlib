<?php
class WebDriver_Object_Timeout extends WebDriver_Object
{
    const WAIT_IMPLICIT = '__wait_implicit__';
    const WAIT_ASYNC_SCRIPT = '__wait_async_script__';

    protected $cache = [
        self::WAIT_ASYNC_SCRIPT => null,
        self::WAIT_IMPLICIT => null
    ];
    /**
     * Set timeouts
     *
     * @param array $timeout
     * @return mixed
     */
    public function setAll($timeout)
    {
        $command = $this->driver->factoryCommand('timeouts', WebDriver_Command::METHOD_POST, $timeout);
        foreach ($this->cache as &$value) {
            $value = $timeout;
        }
        return $this->driver->curl($command)['value'];
    }


    public function asyncScript($timeout)
    {
        $this->cache[self::WAIT_ASYNC_SCRIPT] = $timeout;
        $param = ['ms' => $timeout];
        $command = $this->driver->factoryCommand('timeouts/async_script', WebDriver_Command::METHOD_POST, $param);
        return $this->driver->curl($command)['value'];
    }


    /**
     * Set the amount of time the driver should wait when searching for elements.
     *
     * @param $timeout - The amount of time to wait, in milliseconds. This value has a lower bound of 0.
     * @return mixed
     */
    public function implicitWait($timeout)
    {
        $timeoutNormalize = intval(ceil($timeout / 100));
        if ($this->cache[self::WAIT_IMPLICIT] === $timeoutNormalize) {
            return null;
        }
        $this->cache[self::WAIT_IMPLICIT] = $timeout;
        $param = ['ms' => intval($timeout)];
        $command = $this->driver->factoryCommand('timeouts/implicit_wait', WebDriver_Command::METHOD_POST, $param);
        return $this->driver->curl($command)['value'];
    }


    public function get($timeoutName)
    {
        if (!isset($this->cache[$timeoutName])) {
            throw new WebDriver_Exception('Unknown timeout: ' . $timeoutName);
        }
        return $this->cache[$timeoutName];
    }
}
