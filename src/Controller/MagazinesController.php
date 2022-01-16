<?php

namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Text;
use Cake\Datasource\ConnectionManager;

class MagazinesController extends AppController{
    public function initialize():void{
        parent::initialize();
        $this->loadComponent('MagazineIndex');
    }

    public function optionsRequest(){
        return $this->response;
    }

    public function getMagazineList(){
        $magazines = $this->Magazines->find('all',[
            'order' => ['title' => 'DESC']
        ]);
        $aRet = [];
        foreach($magazines as $magazine){
            $aRet[] = [
                'id' => $magazine->id,
                'name' => $magazine->title,
                'url' => $magazine->uri
            ];
        }
        $sRet = json_encode($aRet);
        $this->response = $this->response->withStringBody($sRet);
        return $this->response;
    }

    public function updateMagazineData(){
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
                'id' => $magazine->id,
                'name' => $magazine->title,
                'url' => $magazine->uri
            ];
            $sRet = json_encode($aRet);
            $this->response = $this->response->withStringBody($sRet);
            return $this->response;
        }catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }
    }

    public function indexMagazines(){
        $sFtpAddress = $this->getSetting('magazine_indexer_ftp_address');
        $sFtpUsername = $this->getSetting('magazine_indexer_ftp_username');
        $sFtpPassword = $this->getSetting('magazine_indexer_ftp_password');
        $sFtpDirectory = $this->getSetting('magazine_indexer_ftp_directory');
        $this->MagazineIndex->indexMagazines($sFtpAddress,$sFtpUsername,$sFtpPassword,$sFtpDirectory);
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
}