<?php

/*
 * Function格納
 */


function reCAPTCHA($ip){
/*
 * google reCAPTCHA認証
*/

//正しく認証された場合、$var ==1 返す。
//   reCAPTCHAのベリファイ　こっから
$url = 'https://www.google.com/recaptcha/api/siteverify';
$data = array(
    'secret' => '',
    'response' => $_REQUEST['g-recaptcha-response'],
    'remoteip' => $ip,  // リバースプロキシ使用したため
);
$url .= '?' . http_build_query($data);
$header = Array(
    'Content-Type: application/x-www-form-urlencoded',
);
$options = array('http' =>
    array(
        'method' => 'GET',
        'header'  => implode("\r\n", $header),
        'ignore_errors' => true
    )
);
$apiResponse = file_get_contents($url, false, stream_context_create($options));
$jsonData = json_decode($apiResponse, TRUE);
if($jsonData['success'] !== TRUE){
    return false;  //認証されませんでした。
} else {
     return true;  //認証されました。
}
}// end of reCAPTCHA

function get_mosaic_imgurl($namespace,$name){
    // Mosaicのサムネ取得
    if($namespace === 'nem' AND $name === 'xem'){
        return './img/iconnemu_32.png';
    }// ( !isset($_SERVER['HTTPS']) || empty($_SERVER['HTTPS']) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST']
    $stmh = CheckMosaicData("$namespace:$name");
    $fetch = $stmh ->fetch(PDO::FETCH_ASSOC);
    
    if($stmh ->rowCount() > 0 AND $fetch['imgtype'] !== 0){
        // サムネあり
        if($fetch['imgtype'] === 1){ // 登録画像あり
            return 'http://i.imgur.com/'.$fetch['imgid'].'.png';
        }else{
            // 他の画像提供サイト
        }
    }else{
        return './img/mosaic_texture.png'; //デフォルト画像
    }
}

function db_connect(){

        global $db_user;
        global $db_pass;
        global $db_host;
        global $db_name;
        global $db_type;
        
        $dsn = "$db_type:host=$db_host;dbname=$db_name;charset=utf8";
        
        try{
            $pdo =new PDO($dsn,$db_user,$db_pass);
            $pdo ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo ->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            //print 'DBに接続しました。<br>';
            
        } catch (Exception $ex) {
             die('エラー接続:'.$ex ->getMessage());
        }   //try ここまで
        return $pdo;
}

function imlocal(){
        /*
         * ユーザーがローカルならtrueを返す
         */
        //$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        global $ipaddr;
        $ip = $ipaddr;
        $sub_ip = substr($ip, 0,8);//192.168.
        if($sub_ip == '192.168.'){
            return TRUE;
        }  else {
            return FALSE;
        }
}

function GreenOrRed(){
    // NISが起動しているか確認
    // 起動していなければFalseを返す
    global $baseurl;
    $url = $baseurl."/status";
    $beat = get_json_array($url);
    if($beat['code'] == 6){
        return TRUE;
    }else{
        return FALSE;
    }
}

function display_shousuuten($amount){
        $num = sprintf("%.8f", $amount);       // 小数点以下8桁に揃える
        $num2 = ereg_replace("0+$", '', $num); // 後ろの連続する0を削除
        //$num3 = ($amount == 0)?0:$num2;
        $num3 = $num2;
        if($num3 == (int)$num3){
            return substr($num3, 0, strlen($num3)-1);
        }  else {
            return $num3;
        }
} // end of display_shousuuten


/*
 *  以下は classへ書き換える前のファンクション
 */

