<?php

namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Text;
use Cake\Datasource\ConnectionManager;

class RecipesController extends AppController{
    public function createRecipeOptionRequest(){
        return $this->response;
    }
    public function createRecipe(){
        $recipe = $this->Recipes->newEmptyEntity();
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $recipe->recipe_name = $aData['name'];
        $recipe->person_number = $aData['persons'];
        $recipe->duration = $aData['time'];
        $recipe->id = Text::uuid();
        if($this->Recipes->save($recipe)){
            $this->response = $this->response->withStringBody(
                json_encode(
                    [
                        'id' => $recipe->id,
                        'name' => $recipe->recipe_name,
                        'persons' => $recipe->person_number,
                        'time' => $recipe->duration
                    ]
                )
            );
            return $this->response;
        } else {
            $this->response = $this->response->withStatus(500,'could not save data');
            return $this->response;
        }
    }
    public function getRecipeList(){
        $recipes = $this->Recipes->find('all');
        $ret = [];
        foreach($recipes as $recipe){
            $ret[] = [
                'id' => $recipe->id,
                'name' => $recipe->recipe_name                
            ];
        }
        $this->response = $this->response->withStringBody(json_encode($ret));
        return $this->response;
    }
    public function getRecipe(){
        $id = $this->request->getParam('id');
        try{
            $recipe = $this->Recipes->get($id);
            $ret = json_encode([
                'id' => $recipe->id,
                'name' => $recipe->recipe_name,
                'persons' => $recipe->person_number,
                'time' => $recipe->duration,
                ]);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }catch(RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }
    }

    public function createRecipeIncredient(){
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
        $recipeIncredient->id = Text::uuid();
        $recipeIncredient->amount = $aData['amount'];
        $recipeIncredient->unit = $aData['unit'];
        $recipeIncredient->recipe_id = $recipeId;
        $recipeIncredient->product_id = $productId;
        if($RecipeIncredient->save($recipeIncredient)){
            $this->response = $this->response->withStringBody(
                json_encode(
                    [
                        'id' => $recipeIncredient->id,
                        'recipeId' => $recipeIncredient->recipe_id,
                        'productId' => $recipeIncredient->product_id,
                        'amount' => $recipeIncredient->amount,
                        'unit' => $recipeIncredient->unit
                    ]
                )
            );
            return $this->response;
        } else {
            $this->response = $this->response->withStatus(500,'could not save data');
            return $this->response;
        }
    }

    public function getRecipeIncredients(){
        $id = $this->request->getParam('id');
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
        $this->response = $this->response->withStringBody($sRet);
        return $this->response;
    }

    public function createRecipeStep(){
        $RecipeSteps = $this->getTableLocator()->get('RecipeSteps');
        $recipeStep = $RecipeSteps->newEmptyEntity();
        $recipeId = $this->request->getParam('id');
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        $recipeStep->id = Text::uuid();
        $recipeStep->step_number = $aData['stepnumber'];
        $recipeStep->step_description = $aData['description'];
        $recipeStep->recipe_id = $recipeId;
        if($RecipeSteps->save($recipeStep)){
            $this->response = $this->response->withStringBody(
                json_encode(
                    [
                        'id' => $recipeStep->id,
                        'stepnumber' => $recipeStep->step_number,
                        'description' => $recipeStep->step_description,
                        'recipe_id' => $recipeStep->recipe_id
                    ]
                )
            );
            return $this->response;
        } else {
            $this->response = $this->response->withStatus(500,'could not save data');
            return $this->response;
        }
    }

    public function getRecipeSteps(){
        $id = $this->request->getParam('id');
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
        $this->response = $this->response->withStringBody($sRet);
        return $this->response;
    }

    public function updateRecipeStep(){
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
            $recipeStep->step_description = $aData['description'];
            $recipeStep->step_number = $aData['stepnumber'];
            if($RecipeSteps->save($recipeStep)){
                $this->response = $this->response->withStringBody(
                    json_encode(
                        [
                            'id' => $recipeStep->id,
                            'description' => $recipeStep->step_description,
                            'stepnumber' => $recipeStep->step_number,
                            'recipeId' => $recipeStep->recipe_id
                        ]
                    )
                );
                return $this->response;
            }
        } catch (RecordNotFoundException $e){
            $this->response = $this->response->withStatus(404,'Product not Found');
            $ret = json_encode(['message' => 'Entity not found']);
            $this->response = $this->response->withStringBody($ret);
            return $this->response;
        }
        

    }
}