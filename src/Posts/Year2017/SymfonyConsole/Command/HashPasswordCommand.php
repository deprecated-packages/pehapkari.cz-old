<?php declare(strict_types=1);

namespace Pehapkari\Website\Posts\Year2017\SymfonyConsole\Command;

use Nette\Security\Passwords;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class HashPasswordCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('hash-password');
        $this->setDescription('Hashes provided password with BCRYPT and prints to output.');
        $this->addArgument('password', InputArgument::REQUIRED, 'Password to be hashed.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $password = $input->getArgument('password');

        $hashedPassword = Passwords::hash($password);

        $output->writeln(sprintf('Your hashed password is: <info>%s</info>', $hashedPassword));

        return 0;
    }
}
