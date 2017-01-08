<?php

/*
 * 必要なDBを一気に作るPHP
 * 既に作られているものは影響を受けない
 */

ini_set('display_errors', 1);
require_once '../config.php';
require_once '../function.php';

$pdo = db_connect();  //DBへ接続

            try{
                /* NEM testnet Faucet の使用履歴
                 * 
                 */
                    $sql = "CREATE TABLE IF NOT EXISTS nem_history 
                            (
                    id MEDIUMINT UNSIGNED NOT NULL auto_increment,
                    coin VARCHAR(128) NOT NULL,
                    amount BIGINT UNSIGNED NOT NULL,
                    nem_address VARCHAR(40) NOT NULL,
                    txid VARCHAR(64) NOT NULL,
                    message VARCHAR(128) DEFAULT NULL,
                    ipaddr VARCHAR(15) NOT NULL,
                    i_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                    )";
            $stmh = $pdo ->prepare($sql);
            $stmh ->execute();  //テーブル作成完了
            } catch (Exception $ex) {
             die('エラーnem_history:'.$ex ->getMessage());
             $pdo ->rollBack();
            }
            
            try{
                /* NEM testnet Faucet の入金履歴
                 * 
                 */
                    $sql = "CREATE TABLE IF NOT EXISTS nem_deposit 
                            (
                    id MEDIUMINT UNSIGNED NOT NULL auto_increment,
                    coin VARCHAR(128) NOT NULL,
                    amount BIGINT UNSIGNED NOT NULL,
                    nem_address VARCHAR(40) NOT NULL,
                    txid VARCHAR(64) NOT NULL,
                    height MEDIUMINT UNSIGNED NOT NULL,
                    message VARCHAR(128) DEFAULT NULL,
                    done TINYINT UNSIGNED DEFAULT 0,
                    i_date DATETIME NOT NULL,
                    PRIMARY KEY (id)
                    )";
            $stmh = $pdo ->prepare($sql);
            $stmh ->execute();  //テーブル作成完了
            } catch (Exception $ex) {
             die('エラーnem_history:'.$ex ->getMessage());
             $pdo ->rollBack();
            }
            

            try{
                /* apidb
                 * API管理用
                 */
                    $sql = "CREATE TABLE IF NOT EXISTS apidb
                        (
                        label VARCHAR(128) DEFAULT NULL,
                        token VARCHAR(10) NOT NULL,
                        status TINYINT UNSIGNED DEFAULT 1,
                        ipaddr VARCHAR(15) NOT NULL,
                        i_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                        ) ENGINE=MEMORY ";
                $stmh = $pdo ->prepare($sql);
                $stmh ->execute();  //テーブル作成完了
            } catch (Exception $ex) {
                die('エラーapidb:'.$ex ->getMessage());
            }
            
            try{
                /* mosaicdb
                 * モザイクに関して情報格納
                 * imgtype 0=無し、1=imgur
                 */
                    $sql = "CREATE TABLE IF NOT EXISTS mosaicdb
                        (
                        coin VARCHAR(64) NOT NULL,
                        imgtype TINYINT UNSIGNED DEFAULT 0,
                        imgid VARCHAR(64) DEFAULT NULL,
                        address VARCHAR(40) NOT NULL,
                        u_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        )";
                $stmh = $pdo ->prepare($sql);
                $stmh ->execute();  //テーブル作成完了
            } catch (Exception $ex) {
                die('エラーmosaicdb:'.$ex ->getMessage());
            }
            
            try{
                /* sbmfdb
                 * SmallBusinessMosaicFaucet設定
                 * minimum 以上 maximum 以下の配布
                 */
                    $sql = "CREATE TABLE IF NOT EXISTS sbmfdb
                        (
                        coin VARCHAR(64) NOT NULL,
                        minimum INT UNSIGNED DEFAULT 0,
                        maximum INT UNSIGNED DEFAULT 0,
                        address VARCHAR(40) NOT NULL,
                        ended TINYINT UNSIGNED DEFAULT 1,
                        u_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        )";
                $stmh = $pdo ->prepare($sql);
                $stmh ->execute();  //テーブル作成完了
            } catch (Exception $ex) {
                die('エラーsbmfdb:'.$ex ->getMessage());
            }