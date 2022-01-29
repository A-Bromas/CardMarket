<?php

namespace App\Http\Controllers;
use App\Models\Carta;
use App\Models\CartaVenta;
use App\Models\User;
use App\Models\Coleccion;
use App\Models\ColeccionCarta;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class CardsController extends Controller
{
    public function crearColeccion(Request $req){

        $respuesta = ["status" => 1, "msg" => ""];

            $usuario = User::where('api_token', '=', $req->api_token)->first();
            if($usuario->roll == 'administrador'){

                $datos = $req -> getContent();
                $datos = json_decode($datos); 

                $coleccion = new Coleccion();
                $coleccion -> nombre = $datos->nombre;
                $coleccion -> simbolo = $datos->simbolo;
                $coleccion -> edicion = $datos->edicion;

                $coleccion->save();
                $respuesta["msg"] = "Coleccion Guardada";

                try {
                    $coleccion->save();
                    $respuesta["msg"] = "Coleccion guardada con id ".$coleccion->id;
                }catch (\Exception $e) {
                    $respuesta["status"] = 0;
                    $respuesta["msg"] = "Se ha producido un error".$e->getMessage();  
                }

            }
        return response()->json($respuesta);
    }
    public function crearCarta(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];

        $validator = Validator::make(json_decode($req->getContent(), true), [
            'nombre' => ['required', 'max:50'],
            'descripcion' => ['required', 'max:400'],
            'coleccion' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {
            //Generar el nueva carta

            $datos = $req->getContent();
            $datos = json_decode($datos);

            $coleccion = Coleccion::where('id','=',$datos->coleccion)->first();

            if ($coleccion) {
                $carta = new Carta();
                $carta->nombre = $datos->nombre;
                $carta->descripcion = $datos->descripcion;

                try {
                    $carta->save();
                   $coleccionCarta = new ColeccionCarta();
                    $coleccionCarta->id_carta = $carta->id;
                    $coleccionCarta->id_coleccion = $coleccion->id;
                    $coleccionCarta->save();
                    $respuesta['msg'] ='Carta guardada con id ' .$carta->id .' y guardado en la coleccion con el id ' .$datos->coleccion;
                } catch (\Exception $e) {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] = 'Se ha producido un error: ' . $e->getMessage();
                }
            } else {
                $respuesta['status'] = 0;
                $respuesta['msg'] = 'La coleccion ingresada no existe';
            }
        }
        return response()->json($respuesta);
    }
    public function busquedaNombre(Request $busqueda){

        $respuesta = ["status" => 1, "msg" => ""];
        try{

            if($busqueda -> has('busqueda')){

               $cartas = Carta::select(['nombre','simbolo','id'])                           
                        ->where('nombre','like','%'. $busqueda -> input('busqueda').'%')
                        ->get();
                        $respuesta['datos'] = $cartas;
            }else{
                $respuesta['msg'] = "Introduce una busqueda";
            }
            
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }
        return response()->json($respuesta);
    }
    public function busquedaVenta(Request $busqueda){

        $respuesta = ["status" => 1, "msg" => ""];
        try{

            if($busqueda -> has('busqueda')){

               $cartas = CartaVenta::select(['carta','id_carta','cantidad','precio','usuario'])                           
                        ->where('carta','like','%'. $busqueda -> input('busqueda').'%')
                        ->get();
                        $respuesta['datos'] = $cartas;
            }else{
                $respuesta['msg'] = "Introduce una busqueda";
            }
            
            
        }catch(\Exception $e){
            $respuesta['status'] = 0;
            $respuesta['msg'] = "Se ha producido un error: ".$e->getMessage();
        }
        return response()->json($respuesta);
    }
}
