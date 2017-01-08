<?php

/*
 * 男もすなる class といふものを、女もしてみむとてするなり。
 */


class WithdrawHistory{
    public $dbname;
    private $pdo;
    public $SpanNEM;
    
    public function __construct() {
        $this->dbname = 'nem_history';
        $this->pdo = db_connect();
    }
    public function Check($ip,$address = null){
        global $SpanNEM;
        // $SpanTestnetNEM秒以内にユーザーはFaucetを回したか？
        // 回していれば前回回した時刻を返し、回していなければFalseを返す
        $addressoption = isset($address)?"OR `nem_address` = :nem_address":"";
        $pdo = $this->pdo;
        $dbname = $this->dbname;
        try{
            $sql = "SELECT `i_date` FROM `$dbname` WHERE ( `ipaddr` = :ipaddr $addressoption ) "
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
        if($count > 0){
            return $fetch['i_date'];
        }else{
            return FALSE;
        }
    }
    public function Get($address = null ,$page = 1){
        // Faucetの使用履歴を表示
        $addressoption = isset($address)?"`WHERE nem_address` = :nem_address":"";
        $pdo = $this->pdo;
        $dbname = $this->dbname;
        $offset = floor( ($page -1) * 20 );
        $limit = 20;
        try{
            $sql = "SELECT * FROM `$dbname` $addressoption ORDER BY `i_date` DESC LIMIT :offset ,:limits ";
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
    public function Insert($ip,$address,$amount,$txid,$message = null,$coin = 'nem:xem'){
        // Faucet使用履歴を記録
        // coinは　namespace:name 形式
        // amountはUnit単位
        $pdo = $this->pdo;
        $dbname = $this->dbname;
        try{
            $pdo->beginTransaction();
            $sql = "INSERT INTO `$dbname` "
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
    }
}

class DepositHistory{
    public $dbname;
    private $pdo;
    
    public function __construct() {
        $this->dbname = 'nem_deposit';
        $this->pdo = db_connect();
    }
    public function Get($address = null ,$page = 1,$where = ''){
        // Faucetの入金履歴
        // $whereを変数にしてはいけない
        $dbname = $this->dbname;
        $pdo = $this->pdo;
        $addressoption = isset($address)?"`WHERE nem_address` = :nem_address":"";
        $offset = floor( ($page -1) * 20 );
        $limit = 20;
        try{
            $sql = "SELECT * FROM `$dbname` $addressoption $where ORDER BY `i_date` DESC LIMIT :offset ,:limits ";
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
    public function Update($id,$flag = 1){
        $dbname = $this->dbname;
        $pdo = $this->pdo;
        try{
            $pdo->beginTransaction();
            $sql = "UPDATE `$dbname` SET `done` = :flag WHERE `id` = :id";
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
    public function Insert($coin,$amount,$address,$txid,$height,$message = 'Donation',$date){
        // Faucet入金履歴を記録
        // coinは　namespace:name 形式
        // amount は最初からμ単位で入力されているのに注意
        $dbname = $this->dbname;
        $pdo = $this->pdo;
        try{
            $pdo->beginTransaction();
            $sql = "INSERT INTO `$dbname` "
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
    }
}

class MosaicData{
    public $dbname;
    private $pdo;
    
    public function __construct() {
        $this->dbname = 'mosaicdb';
        $this->pdo = db_connect();
    }
    public function setting($dbname) {
        $this->dbname = $dbname;
    }
    public function Get($where){
        $dbname = $this->dbname;
        $pdo = $this->pdo;
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
    public function Check($coin){
        // mosaicdbに既に存在するか確認
        // ただし返り値のAdressに注意
        $dbname = $this->dbname;
        $pdo = $this->pdo;
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
    public function Update($coin,$data){
        $dbname = $this->dbname;
        $pdo = $this->pdo;
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
    public function Insert($coin,$address){
        $dbname = $this->dbname;
        $pdo = $this->pdo;
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
}

