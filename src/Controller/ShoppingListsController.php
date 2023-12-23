<?php

namespace App\Controller;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Text;
use Cake\Datasource\ConnectionManager;

class ShoppingListsController extends AppController{
    protected $modelClass = 'ShoppingLists';
    public function optionRequest(){
        return $this->response;
    }

    public function getShoppingLists(){
        $shoppingLists = $this->ShoppingLists->find('all')->order(['title' => 'DESC']);
        $ret = [];
        foreach($shoppingLists as $shoppingList){
            $ret[] = ['id' => $shoppingList->id,'title' => $shoppingList->title];
        }
        $this->response = $this->response->withStringBody(json_encode($ret));
        return $this->response;
    }
    public function getShoppingList(){
        $id = $this->request->getParam('id');
        try{
            $product = $this->ShoppingLists->get($id);
            $ret = json_encode(['id' => $product->id,'title' => $product->title]);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }   
    }
    public function createShoppingList(){
        $shoppingList = $this->ShoppingLists->newEmptyEntity();
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $shoppingList->title = $aData['title'];
        $shoppingList->id = Text::uuid();
        if($this->ShoppingLists->save($shoppingList)){
            $this->response = $this->response->withStringBody(
                json_encode(
                    ['id' => $shoppingList->id,
                    'title' => $shoppingList->title]
                )
            );
            return $this->response;
        }
        $this->response = $this->response->withStatus(500,'could not save data');
        return $this->response;
    }
    public function updateShoppingList(){
        $id = $this->request->getParam('id');
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        try{
            $shoppingList = $this->ShoppingLists->get($id);
            $shoppingList->title = $aData['title'];
            $this->ShoppingLists->save(shoppingList);
            $this->response = $this->response->withStringBody(
                json_encode(
                    ['id' => $shoppingList->id,
                    'title' => $shoppingList->title]
                )
            );
            return $this->response;
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }   
    }
    public function deleteShoppingList(){
        $id = $this->request->getParam('id');
        try{
            /* toDo Check Dependencies*/
            $shoppingList = $this->ShoppingLists->get($id);
            $this->ShoppingLists->delete($shoppingList);
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }   
    }
    public function getShoppingListMapping(){
        $id = $this->request->getParam('id');
        $ShoppingListProducts= $this->getTableLocator()->get('ShoppingListProducts');
        $productsMappings = $ShoppingListProducts->find('all')->where(['shopping_list_id' => $id]);
        $aRet = [];
        foreach($productsMappings as $productsMapping){
            $aRet[] = [
                'id' => $productsMapping->id,
                'shoppingListId' => $productsMapping->shopping_list_id,
                'productId' => $productsMapping->product_id,
                'amount' => $productsMapping->amount,
                'unit' => $productsMapping->unit
            ];
        }
        $sRet = json_encode($aRet);
        $this->response = $this->response->withStringBody($sRet);
        return $this->response;
    }
    public function getShoppingListProduct(){
        $id = $this->request->getParam('id');
        $ShoppingListProducts = $this->getTableLocator()->get('ShoppingListProducts');
        try{
            $productsMapping = $ShoppingListProducts->get($id);
            $aRet = [
                'shoppingListId' => $productsMapping->shopping_list_id,
                'productId' => $productsMapping->product_id,
                'amount' => $productsMapping->amount,
                'unit' => $productsMapping->unit
            ];
            
            $sRet = json_encode($aRet);
            $this->response = $this->response->withStringBody($sRet);
            return $this->response;
        } catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }   
    }
    public function createShoppingListProduct(){
        $id = $this->request->getParam('id');
        $productId = $this->request->getParam('productId');
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }

        $ShoppingListProducts= $this->getTableLocator()->get('ShoppingListProducts');

        $shoppingListProduct = $ShoppingListProducts->newEmptyEntity();
        $shoppingListProduct->shopping_list_id = $id;
        $shoppingListProduct->product_id = $productId;
        $shoppingListProduct->amount = $aData['amount'];
        $shoppingListProduct->unit = $aData['unit'];

        if($ShoppingListProducts->save($shoppingListProduct)){
            $this->response = $this->response->withStringBody(
                json_encode(
                    [
                        'id' => $shoppingListProduct->id,
                        'shoppingListId' => $shoppingListProduct->shopping_list_id,
                        'productId' => $shoppingListProduct->product_id,
                        'amount' => $shoppingListProduct->amount,
                        'unit' => $shoppingListProduct->unit
                    ]
                )
            );
            return $this->response;
        }
        $this->response = $this->response->withStatus(500,'could not save data');
        return $this->response;
    }
    public function getShoppingListProducts(){
        $id = $this->request->getParam('id');//Products
        $Products= $this->getTableLocator()->get('Products');
        $connection = ConnectionManager::get('default');
        $aProductResults = $connection->execute(
            'SELECT p.id,p.product_name 
            FROM products p 
            LEFT JOIN shopping_list_products slp 
            ON p.id = slp.product_id 
            WHERE slp.shopping_list_id = :id',
            ['id' => $id])->fetchAll('assoc');
        $aRet = [];
        foreach($aProductResults as $aProductResult){
            $aRet[] = [
                'id' => $aProductResult['id'],
                'productName' => $aProductResult['product_name']
            ];
        }
        $sRet = json_encode($aRet);
        $this->response = $this->response->withStringBody($sRet);
        return $this->response;
    }
}