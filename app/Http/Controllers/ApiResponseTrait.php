<?php

namespace App\Http\Controllers;

trait ApiResponseTrait{
    public function ApiResponse($data = null ,$msg =null,$status=null){
        $array = [
            'data'=>$data,
            'message'=>$msg,
            'status'=>$status
        ];
        return response($array,$status);
        
    }
}