function SendNEMver1($address,$amount,$fee,$message){
    // NEMを$addressへ送る,Non-mosaic
    // 返り値はTXID、失敗時はFalse
    global $NEMprikey;
    global $NEMpubkey;
    global $baseurl;
    $url = $baseurl ."/transaction/prepare-announce";
    $POST_DATA = json_encode(
            array('transaction'=> array(
                'timeStamp'=> (time() - 1427587585), // NEMは1427587585つまり2015/3/29 0:6:25 UTCスタート
                'amount'   => $amount *1000000,      // NEMは小数点以下6桁まで有効
                'fee'      => $fee    *1000000,
                'recipient'=> $address ,
                'type'     => 257 ,
                'deadline' => (time() - 1427587585 + 43200), // 送金の期限
                'message'=> array(
                    'payload' => bin2hex($message),
                    'type'    => 1
                ),
                'version'  => -1744830463 ,  // mainnetは-1744830465、testnetは-1744830463
                'signer'   => $NEMpubkey  // signer　サイン主のこと
                ),
                'privateKey' => $NEMprikey
            ));
    // testnetは-1744830462だと以下のエラーが出る
    // expected value for property mosaics, but none was found これはNEMをモザイクとして送金する必要があるということ？
    //print_r($POST_DATA);print "<BR>"; // debug
    return get_POSTdata($url, $POST_DATA);
    // 返り値　Array ( [innerTransactionHash] => Array ( )
    //                 [code] => 1
    //                 [type] => 1
    //                 [message] => SUCCESS
    //                 [transactionHash] => Array (
    //                                              [data] => 208a41fb815cc0dd6173213a031ba6f956ef60b6530c255a2926e9a8555198e2 ) 
    //                                      )
    // 返り値(error) Array ( [timeStamp] => 55043675
    //                       [error] => Not Found
    //                       [message] => invalid address 'TB235JLAOGALDATDJC7LXDMZSDMFBUMDVIBFVQ' (org.nem.core.model.Address)
    //                       [status] => 404 ) 
}
function SendMosaicVer2($address,$message,$fee,$mosaic){
    // Mosaic送信用Ver2のトランザクション生成
    // 返り値はTXID、失敗時はFalse
    global $NEMprikey;
    global $NEMpubkey;
    global $baseurl;
    $url = $baseurl ."/transaction/prepare-announce";
    $POST_DATA = json_encode(
        array(
            'transaction'=>array(
                'timeStamp' => (time() - 1427587585),
                'amount'    => 1 * 1000000,    // namowalletでは常に１XEM取られる
                'fee'       => $fee * 1000000,
                'recipient' => $address ,
                'type'      => 257 ,
                'deadline'  => (time() - 1427587585 + 43200),
                'message'   => array(
                    'payload'  => bin2hex($message),
                    'type'    => 1
                    ),
                'version'   => -1744830462, // Testnetは-1744830462　,mainnetは-1744830466
                'signer'    => $NEMpubkey,
                'mosaics'   => $mosaic,
            ),
            'privateKey'=>$NEMprikey
        ));
    return get_POSTdata($url, $POST_DATA);
}
function EstimateFee($amount,$message,$mosaic = false){
    // 送金に必要なFeeを計算し返す
    global $baseurl;
    global $nem_Divisibility;
    if(is_array($mosaic)){
        // With-mosaic
/* $mosaic内は以下のような配列
    [ makoto.metals.silver:coinを１(０桁)、nem:xemを100000000(６桁)を送る場合
      {
        "quantity": 1,
        "mosaicId": {
          "namespaceId": "makoto.metals.silver",
          "name": "coin"
        }
      },
      {
        "quantity": 100000000,
        "mosaicId": {
          "namespaceId": "nem",
          "name": "xem"
        }
      }
    ]
 */
        $fee_tmp = 0;
        foreach ($mosaic as $mosaicValue) {
            $quantity = $mosaicValue['quantity'];
            $namespace = $mosaicValue['mosaicId']['namespaceId'];
            $name = $mosaicValue['mosaicId']['name'];
            $DetailMosaic = SerchMosaicInfo($baseurl,$namespace, $name);
            if($DetailMosaic['initialSupply'] <= 10000 AND $DetailMosaic['divisibility'] === 0){
                // SmallBusinessMosaic
                // 分割０でSupply１万以下のMosaicは"SmallBusinessMosaic"と呼ばれFeeが安いぞぃ
                $fee_tmp += 1;
            }else{
                // Others
                // http://mijin.io/forums/forum/日本語/off-topics/717-雑談のお部屋?p=1788#post1788
                // 
                $initialSupplybyUnit = $DetailMosaic['initialSupply'] * pow(10, $DetailMosaic['divisibility']);
                $fee_tmp += round( max(1, min(25, $quantity * 900000 / $initialSupplybyUnit ) - floor(0.8 * log(9000000000000000 / $initialSupplybyUnit ))));
            }
            // 徴収されるNEMやモザイクは含めなくてもよい
        } // end of foreach ($mosaic as $mosaicValue) {
        $fee = $fee_tmp;
    }else{
        // Non-mosaic
        $fee_tmp = floor($amount / 10000);
        if($fee_tmp < 1){
            $fee = 1;
        }elseif($fee_tmp < 26){
            $fee = $fee_tmp;
        }else{
            $fee = 25;
        }
    }// end of Non-mosaic
    
    if(strlen($message) > 0){
        $fee_tmp = floor(strlen($message) / 32) + 1;
    }else{
        $fee_tmp = 0;
    }
    return  ($fee_tmp + $fee); // messageのFee
}


