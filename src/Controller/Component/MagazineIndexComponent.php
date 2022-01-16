<?php
declare(strict_types=1);
namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

class MagazineIndexComponent extends Component{
    
    protected $modelClass = 'Magazines';

    public function indexMagazines(string $sFtpAddress,string $sFtpUsername, string $sFtpPassword,string $sFtpDirectory){
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