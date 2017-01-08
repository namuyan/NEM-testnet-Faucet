
<?php

/* 
 * Mosaicの定義を返す status = true
 * 無ければ status = false
 * methodはGETでnamespace,nameの二値
 */

    require_once '../config.php';
    require_once '../function.php';
    require_once '../class.php';
    require_once '../NEMApiLibrary.php';
    require_once './api_function.php';
    $error = GreenOrRed();
    $label = 'mosaic_definition';
    
    if(isset($_COOKIE['token'])){
        $token = common::getCookie('token');
    }else{
        $token = RandumStr(10);
        setcookie('token', $token, time() + 60*60*24*30*3);
    }

    $howmany = CheckApiUser($ipaddr, $token);
    if($howmany > $ApiFrequency + $ApiLimit){
        // 使用頻度多すぎる
        InsertBlackList($ipaddr);
        die;
    }elseif($howmany > $ApiFrequency){
        // 制限値に達する
        echo json_encode(array('status' => false,'code' => 3,'message' => 'Too mutch Access !'));
        InsertApiUser($ipaddr, $token, 3, $label);
        die;
    }
    
    $namespace = common::getGET('namespace');
    $name = common::getGET('name');
    
    if(!$error){
        // NISにエラー
        echo json_encode(array('status' => false,'code' => 0,'message' => 'NIS is in trouble.'));
        InsertApiUser($ipaddr, $token, 0, $label);
        die;
    }
    
    if(isset($_GET['namespace']) AND isset($_GET['name'])){
        $MosaicData = SerchMosaicInfo($baseurl,$namespace, $name);
        if($MosaicData){
            $url = $baseurl.'/account/get/from-public-key?publicKey='.$MosaicData['detail']['mosaic']['creator'];
            $tmp = get_json_array($url);
            $MosaicData['detail']['mosaic']['address'] = $tmp['account']['address'];
            $MosaicData['initialSupplyByUnit'] = $MosaicData['initialSupply'] * pow(10, $MosaicData['divisibility']);
            $stmh = CheckMosaicData("$namespace:$name");
            $fetch = $stmh ->fetch(PDO::FETCH_ASSOC);
            
            if($stmh ->rowCount() > 0){
                $MosaicData['img']['type'] = $fetch['imgtype'];
                $MosaicData['img']['id'] = $fetch['imgid'];
            }else{
                $MosaicData['img']['type'] = 0;
                $MosaicData['img']['id'] = null;
            }
            echo json_encode(array('status' => true ,'code' => 1,'detail' => $MosaicData));
            InsertApiUser($ipaddr, $token, 1, $label);
        }else{
            echo json_encode(array('status' => false,'code' => 2,'message' => 'no detail'));
            InsertApiUser($ipaddr, $token, 2, $label);
        }
    }else{
        // Help表示
        $str  = '<link rel="shortcut icon" href="../img/iconnemu_32.png" type="image/vnd.microsoft.icon">';
        $str .= '<h1>NEM Faucet API 使用方法</h1>';
        $str .= '<p>このAPIはMosaicの定義を返します。<br>Methodは　GET<br>パラメータは　namespace ,name です。</p>';
        $str .= "<p>例えば、nem:xemの定義を調べる場合<br><em>http://namuyan.dip.jp/nem/api/mosaic_definition.php?namespace=nem&name=xem</em><br>をリクエストします。</p>";
        $str_tmp = <<< EOS
<p>返り値の例<span style='font-size:20px;'>
{
　　"status": true,
　　"code": 1,
　　"detail": {
　　　　"divisibility": 2,
　　　　"initialSupply": 10000, // 10000.00 という意味であることに注意
　　　　"supplyMutable": true,  // true = 追加発行可能
　　　　"transferable": true,   // true = 譲渡可能
　　　　"detail": {
　　　　　　"meta": {
　　　　　　　　"id": 191
　　　　　　},
　　　　　　"mosaic": {
　　　　　　　　"creator": "47900452f5843f391e6485c5172b0e1332bf0032b04c4abe23822754214caec3",
　　　　　　　　"description": "もってるといいことがある....はず、FaucetのDonationのお返しに送金されるよ",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "namuyan",
　　　　　　　　　　"name": "namu"
　　　　　　　　},
　　　　　　　　"levy": [],
　　　　　　　　"address": "TDEK3DOKN54XWEVUNXJOLWDJMYEF2G7HPK2LRU5W"
　　　　　　}
　　　　},
　　　　"initialSupplyByUnit": 1000000, //計算する時に使用する数字
　　　　"img": {
　　　　　　"type": 1, // type=1の場合、サムネイルはimgur.comに登録されている、未登録はtype=0
　　　　　　"id": "P3HsCbp"  //画像へ直リンするにはhttp://i.imgur.com/P3HsCbp.png
　　　　}
　　}
}</em></p>
EOS;
        $str .= str_replace("\n", "<BR>", $str_tmp);
        echo $str;
        InsertApiUser($ipaddr, $token, 4, $label);
    }
    
    DeleteApiUser();
    
    /*
          "levy": {
            "type": 1,
            "recipient": "TD3RXTHBLK6J3UD2BH2PXSOFLPWZOTR34WCG4HXH",
            "mosaicId": {
              "namespaceId": "nem",
              "name": "xem"
            },
            "fee": 10
          }
     */