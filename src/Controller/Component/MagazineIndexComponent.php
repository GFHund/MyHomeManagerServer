<?php
declare(strict_types=1);
namespace App\Controller\Component;

use App\Model\Table\MagazinesTable;
use Cake\Console\ConsoleIo;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Utility\Text;


/**
 * @property MagazinesTable $Magazines
 */
class MagazineIndexComponent extends Component{

    protected string $modelClass = 'Magazines';

    public function indexMagazines(string $sFtpAddress,string $sFtpUsername, string $sFtpPassword,string $sFtpDirectory):int{
        $oFtpHandle = ftp_connect($sFtpAddress);
        if($oFtpHandle === FALSE){
            //$io->error('Error at connecting to ftp');
            return 0;
        }

        $iCountFiles = 0;
        if(ftp_login($oFtpHandle,$sFtpUsername,$sFtpPassword)){
            ftp_pasv($oFtpHandle,true);
            $changeResult = ftp_chdir($oFtpHandle,$sFtpDirectory);
            if($changeResult === FALSE){
                ftp_close($oFtpHandle);
                //$io->error('Failed to change directory');
                return $iCountFiles;
            }
            $aFiles = ftp_nlist($oFtpHandle,'.');
            /*
            foreach($aFiles as $sFile){
                $this->insertNotIndexedFiles($sFile,$url);
            }
                */
            ftp_close($oFtpHandle);
        }
        return $iCountFiles;
    }

    protected function insertNotIndexedFiles(string $sFilename,string $url/*,ConsoleIo $io*/):void{
        $magazine = $this->Magazines->find('all')->where(['uri =' => $sFilename]);
        $numResults = $magazine->count();
        if($numResults <= 0){
            $newMagazine = $this->Magazines->newEmptyEntity();
            $newMagazine->set('id', Text::uuid());
            $newMagazine->set('title', $sFilename);
            $newMagazine->set('uri', $url.'/index.php?ctName='.$sFilename);
            $newMagazine->set('topics', '');
            if($this->Magazines->save($newMagazine)){
                return;
            }
            //$io->error('Could not save entity');
        } else {
            foreach($magazine->all() as $mag){
                $mag->uri = $url.'/index.php?ctName='.$sFilename;
                $this->Magazines->save($mag);
            }
        }
    }
}
