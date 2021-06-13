<?php
declare(strict_types=1);

namespace App\Command;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
/**
 * CreateAdminUser command.
 */
class SetupHomeManagerCommand extends Command
{
    protected $modelClass = 'Users';
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->addArgument('name',[
            'help' => 'username of the admin user',
            'required' => true
        ]);
        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $username = $args->getArgument('name');

        // You can pass an array of CLI options and arguments.
        $this->executeCommand(\Migrations\Command\MigrationsMigrateCommand::class);

        // You can pass an array of CLI options and arguments.
        $this->executeCommand(CreateAdminUserCommand::class, [$username]);
    }
}