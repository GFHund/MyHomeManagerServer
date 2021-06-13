<?php

namespace App\Controller;
use Cake\Datasource\ConnectionManager;

class SettingsController extends AppController{
    public function optionsRequest(){
        return $this->response;
    }
    public function getSettings(){
        $connection = ConnectionManager::get('default');
        $settings = $connection->execute('
        SELECT 
        sett.id,
        sett.technical_name,
        sett.shown_label,
        sett.group_id,
        sett.value_type,
        sett.value_id,
        grp.shown_label AS groupName,
        setFloat.setting_value AS floatVal,
        setInts.setting_value AS intVal,
        setBool.setting_value AS boolVal,
        setString.setting_value AS stringVal
        FROM settings sett
        LEFT JOIN setting_groups grp ON grp.id = sett.group_id
        LEFT JOIN setting_floats setFloat ON sett.value_id = setFloat.id
        LEFT JOIN setting_ints setInts ON sett.value_id = setInts.id
        LEFT JOIN setting_booleans setBool ON sett.value_id = setBool.id
        LEFT JOIN setting_strings setString ON sett.value_id = setString.id
        ORDER BY sett.group_id
        ')->fetchAll('assoc');
        $aRet = [];
        foreach($settings as $setting){
            $aSetting = [
                'id' => $setting['id'],
                'technicalName' => $setting['technical_name'],
                'label' => $setting['shown_label'],
                'type' => $setting['value_type'],
                'valueId' => $setting['value_id'],
                'groupName' => $setting['groupName']
            ];
            switch($aSetting['type']){
                case 'floats':
                    $aSetting['value'] = $setting['floatVal'];
                    break;
                case 'ints':
                    $aSetting['value'] = $setting['intVal'];
                    break;
                case 'booleans':
                    $aSetting['value'] = $setting['boolVal'];
                    break;
                case 'strings':
                    $aSetting['value'] = $setting['stringVal'];
                    break;
            }
            $aRet[] = $aSetting;
        }
        $sRet = json_encode($aRet);
        $this->response = $this->response->withStringBody($sRet);
        return $this->response;
    }

    public function updateSettings(){
        $sData = $this->request->getData();
        if(is_string($sData)){
            $aData = json_decode($sData,true);
        } else {
            $aData = $sData;
        }
        foreach($aData as $setting){
            $type = $setting['type'];
            $value = $setting['value'];
            $id = $setting['value_id'];
            $sDatabase = 'setting_';
            switch($type){
                case 'floats':
                    $sDatabase .= 'floats';
                    break;
                case 'ints':
                    $sDatabase .= 'ints';
                    break;
                case 'booleans':
                    $sDatabase .= 'booleans';
                    break;
                case 'strings':
                    $sDatabase .= 'strings';
                    break;
                default:
                    $this->response = $this->response->withStatus(400);
                    $this->response = $this->response->withStringBody('type not exsists');
                    return $this->response;
            }
            
            $sSql = 'UPDATE '.$sDatabase.' SET setting_value = :value WHERE id = :id';
            $connection = ConnectionManager::get('default');
            //$settings = $connection->execute($sSql);
            $connection->update($sDatabase,['setting_value' => $value],['id' => $id]);
        }
        $this->response = $this->response->withStatus(201);
        $this->response = $this->response->withStringBody(json_encode([]));
        return $this->response;
    }
}