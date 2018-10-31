<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
use Codedungeon\PHPCliColors\Color;
use Core\Core;
use Curl\Curl;

include "vendor/autoload.php";
include "includes/Core.php";

$Curl = new Curl();
$Core = new Core($Curl);

echo Color::RED."Iniciamos Fase2 Robot Copy Trade V1.1".PHP_EOL;

$i = 3;
while ($i > 2){

    $Consulta1 = $Core->MySQL->query("SELECT * FROM Operaciones WHERE Estado='0' AND Ingresada='1' AND Ob1_A='1' ORDER BY RAND() LIMIT 0,1");

    if (empty($Core->MySQL->error)){

        if ($Consulta1->num_rows == 1){

            $Op = $Consulta1->fetch_object();

            echo Color::GREEN."Consultamos la Operacion con ID: ".$Op->id.PHP_EOL;

            $Ticker = $Core->GetTicker($Op->Monedas);

            $Precio_Compra  = $Op->Precio_Compra;
            $Objetivo_1     = $Op->Ob1;
            $Objetivo_2     = $Op->Ob2;
            $oid            = $Op->id;


            if ($Ticker->Precio >= $Objetivo_2){

                $Core->MySQL->query("UPDATE Operaciones SET Ob1_A='2', Stop='$Objetivo_2' WHERE id='$oid'");

                if (empty($Core->MySQL->error)){
                    echo Color::GREEN."Se ha alcanzado el Objetivo 1, marcamos como realizado y enviamos orden a la Fase 3".PHP_EOL;
                }else{
                    echo Color::RED."Ha ocurrido un error al actualizar el objetivo ".$Core->MySQL->real_escape_string($Core->MySQL->error).PHP_EOL;
                }

            }else{

                if ($Ticker->Precio == $Precio_Compra){

                    echo Color::GREEN."El Precio es el mismo de la compra, seguimos esperando".PHP_EOL;

                }elseif ($Ticker->Precio <= $Op->Stop){

                }
            }

        }else{
            echo Color::RED."No hay transacciones para consultar".PHP_EOL;
        }

    }else{
        echo Color::RED."Ha ocurrido un error en la consulta SQL: ".$Core->MySQL->real_escape_string($Core->MySQL->error).PHP_EOL;
    }

    sleep(1);

}
