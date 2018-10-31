<?php
    /*
     * Esta es la fase 2 del Robot, en la cual el robot va a revisar el estado de las operaciones
     */

    session_start();

    include "vendor/autoload.php";
    include "includes/Core.php";

    $Core = new \Core\Core();
    $Curl = new Curl\Curl();



    echo \Codedungeon\PHPCliColors\Color::RED."Iniciamos el robot Copy Trade Fase 1".PHP_EOL;

    $i=0;
    while ($i < 3){

        echo \Codedungeon\PHPCliColors\Color::GREEN." Revisamos las operaciones que hay actualmente en el robot".PHP_EOL;

        $Consulta = $Core->MySQL->query("SELECT * FROM Operaciones WHERE Estado='0' AND Ingresada='1' AND Ob1_A = '0' ORDER BY RAND() LIMIT 0,1");

        if (empty($Core->MySQL->error)) {
            if ($Consulta->num_rows == 1) {

                $Op = $Consulta->fetch_object();

                /*
                 * Obtenemos el precio actual de la moneda para mirar el precio y si ha llegado a los objetivos
                 */

                try {
                    $Precio = $Core->GetTicker($Op->Monedas);

                    $Ob1 = $Op->Ob1;
                    $Ob2 = $Op->Ob2;
                    $Ob3 = $Op->Ob3;
                    $Ob4 = $Op->Ob4;
                    $oid = $Op->id;

                    if (isset($Ob1)){
                        if ($Op->Ob1_A){
                            echo \Codedungeon\PHPCliColors\Color::CYAN."La operacion ya alcanzo el objetivo 1, brincamos el objetivo 1 y revisamos el objetivo2 ".PHP_EOL;
                        }else {
                            if ($Precio->Precio >= $Ob1) {

                                $Core->MySQL->query("UPDATE Operaciones SET Ob1_A = '1' WHERE id='$oid'");

                                echo \Codedungeon\PHPCliColors\Color::GREEN . "Se ha actualizado el objetivo, ya que se ha alcanzado el objetivo 1" . PHP_EOL;

                            }else{
                                echo \Codedungeon\PHPCliColors\Color::GREEN."La moneda aún no alcanza el nivel, verificamos el stop lost para evitar perdida ".PHP_EOL;

                                if ($Precio->Precio <= $Op->Stop){

                                    /*
                                     * Iniciamos el Stop Lost
                                     */

                                    echo \Codedungeon\PHPCliColors\Color::RED."Se ha alcanzado el Stop Lost, Enviamos orden de venta".PHP_EOL;

                                    //$Core->sell_test()

                                }else{
                                    /*
                                     * Esperamos que alcance algún objetivo
                                     */
                                    echo \Codedungeon\PHPCliColors\Color::GREEN."La Moneda aún no alcanza el Stop lost ni el Objetivo 1, seguimos esperando a que la moneda suba".PHP_EOL;
                                }
                            }
                        }
                    }else{
                        echo \Codedungeon\PHPCliColors\Color::RED."La operacion no cuenta con Objetivo 1".PHP_EOL;
                    }


                }catch (Exception $e){
                    echo \Codedungeon\PHPCliColors\Color::RED.$e->getMessage().PHP_EOL;
                }

            }
        }else{
            echo \Codedungeon\PHPCliColors\Color::RED."Error en consulta SQL: ".$Core->MySQL->error.PHP_EOL;
        }

        sleep(3);
    }

?>