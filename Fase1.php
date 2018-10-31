<?php
/**
 * Created by PhpStorm.
 * User: edgaralanguerreromontejo
 * Date: 17/10/18
 * Time: 01:49
 */

include "vendor/autoload.php";
include "includes/Core.php";



//$Color  = new \Codedungeon\PHPCliColors\Color();
$Curl = new \Curl\Curl();
$Core   = new \Core\Core($Curl);


$i = 3;
while ($i > 2){

    echo \Codedungeon\PHPCliColors\Color::CYAN."Iniciamos El Robot V1.1 ".date('d/m/Y H:i A',time()).PHP_EOL;


    /*
     * Consultamos si existen operaciones para abrir
     */

    $Consulta = $Core->MySQL->query("SELECT * FROM Operaciones WHERE Estado='0' AND Ingresada='0' ORDER BY RAND() LIMIT 0,1");

    /*
     * Si hay algún error, notficamos y sacamos al bufferr;
     */

    if (empty($Core->MySQL->error)) {

        /*
         * Verificamos que tenemos registro, si el resultado es igual a uno, procedemos
         */

        if ($Consulta->num_rows == 1) {

            /*
             * Asignamos la variable de Op para los datos de la operacion
             */

            $Op = $Consulta->fetch_object();


            /*
             * Hacemos el foreach
             */

            $Ex = $Curl->get('https://labs.mybotbcc.io/info');
            $Ex2 = $Ex->symbols;

            foreach ($Ex2 as $Hola){
                $SimboloHola = $Hola->symbol;
                $HolaCantidad= $Hola->filters[1]->minQty;
            }

            /*
             * Al tener una operración para ingresar procedemos a consultar todos los usuarios que tengan el robot activo
             */

            $Consulta2 = $Core->MySQL2->query("SELECT * FROM Usuarios");

            /*
             * Verificamos que no tengamos errores
             */

            if (empty($Core->MySQL->error)) {

                /*
                 * Verificamos que tengamos usuarios activos, de lo contrario notificamos el error
                 */

                if ($Consulta2->num_rows > 0) {

                    /*
                     * Listamos todos los usuarios en un Objeto
                     * Así mismo almacenamos el valor de una variable en conteo para poder saber cual es el ultimo usuario
                     */

                    $e = 1;

                    while ($Usuario = $Consulta2->fetch_object()){

                        /*
                         * Iniciamos la instancia de Binance para verificar que tenga saldo El Usuario
                         */

                        try {

                            /*
                             *
                             $Binance = new \Binance\API($Usuario->Api, $Usuario->Secret);

                            $Binance->useServerTime();

                            $Ticker = $Binance->prices();
                            $BitCoin = $Binance->balances($Ticker);
                            */

                            //print_r($BitCoin);

                            /*
                             * Verrificamos que el usuario tenga saldo
                             *

                            if ($BitCoin['BTC']['available'] > 0){

                                /*
                                 * El Usuario cuenta con saldo, procedemos a calcular el monto que se invertira
                                 **/


                                //$Inversion = number_format((($BitCoin['BTC']['available'] * $Op->Inversion) / 100),8);

                                $Inversion = number_format(((1 * $Op->Inversion) / 100),8);

                                /*
                                 * Calculamos la cantidad de monedas que se compraran
                                 */

                                $Cantidad = number_format(($Inversion / $Op->Precio_Compra),4);


                                //echo  $Op->Inversion. " * ".$BitCoin['BTC']['available']." / 100".PHP_EOL;
                               // echo  $Cantidad.PHP_EOL;

                                 sleep(1);
                                //$Ex = $Curl->get('https://www.binance.com/api/v1/exchangeInfo');
                                //$Ex = $Curl->get('https://labs.mybotbcc.io/info');
                                //sleep(1);
                                //$Ex2 = $Ex->symbols;

                                //foreach ($Ex2 as $Hola){
                                    if($SimboloHola == $Op->Monedas){

                                        if($Cantidad > $HolaCantidad){

                                            //$Cantidad = $Binance->roundStep($Cantidad,$Hola->filters[1]->stepSize);

                                            $Compra   = $Core->buy_test($Op->Monedas,$Cantidad,$Op->Precio_Compra);


                                            $Vars = array(
                                                'Master'  => $Op->UUID,
                                                'orderId' => $Compra['orderId'],
                                                'clientOrderId' => $Compra['clientOrderId'],
                                                'transactTime' => $Compra['transactTime'],
                                                'price'     => $Compra['price'],
                                                'origQty'   => $Compra['origQty'],
                                                'uid' => $Usuario->id,
                                                'Moneda' => $Compra['symbol'],
                                                'Ob1' => $Op->Ob1,
                                                'Ob2' => $Op->Ob2,
                                                'Ob3' => $Op->Ob3,
                                                'Ob4' => $Op->Ob4,
                                                'Stop' => $Op->Stop
                                            );

                                            $Respues = json_decode($Core->MakeComp($Vars));


                                            if ($Respues->Codigo == 200){
                                                echo \Codedungeon\PHPCliColors\Color::GREEN."Se ha ingresado correctamente la orden ".PHP_EOL;

                                                if ($e == $Consulta2->num_rows){
                                                    $oid = $Op->id;
                                                    $Core->MySQL->query("UPDATE Operaciones SET Ingresada='1' WHERE id='$oid'");
                                                }

                                            }else{
                                                echo \Codedungeon\PHPCliColors\Color::RED." Ha ocurrido un error: ".$Respues->Texto.PHP_EOL;
                                            }


                                            /*if ($Op->Precio_Compra * $Cantidad < $Hola->filters[2]->minNotional){

                                                $Cantidad = $Hola->filters[2]->minNotional / $Op->Precio_Compra;

                                                $Cantidad =  $Binance->roundStep($Cantidad,$Hola->filters[1]->stepSize);


                                                print_r($Binance->buyTest($Op->Monedas,$Cantidad,$Op->Precio_Compra));

                                            }else{

                                                $Cantidad = $Binance->roundStep($Cantidad,$Hola->filters[1]->stepSize);

                                                print_r($Binance->buy($Op->Monedas,$Cantidad,$Op->Precio_Compra));

                                            }*/





                                        }else{
                                            echo \Codedungeon\PHPCliColors\Color::RED. "La Cantidad que se ha ingresado es menor a la permitida, marrcamos como ingresado el usuariro".PHP_EOL;
                                        }

                                    }
                                //}


                                /*
                                 * Verrificamos que no haya algun error
                                 */

                                if (isset($Resultado['msg'])){
                                    echo \Codedungeon\PHPCliColors\Color::RED."Ha ocurrido un error: ".$Resultado['msg'].PHP_EOL;
                                }else{



                                }

                                //$Core->MakeOp2->($Op);

                            /*}else{

                                /*
                                 * El usuario no cuenta con saldo suficiente, notificamos el error
                                 *

                                echo \Codedungeon\PHPCliColors\Color::RED."El Usuario no cuenta con saldo suficiente para hacer Trading".PHP_EOL;

                            }*/


                        }catch (Exception $e){
                            echo \Codedungeon\PHPCliColors\Color::RED.$e->getMessage();
                        }

                        $e++;

                    }

                } else {
                    /*
                     * No se han encontrado usuarios, procedemos a notificar el error
                     */

                    echo \Codedungeon\PHPCliColors\Color::RED . "No hay usuarios activos a los cuales ingresar la orden " . date('d/m/Y H:i A', time()) . PHP_EOL;
                }
            }else{
                /*
                 * Tenemos un error, notificamos en consola
                 */

                echo \Codedungeon\PHPCliColors\Color::RED."Hay un errorr en consulta de base de datos ".$Core->MySQL2->error." ".date('d/m/Y H:i A',time()).PHP_EOL;
            }

        } else {
            /*
             * Si el resultado es menor que uno notificamos el error
             */
            echo \Codedungeon\PHPCliColors\Color::RED . "No hay transacciones para ingresar " . date('d/m/Y H:i A', time()) . PHP_EOL;
        }


    }else{
        echo \Codedungeon\PHPCliColors\Color::RED."Ha ocurrido un error en la base de datos: ".$Core->MySQL->error." ".date('d/m/Y H:i A',time()).PHP_EOL;
    }


    /*
     * Dormimos el rrobot cinco segundos para no sobrecargar el servidor
     */
    sleep(5);
}