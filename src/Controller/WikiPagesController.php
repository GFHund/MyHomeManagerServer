<?php
namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Text;
use Cake\Datasource\ConnectionManager;

class WikiPagesController extends AppController{
    public function optionsRequest(){
        return $this->response;
    }
    public function createWikiPage(){
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $wikiPage = $this->WikiPages->newEmptyEntity();
        $wikiPage->id = Text::uuid();
        $wikiPage->title = $aData['title'];
        $wikiPage->wiki_text = $aData['text'];
        if($this->WikiPage->save($wikiPage)){
            $this->response = $this->response->withStringBody(
                json_encode(
                    [
                        'id' => $wikiPage->id,
                        'title' => $wikiPage->title,
                        'text' => $wikiPage->wiki_text

                    ]
                )
            );
            return $this->response;
        } else {
            $this->response = $this->response->withStatus(500,'could not save data');
            return $this->response;
        }

    }
    public function deleteWikiPage(){}
    public function getWikiPage(){
        $id = $this->request->getParam('id');
        try{
            $wikiPage = $this->WikiPages->get($id);
            $aRet = [
                'id' => $wikiPage->id,
                'title' => $wikiPage->title,
                'text' => $wikiPage->wiki_text
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
    public function getWikiPages(){
        $wikiPages = $this->WikiPages->find('all');
        $aRet = [];
        foreach($wikiPages as $wikiPage){
            $aRet[] = 
            [
                'id' => $wikiPage->id,
                'title' => $wikiPage->title,
                'text' => $wikiPage->wiki_text

            ];
        }
        $sRet = json_encode($aRet);
        $this->response = $this->response->withStringBody($sRet);
        return $this->response;
    }
    public function updateWikiPage(){}
}