function CheckUserHistory($ip,$address = null){
    // $SpanTestnetNEM秒以内にユーザーはFaucetを回したか？
    // 回していれば前回回した時刻を返し、回していなければFalseを返す
    global $SpanNEM; //変数としないこと注意
    $addressoption = isset($address)?"OR `nem_address` = :nem_address":"";
    $pdo = db_connect();
    
    try{
        $sql = "SELECT `i_date` FROM `nem_history` WHERE ( `ipaddr` = :ipaddr $addressoption ) "
                . "AND i_date > current_timestamp + interval -$SpanNEM second ORDER BY `i_date` DESC LIMIT 1";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':ipaddr', $ip, PDO::PARAM_STR);
        if(isset($address)){ $stmh ->bindParam(':nem_address', $address, PDO::PARAM_STR); }
        $stmh ->execute();
        $count = $stmh ->rowCount();
        $fetch = $stmh ->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $ex) {
        die ("Error:CheckUserHistory:$ex");
    }
    $pdo = null; //SQL接続断
    if($count > 0){
        return $fetch['i_date'];
    }else{
        return FALSE;
    }
} // end of CheckUserHistory
function GetUserHistory($address = null ,$page = 1){
    // Faucetの使用履歴を表示
    $addressoption = isset($address)?"`WHERE nem_address` = :nem_address":"";
    $pdo = db_connect();
    $offset = floor( ($page -1) * 20 );
    $limit = 20;
    try{
        $sql = "SELECT * FROM `nem_history` $addressoption ORDER BY `i_date` DESC LIMIT :offset ,:limits ";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':offset', $offset, PDO::PARAM_STR);
        $stmh ->bindParam(':limits', $limit, PDO::PARAM_STR);
        if(isset($address)){ $stmh ->bindParam(':nem_address', $address, PDO::PARAM_STR); }
        $stmh ->execute();
    } catch (Exception $ex) {
        die ("Error:GetUserHistory:$ex");
    }
        return $stmh;
}
function InsertUserHistory($ip,$address,$amount,$txid,$message = null,$coin = 'nem:xem'){
    // Faucet使用履歴を記録
    // coinは　namespace:name 形式
    // amountはUnit単位
    $pdo = db_connect();
    try{
        $pdo->beginTransaction();
        $sql = "INSERT INTO `nem_history` "
                . "(`coin`,`amount`,`nem_address`,`txid`,`message`,`ipaddr`) VALUES "
                . "(:coin ,:amount ,:nem_address ,:txid ,:message ,:ipaddr )";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':coin', $coin, PDO::PARAM_STR);
        $stmh ->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmh ->bindParam(':nem_address', $address, PDO::PARAM_STR);
        $stmh ->bindParam(':txid', $txid, PDO::PARAM_STR);
        $stmh ->bindParam(':message', $message, PDO::PARAM_STR);
        $stmh ->bindParam(':ipaddr', $ip, PDO::PARAM_STR);
        $stmh ->execute();
        $pdo ->commit();
    } catch (Exception $ex) {
        $pdo ->rollBack();
        die ("Error:InsertUserHistory:$ex");
    }
} // end of InsertUserHistory

