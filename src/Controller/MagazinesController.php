<?php

namespace App\Controller;

use App\Model\Table\MagazinesTable;
use Cake\Database\Connection;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Text;
use Cake\Datasource\ConnectionManager;
use Cake\Http\Response;

/**
 * @property MagazinesTable $Magazines
 */
class MagazinesController extends AppController{
    public function initialize():void{
        parent::initialize();
        $this->loadComponent('MagazineIndex');
    }

    public function optionsRequest():Response{
        return $this->response;
    }

    public function getMagazineList():Response{
        $magazines = $this->Magazines->find('all',[
            'order' => ['title' => 'DESC']
        ]);
        $aRet = [];
        foreach($magazines as $magazine){
            $aRet[] = [
                'id' => $magazine->get('id'),
                'name' => $magazine->get('title'),
                'url' => $magazine->get('uri')
            ];
        }
        $sRet = json_encode($aRet);
        if($sRet){
            $this->response = $this->response->withStringBody($sRet);
        }
        return $this->response;
    }

    public function updateMagazineData():Response{
        $id = $this->request->getParam('id');
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        try{
            $magazine = $this->Magazines->get($id);
            $magazine->title = $aData['name'];
            //it should have a field for topics
            $this->Magazines->save($magazine);
            $aRet = [
                'id' => $magazine->get('id'),
                'name' => $magazine->get('title'),
                'url' => $magazine->get('uri')
            ];
            $sRet = json_encode($aRet);
            if($sRet){
                $this->response = $this->response->withStringBody($sRet);
            }
            return $this->response;
        }catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            if($ret){
                $this->response = $this->response->withStringBody($ret);
            }
            
            return $this->response;
        }
    }

    public function indexMagazines():Response{
        $sFtpAddress = $this->getSetting('magazine_indexer_ftp_address');
        $sFtpUsername = $this->getSetting('magazine_indexer_ftp_username');
        $sFtpPassword = $this->getSetting('magazine_indexer_ftp_password');
        $sFtpDirectory = $this->getSetting('magazine_indexer_ftp_directory');
        $iCountFiles = $this->MagazineIndex->indexMagazines($sFtpAddress,$sFtpUsername,$sFtpPassword,$sFtpDirectory);
        $this->response = $this->response->withStringBody(json_encode(['read' => $iCountFiles]));
        return $this->response;
    }

    protected function getSetting(string $technicalName):?string{
        /** @var Connection $connection */
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
}