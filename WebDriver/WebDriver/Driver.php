<?php
class WebDriver_Driver
{
    protected $host = '127.0.0.1';
    protected $port = 4444;
    protected $seleniumServerRequestsTimeout=60;


    protected $desiredCapabilities = array(
        'browserName' => 'firefox'
    );

    protected $sessionId = null;
    protected $serverUrl = null;
    protected $isCloseSession = true;


    public function __construct($host, $port=4444, $sessionId=null)
    {
        $this->host = $host;
        $this->port = $port;
        $this->serverUrl = 'http://' . $this->host . ':' . $this->port . '/wd/hub/';
        //$this->serverUrl = 'http://test.dev/proxy/afateev/selenium/wd/hub/';
        if (!$sessionId) {
            $result = $this->curl(
                $this->factoryCommand(
                    'session',
                    WebDriver_Command::METHOD_POST,
                    ['desiredCapabilities' => $this->desiredCapabilities]
                )
            );
            $sessionId = $result['sessionId'];
        } else {
            $this->isCloseSession = false;
        }
        $this->sessionId = $sessionId;
    }


    public function __destruct()
    {
        if ($this->sessionId && $this->isCloseSession) {
            $command = $this->factoryCommand(
                'session/' . $this->sessionId,
                WebDriver_Command::METHOD_DELETE
            )->withoutSession();
            $this->curl($command);
        }
    }


    public function factoryCommand($command, $method, $params=array())
    {
        $command = new WebDriver_Command($command, $method, $params);
        $command->addSession($this->serverUrl, $this->sessionId);
        return $command;
    }


    public function curl(WebDriver_Command $command)
    {
        $url = $command->getUrl();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->seleniumServerRequestsTimeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-type: application/json;charset=UTF-8',
                'Accept: application/json;charset=UTF-8'
            ));

        $method = $command->getMethod();
        $params = $command->getParameters();
        if ($method === WebDriver_Command::METHOD_POST) {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            if ($params && is_array($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            }
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        } else if ($method === WebDriver_Command::METHOD_DELETE) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');        }

        $rawResponse = trim(curl_exec($ch));

        if (curl_errno($ch)) {
            throw new WebDriver_NoSeleniumException(
                'Error connection[' . curl_errno($ch) .'] to ' .
                $url  . ': ' . curl_error($ch)
            );
        }
        $info = curl_getinfo($ch);
        if ($info['http_code'] == 0) {
            throw new WebDriver_NoSeleniumException('No response or broken');
        }
        if ($info['http_code'] == 404) {
            throw new WebDriver_Exception("The command $url is not recognized by the server.");
        }
        curl_close($ch);
        $content = json_decode($rawResponse, TRUE);
        if ($info['http_code'] == 500) {
            if (isset($content['value']['message'])) {
                $message = $content['value']['message'];
            } else {
                $message = "Internal server error while executing $method request at $url. Response: " . var_export($content, TRUE);
            }
            throw new WebDriver_Exception($message);
        }
        $json = json_decode($rawResponse, true);

        if (is_array($json) && array_key_exists('status', $json)) {
            $this->checkResponse($json);
        }
        return $json;
    }


    protected function checkResponse($json)
    {
        $status = $json['status'];

        $statusList = [
            //0 => 'The command executed successfully.',
            6 => 'A session is either terminated or not started',
            7 => 'NoSuchElement - An element could not be located on the page using the given search parameters.',
            8 => 'NoSuchFrame - A request to switch to a frame could not be satisfied because the frame could not be found.',
            9 => 'UnknownCommand - The requested resource could not be found, or a request was received using an HTTP method that is not supported by the mapped resource.',
            10 => 'StaleElementReference - An element command failed because the referenced element is no longer attached to the DOM.',
            11 => 'ElementNotVisible - An element command could not be completed because the element is not visible on the page.',
            12 => 'InvalidElementState - An element command could not be completed because the element is in an invalid state (e.g. attempting to click a disabled element).',
            13 => 'UnknownError - An unknown server-side error occurred while processing the command.',
            15 => 'ElementIsNotSelectable - An attempt was made to select an element that cannot be selected.',
            17 => 'JavaScriptError - An error occurred while executing user supplied JavaScript.',
            19 => 'XPathLookupError - An error occurred while searching for an element by XPath.',
            21 => 'Timeout - An operation did not complete before its timeout expired.',
            23 => 'NoSuchWindow - A request to switch to a different window could not be satisfied because the window could not be found.',
            24 => 'InvalidCookieDomain - An illegal attempt was made to set a cookie under a different domain than the current page.',
            25 => 'UnableToSetCookie - A request to set a cookie\'s value could not be satisfied.',
            26 => 'UnexpectedAlertOpen - A modal dialog was open, blocking this operation',
            27 => 'NoAlertOpenError - An attempt was made to operate on a modal dialog when one was not open.',
            28 => 'ScriptTimeout - A script did not complete before its timeout expired.',
            29 => 'InvalidElementCoordinates - The coordinates provided to an interactions operation are invalid.',
            30 => 'IMENotAvailable - IME was not available.',
            31 => 'IMEEngineActivationFailed - An IME engine could not be started.',
            32 => 'InvalidSelector - Argument was an invalid selector (e.g. XPath/CSS).',
            33 => 'SessionNotCreatedException - A new session could not be created.',
            34 => 'MoveTargetOutOfBounds - Target provided for a move action is out of bounds. ',
        ];

        if ($status != 0) {
            $errorMessage = 'Unknown error status: ' . $status;
            $errorMessage = (isset($statusList[$status]))?$statusList[$status]:$errorMessage;
            throw new WebDriver_Exception($errorMessage, $status);
        }
    }




}
