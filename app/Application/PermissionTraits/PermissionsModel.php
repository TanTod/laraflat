<?php

namespace App\Application\PermissionTraits;

use App\Application\Model\Group;
use App\Application\Model\Permission;
use App\Application\Model\Role;
use App\Application\Model\User;
use Illuminate\Database\Eloquent\Model;

class PermissionsModel{

    protected function checkRolePermission($action , $roles , $model = null){
        $response = [];
        foreach($roles as $role){
            if($role->permission->count() > 1){
                $response = array_merge($response , $this->moreThanRole($action , $role->permission , $model)) ;
            }else{
                $name = isset($role->permission->all()[0]->model) ? $role->permission->all()[0]->model : $role->slug;
                $response[$name] =  $this->checkPermissions($action , $role->permission , $model);
            }
        }
        return empty($response) ? false : $response;
    }

    protected function moreThanRole($action , $roles , $model = null){
            $response = [];
            foreach($roles as $key => $role){
                    if(array_key_exists($role->model , $response)){
                        $index = $role->model.'_'.$key;
                    }else{
                        $index  =$role->model;
                    }
                    $response[$index] =  $this->checkPermissions($action , $role , $model , 'object');
            }
            return empty($response) ? false : $response;
    }

    protected function checkPermissions($action , $permissions , $model  ,  $type = 'array' , $role = false){
        if(count($permissions) == 0 && $type == 'array'){
            return false;
        }
        if($type == 'object' && isset($permissions->id)){
            $permissions = [$permissions];
        }
        $array = [];
        $action = $this->returnArray($action);
        $model = $this->returnArray($model);
        foreach($permissions as $permission){
            if(in_array($permission->model , $model)){
                if($role !== false){
                    $array[$permission->model]['actions'] = $this->checkAction($permission , $action);
                } else{
                    $array['actions'] = $this->checkAction($permission , $action);
                }

            }else{
                $array[$permission->model] = false;
            }
        }
        return count($array) == 0 ? false :  $array;
    }

    protected function checkAction($permission , $actions){
        $response = [];
        foreach($this->actionArray() as $action){
            if(in_array($action , $actions)){
                if($permission['action_'.$action] == 'on'){
                    $response[$action] = true;
                }else{
                    $response[$action] = false;
                }
            }else{
                if($permission['action_'.$action] == 'on'){
                    $response[$action] = true;
                }else{
                    $response[$action] = false;
                }
            }
        }
        return count($response) == 0 ? false  : $response;
    }

    protected function getUserPermissions($user , $model){
        $permissions = $user->with(['permission' => function ($query) use  ($model) {
            return  $query->whereIn('model', $this->returnArray($model));
        }])->first();
        return $permissions->permission;
    }

    protected function getGroupPermissions($user , $model){
        $permissions = Group::where('id' , $user->group_id)->with(['permission' => function ($query) use  ($model) {
            return  $query->whereIn('model', $this->returnArray($model));
        }])->first();
        return $permissions->permission;
    }

    protected function getUserRoles($user , $model){
        $roles = $user->with('role')->first();
        $ids = $roles->role->pluck('id');
        $permissions = Role::whereIn('id' , $ids)->with(['permission' => function ($query) use  ($model) {
            return  $query->whereIn('model', $this->returnArray($model));
        }])->get();
        return $permissions;
    }

    protected function getGroupRoles($user , $model){
        $roles = Group::where('id' , $user->group_id)->with('role')->first();
        $ids = $roles->role->pluck('id');
        $permissions = Role::whereIn('id' , $ids)->with(['permission' => function ($query) use  ($model) {
            return  $query->whereIn('model', $this->returnArray($model));
            }])->get();
        return $permissions;
    }

    protected function returnArray($array){
        return is_array($array) ? $array : [$array];
    }

    protected function actionArray(){
        return [
            'add' ,'edit' , 'view' , 'delete'
        ];
    }

    public function can($user , $action , $model){
        $checkGroupPermission = $this->checkPermissions($action , $this->getGroupPermissions($user , $model) , $model , 'object' , true);
        $checkUserPermission = $this->checkPermissions($action , $this->getUserPermissions($user , $model) , $model , 'object' , true);
        $checkGroupRoles = $this->checkRolePermission($action , $this->getGroupRoles($user , $model) , $model);
        $checkUserRoles = $this->checkRolePermission($action , $this->getUserRoles($user , $model) ,$model);
        return [
            'GroupPermissions' => $checkGroupPermission,
            'UserPermissions' => $checkUserPermission,
            'GroupRoles' => $checkGroupRoles,
            'UserRoles' => $checkUserRoles
        ];
    }

    public function canGroupAdd($user  , $model , $action = 'add'){
        $per = $this->assignModelActionToGroup($user  , $model , $action);
        return $per != null ? $per : false;
    }

    public function canGroupEdit($user  , $model , $action = 'edit'){
        $per = $this->assignModelActionToGroup($user  , $model , $action);
        return $per != null ? $per : false;
    }

    public function canGroupDelete($user  , $model , $action = 'delete'){
        $per = $this->assignModelActionToGroup($user  , $model , $action);
        return $per != null ? $per : false;
    }

    public function canGroupView($user  , $model , $action = 'view'){
        $per = $this->assignModelActionToGroup($user  , $model , $action);
        return $per != null ? $per : false;
    }

    public function canUserAdd($user , $model , $action = "add"){
         $per = $this->assignModelActionToUser($user  , $model , $action);
         return $per != null ? $per : false;
    }

    public function canUserEdit($user , $model , $action = "edit"){
        $per = $this->assignModelActionToUser($user  , $model , $action);
        return $per != null ? $per : false;
    }

    public function canUserDelete($user , $model , $action = "delete"){
        $per = $this->assignModelActionToUser($user  , $model , $action);
        return $per != null ? $per : false;
    }

    public function canUserView($user , $model , $action = "view"){
        $per = $this->assignModelActionToUser($user  , $model , $action);
        return $per != null ? $per : false;
    }

    protected function assignModelActionToUser($user ,$model , $action){
        $can = $this->can($user ,$action ,$model);
        if($can['UserPermissions'][$model]['actions'][$action] != false){
            return $can['UserPermissions'][$model]['actions'][$action];
        }
        return $can['UserRoles'][$model]['actions'][$action];
    }

    protected function assignModelActionToGroup($user ,$model , $action){
        $can = $this->can($user ,$action ,$model);
        if($can['GroupPermissions'][$model]['actions'][$action] != false){
            return $can['GroupPermissions'][$model]['actions'][$action];
        }
       return $can['GroupRoles'][$model]['actions'][$action];
    }

    public function canUserGroup($user , $model , $action ){
        $can = $this->can($user ,$action ,$model);
        if($can['GroupPermissions'][$model]['actions'][$action] != false){
            return $can['GroupPermissions'][$model]['actions'][$action];
        }
        if($can['GroupRoles'][$model]['actions'][$action] != false){
            return $can['GroupRoles'][$model]['actions'][$action];
        }
        if($can['UserPermissions'][$model]['actions'][$action] != false){
            return $can['UserPermissions'][$model]['actions'][$action];
        }
        if($can['UserRoles'][$model]['actions'][$action] != false){
            return $can['UserRoles'][$model]['actions'][$action];
        }
        return false;
    }

}