<?php
namespace App\Controller;

use App\Model\Table\WikiPagesTable;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Text;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;

/**
 * @property WikiPagesTable $WikiPages 
 */
class WikiPagesController extends AppController{
    public function optionsRequest():Response{
        return $this->response;
    }
    public function createWikiPage():Response{
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $wikiPage = $this->WikiPages->newEmptyEntity();
        $wikiPage->set('id',Text::uuid());
        $wikiPage->set('title',$aData['title']);
        $wikiPage->set('wiki_text',$aData['text']);
        if($this->WikiPages->save($wikiPage)){
            $json = json_encode(
                [
                    'id' => $wikiPage->get('id'),
                    'title' => $wikiPage->get('title'),
                    'text' => $wikiPage->get('wiki_text')
                ]
            );
            if($json){
                $this->response = $this->response->withStringBody(
                    $json
                );
                return $this->response;
            }
            return $this->response;
        } else {
            $this->response = $this->response->withStatus(500,'could not save data');
            return $this->response;
        }

    }
    public function deleteWikiPage():void{}
    public function getWikiPage():Response{
        $id = $this->request->getParam('id');
        try{
            $wikiPage = $this->WikiPages->get($id);
            $aRet = [
                'id' => $wikiPage->get('id'),
                'title' => $wikiPage->get('title'),
                'text' => $wikiPage->get('wiki_text')
            ];
            $sRet = json_encode($aRet);
            if($sRet){
                $this->response = $this->response->withStringBody($sRet);
                return $this->response;
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
    public function getWikiPages():Response{
        $wikiPages = $this->WikiPages->find('all');
        $aRet = [];
        foreach($wikiPages as $wikiPage){
            if($wikiPage instanceof EntityInterface){
                $aRet[] = 
                [
                    'id' => $wikiPage->get('id'),
                    'title' => $wikiPage->get('title'),
                    'text' => $wikiPage->get('wiki_text')

                ];
            } else {
                $aRet[] = 
                [
                    'id' => $wikiPage['id'],
                    'title' => $wikiPage['title'],
                    'text' => $wikiPage['wiki_text']

                ];
            }
        }
        $sRet = json_encode($aRet);
        if($sRet){
            $this->response = $this->response->withStringBody($sRet);
        }
        return $this->response;
    }
    public function updateWikiPage():Response{
        $id = $this->request->getParam('id');
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        try{
            $wikiPage = $this->WikiPages->get($id);
            $wikiPage->set('title',$aData['title']);
            $wikiPage->set('wiki_text',$aData['text']);
            if($this->WikiPages->save($wikiPage)){
                $json = json_encode(
                    [
                        'id' => $wikiPage->get('id'),
                        'title' => $wikiPage->get('title'),
                        'text' => $wikiPage->get('wiki_text')

                    ]
                    );
                if($json){
                    $this->response = $this->response->withStringBody($json);
                    return $this->response;
                }
                return $this->response;
            } else {
                $this->response = $this->response->withStatus(500,'could not save data');
                return $this->response;
            }
        }catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            if($ret){
                $this->response = $this->response->withStringBody($ret);
            }
            return $this->response;
        }
    }
}