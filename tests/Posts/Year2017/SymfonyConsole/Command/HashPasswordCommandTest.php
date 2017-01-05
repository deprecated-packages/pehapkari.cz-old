<?php

declare(strict_types=1);

namespace Pehapkari\Website\Tests\Posts\Year2016\SymfonyConsole\Command;

use Pehapkari\Website\Posts\Year2016\SymfonyConsole\Command\HashPasswordCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class HashPasswordCommandTest extends TestCase
{
    public function test()
    {
        $application = new Application();
        $application->setAutoExit(false); // required for testing output
        $application->add(new HashPasswordCommand());

        // same as when you run "bin/console hash-password Y2Kheslo123"
        $input = new StringInput('hash-password Y2Kheslo123');
        $output = new BufferedOutput();

        $result = $application->run($input, $output);
        $this->assertSame(0, $result); // 0 = success, sth else = fail
        $this->assertStringStartsWith('Your hashed password is: $2y$10$', $output->fetch());
    }
}
