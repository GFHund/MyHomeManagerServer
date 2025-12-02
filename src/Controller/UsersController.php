<?php

namespace App\Controller;

use App\Controller\Component\JwtHandleComponent;
use App\Model\Entity\Users;
use App\Model\Table\UsersTable;
use Firebase\JWT\JWT;
use Cake\Core\Configure;
use Cake\Http\Response;

/**
 * @property UsersTable $Users
 * @property JwtHandleComponent $JwtHandle
 */
class UsersController extends AppController{
    protected string $modelClass = 'Users';

    public function initialize():void{
        parent::initialize();
        $this->loadComponent('JwtHandle');
    }

    public function loginUser():Response{
        if(!$this->request->is(['post'])){
            $json = json_encode([
                'success' => false,
                'message' => 'http request has the wrong http type'
            ]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            return $this->response;
        }
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        if(!is_array($aData)){
            $json = json_encode([
                'success' => false,
                'message' => 'could not determine datatype of post body'
            ]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            return $this->response;
        }
        
        $this->response = $this->response->withType('application/json');
        if(!isset($aData['username']) || !isset($aData['passwort'])){
            $json = json_encode([
                'success' => false,
                'message' => 'username or password wrong'
            ]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            return $this->response;
        }

        $aUsers = $this->Users->find()->where(['user_name' => $aData['username']])->all()->toList();
        if(count($aUsers) <= 0){
            $json = json_encode([
                'success' => false,
                'message' => 'username or password wrong'
            ]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            
            return $this->response;
        }
        $sUserPassword = $aUsers[0]->password;
        $result = \password_verify($aData['passwort'],$sUserPassword);
        if(!$result){
            $json = json_encode([
                'success' => false,
                'message' => 'username or password wrong'
            ]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            
            return $this->response;
        }

        if(Configure::read('AuthenticationMethod')=== 'session'){
            $this->request->getSession()->write('user.userId',$aUsers[0]->id);
            $this->request->getSession()->write('user.isAdmin',$aUsers[0]->is_admin);
            $this->request->getSession()->write('user.userName',$aUsers[0]->user_name);
            return $this->response;
        } else {
            $sJwt = $this->JwtHandle->generateJwtToken($aUsers[0]);
            //$this->response = $this->response->withHeader('Authorization','Bearer '.$sJwt);
            $json = json_encode([
                'success' => true,
                'token' => $sJwt
            ]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            
            return $this->response;
        }
        

    }
}