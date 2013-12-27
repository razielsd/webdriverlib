<?php

class WebDriver_Element
{

    /**
     * @var WebDriver_Driver
     */
    protected $driver = null;
    protected $locator = null;
    protected $elementId = null;
    protected $parentId = null;
    /**
     * @var WebDriver
     */
    protected $webDriver = null;


    /**
     * Last button pressed down, saved in self::buttonDown
     */
    protected $state = [
        'buttonDown' => null,
        'tagName' => null,
        'size' => null,
    ];


    public function __construct(WebDriver $webDriver, $locator, $parentId=null)
    {
        $this->webDriver = $webDriver;
        $this->driver = $webDriver->getDriver();
        $this->locator = $locator;
        $this->parentId = $parentId;

    }


    /**
     * Get element Id from webdriver
     *
     * @return int
     * @throws WebDriver_Exception
     */
    protected function getElementId()
    {
        if ($this->elementId === null) {
            $param = $this->parseLocator($this->locator);
            $command = 'element';
            if ($this->parentId !== null) {
                $command = sprintf('element/%d/element', $this->parentId);
            }

            $command = $this->driver->factoryCommand($command, WebDriver_Command::METHOD_POST, $param);
            $result = $this->driver->curl($command);
            if (!isset($result['value']['ELEMENT'])) {
                throw new WebDriver_Exception ("Element not found: " . $this->locator);
            }
            $this->elementId = (int)$result['value']['ELEMENT'];
        }
        return $this->elementId;
    }


    protected function parseLocator($locator)
    {
        $strategyList = array(
            'class' => 'class name',
            'css' => 'css selector',
            'id' => 'id',
            'name' => 'name',
            'link' => 'link text',
            'partial_link' => 'partial link text',
            'tag' => 'tag name',
            'xpath' => 'xpath'
        );
        $info = explode('=', $locator, 2);
        if (count($info) != 2) {
            throw new WebDriver_Exception (
                'Bad locator format, required <strategy>=<search>, locator:' . $locator
            );
        }
        $strategy = $info[0];
        if (!isset($strategyList[$strategy])) {
            throw new WebDriver_Exception ("Unknown locator strategy {$strategy} for locator: " . $locator);
        }
        return ['using' => $strategy, 'value' => $info[1]];
    }


    protected function sendCommand($command, $method, $params=array())
    {
        $command = $this->driver->factoryCommand($command, $method, $params)
            ->param(['id' => $this->getElementId()]);
        return $this->driver->curl($command);
    }

    /**
     * @return WebDriver_Element
     */
    public function click()
    {
        $r = $this->sendCommand('element/:id/click', WebDriver_Command::METHOD_POST);
        return $this;
    }


    /**
     * @param int $xoffset
     * @param int $yoffset
     * @return WebDriver_Element
     */
    public function moveto($xoffset=null, $yoffset=null, WebDriver_Element $element=null)
    {
        $element = ($element)?$element:$this;
        $params = [
            'element' => "{$element->getElementId()}"
        ];
        if ($xoffset !== null) {
            $params['xoffset'] = intval($xoffset);
            $params['yoffset'] = intval($yoffset);
        }
        $this->sendCommand('moveto', WebDriver_Command::METHOD_POST, $params);
        return $this;
    }


    /**
     * @param int $btn
     * @return WebDriver_Element
     */
    public function buttonDown($btn)
    {
        $size = $this->size();
        $this->moveto();
        $this->webDriver->buttonDown($btn);
        $this->state['buttonDown'] = $btn;
        return $this;
    }


    /**
     * @param int $btn
     * @return WebDriver_Element
     */
    public function buttonUp($btn=null)
    {
        $btn = ($btn)?$btn:$this->state['buttonDown'];
        $this->webDriver->buttonUp($btn);
        $this->state['buttonDown'] = null;
        return $this;
    }



    /**
     * @param $xoffset
     * @param $yoffset
     * @return WebDriver_Element
     */
    public function dragAndDrop($xoffset, $yoffset, WebDriver_Element $element=null)
    {
        $e = $element?$element:$this;
        $size = $e->size();
        $this->buttonDown(WebDriver::BUTTON_LEFT)
            ->moveto($xoffset + ceil($size['width']/2), $yoffset + ceil($size['height']/2), $element)
            ->buttonUp();
        return $this;
    }

