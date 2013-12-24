<?php
class WebDriver_Object_Timeout extends WebDriver_Object
{

    /**
     * Set timeouts
     *
     * @param array $timeout
     * @return mixed
     */
    public function setAll($timeout)
    {
        $command = $this->driver->factoryCommand('timeouts', WebDriver_Command::METHOD_POST, $timeout);
        return $this->driver->curl($command);
    }


    public function asyncScript($time)
    {
        $param = ['ms' => $time];
        $command = $this->driver->factoryCommand('timeouts/async_script', WebDriver_Command::METHOD_POST, $param);
        return $this->driver->curl($command);
    }


    public function implicitWait($time)
    {
        $param = ['ms' => $time];
        $command = $this->driver->factoryCommand('timeouts/implicit_wait', WebDriver_Command::METHOD_POST, $param);
        return $this->driver->curl($command);
    }
}