function GetDepositHistory($address = null ,$page = 1,$where = ''){
    // Faucetの入金履歴
    // $whereを変数にしてはいけない
    $addressoption = isset($address)?"`WHERE nem_address` = :nem_address":"";
    $pdo = db_connect();
    $offset = floor( ($page -1) * 20 );
    $limit = 20;
    try{
        $sql = "SELECT * FROM `nem_deposit` $addressoption $where ORDER BY `i_date` DESC LIMIT :offset ,:limits ";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':offset', $offset, PDO::PARAM_STR);
        $stmh ->bindParam(':limits', $limit, PDO::PARAM_STR);
        if(isset($address)){ $stmh ->bindParam(':nem_address', $address, PDO::PARAM_STR); }
        $stmh ->execute();
    } catch (Exception $ex) {
        die ("Error:GetDepositHistory:$ex");
    }
    return $stmh;
}
function UpdateDepositHistory($id,$flag = 1){
    $pdo = db_connect();
    try{
        $pdo->beginTransaction();
        $sql = "UPDATE `nem_deposit` SET `done` = :flag WHERE `id` = :id";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':flag', $flag, PDO::PARAM_STR);
        $stmh ->bindParam(':id', $id, PDO::PARAM_STR);
        $stmh ->execute();
        $pdo ->commit();
    } catch (Exception $ex) {
        $pdo ->rollBack();
        die ("Error:InsertDepositHistory:$ex");
    }
}
function InsertDepositHistory($coin,$amount,$address,$txid,$height,$message = 'Donation',$date){
    // Faucet入金履歴を記録
    // coinは　namespace:name 形式
    // amount は最初からμ単位で入力されているのに注意
    $pdo = db_connect();
    try{
        $pdo->beginTransaction();
        $sql = "INSERT INTO `nem_deposit` "
                . "(`coin`,`amount`,`nem_address`,`txid`,`height`,`message`,`i_date`) VALUES "
                . "(:coin ,:amount ,:nem_address ,:txid ,:height ,:message ,:i_date )";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':coin', $coin, PDO::PARAM_STR);
        $stmh ->bindParam(':amount', $amount, PDO::PARAM_STR);
        $stmh ->bindParam(':nem_address', $address, PDO::PARAM_STR);
        $stmh ->bindParam(':txid', $txid, PDO::PARAM_STR);
        $stmh ->bindParam(':height', $height, PDO::PARAM_STR);
        $stmh ->bindParam(':message', $message, PDO::PARAM_STR);
        $stmh ->bindParam(':i_date', $date, PDO::PARAM_STR);
        $stmh ->execute();
        $pdo ->commit();
    } catch (Exception $ex) {
        $pdo ->rollBack();
        die ("Error:InsertDepositHistory:$ex");
    }
} // end of InsertDepositHistory

function GetMosaicData($where,$dbname = 'mosaicdb'){
    $pdo = db_connect();
    // $where を変数にしないこと！
    try{
        $sql = "SELECT * FROM `$dbname` $where";
        $stmh = $pdo ->prepare($sql);
        $stmh ->execute();
    } catch (Exception $ex) {
die("CheckMosaicData:$ex");
    }
    return $stmh;
}
function CheckMosaicData($coin,$dbname = 'mosaicdb'){
    // mosaicdbに既に存在するか確認
    // ただし返り値のAdressに注意
    $pdo = db_connect();
    try{
        $sql = "SELECT * FROM `$dbname` WHERE `coin` = :coin";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':coin', $coin, PDO::PARAM_STR);
        $stmh ->execute();
    } catch (Exception $ex) {
die("CheckMosaicData:$ex");
    }
    return $stmh;
}
function UpdateMosaicData($coin,$data,$dbname = 'mosaicdb'){
    $pdo = db_connect();
    try{
        $pdo->beginTransaction();
        $sql = "UPDATE `$dbname` SET ";
        foreach ( array_keys($data) as $key) {
            $sql .= "`$key` = :$key ,";
        }
        $sql = substr($sql, 0, -1);
        $sql .= "WHERE `coin` = :coin";
        $stmh = $pdo ->prepare($sql);
        foreach ($data as $key => $value) {
            $stmh ->bindValue(":$key", $value, PDO::PARAM_STR); // Paramは参照値なので注意
        }
        $stmh ->bindParam(':coin', $coin, PDO::PARAM_STR);
        $stmh ->execute();
        $pdo ->commit();
    } catch (Exception $ex) {
        $pdo ->rollBack();
        die('UpdateMosaicData:'.$ex ->getMessage());
    }
}
function InsertMosaicData($coin,$address,$dbname = 'mosaicdb'){
    $pdo = db_connect();
    try{
        $pdo->beginTransaction();
        $sql = "INSERT INTO `$dbname` "
                . "(`coin`,`address`) VALUES "
                . "(:coin ,:address )";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':coin', $coin, PDO::PARAM_STR);
        $stmh ->bindParam(':address', $address, PDO::PARAM_STR);
        $stmh ->execute();
        $pdo ->commit();
    } catch (Exception $ex) {
        $pdo ->rollBack();
        die('InsertMosaicData:'.$ex ->getMessage());
    }
}