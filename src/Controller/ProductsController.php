<?php

namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Text;

class ProductsController extends AppController{
    protected $modelClass = 'Products';

    public function initialize():void{
        parent::initialize();
        $this->loadComponent('JwtHandle');
    }
    
    public function productAction(){
        if($this->request->is(['get'])){
            return $this->getProducts();
        }
        else if($this->request->is(['post'])){
            return $this->postProducts();
        }
        else if($this->request->is(['options'])){
            return $this->response;
        }
        else {
            $this->response = $this->response->withStatus(405);
            return $this->response;
        }
    }

    public function getProducts(){
        $sProductTitle = $this->request->getQuery('productTitle','');

        $products = $this->Products->find('all');
        $products->order('product_name');
        if(!empty($sProductTitle)){
            $products->where(['product_name LIKE' => $sProductTitle."%"]);
        }
        
        $ret = [];
        foreach($products as $product){
            $ret[] = ['id' => $product->id,'productName' => $product->product_name];
        }
        $this->response = $this->response->withStringBody(json_encode($ret));
        return $this->response;
    }
    public function postProducts(){
        $product = $this->Products->newEmptyEntity();
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }

        $product->product_name = $aData['productName'];
        $product->id = Text::uuid();
        if($this->Products->save($product)){
            $this->response = $this->response->withStringBody(
                json_encode(
                    ['id' => $product->id,
                    'productName' => $product->product_name]
                )
            );
            return $this->response;
        }
        $this->response = $this->response->withStatus(500,'could not save data');
        return $this->response;
    }

    public function productDetailAction(){
        $id = $this->request->getParam('id');
        if($this->request->is(['get'])){
            return $this->getProduct($id);
        }else if( $this->request->is(['put']) ){
            return $this->updateProduct($id);
        } else if( $this->request->is(['delete']) ) {
            return $this->deleteProduct($id);
        }else {
            $this->response = $this->response->withStatus(405);
            return $this->response;
        }
    }
    public function getProduct(string $id){
        try{
            $product = $this->Products->get($id);
            $this->response = $this->response->withStringBody(
                json_encode([
                    'id' => $product->id,
                    'productName' => $product->product_name
                ])
            );
            
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }
        return $this->response;
    }
    public function updateProduct(string $id){
        try{
            $product = $this->Products->get($id);
            $sData = $this->request->getData();
            if(is_string($sData)){
                $aData = json_decode($sData,true);
            } else {
                $aData = $sData;
            }
            $product->product_name = $aData['productName'];
            $this->Products->save($product);
            $this->response = $this->response->withStringBody(
                json_encode([
                    'id' => $product->id,
                    'productName' => $product->product_name
                ])
            );
            return $this->response;
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }
    }
    public function deleteProduct($id){
        try{
            /*toDo: Check of dependencys*/
            $product = $this->Products->get($id);
            $this->Products->delete($product);
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }
    }
}