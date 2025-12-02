<?php

namespace App\Controller;

use App\Model\Table\ShoppingListsTable;
use Cake\Database\Connection;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Text;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;

/**
 * @property ShoppingListsTable $ShoppingLists
 */
class ShoppingListsController extends AppController{
    protected string $modelClass = 'ShoppingLists';
    public function optionRequest():Response{
        return $this->response;
    }

    public function getShoppingLists():Response{
        $shoppingLists = $this->ShoppingLists->find('all')->orderBy(['title' => 'DESC']);
        $ret = [];
        foreach($shoppingLists as $shoppingList){
            if($shoppingList instanceof EntityInterface){
                $ret[] = ['id' => $shoppingList->get('id'),'title' => $shoppingList->get('title')];
            } else {
                $ret[] = ['id' => $shoppingList['id'],'title' => $shoppingList['title']];
            }
            
        }
        $json = json_encode($ret);
        if($json){
            $this->response = $this->response->withStringBody($json);
        }
        return $this->response;
    }
    public function getShoppingList():Response{
        $id = $this->request->getParam('id');
        try{
            $product = $this->ShoppingLists->get($id);
            $ret = json_encode(['id' => $product->get('id'),'title' => $product->get('title')]);
            if($ret){
                $this->response = $this->response->withStringBody($ret);
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
    public function createShoppingList():Response{
        $shoppingList = $this->ShoppingLists->newEmptyEntity();
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $shoppingList->set('title', $aData['title']);
        $shoppingList->set('id', Text::uuid());
        if($this->ShoppingLists->save($shoppingList)){
            $json = json_encode(
                    ['id' => $shoppingList->get('id'),
                    'title' => $shoppingList->get('title')]
            );
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            return $this->response;
        }
        $this->response = $this->response->withStatus(500,'could not save data');
        return $this->response;
    }
    public function updateShoppingList():Response{
        $id = $this->request->getParam('id');
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        try{
            $shoppingList = $this->ShoppingLists->get($id);
            $shoppingList->set('title', $aData['title']);
            $this->ShoppingLists->save($shoppingList);
            $json = json_encode(
                    ['id' => $shoppingList->get('id'),
                    'title' => $shoppingList->get('title')]
            );
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
    public function deleteShoppingList():Response{
        $id = $this->request->getParam('id');
        try{
            /* toDo Check Dependencies*/
            $shoppingList = $this->ShoppingLists->get($id);
            $this->ShoppingLists->delete($shoppingList);
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
    public function getShoppingListMapping():Response{
        $id = $this->request->getParam('id');
        $ShoppingListProducts= $this->getTableLocator()->get('ShoppingListProducts');
        $productsMappings = $ShoppingListProducts->find('all')->where(['shopping_list_id' => $id]);
        $aRet = [];
        foreach($productsMappings as $productsMapping){
            if($productsMapping instanceof EntityInterface){
                $aRet[] = [
                   'id' => $productsMapping->get('id'),
                   'shoppingListId' => $productsMapping->get('shopping_list_id'),
                   'productId' => $productsMapping->get('product_id'),
                   'amount' => $productsMapping->get('amount'),
                   'unit' => $productsMapping->get('unit')
                ];
            } else {
                $aRet[] = [
                   'id' => $productsMapping['id'],
                   'shoppingListId' => $productsMapping['shopping_list_id'],
                   'productId' => $productsMapping['product_id'],
                   'amount' => $productsMapping['amount'],
                   'unit' => $productsMapping['unit']
                ];
            }
            
        }
        $sRet = json_encode($aRet);
        if($sRet){
            $this->response = $this->response->withStringBody($sRet);
        }
        return $this->response;
    }
    public function getShoppingListProduct():Response{
        $id = $this->request->getParam('id');
        $ShoppingListProducts = $this->getTableLocator()->get('ShoppingListProducts');
        try{
            $productsMapping = $ShoppingListProducts->get($id);
            $aRet = [
                'shoppingListId' => $productsMapping->get('shopping_list_id'),
                'productId' => $productsMapping->get('product_id'),
                'amount' => $productsMapping->get('amount'),
                'unit' => $productsMapping->get('unit')
            ];
            
            $sRet = json_encode($aRet);
            if($sRet){
                $this->response = $this->response->withStringBody($sRet);
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
    public function createShoppingListProduct():Response{
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
        $shoppingListProduct->set('shopping_list_id', $id);
        $shoppingListProduct->set('product_id', $productId);
        $shoppingListProduct->set('amount', $aData['amount']);
        $shoppingListProduct->set('unit', $aData['unit']);

        if($ShoppingListProducts->save($shoppingListProduct)){
            $json = json_encode(
                    [
                        'id' => $shoppingListProduct->get('id'),
                        'shoppingListId' => $shoppingListProduct->get('shopping_list_id'),
                        'productId' => $shoppingListProduct->get('product_id'),
                        'amount' => $shoppingListProduct->get('amount'),
                        'unit' => $shoppingListProduct->get('unit')
                    ]
                    );
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            
            return $this->response;
        }
        $this->response = $this->response->withStatus(500,'could not save data');
        return $this->response;
    }
    public function getShoppingListProducts():Response{
        $id = $this->request->getParam('id');//Products
        $Products = $this->getTableLocator()->get('Products');
        /** @var Connection */
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
        if($sRet){
            $this->response = $this->response->withStringBody($sRet);
        }
        return $this->response;
    }
}