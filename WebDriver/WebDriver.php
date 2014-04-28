<?php

require_once __DIR__ . '/WebDriver/Driver.php';
require_once __DIR__ . '/WebDriver/Command.php';
require_once __DIR__ . '/WebDriver/Exception.php';
require_once __DIR__ . '/WebDriver/NoSeleniumException.php';
require_once __DIR__ . '/WebDriver/Object.php';
require_once __DIR__ . '/WebDriver/Object/Alert.php';
require_once __DIR__ . '/WebDriver/Object/Timeout.php';
require_once __DIR__ . '/WebDriver/Object/Cookie.php';
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
    public function __construct($host, $port=4444, $desiredCapabilities=null, $sessionId=null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->sessionId = $sessionId;
        $desiredCapabilities = (empty($desiredCapabilities))?$this->desiredCapabilities:$desiredCapabilities;
        $this->driver = new WebDriver_Driver($this->host, $this->port, $desiredCapabilities, $this->sessionId);
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


    /**
     * @return WebDriver_Object_Alert
     */
    public function alert()
    {
        if (!isset($this->objectList['alert'])) {
            $this->objectList['alert'] = new WebDriver_Object_Alert($this->driver);
        }
        return $this->objectList['alert'];
    }


    /**
     * @return WebDriver_Object_Cookie
     */
    public function cookie()
    {
        if (!isset($this->objectList['cookie'])) {
            $this->objectList['cookie'] = new WebDriver_Object_Cookie($this->driver);
        }
        return $this->objectList['cookie'];
    }


    /**
     * Retrieve/Navigate the URL of the current page.
     *
     * @param null $url
     * @return mixed
     */
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


    /**
     * Navigate forwards in the browser history, if possible.
     */
    public function forward()
    {
        $this->driver->curl(
            $this->driver->factoryCommand('forward', WebDriver_Command::METHOD_POST)
        );
    }


    /**
     * Navigate backwards in the browser history, if possible.
     */
    public function back()
    {
        $this->driver->curl(
            $this->driver->factoryCommand('back', WebDriver_Command::METHOD_POST)
        );
    }


    /**
     * Refresh the current page.
     */
    public function refresh()
    {
        $this->driver->curl(
            $this->driver->factoryCommand('forward', WebDriver_Command::METHOD_POST)
        );
    }


    /**
     * Inject a snippet of JavaScript into the page for execution in the context of the currently selected frame
     *
     * @param string $js
     * @param array $args
     * @return mixed
     */
    public function execute($js, $args=[])
    {
        $params = ['script' => $js, 'args' => $args];
        $result = $this->driver->curl(
            $this->driver->factoryCommand('execute', WebDriver_Command::METHOD_POST, $params)
        );
        return isset($result['value'])?$result['value']:false;
    }


    public function executeAsync($js, $args=[])
    {
        $params = ['script' => $js, 'args' => $args];
        $result = $this->driver->curl(
            $this->driver->factoryCommand('execute_async', WebDriver_Command::METHOD_POST, $params)
        );
        return $result['value'];
    }


    /**
     * Save screenshot of the current page to file $filename
     *
     * @param $filename
     */
    public function screenshot($filename)
    {
        $image = $this->driver->curl(
            $this->driver->factoryCommand('screenshot', WebDriver_Command::METHOD_GET)
        );
        $image = base64_decode($image['value']);
        file_put_contents($filename, $image);
    }


    /**
     * Get page element using locator
     *
     * @param $locator
     * @return WebDriver_Element
     */
    public function find($locator)
    {
        return new WebDriver_Element($this, $locator);
    }


    /**
     * Click and hold the left mouse button (at the coordinates set by the last moveto command).
     *
     * @param $btn - const WebDriver::BUTTON_*
     */
    public function buttonDown($btn)
    {
        $command = $this->driver->factoryCommand('buttondown', WebDriver_Command::METHOD_POST, ['button' => $btn]);
        $this->driver->curl($command);
    }


    /**
     * Releases the mouse button previously held (where the mouse is currently at).
     * Must be called once for every buttondown command issued.
     *
     * @param $btn
     */
    public function buttonUp($btn)
    {
        $command = $this->driver->factoryCommand('buttonup', WebDriver_Command::METHOD_POST, ['button' => $btn]);
        $this->driver->curl($command);
    }


    /**
     * Get the current page source.
     *
     * @return string
     */
    public function source()
    {
        $command = $this->driver->factoryCommand('source', WebDriver_Command::METHOD_GET);
        $result = $this->driver->curl($command);
        return $result['value'];
    }


    /**
     * Get the current page title.
     *
     * @return string
     */
    public function title()
    {
        $command = $this->driver->factoryCommand('title', WebDriver_Command::METHOD_GET);
        $result = $this->driver->curl($command);
        return $result['value'];
    }

}
