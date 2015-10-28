<?php
/**
 * Spiral: PSR7 aware cURL client (https://github.com/juliangut/spiral)
 *
 * @link https://github.com/juliangut/spiral for the canonical source repository
 * @license https://raw.githubusercontent.com/juliangut/spiral/master/LICENSE
 */

namespace Jgut\Spiral\Tests\Option;

use Jgut\Spiral\Option\AutoReferer;

/**
 * @cover Jgut\Spiral\Option\OptionBoolean
 */
class OptionBooleanTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @cover Jgut\Spiral\Option\OptionBoolean::setValue
     */
    public function testAccessors()
    {
        $option = new AutoReferer(true);

        $this->assertEquals(CURLOPT_AUTOREFERER, $option->getOption());
        $this->assertTrue($option->getValue());

        $option = new AutoReferer(false);
        $this->assertFalse($option->getValue());
    }
}