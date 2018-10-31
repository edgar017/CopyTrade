<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    use Codedungeon\PHPCliColors\Color;
    use Core\Core;

    include "vendor/autoload.php";
    include "includes/Core.php";

    $Core   = new Core();

    echo Color::RED .date('H:i:s'). " Iniciando Robot Copy Trade V 1.0 Fase 2".PHP_EOL;

    $i = 3;
    while ($i > 1){

        $Consulta = $Core->MySQL->query("SELECT * FROM Operaciones WHERE Estado='0' AND Ingresada='1' ORDER BY RAND() LIMIT 0,1");

        if ($Consulta->num_rows == 1){

            $Op = $Consulta->fetch_object();

            echo Color::GREEN.date('H:i:s'). " Obtenemos el Precio Actual de la Moneda".PHP_EOL;

            $PrecioActual = $Core->GetTicker($Op->Monedas);

            sleep(1);

            if ($Op->Ob1_A){

                if ($Op->Ob2_A){

                    if ($Op->Ob3_A){

                        if ($Op->Ob4_A){

                            if (empty($Op->Ob4)){
                                echo  Color::CYAN.date('H:i:s'). " No hay un Objetivo 4 definido para la operación, esperamos a que descienda el mercado al objetivo anterior para vender. ".PHP_EOL;
                            }else {

                                if ($PrecioActual->Precio_Test >= $Op->Ob3) {

                                    $oid = $Op->id;
                                    $Core->MySQL->query("UPDATE Operaciones SET Ob2_A='1' WHERE id='$oid'");
                                    $Core->MySQL->query("UPDATE Operaciones SET Stop='" . $PrecioActual->Precio_Test . "' WHERE id='$oid'");
                                    echo Color::GREEN . " Se marca como cumplido el objetivo 2" . PHP_EOL;

                                } else {
                                    echo Color::GREEN . date('H:i:s') . " La operación se encuentra en espera ya que no ha alcanzado el segundo objetivo." . PHP_EOL;
                                }
                            }

                        }else{

                            if (empty($Op->Ob3)){
                                echo  Color::CYAN.date('H:i:s'). " No hay un Objetivo 4 definido para la operación, esperamos a que descienda el mercado al objetivo anterior para vender. ".PHP_EOL;
                            }else {

                                if ($PrecioActual->Precio_Test >= $Op->Ob3) {

                                    $oid = $Op->id;
                                    $Core->MySQL->query("UPDATE Operaciones SET Ob2_A='1' WHERE id='$oid'");
                                    $Core->MySQL->query("UPDATE Operaciones SET Stop='" . $PrecioActual->Precio_Test . "' WHERE id='$oid'");
                                    echo Color::GREEN . " Se marca como cumplido el objetivo 2" . PHP_EOL;

                                } else {
                                    echo Color::GREEN . date('H:i:s') . " La operación se encuentra en espera ya que no ha alcanzado el segundo objetivo." . PHP_EOL;
                                }
                            }

                        }

                    }else{

                        if ($PrecioActual->Precio_Test <= $Op->Stop){
                            echo Color::CYAN.date('H:i:s'). " Se ha detectado una caida de mercado, iniciamos con el Stop Lost, lanzamos venta ".$PrecioActual->Precio_Test.PHP_EOL;
                        }else{

                            if (empty($Op->Ob3)){
                                echo  Color::CYAN.date('H:i:s'). " No hay un Objetivo 3 definido para la operación, esperamos a que descienda el mercado al objetivo anterior para vender. ".PHP_EOL;
                            }else {

                                if ($PrecioActual->Precio_Test >= $Op->Ob3) {

                                    $oid = $Op->id;
                                    $Core->MySQL->query("UPDATE Operaciones SET Ob2_A='1' WHERE id='$oid'");
                                    $Core->MySQL->query("UPDATE Operaciones SET Stop='" . $PrecioActual->Precio_Test . "' WHERE id='$oid'");
                                    echo Color::GREEN . " Se marca como cumplido el objetivo 2" . PHP_EOL;

                                } else {
                                    echo Color::GREEN . date('H:i:s') . " La operación se encuentra en espera ya que no ha alcanzado el segundo objetivo." . PHP_EOL;
                                }
                            }

                        }

                    }

                }else{

                    if ($PrecioActual->Precio_Test <= $Op->Stop){
                        echo Color::CYAN.date('H:i:s'). " Se ha detectado una caida de mercado, iniciamos con el Stop Lost, lanzamos venta ".$PrecioActual->Precio_Test.PHP_EOL;
                    }else{

                        if ($PrecioActual->Precio_Test >= $Op->Ob2){

                            $oid = $Op->id;
                            $Core->MySQL->query("UPDATE Operaciones SET Ob2_A='1' WHERE id='$oid'");
                            $Core->MySQL->query("UPDATE Operaciones SET Stop='".$PrecioActual->Precio_Test."' WHERE id='$oid'");
                            echo Color::GREEN." Se marca como cumplido el objetivo 2".PHP_EOL;

                        }else{
                            echo Color::GREEN.date('H:i:s')." La operación se encuentra en espera ya que no ha alcanzado el segundo objetivo.".PHP_EOL;
                        }

                    }

                }

            }else{

                if ($PrecioActual->Precio_Test <= $Op->Stop){
                    echo Color::CYAN.date('H:i:s'). " Se ha detectado una caida de mercado, iniciamos con el Stop Lost ".$PrecioActual->Precio_Test.PHP_EOL;
                }else{

                    if ($PrecioActual->Precio_Test >= $Op->Ob1){

                        $oid = $Op->id;
                        $Core->MySQL->query("UPDATE Operaciones SET Ob1_A='1' WHERE id='$oid'");
                        $Core->MySQL->query("UPDATE Operaciones SET Stop='".$PrecioActual->Precio_Test."' WHERE id='$oid'");
                        echo Color::GREEN." Se marca como cumplido el objetivo 1".PHP_EOL;

                    }else{
                        echo Color::GREEN.date('H:i:s')." La operación se encuentra en espera ya que no ha alcanzado el primer objetivo.".PHP_EOL;
                    }

                }

            }

        }else{
            echo Color::RED.date('H:i:s')." No hay operaciones abiertas para revisar".PHP_EOL;

            sleep(5);
        }

        //$i++;

    }