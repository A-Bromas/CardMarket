<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsersController extends Controller
{
    public function crear(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];
        $validator = Validator::make(json_decode($req->
        getContent(),true), [
            "name" => 'required|max:50',
            "email" => 'required|email|unique:App\Models\User,email|max:50',
            "password" => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/',
            "rol" => 'required|in:particular,professional,administrator'
        ]);

        if($validator -> fails()){
            $respuesta["status"] = 0;
            $respuesta["msg"] = $validator->errors(); 
        } else {

            $datos = $req -> getContent();
            $datos = json_decode($datos); 
    
            $usuario = new User();
            $usuario -> name = $datos -> name;
            $usuario -> email = $datos -> email;
            $usuario -> password = Hash::make($datos->password);
            $usuario -> rol = $datos -> rol;

            try {
                $usuario->save();
                $respuesta["msg"] = "Usuario guardado con id ".$usuario->id;
            }catch (\Exception $e) {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
            }
        }
       return response()->json($respuesta);
    }
    
    public function login(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];

        $datos = $req -> getContent();
        $datos = json_decode($datos); 
        $email = $req->email;
        $usuario = User::where('email', '=', $datos->email)->first();

        if ($usuario){
            if (Hash::check($datos->password, $usuario -> password)){

                do {
                    $token = Hash::make($usuario->id.now());
                } while(User::where('api_token', $token) -> first());

                $usuario -> api_token = $token;
                $usuario -> save();
                $respuesta["msg"] = "Login correcto, tu api token es: ".$usuario -> api_token;  

            } else {
                $respuesta["status"] = 0;
                $respuesta["msg"] = "La contraseña no es correcta";  
            }

        } else {
            $respuesta["status"] = 0;
            $respuesta["msg"] = "Usuario no encontrado";  
        }

        return response()->json($respuesta);  
    }
    public function recuperarPassword(Request $req){
        
        //Obtener el email y validarlo 
        $response = ["status" => 1, "msg" => ""];
        $data = $req->getContent();
        $data = json_decode($data);

        //Buscar el email
        $email = $req->email;

        //Encontrar al usuario con ese email
        $user = User::where('email', '=', $data->email)->first();

        //Comprobar si existe el usuario
        if($user){
           
            $user->api_token = null;

            
            $password = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz";
            $characterLength  = strlen($password);
            $newPassword = "";

            for($i = 0; $i < 8; $i++){
                $newPassword .= $password[rand(0, $characterLength  -1)];
            }
                  
            //Guardamos al usuario con la nueva contraseña cifrada
            $user->password = Hash::make($newPassword);
            $user->save();
            $response['msg'] = "Nueva contraseña generada: ".$user->password;

        }
        else{
            $response['status'] = 0;
            $response['msg'] = "Usuario no encontrado";
        }
        
        return response()->json($response);
    }
}
