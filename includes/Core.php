<?php
/**
 * Created by PhpStorm.
 * User: edgar017
 * Date: 11/10/18
 * Time: 21:45
 */

namespace Core;
//use Binance;

class Core
{
    public function __construct($Curl)
    {
        $this->MySQL = new \mysqli('localhost', 'root', '4953816A!.', 'Alan');
        $this->MySQL2= new \mysqli('149.28.63.9 ','Alan','4953816A!.','BCC');
        $this->Curl = $Curl;
    }

    public function error($Error,$Codigo){
        return json_encode(array('Codigo' => 500, 'Texto' => $Error, 'Errno' => $Codigo));
    }

    public function buy_test($Moneda,$Cantidad,$Precio){

        $Array = array('Moneda' => $Moneda, 'Precio' => $Precio, 'Cantidad' => $Cantidad);
        $Data  = json_encode($Array);
        $this->Curl->setDefaultJsonDecoder($assoc = true);
        $this->Curl->setHeader('Content-Type', 'application/json');
        $Resp = $this->Curl->post('https://labs.mybotbcc.io/buy',$Data);

        return $Resp;

    }

    public function sell_test($Moneda,$Cantidad,$Precio){

        $Array = array('Moneda' => $Moneda, 'Precio' => $Precio, 'Cantidad' => $Cantidad);
        $Data  = json_encode($Array);
        $this->Curl->setDefaultJsonDecoder($assoc = true);
        $this->Curl->setHeader('Content-Type', 'application/json');
        $Resp = $this->Curl->post('https://labs.mybotbcc.io/sell',$Data);

    }

    public function GetTicker($Moneda,$test = true){

        if ($test){

            $Consulta = $this->MySQL->query("SELECT * FROM Ticker WHERE Moneda='$Moneda' LIMIT 0,1");

            if ($Consulta->num_rows > 0){

                $Moneda = $Consulta->fetch_object();

                return $Moneda;

            }else{
                throw new \Exception('La moneda indicada no existe!');
            }



        }

    }

    public function Slack($Mensaje,$Canal){

        $settings = [
            'username' => 'MyBotBCC',
            'channel' => $Canal,
            'link_names' => true
        ];

        $Slack  = new Maknz\Slack\Client('https://hooks.slack.com/services/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',$settings);

        $Slack->send($Mensaje);

    }

    public function Usuario($id){

        $Consulta = $this->MySQL2->query("SELECT * FROM Usuarios WHERE id='$id'");

        if ($Consulta->num_rows == 1){

            return $Consulta->fetch_object();

        }else{
            throw new \Exception('No se ha encontrado el usuario');
        }

    }

    public function MakeBuy($Op,$uid,$Binance){

        $Usuario = $this->Usuario($uid);

        /*
         *
         * Iniciamos la parte donde se ingresa la orden a Binance
         *
         */

        //$Binance = new Binance\API($Usuario->Api,$Usuario->Secret);

        $Binance->useServerTime();

        $Ticker  = $Binance->prices();

        $Binance->useServerTime();

        $Saldo = $Binance->balances($Ticker);

        $SaldoA = $Saldo['BTC']['available'];

        print_r($Saldo);

        if ($SaldoA > 0) {

            $Inversion = (($SaldoA * $Op->Inversion) / 100);



            $idop = $Op->id;
            $Tiempo = time();
            $Ob1 = $Op->Ob1;
            $Ob2 = $Op->Ob2;
            $Ob3 = $Op->Ob3;
            $Ob4 = $Op->Ob4;
            $UUID = $Op->UUID;
            $Precio = $Op->Precio_Compra;
            $Moneda = $Op->Monedas;
            $Cantidad = $Precio / $Inversion;
            $Stop = $Op->Stop;
            $uid = $Usuario->id;

            $Binance->useServerTime();

            $Test = $Binance->buy_test($Op->Monedas, $Cantidad, $Precio);


            print_r($Test);

            //$Core->MySQL2->query("INSERT INTO Operaciones (Master_UUID, OrderId, transactTime, price, origQty, executedQty, status, timeInForce, side, Moneda, Precio_Venta, Fecha, Ob1, Ob2, Ob3, Ob4, Stop, Cantidad, uid) VALUES ('$UUID', '1234567890', '$Tiempo', '$Precio', '$Cantidad', '$Cantidad', '0', '0', '0', '$Moneda', '0', '$Tiempo', '$Ob1', '$Ob2', '$Ob3', '$Ob4', '$Stop', '$Cantidad', '$uid')");
        }else{
            throw new \Exception('El usuario no tiene saldo');
        }
    }

    public function MakeComp($Vars){

        $Master = $Vars['Master'];
        $UUID   = $Vars['orderId'];
        $CUUID  = $Vars['clientOrderId'];
        $TT     = $Vars['transactTime'];
        $Price  = $Vars['price'];
        $Cant   = $Vars['origQty'];
        $uid    = $Vars['uid'];
        $Ob1    = $Vars['Ob1'];
        $Ob2    = $Vars['Ob2'];
        $Ob3    = $Vars['Ob3'];
        $Ob4    = $Vars['Ob4'];
        $Stop   = $Vars['Stop'];
        $Tiempo = time();
        $Cantidad = $Cant;
        $Moneda = $Vars['Moneda'];


        $this->MySQL2->query("INSERT INTO Operaciones (Master_UUID, OrderId, ClientOrderId, transactTime, price, origQty, status, type, Moneda, Precio_Venta, Fecha, Ob1, Ob2, Ob3, Ob4, Stop, Cantidad, uid) VALUES ('$Master', '$UUID', '$CUUID', '$TT', '$Price', '$Cant', '0', '1', '$Moneda', '0', '$Tiempo', '$Ob1', '$Ob2', '$Ob3', '$Ob4', '$Stop', '$Cantidad', '$uid')");

        if (empty($this->MySQL2->error)){

            return json_encode(array('Codigo' => 200, 'Texto' => 'Se ha ingresado correctamente la orden'));

        }else{

        }

    }


}