<?php

namespace App\Application\Controllers;

use App\Application\Model\User;
use App\Application\PermissionTraits\PermissionControl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;

abstract class AbstractController extends  Controller{

    public $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function GetAll($view , $with = [] , $paginate = 30){
        $items = $this->model->with($with)->paginate($paginate);
        return view($view , compact('items'));
    }

    public function createOrEdit($view , $id = null , $data = ['']){
        if($id == null){
            return view($view , compact('data'));
        }
        $item = $this->model->where('id' , $id)->first();
        return view($view , compact('item' , 'data'));
    }

    public function storeOrUpdate(Request $request , $id = null , $callback = true){
        $validation =  $this->itemValidation($request->all());
        if($validation !== true){
            return redirect()->back()->with(['errors' => $validation]);
        }
        $field = checkIfFiledFile($request->all());
        if($field){
            $request = $this->uploadFile($request , $field);
        }else{
            $request = $request->all();
        }
        if($id == null){
            return $this->storeItem($request , $callback);
        }
        $item =  $this->model->where('id' , $id)->first();
        return $this->updateItem($request , $item , $callback , $id);
    }

    public function itemValidation($array){
        $valid = Validator::make($array,$this->model->validation);
        if($valid->fails()){
            return $valid->errors();
        }
        return true;
    }

    public function storeItem($array  , $callback){
        $new = $this->model->create($array);
        $this->saveRolePermission($array , $new);
        if($callback !== true){
            return redirect($callback);
        }
        return $new;
    }

    public function updateItem($array , $item , $callback , $id){
       $update = $item->update($array);
        $this->saveRolePermission($array , null , $id);
        if($update){
            if($callback !== true){
                return redirect($callback);
            }
            return redirect()->back();
        }
        return redirect(404);
    }

    protected function saveRolePermission($array , $item = null ,  $id = null){
        $addPermission = $item != null ? $item  : $this->model->find($id);
        if(method_exists( $this->model ,'role') && class_basename($this->model) != 'Permission'){
            $roles = array_has($array , 'roles') ?  $array['roles'] : [];
            $this->saveRoles($roles , $addPermission);
        }
        if(method_exists( $this->model ,'permission')) {
            $permission = array_has($array , 'permission') ?  $array['permission'] : [];
            $this->savePermission($permission, $addPermission);
        }
    }

    public function saveRoles($array , $item){
        if(count($array) > 0){
            $request = $this->checkIfArray($array);
            return $item->role()->sync($request);
        }
        return $item->role()->sync([]);
    }

    public function savePermission($array , $item){
        if(count($array) > 0){
            $request = $this->checkIfArray($array);
            return $item->permission()->sync($request);
        }
        return $item->permission()->sync([]);
    }

    public function deleteItem($id , $callBack = null){
        $item = $this->model->find($id);
        $item = $item ? $item : null;
        if($item == null){
            return redirect(404);
        }
        if($item->delete()){
            if($callBack != null){
                return redirect($callBack);
            }
            return redirect()->back();
        }
        return redirect('404');
    }

    public function uploadFile($request , $field){
        $destinationPath = env('UPLOAD_PATH');
        $extension = $request->file($field)->getClientOriginalExtension();
        $fileName = rand(11111,99999).'_'.time().'.'.$extension;
        if($request->file($field)->move($destinationPath  , $fileName)){
            $request = $request->except($field);
            $request[$field] = $fileName;
            return $request;
        }
        return false;
    }



    protected function checkIfArray($request){
        return is_array($request) ? $request : [$request];
    }



}