webdriverlib
--


About
--

WebDriverLib - simple usage api for JSON Wire Protocol such as Selenium WebDriver.


Example
---
```
//connect to Selenium
self::$driver = new WebDriver('localhost', 4444);
//set select field value
$driver->find('xpath=//select')->value(555);
//set input field value
$driver->find('xpath=//input')->value(555);
```
