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
use Illuminate\Support\Facades\Log;


use Illuminate\Http\Request;

class CardsController extends Controller
{
    public function crearColeccion(Request $req)
    {
        $respuesta = ['status' => 1, 'msg' => ''];
    
        $validator = Validator::make(json_decode($req->getContent(), true), [
            'nombre' => ['required', 'max:50'],
            'simbolo' => ['required', 'max:100'],
            'edicion' => ['required', 'date'],
            'carta' => ['required']
        ]);
    
        if ($validator->fails()) {
             $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();
        } else {

            $datos = $req -> getContent();
            $datos = json_decode($datos); 

             $cartasCol =[];
            foreach ($datos->carta as $cartaNueva) {
                if(isset($cartaNueva->id)){
                    $carta = Carta::where('id','=',$cartaNueva->id)->first();
                    if($carta){
                        array_push($cartasCol,$carta->id);
                    }
                }elseif (isset($cartaNueva->nombre) && isset($cartaNueva->descripcion)) {
                    $nuevaCarta = new Card();
                    $nuevaCarta->name = $nombre->nombre;
                    $nuevaCarta->descripcion = $cartaNueva->descripcion;
    
                        try {
                            $nuevaCarta->save();
                            array_push($cartasCol,$nuevaCarta->id);
                            $respuesta['msg'] ='Carta guardada con id ' .$nuevaCarta->id;
                                        
                        } catch (\Exception $e) {
                            $respuesta['status'] = 0;
                             $respuesta['msg'] ='Se ha producido un error: ' . $e->getMessage();
                        }
                }else{
                    $respuesta['status'] = 0;
                    $respuesta['msg'] ='Los datos ingresados no son correctos';
                }  
            }
    
            if(!empty($cartasCol)){
                $cartaIds = implode (", ",$cartasCol); 
                try{

                    $coleccion = new Coleccion();
                    $coleccion -> nombre = $datos->nombre;
                    $coleccion -> simbolo = $datos->simbolo;
                    $coleccion -> edicion = $datos->edicion;
                    $coleccion->save();
                    $respuesta["msg"] = "Coleccion guardada con id ".$coleccion->id;
                     
                    foreach($cartasCol as $id){
                    $coleccionCarta = new ColeccionCarta();
                    $coleccionCarta->id_carta = $id;
                    $coleccionCarta->id_coleccion = $coleccion->id;
                    $coleccionCarta->save();
                    }
                    $respuesta['msg'] ='Se ha creado la coleccion con id: '.$coleccionCarta->id .' y se le han agregado las cartas con id: '.$cartaIds;
                
                }catch (\Exception $e) {
                    $respuesta['status'] = 0;
                    $respuesta['msg'] ='Se ha producido un error: ' . $e->getMessage();
                }
            }
        }
        
        return response()->json($respuesta);
    }
    public function asociarCartaColeccion(Request $req)
    {
         $respuesta = ['status' => 1, 'msg' => ''];

         $validator = Validator::make(json_decode($req->getContent(), true), [
            'carta' => ['required'],
            'coleccion' => ['required']
        ]);
    
        if ($validator->fails()) {
             $respuesta['status'] = 0;
            $respuesta['msg'] = $validator->errors();

        } else {

            $datos = $req -> getContent();
            $datos = json_decode($datos); 

            try{
                $coleccionCarta = new ColeccionCarta();
                $coleccionCarta->id_carta = $datos->carta;
                $coleccionCarta->id_coleccion = $datos->coleccion;
                $coleccionCarta->save();
                

                $respuesta['msg'] ='Se ha agregado la coleccion con id: '.$datos->coleccion .' y se le ha agregado la carta con id: '.$datos->carta;
        
            }catch (\Exception $e) {
                $respuesta['status'] = 0;
                $respuesta['msg'] ='Se ha producido un error: ' . $e->getMessage();
            }
        }
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
        log::info("Carga de la funcion en la busqueda");
        try{

            if($busqueda -> has('busqueda')){
                log::info("Entra en el try y comprueba si se ha introducido la busqueda");

               $cartas = Carta::select(['nombre','descripcion','id'])                           
                        ->where('nombre','like','%'. $busqueda -> input('busqueda').'%')
                        ->get();
                        log::info("Realiza la busqueda en funcion del parametro introducido");

                        $respuesta['datos'] = $cartas;
            }else{
                log::info("No se ha introducido ninguna busqueda por lo que devuelve el mensaje de introducir busqueda");

                $respuesta['msg'] = "Introduce una busqueda";
            }
            
            
        }catch(\Exception $e){
            log::info("Entra en el catch y muestra el error");
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
