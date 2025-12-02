<?php

namespace App\Controller;

use App\Model\Table\RecipesTable;
use Cake\Database\Connection;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Text;
use Cake\Datasource\ConnectionManager;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;

/** @property RecipesTable $Recipes */
class RecipesController extends AppController{
    public function createRecipeOptionRequest():Response{
        return $this->response;
    }
    public function createRecipe():Response{
        $recipe = $this->Recipes->newEmptyEntity();
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $recipe->set('recipe_name', $aData['name']);
        $recipe->set('person_number', $aData['persons']);
        $recipe->set('duration', $aData['time']);
        $recipe->set('id', Text::uuid());
        if($this->Recipes->save($recipe)){
            $json = json_encode(
                [
                    'id' => $recipe->get('id'),
                    'name' => $recipe->get('recipe_name'),
                    'persons' => $recipe->get('person_number'),
                    'time' => $recipe->get('duration')
                ]
            );
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            return $this->response;
        } else {
            $this->response = $this->response->withStatus(500,'could not save data');
            return $this->response;
        }
    }
    public function getRecipeList():Response{
        $recipes = $this->Recipes->find('all');
        $ret = [];
        foreach($recipes as $recipe){
            if($recipe instanceof EntityInterface){
                $ret[] = [
                    'id' => $recipe->get('id'),
                    'name' => $recipe->get('recipe_name')
                ];
            } else {
                $ret[] = [
                    'id' => $recipe['id'],
                    'name' => $recipe['recipe_name']
                ];
            }
            
        }
        $json = json_encode($ret);
        if($json){
            $this->response = $this->response->withStringBody($json);
        }
        
        return $this->response;
    }
    public function getRecipe():Response{
        $id = $this->request->getParam('id');
        try{
            $recipe = $this->Recipes->get($id);
            $ret = json_encode([
                'id' => $recipe->get('id'),
                'name' => $recipe->get('recipe_name'),
                'persons' => $recipe->get('person_number'),
                'time' => $recipe->get('duration'),
                ]);
            if($ret){
                $this->response = $this->response->withStringBody($ret);
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

    public function createRecipeIncredient():Response{
        $RecipeIncredient = $this->getTableLocator()->get('RecipeIncredient');
        $recipeIncredient = $RecipeIncredient->newEmptyEntity();
        $recipeId = $this->request->getParam('id');
        $productId = $this->request->getParam('productId');
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $recipeIncredient->set('id',Text::uuid());
        $recipeIncredient->set('amount', $aData['amount']);
        $recipeIncredient->set('unit', $aData['unit']);
        $recipeIncredient->set('recipe_id', $recipeId);
        $recipeIncredient->set('product_id', $productId);
        if($RecipeIncredient->save($recipeIncredient)){
            $json = json_encode(
                [
                    'id' => $recipeIncredient->get('id'),
                    'recipeId' => $recipeIncredient->get('recipe_id'),
                    'productId' => $recipeIncredient->get('product_id'),
                    'amount' => $recipeIncredient->get('amount'),
                    'unit' => $recipeIncredient->get('unit')
                ]
            );
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            return $this->response;
        } else {
            $this->response = $this->response->withStatus(500,'could not save data');
            return $this->response;
        }
    }

    public function getRecipeIncredients():Response{
        $id = $this->request->getParam('id');
        /** @var Connection $connection */
        $connection = ConnectionManager::get('default');
        $recipeIncredients = $connection->execute(
            'SELECT id,recipe_id,product_id,amount,unit 
            FROM recipe_incredient 
            WHERE recipe_id = :id',
            ['id' => $id])->fetchAll('assoc');
        $aRet = [];
        foreach($recipeIncredients as $recipeIncredient){
            $aRet[] = [
                'id' => $recipeIncredient['id'],
                'recipeId' => $recipeIncredient['recipe_id'],
                'productId' => $recipeIncredient['product_id'],
                'amount' => $recipeIncredient['amount'],
                'unit' => $recipeIncredient['unit']
            ];
        }
        $sRet = json_encode($aRet);
        if($sRet){
            $this->response = $this->response->withStringBody($sRet);
        }
        return $this->response;
    }

    public function createRecipeStep():Response{
        $RecipeSteps = $this->getTableLocator()->get('RecipeSteps');
        $recipeStep = $RecipeSteps->newEmptyEntity();
        $recipeId = $this->request->getParam('id');
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $recipeStep->set('id', Text::uuid());
        $recipeStep->set('step_number', $aData['stepnumber']);
        $recipeStep->set('step_description', $aData['description']);
        $recipeStep->set('recipe_id', $recipeId);
        if($RecipeSteps->save($recipeStep)){
            $json = json_encode(
                [
                    'id' => $recipeStep->get('id'),
                    'stepnumber' => $recipeStep->get('step_number'),
                    'description' => $recipeStep->get('step_description'),
                    'recipe_id' => $recipeStep->get('recipe_id')
                ]
            );
            if($json){
                $this->response = $this->response->withStringBody($json);
            }
            
            return $this->response;
        } else {
            $this->response = $this->response->withStatus(500,'could not save data');
            return $this->response;
        }
    }

    public function getRecipeSteps():Response{
        $id = $this->request->getParam('id');
        /** @var Connection $connection */
        $connection = ConnectionManager::get('default');
        $recipeSteps = $connection->execute(
            'SELECT id,recipe_id,step_number,step_description 
            FROM recipe_steps 
            WHERE recipe_id = :id ORDER BY step_number',
            ['id' => $id])->fetchAll('assoc');
        $aRet = [];
        foreach($recipeSteps as $recipeStep){
            $aRet[] = [
                'id' => $recipeStep['id'],
                'description' => $recipeStep['step_description'],
                'stepnumber' => $recipeStep['step_number'],
                
            ];
        }
        $sRet = json_encode($aRet);
        if($sRet){
            $this->response = $this->response->withStringBody($sRet);
        }
        
        return $this->response;
    }

    public function updateRecipeStep():Response{
        $recipeId = $this->request->getParam('id');
        $stepId = $this->request->getParam('stepId');
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $RecipeSteps = $this->getTableLocator()->get('RecipeSteps');
        try{
            $recipeStep = $RecipeSteps->get($stepId);
            $recipeStep->set('step_description', $aData['description']);
            $recipeStep->set('step_number', $aData['stepnumber']);
            if($RecipeSteps->save($recipeStep)){
                $json = json_encode(
                    [
                        'id' => $recipeStep->get('id'),
                        'description' => $recipeStep->get('step_description'),
                        'stepnumber' => $recipeStep->get('step_number'),
                        'recipeId' => $recipeStep->get('recipe_id')
                    ]
                );
                if($json){
                    $this->response = $this->response->withStringBody($json);
                }
            }
            return $this->response;
        } catch (RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            if($ret){
                $this->response = $this->response->withStringBody($ret);
            }
            
            return $this->response;
        }
        

    }
}