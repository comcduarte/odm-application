<?php
declare(strict_types=1);

namespace Session\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Help extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $output->writeln('Hello World');
        return 0;
    }
}