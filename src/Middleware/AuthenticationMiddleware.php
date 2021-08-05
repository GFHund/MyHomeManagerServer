<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Cake\Datasource\ConnectionManager;
use Firebase\JWT\JWT;
use Cake\Http\Response;
use Cake\Core\Configure;

class AuthenticationMiddleware implements MiddlewareInterface{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        
        if(Configure::read('AuthenticationMethod')=== 'session'){
            return $handler->handle($request);
        }

        if($request->getMethod() == 'OPTIONS'){
            file_put_contents(dirname(__FILE__).'/Philipp.txt',date('c').': Options drin'."\n",FILE_APPEND);
            return $handler->handle($request);
        }
        file_put_contents(dirname(__FILE__).'/Philipp.txt',date('c').': headers '.var_export($request->getHeaders(),true)."\n",FILE_APPEND);
        $authorizationHeader = $request->getHeader('Authorization');
        if(!empty($authorizationHeader)){
            $sToken = preg_replace('/^Bearer\s([0-9a-fA-F\.])/','$1',$authorizationHeader[0]);
            $aToken = explode('.',$sToken);
            if(count($aToken) != 3){
                file_put_contents(dirname(__FILE__).'/Philipp.txt',date('c').': token has not 3 parts'."\n",FILE_APPEND);
                return $this->getNonAuthorisationResponse($request);
            }
            $sContent = base64_decode($aToken[1]);
            $aContent = json_decode($sContent,true);
            if(!isset($aContent['user_id'])){
                file_put_contents(dirname(__FILE__).'/Philipp.txt',date('c').': dont found user_id'."\n",FILE_APPEND);
                return $this->getNonAuthorisationResponse($request);
            }
            $connection = ConnectionManager::get('default');
            $settings = $connection->execute('SELECT count(*) FROM Users WHERE id = :id',['id' => $aContent['user_id']]);
            try{
                $payload = JWT::decode($payload,$key);
            }catch(RecordNotFoundException $e){
                file_put_contents(dirname(__FILE__).'/Philipp.txt',date('c').': Record Not Found Exception'."\n",FILE_APPEND);
                return $this->getNonAuthorisationResponse($request);
            } catch(SignatureInvalidException $e){
                file_put_contents(dirname(__FILE__).'/Philipp.txt',date('c').': Signature Invalid Exception'."\n",FILE_APPEND);
                return $this->getNonAuthorisationResponse($request);
            }
            catch(\Exception $e){
                file_put_contents(dirname(__FILE__).'/Philipp.txt',date('c').': Exception'."\n",FILE_APPEND);
                return $this->getNonAuthorisationResponse($request);
            }
            
        } else {
            file_put_contents(dirname(__FILE__).'/Philipp.txt',date('c').': Authorisation Header not found'."\n",FILE_APPEND);
            return $this->getNonAuthorisationResponse($request);
        }
        
        $response = $handler->handle($request);
        return $response;
    }

    private function getNonAuthorisationResponse($request){
        $response = new Response(['status' => 401]);
        return $response->cors($request)
        ->allowOrigin(['*','http://127.0.0.1:4200','http://localhost:4200'])
        ->allowMethods(['GET',' POST',' PUT','DELETE',' OPTIONS'])
        ->allowHeaders(['*'])
        ->build();
    }
}