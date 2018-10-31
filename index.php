<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
use Codedungeon\PHPCliColors\Color;
use Core\Core;

    include "vendor/autoload.php";
    include "includes/Core.php";
    $Curl = Curl();

    $Core   = new Core($Curl);


    echo Color::RED .date('H:i:s'). " Iniciando Robot Copy Trade V 1.0".PHP_EOL;
    $i = 3;
    while ($i > 1){
        echo Color::BLUE.date('H:i:s')." Verificando operaciones nuevas".PHP_EOL;
        sleep(1);

        $Consulta1 = $Core->MySQL->query("SELECT * FROM Operaciones WHERE Estado = '0' AND Ingresada='0'");

        //print_r($Consulta1);

        if ($Consulta1->num_rows > 0){

           // print_r($Consulta1->fetch_object());

            while ($Op = $Consulta1->fetch_object()){

                echo Color::GREEN.date('H:i:s'). " Se encontro una operacion de ".$Op->Monedas." Comenzamos con las ordenes".PHP_EOL;
                sleep(3);

                $Consulta2 = $Core->MySQL2->query("SELECT * FROM Usuarios WHERE Estado='1' AND CopyTrade='1'");
                if ($Consulta2->num_rows > 0){

                    while ($Usuario = $Consulta2->fetch_object()){
                        echo Color::GREEN.date('H:i:s')." Usuario: ".$Usuario->Usuario." tiene el bot activado, ingresando orden".PHP_EOL;
                        usleep(500);

                        if ($Usuario->Test == 0){

                            echo Color::CYAN.date('H:i:s')." Verificamos que este correcto sus credenciales de acceso...".PHP_EOL;
                            usleep(500);

                            try{

                                $Binance = new Binance\API($Usuario->Api,$Usuario->Secret);
                                //$Binance = new Binance\RateLimiter($Binance);

                                Color::GREEN."Verificamos que el precio ingresado no sea superior al actual ".PHP_EOL;

                                $PrecioTicker = $Binance->price($Op->Monedas);

                                $PrecioPor =  (50*$PrecioTicker/100);

                                $Limite = $PrecioTicker + $PrecioPor;

                                if ($Op->Precio_Compra >= $Limite){

                                    $Def = ($Op->Precio_Compra - $PrecioTicker);
                                    $Deface = (($Def/$PrecioTicker)*100);
                                    $id = $Op->id;

                                    echo Color::RED."Se ha detectado un intento de ABUSO, enviando la notificación a los administradores".PHP_EOL;

                                    $Core->Slack('Se ha detectado un intento de abuso del sistema, es necesario revisar los accesos a las cuentas de Administradores 
                                    UUID: '.$Op->UUID.' 
                                    Moneda: '.$Op->Monedas.' 
                                    Precio de Compra: '.$Op->Precio_Compra.'
                                    Precio Actual: '.$PrecioTicker.' 
                                    Deface:'.$Deface.'%
                                    Se ha cancelado la operacion.
                                    ',"#errores");

                                    $Core->MySQL->query("UPDATE Operaciones SET Estado='6', Ingresada='1' WHERE id='$id'");

                                    //echo $Core->MySQL->error;

                                }else{

                                    switch ($Op->Tipo){
                                        case "1":

                                            echo Color::GREEN."Ingresamos la orden".PHP_EOL;

                                            $Binance = new \Binance\API($Usuario->Api,$Usuario->Secret);

                                            $Core->MakeBuy($Op,$Usuario->id,$Binance);

                                            $idop = $Op->id;

                                            echo $Core->MySQL2->error;

                                            $Core->MySQL->query("UPDATE Operaciones SET Ingresada='1' WHERE id='$idop'");

                                            break;

                                        default:

                                            echo Color::RED."Error Grave".PHP_EOL;

                                            break;
                                    }

                                }

                            }catch (Exception $e){
                                echo Color::RED."Errror: ".$e->getMessage().PHP_EOL;
                            }

                        }else{
                            echo Color::RED.date('H:i:s')." El sistema está en modo pruebas".PHP_EOL;
                        }
                    }

                }

            }

        }else{
            echo Color::BOLD_WHITE. "No hay Operaciones a ingresar".PHP_EOL;

            sleep(10);
        }
    }
