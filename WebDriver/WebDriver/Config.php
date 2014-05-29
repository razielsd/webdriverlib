<?php
class WebDriver_Config
{
    /** trim text node value for WebDriver_Element::value() */
    const TRIM_TEXT_NODE_VALUE = 'trim_text_node_value';

    protected $config = [
        self::TRIM_TEXT_NODE_VALUE => true,
    ];


    public function get($paramName)
    {
        if (!isset($this->config[$paramName])) {
            throw new WebDriver_Exception('Unknown configure parameter: ' . $paramName);
        }
        return $this->config[$paramName];
    }


    public function set($paramName, $value)
    {
        $this->config[$paramName] = $value;
    }
}
