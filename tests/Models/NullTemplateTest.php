<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Helper\tests\Models;

use Modules\Helper\Models\NullTemplate;

/**
 * @internal
 */
final class NullTemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Helper\Models\NullTemplate
     * @group framework
     */
    public function testNull() : void
    {
        self::assertInstanceOf('\Modules\Helper\Models\Template', new NullTemplate());
    }

    /**
     * @covers Modules\Helper\Models\NullTemplate
     * @group framework
     */
    public function testId() : void
    {
        $null = new NullTemplate(2);
        self::assertEquals(2, $null->getId());
    }
}
