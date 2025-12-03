<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Model\Entity\Users;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Firebase\JWT\JWT;
use Cake\Datasource\Exception\RecordNotFoundException;
use Firebase\JWT\SignatureInvalidException;

/**
 * JwtHandle component
 */
class JwtHandleComponent extends Component
{
    protected string $modelClass = 'Users';
    /**
     * Default configuration.
     *
     * @var array<string,mixed>
     */
    protected array $_defaultConfig = [];

    /**
     * @param Users $oUser
     * @return string The Jwt String
     */
    public function generateJwtToken(Users $oUser):string{
        $key = bin2hex(random_bytes(64));
        $oUser->set('jwt_key', $key);
        $oUserTable = $this->getController()->getTableLocator()->get('Users');
        $oUserTable->save($oUser);
        $payload = [
            'iss' => '127.0.0.1',
            'aud' => $oUser->get('user_name'),
            'iat' => time(),
            'user_id' => $oUser->get('id'),
            'admin' => $oUser->get('is_admin')
        ];
        $jwt = JWT::encode($payload,$key);
        return $jwt;
    }

    /**
     * @param string $jwt
     * @return object|false
     */
    public function verifyJwtToken(string $jwt,string $payload):false|object{
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
            $sKey = $oUser->get('jwt_key');
            $payload = JWT::decode($payload,$sKey);
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
