<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Datasource\ConnectionManager;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Utility\Text;

/**
 * CreateAdminUser command.
 */
class IndexMagazinesCommand extends Command
{
    protected $modelClass = 'Magazines';
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
        $parser->addOption('ftp_address',[
            'help' => 'ftp address of the place to index',
            'required' => false
        ])
        ->addOption('username',[
            'help' => 'username of the ftp server'
        ])
        ->addOption('password',[
            'help' => 'username of the ftp server'
        ])
        ->addOption('directory',[
            'help' => 'directory which the indexed files lies in'
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
        $sFtpAddress = $args->getOption('ftp_address');
        $sFtpUsername = $args->getOption('username');
        $sFtpPassword = $args->getOption('password');
        $sFtpDirectory = $args->getOption('directory');

        if($sFtpAddress == null){
            $sFtpAddress = $this->getSetting('magazine_indexer_ftp_address');
            $sFtpUsername = $this->getSetting('magazine_indexer_ftp_username');
            $sFtpPassword = $this->getSetting('magazine_indexer_ftp_password');
            $sFtpDirectory = $this->getSetting('magazine_indexer_ftp_directory');
        }
        if(empty($sFtpAddress)){
            $io->error('ftp address is not set in the settings or as argument');
            return;
        }

        $oFtpHandle = ftp_connect($sFtpAddress);
        if($oFtpHandle === FALSE){
            $io->error('Error at connecting to ftp');
            return;
        }
        $io->out('username: '.$sFtpUsername.' password: '.$sFtpPassword);
        if(ftp_login($oFtpHandle,$sFtpUsername,$sFtpPassword)){
            ftp_pasv($oFtpHandle,true);
            $changeResult = ftp_chdir($oFtpHandle,$sFtpDirectory);
            if($changeResult === FALSE){
                ftp_close($oFtpHandle);
                $io->error('Failed to change directory');
                return;
            }
            $aFiles = ftp_nlist($oFtpHandle,'.');
            foreach($aFiles as $sFile){
                $this->insertNotIndexedFiles($sFile,$url,$io);
            }
            ftp_close($oFtpHandle);
        }
    }
    protected function getSetting(string $technicalName):?string{
        $connection = ConnectionManager::get('default');
        $settingProperty = $connection->execute('SELECT value_type, value_id FROM settings WHERE technical_name = "'.$technicalName.'"')->fetchAll('assoc');
        if(count($settingProperty) <= 0){
            return null;
        }
        $database = 'setting_'.$settingProperty[0]['value_type'];
        $settingVal = $connection->execute('SELECT setting_value FROM '.$database.' WHERE id = "'.$settingProperty[0]['value_id'].'"')->fetchAll('assoc');
        if(count($settingVal) <= 0){
            return null;
        }
        return $settingVal[0]['setting_value'];
    }
    protected function insertNotIndexedFiles(string $sFilename,string $url,ConsoleIo $io){
        $magazine = $this->Magazines->find('all')->where(['uri =' => $sFilename]);
        $numResults = $magazine->count();
        if($numResults <= 0){
            $newMagazine = $this->Magazines->newEmptyEntity();
            $newMagazine->id = Text::uuid();
            $newMagazine->title = $sFilename;
            $newMagazine->uri = $url.'/index.php?ctName='.$sFilename;
            $newMagazine->topics = '';
            if($this->Magazines->save($newMagazine)){
                return;
            }
            $io->error('Could not save entity');
        } else {
            foreach($magazine->all() as $mag){
                $mag->uri = $url.'/index.php?ctName='.$sFilename;
                $this->Magazines->save($mag);
            }
        }
    }
}