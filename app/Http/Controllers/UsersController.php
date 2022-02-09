<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Carta;
use App\Models\CartaVenta;
use App\Models\CartaCompra;


class UsersController extends Controller
{
    public function crear(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];
        $validator = Validator::make(json_decode($req->
        getContent(),true), [
            "name" => 'required|max:50',
            "email" => 'required|email|unique:App\Models\User,email|max:50',
            "password" => 'required|regex:/(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}/',
            "roll" => 'required|in:particular,profesional,administrador'
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
            $usuario -> roll = $datos -> roll;

            
            try{
                 $usuario->save();
                 $respuesta['msg'] = "Usuario guardado con id ".$usuario->id;          
                 
             }catch(\Exception $e){
                 $respuesta['status'] = 0;
                $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
             }
            
        }
       return response()->json($respuesta);
    }
    
    public function login(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];

        $datos = $req -> getContent();
        $datos = json_decode($datos); 
        $usuario = User::where('name', '=', $datos->name)->first();

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
        
 
        $response = ["status" => 1, "msg" => ""];
        $datos = $req->getContent();
        $datos = json_decode($datos);

        $email = $req->email;

        $user = User::where('email', '=', $datos->email)->first();

        try{
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
                $response['msg'] = "Nueva contraseña generada: ".$newPassword;

            }
            else{
                $response['status'] = 0;
                $response['msg'] = "Usuario no encontrado";
            }
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }   
        return response()->json($response);
    }
    public function comprarCarta(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];


            $datos = $req->getContent();
            $datos = json_decode($datos);

            $usuario = User::where('api_token', '=', $req->api_token)->first();

            $carta = Carta::where('id','=',$datos->carta)->first();

            if ($usuario) {
                try {
                    $cartaCompra = new CartaCompra();
                    $cartaCompra->id_carta = $carta->id;
                    $cartaCompra->id_user = $usuario->id;
                    $cartaCompra->save();
                    $respuesta['msg'] ='Carta comprada con id ' .$carta->id;
                } catch (\Exception $e) {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] = 'Se ha producido un error: ' . $e->getMessage();
                }
            } else {
                $respuesta['status'] = 0;
                $respuesta['msg'] = 'La carta ingresada no existe';
            }
        return response()->json($respuesta);
    }
    public function ventaCarta(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];

        $validator = Validator::make(json_decode($req->getContent(), true),
        [
           'id_carta' => ['required', 'integer'],
           'cantidad' => ['required', 'integer'],
           'precio' => ['required', 'numeric','min:0','not_in:0'],

       ]);
        if ($validator->fails()) {
             $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {
            $datos = $req -> getContent();
            $datos = json_decode($datos); 
            $usuario = User::where('api_token', '=', $req->api_token)->first();

            $cartas = Carta::select('id')                           
            ->where('id','=',$datos->id_carta)
             ->get();
            if ($cartas){
                $ventaCarta = new CartaVenta();
                $ventaCarta -> carta = $datos -> id_carta;
                $ventaCarta -> cantidad = $datos -> cantidad;
                $ventaCarta -> precio = $datos->precio;
                $ventaCarta -> usuario = $usuario->id;
                
                try {
                    $ventaCarta->save();
                    $respuesta['msg'] = "Venta de carta guardada con id ".$ventaCarta->id;
                } catch (\Exception $e) {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] ='Se ha producido un error: ' . $e->getMessage();
                }
    
            }else{
                $respuesta['msg'] =
                'La carta que intenta vender no esta registrada, busque el id correcto';
                    
             }
        }
           
            

        return response()->json($respuesta);
    }
    
}
