<?php

/* 
 * SBMFaucetはSmallBusinessMosaicのFaucet
 * 送料が格段に安いので特別扱い
 * 
 */

    $root_dir = '/opt/lampp/htdocs/main/';
    require_once $root_dir.'config.php';
    require_once $root_dir.'function.php';
    require_once $root_dir.'class.php';
    require_once $root_dir.'NEMApiLibrary.php';
    $page = 1;
    $where = "WHERE `done` = 0 AND `coin` = '$SBMFaucetMosaic' ";
    $stmh = GetDepositHistory( null , $page, $where);
    print $stmh ->rowCount() ."データ<BR>";
    
    while ($fetch = $stmh ->fetch(PDO::FETCH_ASSOC)){
        echo "<PRE>";
        print_r($fetch);
        $id = $fetch['id'];
        $amount = $fetch['amount'];
        $address = $fetch['nem_address'];
        $message = $fetch['message'];
        if(preg_match('/^sbmf,([^:]+?):([^:]+?),([0-9]+?),([0-9]+?)$/', $message, $matches)){
            // $1=namespace $2=name $3=amount_minimum $4=amount_maximum
            $namespace = $matches[1];
            $name = $matches[2];
            $amount_minimum = (int)$matches[3];
            $amount_maximum = (int)$matches[4];
            $MosaicData = SerchMosaicInfo($baseurl,$namespace, $name);
            if($MosaicData){
                // Mosaicは存在する
                $url = $baseurl.'/account/get/from-public-key?publicKey='.$MosaicData['detail']['mosaic']['creator'];
                $tmp = get_json_array($url);
                $creator = $tmp['account']['address'];
                print_r($MosaicData);
                if($MosaicData['divisibility'] === 0 AND $MosaicData['initialSupply'] <= 10000 AND 
                    0 < $amount_minimum AND $amount_minimum <= $amount_maximum AND $amount_maximum < $MosaicData['initialSupply'] AND
                    $creator === $address AND $amount >= $SBMFaucetFee * pow(10, $SBMFaucetDivisibility)){
                    // SBMであり、かつ条件を満たす
                    echo 'SBMであり、かつ条件を満たす';
                    $dbname = 'sbmfdb';
                    $coin = "$namespace:$name";
                    $stmh = CheckMosaicData($coin, $dbname);
                    if($stmh ->rowCount() === 0){
                        // 未登録
                        InsertMosaicData($coin, $address, $dbname);
                        echo "INSERT $coin <BR>";
                    }
                    $data = array('minimum' => $amount_minimum ,'maximum' => $amount_maximum,'ended' => 0);
                    UpdateMosaicData($coin, $data, $dbname);
                    UpdateDepositHistory($id); // doneフラグを立てる
                    echo "UPDATE $coin <BR>";
                }else{
                    echo 'SBMの条件を満たさず',$creator," ",$address,'<BR>',$amount,' ',$amount_minimum,' ',$amount_maximum,'<BR>';
                    echo $SBMFaucetFee * pow(10, $SBMFaucetDivisibility),'<BR>';
                }
            }

        }elseif(preg_match('/^twistSBMF$/', $message)){
            // Faucetが回されたか？
            // ランダムで１つのMosaicを amount 分送金
            // minimum <= amount <= maximum
            if($amount >= $SBMFaucetUserFee * pow(10, $SBMFaucetUserDivisibility)){
                $dbname = 'sbmfdb';
                $where = "WHERE `ended` = 0 ";
                $stmh = GetMosaicData($where,$dbname);
                if($stmh ->rowCount() > 0){
                    while ($fetch = $stmh ->fetch(PDO::FETCH_ASSOC)){
                        $data[] = $fetch;
                    } // 全コインデータを入れて
                    $all = count($data);
                    $selected = mt_rand(0, $all -1);
                    preg_match('/^([^:]+?):([^:]+?)$/', $data[$selected]['coin'], $matches);
                        $namespace = $matches[1];
                        $name = $matches[2];
                        $creator = $data[$selected]['address'];
                        $minimum = $data[$selected]['minimum'];
                        $maximum = $data[$selected]['maximum'];
                    $url = $baseurl.'/account/mosaic/owned?address='.$NEMAddress;
                    $MosaicRawData = get_json_array($url);
                    unset($quantity);
                    foreach ($MosaicRawData['data'] as $MosaicRawDataValue) {
                        if($MosaicRawDataValue['mosaicId']['namespaceId'] === $namespace AND $MosaicRawDataValue['mosaicId']['name'] === $name){
                            print "OK<BR>";
                            $quantity = $MosaicRawDataValue['quantity'];
                            break;
                        }
                    }
                    if(!isset($quantity)){
                        // DBエラーの為、配布終了フラグ
                        $data = array( 'ended' => 1 );
                        $coin = "$namespace:$name";
                        UpdateMosaicData($coin, $data, $dbname);
                        //die("sbmdbにエラー発生中");
                    }
                    $distribute = min( mt_rand($minimum, $maximum)  ,$quantity);
                    $mosaic = array(
                            array(
                                'quantity' => $distribute,
                                'mosaicId' => array(
                                    'namespaceId' => $namespace,
                                    'name' => $name
                                )
                            )
                        );
                    $message_user = "Thank you for twist SBMF!";
                    $fee_user = EstimateFee( 1 , $message, $mosaic); // userには
                    $message_creator = "twisted yor SBMF $namespace:$name";
                    $fee_creator = EstimateFee( 1 , $message_creator); // 10000 NEM 以下なら 1 NEMがFee
                    // 送金
                    $reslt_user = SendMosaicVer2($address, $message_user, $fee_user, $mosaic); //合計 1+$fee_user NEM かかる
                    sleep(1); // 1s休み、念のため
                    $amount_creator = $amount / pow(10, $SBMFaucetDivisibility) -1 -$fee_user -$fee_creator -$SBMFaucetUserIncome;
                    echo "$amount_creator <BR>";
                    $reslt_creator = SendNEMver1($creator, $amount_creator, $fee_creator, $message_creator);
                    // $SBMFaucetUserIncome分がFaucet設置主に残る....というわけ、
                    
                    if($reslt_creator['message'] === 'SUCCESS' AND $reslt_user['message'] === 'SUCCESS'){
                        // 送金が成功
                        $coin = "$namespace:$name";
                        UpdateDepositHistory($id);
                        InsertUserHistory('SBMF', $creator, $amount_creator * pow(10, $nem_Divisibility), $reslt_creator['transactionHash']['data'],$message_creator);
                        InsertUserHistory('SBMF', $address, $distribute * pow(10, 0), $reslt_user['transactionHash']['data'],$message_user,$coin);
                        if($distribute === $quantity){
                            // 配布終了フラグ
                            $data = array( 'ended' => 1 );
                            UpdateMosaicData($coin, $data, $dbname);
                        }
                    }
                        echo "<PRE>"; //debug
                        print_r($reslt_user);
                        print_r($reslt_creator);
                        echo "</PRE>";
                }else{
                    // 配布するモザイクが無い時
                    // 処理未定
                }
            }
        }
        echo "</PRE>";
    }