    /**
     * Submit form element
     *
     * @return null
     */
    public function submit()
    {
        $this->sendCommand('element/:id/submit', WebDriver_Command::METHOD_POST);
    }


    public function text()
    {
        $result = $this->sendCommand('element/:id/text', WebDriver_Command::METHOD_GET);
        return $result['value'];
    }


    /**
     * Set element value
     *
     * @param $value
     * @return WebDriver_Element|string
     */
    public function value($value=null)
    {
        $tagName = $this->tagName();
        if ($value !== null) {
            switch ($tagName) {
                case 'input':
                case 'textarea':
                    $params = ['value' => ["{$value}"]];
                    $this->sendCommand('element/:id/value', WebDriver_Command::METHOD_POST, $params);
                    break;
                case 'select':
                    $option = $this->child(sprintf("xpath=descendant::option[@value='%s']", $value));
                    $option->click();
                    break;
            }
            return $this;
        } else {
            $value = null;
            switch ($tagName) {
                case 'input':
                case 'textarea':
                    $value = $this->attribute('value');
                    break;
                case 'select':
                    $value = $this->sendCommand(
                        'element',
                        WebDriver_Command::METHOD_POST,
                        $this->parseLocator($this->locator)
                    );
                    $optionElementId = $value['value']['ELEMENT'];
                    $result = $this->sendCommand(
                        sprintf('element/%d/attribute/value', $optionElementId),
                        WebDriver_Command::METHOD_GET
                    );
                    $value = $result['value'];
                    break;
                default:
                    $value = $this->text();
            }
            return $value;
        }
    }


    /**
     * Clear textarea/input field, for select field choose first option
     *
     * @return WebDriver_Element
     */
    public function clear()
    {
        $tagName = $this->tagName();
        switch ($tagName) {
            case 'input':
            case 'textarea':
                $this->sendCommand('element/:id/clear', WebDriver_Command::METHOD_POST);
                break;
            case 'select':
                $option = $this->child("xpath=descendant::option[1]");
                $option->click();
                break;
        }
        return $this;
    }


    /**
     * Get element tag name
     *
     * @return mixed
     */
    public function tagName()
    {
        if (!$this->state['tagName']) {
            $result = $this->sendCommand('element/:id/name', WebDriver_Command::METHOD_GET);
            $this->state['tagName'] = strtolower($result['value']);
        }
        return $this->state['tagName'];
    }


    /**
     * Get element attribute value
     *
     * @param $name
     * @return string
     */
    public function attribute($attrName)
    {
        $result = $this->sendCommand('element/:id/attribute/' . $attrName, WebDriver_Command::METHOD_GET);
        return $result['value'];
    }


    public function child($locator)
    {
        return new self($this->webDriver, $locator, $this->getElementId());
    }


    public function enabled()
    {
        $result = $this->sendCommand('element/:id/enabled', WebDriver_Command::METHOD_GET);
        return (bool)$result['value'];
    }


    public function size()
    {
        //if ($this->state['size'] === null) {
            $result = $this->sendCommand('element/:id/size', WebDriver_Command::METHOD_GET);
            $value = $result['value'];
            //$this->state['size'] =
            return ['width' => $value['width'], 'height' => $value['height']];
        //}
        //return $this->state['size'];
    }


    /**
     * Get element upper-left corner of the page
     */
    public function location()
    {
        $result = $this->sendCommand('element/:id/location', WebDriver_Command::METHOD_GET);
        $value = $result['value'];
        return ['x' => $value['x'], 'y' => $value['y']];
    }


    public function isPresent()
    {
        try {
            $this->getElementId();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }


    public function isDisplayed()
    {
        $result = $this->sendCommand('element/:id/displayed', WebDriver_Command::METHOD_GET);
        return (bool)$result['value'];
    }


    public function waitPresent()
    {
        try {
            $this->getElementId();
            return $this;
        } catch (WebDriver_Exception $e) {
            throw $e;
        }
        return $this;
    }


    public function waitDisplayed()
    {
        for ($i=0;$i<30;$i++) {
            if ($this->isDisplayed()) {
                return $this;
            }
            sleep(1);
        }
        throw new WebDriver_Exception ("Element " . $this->locator . ' not displayed after timeout');
    }


    /*
    session/:sessionId/keys
    -------------


    /session/:sessionId/element/:id/attribute/:name
    equals
    location | location_in_view
    css
    */
}
