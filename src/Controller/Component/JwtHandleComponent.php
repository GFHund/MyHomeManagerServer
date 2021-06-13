<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Firebase\JWT\JWT;
use Cake\Datasource\Exception\RecordNotFoundException;

/**
 * JwtHandle component
 */
class JwtHandleComponent extends Component
{
    protected $modelClass = 'Users';
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * @param \App\Model\Entity\Users
     * @return string The Jwt String
     */
    public function generateJwtToken($oUser){
        $key = bin2hex(random_bytes(64));
        $oUser->jwt_key = $key;
        $oUserTable = $this->getController()->getTableLocator()->get('Users');
        $oUserTable->save($oUser);
        $payload = [
            'iss' => '127.0.0.1',
            'aud' => $oUser->user_name,
            'iat' => time(),
            'user_id' => $oUser->id,
            'admin' => $oUser->is_admin
        ];
        $jwt = JWT::encode($payload,$key);
        return $jwt;
    }

    /**
     * @param $jwt string
     * @return array|boolean
     */
    public function verifyJwtToken($jwt,$payload){
        $aJwtTokenParts = explode('.',$jwt);
        if(count($aJwtTokenParts) != 3){
            return false;
        }
        $sContent = base64_decode($aJwtTokenParts[1]);
        $aContent = json_decode($sContent,true);
        if(!isset($aContent['user_id'])){
            return false;
        }
        try{
            $oUserTable = $this->getController()->getTableLocator()->get('Users');
            $oUser = $oUserTable->get($aContent['user_id']);
            $sKey = $oUser->jwt_key;
            $payload = JWT::decode($payload,$key);
            return $payload;

        }catch(RecordNotFoundException $e){
            return false;
        } catch(SignatureInvalidException $e){
            return false;
        }
        catch(\Exception $e){
            return false;
        }
        

    }
}
