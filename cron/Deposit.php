<?php

/* 
 * 入金をcronで定期確認
 */
    $root_dir = '/opt/lampp/htdocs/main/';
    require_once $root_dir.'config.php';
    require_once $root_dir.'function.php';
    require_once $root_dir.'class.php';
    require_once $root_dir.'NEMApiLibrary.php';
    
    $url = $baseurl."/account/transfers/incoming?address=".$NEMAddress;
    $DepositData = get_json_array($url);
    $txarray = $DepositData['data'];
    
        $i=0;
        while ($i<1) {
            echo $i,'回目<br>';
            if(count($txarray) === 0){echo '<br>$txarrayが空ですbreakします';break;}
            $url_tmp = $url.'&id='.$txarray[count($txarray) -1]['meta']['id'];
            echo $url_tmp,'<br>';
            unset($data_tmp);
            $data_tmp = get_json_array($url_tmp);
            if(empty($data_tmp['data'])){echo '<br>$data_tmpが空ですbreakします';break;}
            $txarray = array_merge($txarray, $data_tmp['data']);
            echo $i++,'回目完了<br>';
        }
        print json_encode($txarray)."<br>"; //debug
        
        unset($height);unset($txid);unset($mosaicname); unset($amount); unset($message); unset($signer); //unset($fee);
        foreach ($txarray as $txarray_value) {
            // NEMはμ単位だが内部処理では小数点以下8桁
            if(!isset($txarray_value['transaction']['mosaics'])){
                //通常のXEM入金
                $height[] = $txarray_value['meta']['height'];
                $txid[] = $txarray_value['meta']['hash']['data'];
                $mosaicname[] = 'nem:xem';
                if(isset($txarray_value['transaction']['otherTrans'])){
                    $amount[] = $txarray_value['transaction']['otherTrans']['amount'] ;
                    //$fee[] = $txarray_value['transaction']['otherTrans']['fee'] ;
                    $message[] = ($txarray_value['transaction']['otherTrans']['message']['type'] === 1)?hex2bin($txarray_value['transaction']['otherTrans']['message']['payload']):'';
                }else{
                    $amount[] = $txarray_value['transaction']['amount'] ;
                    //$fee[] = $txarray_value['transaction']['fee'] ;
                    $message[] = ($txarray_value['transaction']['message']['type'] === 1)?hex2bin($txarray_value['transaction']['message']['payload']):'';
                }
                $signer[] = $txarray_value['transaction']['signer'];
                $date[] = $txarray_value['transaction']['timeStamp'] + 1427587585;
            }else{ // モザイク入金
                foreach ($txarray_value['transaction']['mosaics'] as $mosaicValue) {
                    $height[] = $txarray_value['meta']['height'];
                    $txid[] = $txarray_value['meta']['hash']['data'];
                    //$fee[] = $txarray_value['transaction']['fee'] ;
                    $message[] = ($txarray_value['transaction']['message']['type'] === 1)?hex2bin($txarray_value['transaction']['message']['payload']):'';
                    if($mosaicValue['mosaicId']['namespaceId'] === 'nem' AND $mosaicValue['mosaicId']['name'] === 'xem'){
                        // モザイクとしてNEMが入金された
                        $mosaicname[] = 'nem:xem';
                        $amount[] = $mosaicValue['quantity'] ;
                    }else{
                        // NEM以外のモザイク
                        $mosaicname[] = $mosaicValue['mosaicId']['namespaceId'].':'.$mosaicValue['mosaicId']['name']; //gox:gox namespace:name
                        $amount[] = $mosaicValue['quantity'] ;
                    }
                    $signer[] = $txarray_value['transaction']['signer'];
                    $date[] = $txarray_value['transaction']['timeStamp'] + 1427587585;
                }
            }
        } // この時点で$height,$txid,$message,$mosaicname,$amount,$signer
        
        
            $all = count($mosaicname);
            for($i=0;$i<$all;$i++){
                if($amount == 0){continue;}
                $where = "WHERE `coin` = '$mosaicname[$i]' AND `amount` = '$amount[$i]' AND `txid` = '$txid[$i]' AND `height` = '$height[$i]' ";
                echo $where,'<br>';
                $stmh = GetDepositHistory(null, 1, $where);
                if( $stmh ->rowCount() == 0 ){
                    // まだ記録されていない
                    $url = $baseurl.'/account/get/from-public-key?publicKey='.$signer[$i];
                    $nem_address_tmp = get_json_array($url);
                    $nem_address = $nem_address_tmp['account']['address'];
                    echo $nem_address,'<BR>';
                    // 記録
                    InsertDepositHistory($mosaicname[$i], $amount[$i], $nem_address, $txid[$i], $height[$i], $message[$i] ,date("Y-m-d H:i:s",$date[$i]) );
                }
            }// end of for