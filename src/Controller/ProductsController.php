<?php

namespace App\Controller;

use App\Model\Table\ProductsTable;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Response;
use Cake\Utility\Text;

/**
 * @property ProductsTable $Products
 */
class ProductsController extends AppController{
    protected string $modelClass = 'Products';

    public function initialize():void{
        parent::initialize();
        $this->loadComponent('JwtHandle');
    }

    public function productAction():Response{
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

    public function getProducts():Response{
        /** @var string */
        $sProductTitle = $this->request->getQuery('productTitle','');

        $products = $this->Products->find('all');
        $products->orderBy('product_name');
        if(!empty($sProductTitle)){
            $products->where(['product_name LIKE' => $sProductTitle."%"]);
        }

        $ret = [];
        foreach($products as $product){
            if($product instanceof EntityInterface){
                $ret[] = ['id' => $product->get('id'),'productName' => $product->get('product_name')];
            } else {
                $ret[] = ['id' => $product['id'],'productName' => $product['product_name']];
            }   
        }
        $json = json_encode($ret);
        if($json){
            $this->response = $this->response->withStringBody($json);
        }
        
        return $this->response;
    }
    public function postProducts():Response{
        $product = $this->Products->newEmptyEntity();
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }

        $product->set('product_name', $aData['productName']);
        $product->set('id', Text::uuid());
        if($this->Products->save($product)){
            $json = json_encode([
                'id' => $product->get('id'),
                'productName' => $product->get('product_name')
            ]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            return $this->response;
        }
        $this->response = $this->response->withStatus(500,'could not save data');
        return $this->response;
    }

    public function productDetailAction():Response{
        $id = $this->request->getParam('id');
        if($this->request->is(['get'])){
            return $this->getProduct($id);
        }else if( $this->request->is(['put']) ){
            return $this->updateProduct($id);
        } else if( $this->request->is(['delete']) ) {
            return $this->deleteProduct($id);
        }else if($this->request->is(['options'])){
            return $this->response;
        }else {
            $this->response = $this->response->withStatus(405);
            return $this->response;
        }
    }
    public function getProduct(string $id):Response{
        try{
            $product = $this->Products->get($id);
            $json = json_encode([
                    'id' => $product->get('id'),
                    'productName' => $product->get('product_name')
            ]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            if($ret){
                $this->response = $this->response->withStringBody($ret);
            }
            return $this->response;
        }
        return $this->response;
    }
    public function updateProduct(string $id):Response{
        try{
            $product = $this->Products->get($id);
            $sData = $this->request->getData();
            if(is_string($sData)){
                $aData = json_decode($sData,true);
            } else {
                $aData = $sData;
            }
            $product->set('product_name', $aData['productName']);
            $this->Products->save($product);
            $json = json_encode([
                    'id' => $product->get('id'),
                    'productName' => $product->get('product_name')
            ]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            return $this->response;
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            if($ret){
                $this->response = $this->response->withStringBody($ret);
            }
            return $this->response;
        }
    }
    public function deleteProduct(string $id):Response{
        try{
            /*toDo: Check of dependencys*/
            $product = $this->Products->get($id);
            $this->Products->delete($product);
            $json = json_encode(['success' => true]);
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            return $this->response;
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            if($ret){
                $this->response = $this->response->withStringBody($ret);
            }
            return $this->response;
        }
    }
}
