<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\ConfirmOrderCommand;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Command\ConfirmOrderCommand Test Case
 *
 * @uses \App\Command\ConfirmOrderCommand
 */
class ConfirmOrderCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->useCommandRunner();
    }
}
