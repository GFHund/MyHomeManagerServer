<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use App\Model\Entity\Users;
use Cake\Utility\Text;

/**
 * CreateAdminUser command.
 */
class CreateAdminUserCommand extends Command
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
        $password = $io->ask('You have to enter a password');
        $passwordHash = \password_hash($password,\PASSWORD_DEFAULT);
        $adminUser = new Users([
            'id' => Text::uuid(),
            'user_name' => $username,
            'is_admin' => true,
            'password' => $passwordHash
        ]);
        /** @ToDo: Check for double names */
        if($this->Users->save($adminUser)){
            $io->success('User successfully created');
        }
        else{
            $io->error('Could not create User');
        }
    }
}
