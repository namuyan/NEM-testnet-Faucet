<?php

/* 
 * コインの画像登録
 * 
 * -登録手順-
 * １、imgurに登録したいモザイク namuyan:namu のサムネルをアップロード(例 http://imgur.com/P3HsCbp)
 * ２、$NEMAddress宛てに$ImageRegFee以上、messageに　regimg,imgur,P3HsCbp,namuyan:namu と書いて送る。
 * ３、1時間以内に登録されたかmosaic_definition.phpで確認
 */
    $root_dir = '/opt/lampp/htdocs/main/';
    require_once $root_dir.'config.php';
    require_once $root_dir.'function.php';
    require_once $root_dir.'class.php';
    require_once $root_dir.'NEMApiLibrary.php';
    $page = 1;
    $where = "WHERE `done` = 0 AND `coin` = '$ImageRegMosaic' ";
    $stmh = GetDepositHistory( null , $page, $where);
    
    while ($fetch = $stmh ->fetch(PDO::FETCH_ASSOC)){
        $id = $fetch['id'];
        $amount = $fetch['amount'];
        $address = $fetch['nem_address'];
        $message = $fetch['message'];
        if(preg_match('/^regimg,([a-z0-9]+?),([a-zA-Z0-9]+?),([^:]+?):([^:]+?)$/', $message, $matches)){
            // $1=imgur $2=imgur_id $3=namespace $4=namu
            $imgsite = $matches[1];
            $imgID = $matches[2];
            $namespace = $matches[3];
            $name = $matches[4];
            $MosaicData = SerchMosaicInfo($baseurl,$namespace, $name);
            if($MosaicData){
                // 入力されたモザイクは存在する
                $url = $baseurl.'/account/get/from-public-key?publicKey='.$MosaicData['detail']['mosaic']['creator'];
                $tmp = get_json_array($url);
                $creator = $tmp['account']['address'];
                if( $address === $creator AND $amount >= $ImageRegFee * pow(10, $ImageRegDivisibility)){
                    // 登録者と作成者が同じかつFeeが十分
                    if($imgsite === 'imgur'){
                        // imgur.com
                        // 直リンク：http://i.imgur.com/W3pMzrg.png
                        $stmh = CheckMosaicData("$namespace:$name");
                        $count = $stmh ->rowCount();
                        if($count === 0){
                            // 未登録
                            InsertMosaicData("$namespace:$name", $creator);
                            echo "$namespace:$name をINSERT<BR>";
                        }
                        $data = array('imgtype' => 1,'imgid' => $imgID,'address' => $creator);
                        UpdateMosaicData("$namespace:$name", $data);
                        echo "$namespace:$name をUPDATE<BR>";
                    }else{
                        // 別の画像登録サイト
                    }
                    UpdateDepositHistory($id);
                }
            }
        }
    }
    