<?php

use Codedungeon\PHPCliColors\Color;
use Core\Core;
use Curl\Curl;

include "vendor/autoload.php";
    include "includes/Core.php";

    $Curl = new Curl();
    $Core = new Core($Curl);

    echo Color::RED."Iniciamos el Robot v1.1".PHP_EOL;

    $i = 3;
    while ($i > 2){

        echo Color::GREEN."Consultamos todas las operaciones pendientes por crear".PHP_EOL;

        $Consulta = $Core->MySQL->query("SELECT * FROM Operaciones WHERE Ingresada='0' ORDER BY RAND() LIMIT 0,1");

        if (empty($Core->MySQL->error)){

            if ($Consulta->num_rows == 1){

                $Op = $Consulta->fetch_object();

                echo Color::GREEN."Iniciamos la creacion de las operaciones con el ID: ".$Op->id.PHP_EOL;

                //sleep(1);

                echo Color::GREEN."Iniciamos la consulta de usuarios".PHP_EOL;

                $Consulta2 = $Core->MySQL2->query("SELECT * FROM Usuarios");

                if (empty($Core->MySQL->error)) {

                    if ($Consulta2->num_rows > 0) {

                        $oid = $Op->id;

                        $Core->MySQL->query("UPDATE Operaciones SET Ingresando='1' WHERE id='$oid'");

                        $e = 1;

                        while ($Usuario = $Consulta2->fetch_object()) {

                            try {

                                /*$Binance = new \Binance\API($Usuario->Api, $Usuario->Secret);

                                $Binance->useServerTime();

                                $Ticker = $Binance->prices();
                                $BitCoin = $Binance->balances($Ticker);
                                */

                                //print_r($BitCoin);

                                /*
                                 * Verrificamos que el usuario tenga saldo
                                 */

                                //if ($BitCoin['BTC']['available'] > 0){
                                if (1 > 0) {

                                    /*
                                     * El Usuario cuenta con saldo, procedemos a calcular el monto que se invertira
                                     **/


                                    //$Inversion = number_format((($BitCoin['BTC']['available'] * $Op->Inversion) / 100),8);

                                    $Inversion = number_format(((1 * $Op->Inversion) / 100), 8);

                                    /*
                                     * Calculamos la cantidad de monedas que se compraran
                                     */

                                    $Cantidad = number_format(($Inversion / $Op->Precio_Compra), 4);


                                    //echo  $Op->Inversion. " * ".$BitCoin['BTC']['available']." / 100".PHP_EOL;
                                    // echo  $Cantidad.PHP_EOL;

                                    //usleep(500);

                                    $Ticker = $Core->GetTicker($Op->Monedas);

                                    if ($Ticker->Moneda == $Op->Monedas) {

                                        if ($Cantidad > $Ticker->Minimo AND $Cantidad < $Ticker->Maximo) {

                                            //$Cantidad = $Binance->roundStep($Cantidad,$Ticker->Step);

                                            $Compra = $Core->buy_test($Op->Monedas, $Cantidad, $Op->Precio_Compra);


                                            $Vars = array(
                                                'Master' => $Op->UUID,
                                                'orderId' => $Compra['orderId'],
                                                'clientOrderId' => $Compra['clientOrderId'],
                                                'transactTime' => $Compra['transactTime'],
                                                'price' => $Compra['price'],
                                                'origQty' => $Compra['origQty'],
                                                'uid' => $Usuario->id,
                                                'Moneda' => $Compra['symbol'],
                                                'Ob1' => $Op->Ob1,
                                                'Ob2' => $Op->Ob2,
                                                'Ob3' => $Op->Ob3,
                                                'Ob4' => $Op->Ob4,
                                                'Stop' => $Op->Stop
                                            );

                                            $Respues = json_decode($Core->MakeComp($Vars));


                                            if ($Respues->Codigo == 200) {
                                                echo \Codedungeon\PHPCliColors\Color::GREEN . "Se ha ingresado correctamente la orden al Usuario con el ID: ".$Usuario->id . PHP_EOL;

                                                if ($e == $Consulta2->num_rows) {
                                                    $oid = $Op->id;
                                                    $Core->MySQL->query("UPDATE Operaciones SET Ingresada='1' WHERE id='$oid'");
                                                }

                                            } else {
                                                echo \Codedungeon\PHPCliColors\Color::RED . " Ha ocurrido un error: " . $Respues->Texto . PHP_EOL;
                                            }


                                            /*if ($Op->Precio_Compra * $Cantidad < $Hola->filters[2]->minNotional){

                                                $Cantidad = $Hola->filters[2]->minNotional / $Op->Precio_Compra;

                                                $Cantidad =  $Binance->roundStep($Cantidad,$Hola->filters[1]->stepSize);


                                                print_r($Binance->buyTest($Op->Monedas,$Cantidad,$Op->Precio_Compra));

                                            }else{

                                                $Cantidad = $Binance->roundStep($Cantidad,$Hola->filters[1]->stepSize);

                                                print_r($Binance->buy($Op->Monedas,$Cantidad,$Op->Precio_Compra));

                                            }*/


                                        } else {
                                            echo \Codedungeon\PHPCliColors\Color::RED . "La Cantidad que se ha ingresado es menor a la permitida, marrcamos como ingresado el usuariro" . PHP_EOL;
                                        }

                                    }

                                } else {

                                    /*
                                     * El usuario no cuenta con saldo suficiente, notificamos el error
                                     */

                                    echo \Codedungeon\PHPCliColors\Color::RED . "El Usuario no cuenta con saldo suficiente para hacer Trading" . PHP_EOL;

                                }

                            } catch (Exception $e) {
                                echo $e->getMessage() . PHP_EOL;
                            }

                            $e++;
                        }

                    }else{
                        echo $Core->error('No hay usuarios disponibles',004).PHP_EOL;
                    }
                }else{
                    echo $Core->error('Error en consulta SQL: '.$Core->MySQL->real_escape_string($Core->MySQL->error),003).PHP_EOL;
                }

            }else{
                echo Color::CYAN."No hay Transacciones para procesar".PHP_EOL;
            }

        }else{
            echo $Core->error('Error en la consulta SQL: '.$Core->MySQL->error,001).PHP_EOL;
        }

    //break;
    sleep(5);
    }
