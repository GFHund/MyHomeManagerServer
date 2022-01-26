<?php
declare(strict_types=1);
namespace App\Controller\Component;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Utility\Text;

class MagazineIndexComponent extends Component{
    
    protected $modelClass = 'Magazines';

    public function indexMagazines(string $sFtpAddress,string $sFtpUsername, string $sFtpPassword,string $sFtpDirectory){
        $oFtpHandle = ftp_connect($sFtpAddress);
        if($oFtpHandle === FALSE){
            return;
        }
        
        $iCountFiles = 0;
        if(ftp_login($oFtpHandle,$sFtpUsername,$sFtpPassword)){
            ftp_pasv($oFtpHandle,true);
            $changeResult = ftp_chdir($oFtpHandle,$sFtpDirectory);
            if($changeResult === FALSE){
                ftp_close($oFtpHandle);
                return;
            }
            $aFiles = ftp_nlist($oFtpHandle,'.');
            foreach($aFiles as $sFile){
                $this->insertNotIndexedFiles($sFile,$sFtpAddress);
                $iCountFiles++;
            }
            ftp_close($oFtpHandle);
        }
        return $iCountFiles;
    }
    
    protected function insertNotIndexedFiles(string $sFilename,string $url){
        $oMagazinesTable = $this->getController()->getTableLocator()->get('Magazines');
        $magazine = $oMagazinesTable->find('all')->where(['title =' => $sFilename]);
        $numResults = $magazine->count();
        if($numResults <= 0){
            $newMagazine = $oMagazinesTable->newEmptyEntity();
            $newMagazine->id = Text::uuid();
            $newMagazine->title = $sFilename;
            $newMagazine->uri = 'http://'.$url.'/index.php?ctName='.$sFilename;
            $newMagazine->topics = '';
            if($oMagazinesTable->save($newMagazine)){
                return;
            }
            
        } else {
            foreach($magazine->all() as $mag){
                $mag->uri = 'http://'.$url.'/index.php?ctName='.$sFilename;
                $oMagazinesTable->save($mag);
            }
        }
    }
}