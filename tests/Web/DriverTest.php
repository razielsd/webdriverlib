<?php
/**
 * @readme
 * Modify Web_DriverTest::setUpBeforeClass
 */

class Web_DriverTest extends Testing_TestCase
{

    /**
     * @var WebDriver
     */
    protected static $driver = null;
    /**
     * Url for test page in tests/www/webdrivertest.html
     * @var string
     */
    protected static $testUrl = 'http://images.dev/webdrivertest.html';

    protected $backupStaticAttributesBlacklist = array(
        'Web_DriverTest' => array('driver')
    );


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        //selenium host
        self::$driver = new WebDriver('192.168.56.103', 4444);
        self::$driver->url(self::$testUrl);
    }


    public function testChild()
    {
        $childTwo = self::$driver->find("xpath=//div[@id='childTwo']");

        $this->assertEquals(
            'Child Two',
            $childTwo->child("xpath=div[@class='child']")->text(),
            "Error select child element"
        );
    }


    public function testUrl()
    {
        $url = self::$driver->url();
        $this->assertEquals(self::$testUrl, $url, "Wrong test url");
    }


    public function testClick()
    {
        $this->assertEquals(
            1,
            self::$driver->find('id=e_button')->click()->value(),
            "Error on test mouse click"
        );
    }


    public function testTagName()
    {
        $this->assertEquals(
            'input',
            self::$driver->find('id=e_button')->tagName(),
            "Error getting element tag name"
        );
    }


    public function testDragAndDrop()
    {
        $locator = "xpath=//div[@id='e_draggable']";
        $drag = self::$driver->find($locator);
        $beforeLoc = $drag->location();
        $drag->buttonDown(WebDriver::BUTTON_LEFT);
        $shift = ['x' => 50, 'y' => 30];
        $drag->dragAndDrop($shift['x'], $shift['y']);
        $afterLoc = $drag->location();
        $this->assertEquals(
            $shift['x'], $afterLoc['x'] - $beforeLoc['x'], 'Wrong X-position after drag and drop'
        );
        $this->assertEquals(
            $shift['y'], $afterLoc['y'] - $beforeLoc['y'], 'Wrong Y-position after drag and drop'
        );
        //back to start position
        $drag->dragAndDrop(-$shift['x'], -$shift['y']);
        $afterLoc = $drag->location();
        $this->assertEquals(
            $beforeLoc['x'], $afterLoc['x'], 'Wrong X-position after drag and drop back'
        );
        $this->assertEquals(
            $beforeLoc['y'], $afterLoc['y'], 'Wrong Y-position after drag and drop back'
        );

    }


    public function testValue()
    {
        $txt = 'ВебДрайвер WebDriver: ' . date('Y-m-d H:i:s');
        $this->assertEquals(
            $txt,
            self::$driver->find('id=e_string')->value($txt)->value(),
            'Error set/get value for input'
        );
        $this->assertEquals(
            $txt,
            self::$driver->find('id=e_textarea')->value($txt)->value(),
            'Error set/get value for textarea'
        );

        $this->assertEquals(
            2,
            self::$driver->find('id=e_select')->value(2)->value(),
            'Error set/get value for select/option'
        );
        $this->assertEquals(
            3,
            self::$driver->find('id=e_select_opt')->value(3)->value(),
            'Error set/get value for select/optgroup/option'
        );
    }


    public function testClear()
    {
        $txt = 'WebDriver: ' . date('Y-m-d H:i:s');
        $this->assertEmpty(
            self::$driver->find('id=e_string')->value($txt)->clear()->value(),
            'Error clear input field'
        );

        $this->assertEmpty(
            self::$driver->find('id=e_textarea')->value($txt)->clear()->value(),
            'Error clear input field'
        );

        $this->assertEquals(
            1,
            self::$driver->find('id=e_select')->value(2)->clear()->value(),
            'Error clear select field (choose first element)'
        );

        $this->assertEquals(
            1,
            self::$driver->find('id=e_select_opt')->value(2)->clear()->value(),
            'Error clear select field (choose first element)'
        );
    }


    public function testScreenshot()
    {
        $filename = '/tmp/image' . time() . '_' . mt_rand(1000, 9999) . '.png';
        self::$driver->screenshot($filename);
        $image = getimagesize($filename);
        $this->assertNotEmpty($image, "Error create screenshot: " . $filename);
        $this->assertArrayHasKey(0, $image, "Bad image format: " . $filename);
        $this->assertGreaterThan(1, $image[0], "Bad image format: " . $filename);
        @unlink($filename
        );
    }

    public function testEnabled()
    {
        $this->assertFalse(
            self::$driver->find('id=el_disabled')->enabled(),
            'Element id=el_disabled must be disabled'
        );
        $this->assertTrue(
            self::$driver->find('id=el_enabled')->enabled(),
            'Element id=el_enabled must be enabled'
        );
    }


    public function testSize()
    {
        $size = self::$driver->find('id=fixed_size')->size();
        $this->assertArrayHasKey('width', $size, 'No key width for element size');
        $this->assertArrayHasKey('height', $size, 'No key height for element size');
        $this->assertEquals(142, $size['width'], 'Bad size width');
        $this->assertEquals(102, $size['height'], 'Bad size height');
    }


    public function testDisplayed()
    {
        $this->assertFalse(
            self::$driver->find('id=el_hidden')->isDisplayed(),
            'Element id=el_hidden must be hidden'
        );
        $this->assertTrue(
            self::$driver->find('id=el_enabled')->isDisplayed(),
            'Element id=el_enabled must be displayed'
        );
    }
}
