<?php

require_once __DIR__ . '/WebDriver/Driver.php';
require_once __DIR__ . '/WebDriver/Command.php';
require_once __DIR__ . '/WebDriver/Exception.php';
require_once __DIR__ . '/WebDriver/NoSeleniumException.php';
require_once __DIR__ . '/WebDriver/Object.php';
require_once __DIR__ . '/WebDriver/Element.php';

/**
 * Class WebDriver
 */

class WebDriver
{

    const ERROR_NO_SUCH_ELEMENT = 7;

    const BUTTON_LEFT   = 0;
    const BUTTON_MIDDLE = 1;
    const BUTTON_RIGHT  = 2;

    /**
     * @var WebDriver_Driver
     */
    protected $driver = null;
    protected $host = '127.0.0.1';
    protected $port = 4444;
    protected $sessionId = null;
    protected $seleniumServerRequestsTimeout=30;

    protected $objectList = array();


    protected $desiredCapabilities = array(
        'browserName' => 'firefox'
    );


    /**
     *
     *
     * @param $host
     * @param int $port
     * @param string $sessionId - use active session,
     */
    public function __construct($host, $port=4444, $sessionId=null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->sessionId = $sessionId;
        $this->driver = new WebDriver_Driver($this->host, $this->port, $this->sessionId);
    }


    public function getDriver()
    {
        return $this->driver;
    }


    /**
     * @return WebDriver_Object_Timeout
     */
    public function timeout()
    {
        if (!isset($this->objectList['timeout'])) {
            $this->objectList['timeout'] = new WebDriver_Object_Timeout($this->driver);
        }
        return $this->objectList['timeout'];
    }


    public function url($url=null)
    {
        if ($url) {
            $this->driver->curl(
                $this->driver->factoryCommand('url', WebDriver_Command::METHOD_POST, ['url' => $url])
            );
        } else {
            $result = $this->driver->curl(
                $this->driver->factoryCommand(
                    'url',
                    WebDriver_Command::METHOD_GET
                )
            );

            return $result['value'];
        }
    }


    public function forward()
    {
        $this->driver->curl(
            $this->driver->factoryCommand('forward', WebDriver_Command::METHOD_POST)
        );
    }


    public function back()
    {
        $this->driver->curl(
            $this->driver->factoryCommand('back', WebDriver_Command::METHOD_POST)
        );
    }


    public function refresh()
    {
        $this->driver->curl(
            $this->driver->factoryCommand('forward', WebDriver_Command::METHOD_POST)
        );
    }


    public function execute($js, $args=[])
    {
        $params = ['script' => $js, 'args' => $args];
        $this->driver->curl(
            $this->driver->factoryCommand('execute', WebDriver_Command::METHOD_POST, $params)
        );
    }


    public function executeAsync($js, $args=[])
    {
        $params = ['script' => $js, 'args' => $args];
        $this->driver->curl(
            $this->driver->factoryCommand('execute_async', WebDriver_Command::METHOD_POST, $params)
        );
    }


    public function screenshot($filename)
    {
        $image = $this->driver->curl(
            $this->driver->factoryCommand('screenshot', WebDriver_Command::METHOD_GET)
        );
        $image = base64_decode($image['value']);
        file_put_contents($filename, $image);
    }


    /**
     * @param $locator
     * @return WebDriver_Element
     */
    public function find($locator)
    {
        return new WebDriver_Element($this, $locator);
    }


    public function buttonDown($btn)
    {
        $command = $this->driver->factoryCommand('buttondown', WebDriver_Command::METHOD_POST, ['button' => $btn]);
        $this->driver->curl($command);
        return $this;

    }


    public function buttonUp($btn)
    {
        $command = $this->driver->factoryCommand('buttonup', WebDriver_Command::METHOD_POST, ['button' => $btn]);
        $this->driver->curl($command);
        return $this;
    }

}
