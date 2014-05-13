<?php
class WebDriver_Object_Window extends WebDriver_Object
{
    protected $windowHandle = 'current';


    /**
     * Change focus to another window. The window to change focus to may be specified by its server assigned window handle,
     * or by the value of its name attribute.
     * !!NEVER TESTED
     *
     * @param $windowName
     */
    public function focus($windowName)
    {
        $params = ['name' => $windowName];
        $command = $this->driver->factoryCommand(
            'window',
            WebDriver_Command::METHOD_POST,
            $params
        );
        $this->driver->curl($command)['value'];
    }


    /**
     * Close the current window.
     * !!NEVER TESTED
     */
    public function close()
    {
        $command = $this->driver->factoryCommand(
            'window',
            WebDriver_Command::METHOD_POST
        );
        return $this->driver->curl($command)['value'];
    }


    /**
     * Set window handle for window
     *
     * @param $windowHandle
     */
    public function setWindowHandle($windowHandle)
    {
        $this->windowHandle = $windowHandle;
    }


    /**
     * Get the size of the specified window.
     *
     * @return array
     */
    public function getSize()
    {
        $command = $this->driver->factoryCommand(
            'window/' . $this->windowHandle . '/size',
            WebDriver_Command::METHOD_GET
        );
        $value = $this->driver->curl($command)['value'];
        return ['height' => $value['height'], 'width' => $value['width']];
    }


    /**
     * Change the size of the specified window.
     *
     * @param $width
     * @param $height
     * @return mixed
     */
    public function setSize($width, $height)
    {
        $params = [
            'width' => intval($width),
            'height' => intval($height)
        ];
        $command = $this->driver->factoryCommand(
            'window/' . $this->windowHandle . '/size',
            WebDriver_Command::METHOD_POST,
            $params
        );
        return $this->driver->curl($command)['value'];
    }


    /**
     * Maximize the specified window if not already maximized.
     *
     * @return mixed
     */
    public function maximize()
    {
        $command = $this->driver->factoryCommand(
            'window/' . $this->windowHandle . '/maximize',
            WebDriver_Command::METHOD_POST
        );
        return $this->driver->curl($command)['value'];
    }


    /**
     * Get the position of the specified window.
     *
     * @return array
     */
    public function getPosition()
    {
        $command = $this->driver->factoryCommand(
            'window/' . $this->windowHandle . '/position',
            WebDriver_Command::METHOD_GET
        );
        $value = $this->driver->curl($command)['value'];
        return ['left' => $value['x'], 'top' => $value['y']];

    }


    /**
     * Change the position of the specified window.
     *
     * @param $left
     * @param $top
     * @return mixed
     */
    public function setPosition($left, $top)
    {
        $params = [
            'x' => intval($left),
            'y' => intval($top)
        ];
        $command = $this->driver->factoryCommand(
            'window/' . $this->windowHandle . '/position',
            WebDriver_Command::METHOD_POST,
            $params
        );
        return $this->driver->curl($command)['value'];
    }
}
