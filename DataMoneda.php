<?php

use Core\Core;
use Curl\Curl;

include "vendor/autoload.php";
include "includes/Core.php";

$Curl = new Curl();
$Core = new Core($Curl);


$Info = $Curl->get('https://api.binance.com/api/v1/exchangeInfo');

foreach ($Info->symbols as $Var){
    $Simbolos = $Var->symbol;

    /*echo $Simbolos.PHP_EOL;
    echo $Var->filters[1]->minQty.PHP_EOL;
    echo $Var->filters[1]->stepSize.PHP_EOL;
    */

    $Prec = $Curl->get('https://api.binance.com/api/v1/ticker/price?symbol='.$Simbolos);

    $Precio = $Prec->price;
    $Tiempo = time();
    $Min    = $Var->filters[1]->minQty;
    $Max    = $Var->filters[1]->maxQty;
    $Step   = $Var->filters[1]->stepSize;

    $Core->MySQL->query("INSERT INTO Ticker (Moneda, Precio, Precio_Test, Actualizada, Minimo, Maximo, Step) VALUES ('$Simbolos', '$Precio', '$Precio', '$Tiempo', '$Min', '$Max', '$Step')");

    if (!empty($Core->MySQL->error)){
        echo $Core->error('Error en insersion a la base de datos: '.$Core->MySQL->real_escape_string($Core->MySQL->error),001);
    }else{
        echo 'Se ha insertado correctamente el registro'.PHP_EOL;
    }

    sleep(2);

}