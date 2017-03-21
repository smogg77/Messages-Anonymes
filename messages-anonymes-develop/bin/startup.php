<?php

if (!is_file('composer.json')) {
    throw new \RuntimeException('This script must be started from the project root folder');
}

$rootDir = __DIR__ . '/..';
require_once __DIR__ . '/../app/bootstrap.php.cache';
use Symfony\Component\Console\Output\OutputInterface;
// reset data
$fs = new \Symfony\Component\Filesystem\Filesystem;
$output = new \Symfony\Component\Console\Output\ConsoleOutput();

/**
 * @param $commands
 * @param \Symfony\Component\Console\Output\ConsoleOutput $output
 *
 * @return boolean
 */
function execute_commands($commands, $output)
{
    foreach($commands as $command) {
        $output->writeln(sprintf('<info>Executing : </info> %s', $command));
        $p = new \Symfony\Component\Process\Process($command);
        $p->setTimeout(null);
        $p->run(function($type, $data) use ($output) {
            $output->write($data, false, OutputInterface::OUTPUT_RAW);
        });
        if (!$p->isSuccessful()) {
            return false;
        }
        $output->writeln("");
    }
    return true;
}
// find out the default php runtime
$bin = 'php';
if (defined('PHP_BINARY')) {
    $bin = PHP_BINARY;
}

$success = execute_commands(array(
    'rm -rf ./app/cache/*',
    $bin . ' ./app/console cache:warmup --env=prod --no-debug',
    $bin . ' ./app/console assetic:dump',
    $bin . ' ./app/console assets:install --symlink web'
), $output);

if (!$success) {
    $output->writeln('<info>An error occured when running a command!</info>');
    exit(1);
}
$output->writeln('<info>Done!</info>');
exit(0);