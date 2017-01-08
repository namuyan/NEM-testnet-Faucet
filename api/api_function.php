<?php

/*
 * API の使用制限および保護
 */


function CheckApiUser($ip,$token){
    // API 使用頻度をチェック
    // ある期間の間に何回使用したかを返す
    global $ApiSpan;
    $pdo = db_connect();
    try{
        $sql = "SELECT `token` FROM `apidb` WHERE  (`ipaddr` = :ipaddr OR `token` = :token ) AND "
                . "`i_date` > current_timestamp + interval -$ApiSpan second";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':ipaddr', $ip, PDO::PARAM_STR);
        $stmh ->bindParam(':token', $token, PDO::PARAM_STR);
        $stmh ->execute();
    } catch (Exception $ex) {
        die("CheckApiUser:$ex");
    }
    return $stmh ->rowCount();
}

function InsertApiUser($ip,$token,$status,$label = ''){
    // API 使用状況を記録
    // statusはcodeのこと
    $pdo = db_connect();
    try{
        $pdo->beginTransaction();
        $sql = "INSERT INTO `apidb` "
                . "(`label`,`token`,`status`,`ipaddr`) VALUES "
                . "(:label ,:token ,:status ,:ipaddr )";
        $stmh = $pdo ->prepare($sql);
        $stmh ->bindParam(':label', $label, PDO::PARAM_STR);
        $stmh ->bindParam(':token', $token , PDO::PARAM_STR);
        $stmh ->bindValue(':status', $status, PDO::PARAM_STR);
        $stmh ->bindParam(':ipaddr', $ip, PDO::PARAM_STR);
        $stmh ->execute();
        $pdo ->commit();
    } catch (Exception $ex) {
        $pdo ->rollBack();
        die("InsertApiUser:$ex");
    }
}

function DeleteApiUser(){
    // 一定期間過ぎた記録は消すこと
    $pdo = db_connect();
    global $ApiSpan;
    try{
        $pdo->beginTransaction();
        $sql = "DELETE FROM `apidb` WHERE `i_date` < current_timestamp + interval -$ApiSpan second";
        $stmh = $pdo ->prepare($sql);
        $stmh ->execute();
        $pdo ->commit();
    } catch (Exception $ex) {
        $pdo ->rollBack();
        die("DeleteApiUser:$ex");
    }
}

function InsertBlackList($ip){
    // ブラックリスト
    // 攻撃回避になるか不明
    $str = "deny from ".$ip;
    file_put_contents('../.htaccess', $str, FILE_APPEND | LOCK_EX);
}

function RemoveBlackList($ip){
    // ブラックリスト
    $data = file_get_contents('../.htaccess');
    $str = "\ndeny from ".$ip;
    preg_replace($str, '', $data);
    file_put_contents('../.htaccess', $data, LOCK_EX);
}