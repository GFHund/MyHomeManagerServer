<?php

namespace App\Controller;
use App\Model\Entity\Users;
use App\Model\Table\UsersTable;
use Firebase\JWT\JWT;

class UsersController extends AppController{
    protected $modelClass = 'Users';

    public function initialize():void{
        parent::initialize();
        $this->loadComponent('JwtHandle');
    }

    public function loginUser(){
        if(!$this->request->is(['post'])){
            $this->response = $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'http request has the wrong http type'
            ]));
            return $this->response;
        }
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        if(!is_array($aData)){
            $this->response = $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'could not determine datatype of post body'
            ]));
            return $this->response;
        }
        
        $this->response = $this->response->withType('application/json');
        if(!isset($aData['username']) || !isset($aData['passwort'])){
            $this->response = $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'username or password wrong'
            ]));
            return $this->response;
        }

        $aUsers = $this->Users->find()->where(['user_name' => $aData['username']])->toList();
        if(count($aUsers) <= 0){
            $this->response = $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'username or password wrong'
            ]));
            return $this->response;
        }
        $sUserPassword = $aUsers[0]->password;
        $result = \password_verify($aData['passwort'],$sUserPassword);
        if(!$result){
            $this->response = $this->response->withStringBody(json_encode([
                'success' => false,
                'message' => 'username or password wrong'
            ]));
            return $this->response;
        }

        $sJwt = $this->JwtHandle->generateJwtToken($aUsers[0]);
        //$this->response = $this->response->withHeader('Authorization','Bearer '.$sJwt);

        $this->response = $this->response->withStringBody(json_encode([
            'success' => true,
            'token' => $sJwt
        ]));
        return $this->response;

    }
}