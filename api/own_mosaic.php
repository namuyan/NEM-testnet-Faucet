<?php

/* 
 * 所有Mosaicの詳細を返す status = true
 * 失敗すれば status = false
 * methodはPOST,GETどちらでも
 */

    require_once '../config.php';
    require_once '../function.php';
    require_once '../class.php';
    require_once '../NEMApiLibrary.php';
    require_once './api_function.php';
    $error = GreenOrRed();
    $label = 'own_mosaic';
    
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

    if(!$error){
        // NISにエラー
        echo json_encode(array('status' => false,'code' => 0,'message' => 'NIS is in trouble.'));
        InsertApiUser($ipaddr, $token, 0, $label);
        die;
    }
    
    
    if(isset($_GET['address'])){
        $address = common::getGET('address');
        $url = $baseurl.'/account/mosaic/owned?address='.$address;
        $MosaicRawData = get_json_array($url);
        
        if(isset($MosaicRawData['data'])){
            foreach ($MosaicRawData['data'] as $MosaicRawDataValue) {
                $quantity = $MosaicRawDataValue['quantity'];
                $namespace = $MosaicRawDataValue['mosaicId']['namespaceId'];
                $name = $MosaicRawDataValue['mosaicId']['name'];
                $MosaicData = SerchMosaicInfo($baseurl,$namespace, $name);
                if($MosaicData){
                    $detail_tmp['divisibility'] = $MosaicData['divisibility'];
                    $detail_tmp['initialSupply'] = $MosaicData['initialSupply'];
                    $detail_tmp['initialSupplyByUnit'] = $MosaicData['initialSupply'] * pow(10, $MosaicData['divisibility']);
                    $detail_tmp['supplyMutable'] = $MosaicData['supplyMutable'];
                    $detail_tmp['transferable'] = $MosaicData['transferable'];
                    $detail_tmp['quantityByUnit'] = $quantity;
                    $detail_tmp['mosaic'] = $MosaicData['detail']['mosaic'];
                    $detail_tmp['thumbnail'] = str_replace('./img/','http://namuyan.dip.jp/nem/img/',get_mosaic_imgurl($namespace, $name));
                    $detail[] = $detail_tmp;
                }
            }
            
            echo json_encode(array('status' => true ,'code' => 1,'detail' => $detail));
            InsertApiUser($ipaddr, $token, 1, $label);
        }else{
            echo json_encode(array('status' => false,'code' => 2,'message' => 'no mosaics'));
            InsertApiUser($ipaddr, $token, 2, $label);
        }
    }else{
        // Help表示
        $str  = '<link rel="shortcut icon" href="../img/iconnemu_32.png" type="image/vnd.microsoft.icon">';
        $str .= '<h1>own_mosaic.php 使用方法</h1>';
        $str .= '<p>このAPIはアドレスが所有するMosaicの詳細を返します。<br>Methodは GET<br>パラメータは　address です。</p>';
        $str .= "<p>例えば、TDEK3DOKN54XWEVUNXJOLWDJMYEF2G7HPK2LRU5W のの所有するMosaicを調べる場合<br><em>http://namuyan.dip.jp/nem/api/own_mosaic.php?address=TDEK3DOKN54XWEVUNXJOLWDJMYEF2G7HPK2LRU5W</em><br>をリクエストします。</p>";
        $str_tmp = <<< EOS
<p>返り値の例<BR><span style='font-size:20px;'>
{
　　"status": true,
　　"code": 1,
　　"detail": [
　　　　{
　　　　　　"divisibility": 6,
　　　　　　"initialSupply": 8999999999,
　　　　　　"initialSupplyByUnit": 8999999999000000,
　　　　　　"supplyMutable": false,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 246350397199,
　　　　　　"mosaic": {
　　　　　　　　"creator": "",
　　　　　　　　"description": "NEMの基軸通貨、ダミー情報なので注意",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "",
　　　　　　　　　　"name": ""
　　　　　　　　},
　　　　　　　　"levy": []
　　　　　　},
　　　　　　"thumbnail": "http://namuyan.dip.jp/nem/img/iconnemu_32.png"
　　　　},
　　　　{
　　　　　　"divisibility": 1,
　　　　　　"initialSupply": 10000,
　　　　　　"initialSupplyByUnit": 100000,
　　　　　　"supplyMutable": true,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 1145141919,
　　　　　　"mosaic": {
　　　　　　　　"creator": "47900452f5843f391e6485c5172b0e1332bf0032b04c4abe23822754214caec3",
　　　　　　　　"description": "小数点以下１",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "namuyan",
　　　　　　　　　　"name": "namu1"
　　　　　　　　},
　　　　　　　　"levy": []
　　　　　　},
　　　　　　"thumbnail": "http://namuyan.dip.jp/nem/img/mosaic_texture.png"
　　　　},
　　　　{
　　　　　　"divisibility": 0,
　　　　　　"initialSupply": 1000000000,
　　　　　　"initialSupplyByUnit": 1000000000,
　　　　　　"supplyMutable": true,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 1341,
　　　　　　"mosaic": {
　　　　　　　　"creator": "4db90d1bf5360393def702a73fbaa24904e8b7ccc69928ee6d90fe32f4126db8",
　　　　　　　　"description": "test mosaic<BR>テストモザイク",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "unknown_test",
　　　　　　　　　　"name": "jpy1"
　　　　　　　　},
　　　　　　　　"levy": {
　　　　　　　　　　"fee": 100000,
　　　　　　　　　　"recipient": "TBAFJKMX6ABTKS5BSZRULMZJ5JDYH2QZKORBUFND",
　　　　　　　　　　"type": 2,
　　　　　　　　　　"mosaicId": {
　　　　　　　　　　　　"namespaceId": "nem",
　　　　　　　　　　　　"name": "xem"
　　　　　　　　　　}
　　　　　　　　}
　　　　　　},
　　　　　　"thumbnail": "http://namuyan.dip.jp/nem/img/mosaic_texture.png"
　　　　},
　　　　{
　　　　　　"divisibility": 1,
　　　　　　"initialSupply": 100000,
　　　　　　"initialSupplyByUnit": 1000000,
　　　　　　"supplyMutable": true,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 4971,
　　　　　　"mosaic": {
　　　　　　　　"creator": "47900452f5843f391e6485c5172b0e1332bf0032b04c4abe23822754214caec3",
　　　　　　　　"description": "徴収モザイク、1000NAMU2当たり123456XEM",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "namuyan",
　　　　　　　　　　"name": "namu2"
　　　　　　　　},
　　　　　　　　"levy": {
　　　　　　　　　　"fee": 123456,
　　　　　　　　　　"recipient": "TDEK3DOKN54XWEVUNXJOLWDJMYEF2G7HPK2LRU5W",
　　　　　　　　　　"type": 2,
　　　　　　　　　　"mosaicId": {
　　　　　　　　　　　　"namespaceId": "nem",
　　　　　　　　　　　　"name": "xem"
　　　　　　　　　　}
　　　　　　　　}
　　　　　　},
　　　　　　"thumbnail": "http://namuyan.dip.jp/nem/img/mosaic_texture.png"
　　　　},
　　　　{
　　　　　　"divisibility": 0,
　　　　　　"initialSupply": 10000,
　　　　　　"initialSupplyByUnit": 10000,
　　　　　　"supplyMutable": true,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 4545,
　　　　　　"mosaic": {
　　　　　　　　"creator": "efcd2960eca6eeac6091a1324c6e5b4d196a00f77e9962a9ead33bea02c8a722",
　　　　　　　　"description": "サムネイル付き検証用",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "namutest",
　　　　　　　　　　"name": "imges"
　　　　　　　　},
　　　　　　　　"levy": []
　　　　　　},
　　　　　　"thumbnail": "http://i.imgur.com/P3HsCbp.png"
　　　　},
　　　　{
　　　　　　"divisibility": 0,
　　　　　　"initialSupply": 500,
　　　　　　"initialSupplyByUnit": 500,
　　　　　　"supplyMutable": true,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 1934,
　　　　　　"mosaic": {
　　　　　　　　"creator": "6e2c721e8637e0f49f4a7048f983e33dadda41fdbe5de9bc16d9341e2a1c62c8",
　　　　　　　　"description": "タヌ神の神聖なるモザイク",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "godtanu",
　　　　　　　　　　"name": "godtanu"
　　　　　　　　},
　　　　　　　　"levy": []
　　　　　　},
　　　　　　"thumbnail": "http://i.imgur.com/RfCMOaf.png"
　　　　},
　　　　{
　　　　　　"divisibility": 6,
　　　　　　"initialSupply": 100000000,
　　　　　　"initialSupplyByUnit": 100000000000000,
　　　　　　"supplyMutable": true,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 19194545,
　　　　　　"mosaic": {
　　　　　　　　"creator": "6e2c721e8637e0f49f4a7048f983e33dadda41fdbe5de9bc16d9341e2a1c62c8",
　　　　　　　　"description": "タヌxem　税金付き",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "godtanu",
　　　　　　　　　　"name": "tanu_xem"
　　　　　　　　},
　　　　　　　　"levy": {
　　　　　　　　　　"fee": 1,
　　　　　　　　　　"recipient": "TB235JLAOGALDATDJC7LXDMZSDMFBUMDVIBFVQPF",
　　　　　　　　　　"type": 1,
　　　　　　　　　　"mosaicId": {
　　　　　　　　　　　　"namespaceId": "nem",
　　　　　　　　　　　　"name": "xem"
　　　　　　　　　　}
　　　　　　　　}
　　　　　　},
　　　　　　"thumbnail": "http://namuyan.dip.jp/nem/img/mosaic_texture.png"
　　　　},
　　　　{
　　　　　　"divisibility": 2,
　　　　　　"initialSupply": 10000,
　　　　　　"initialSupplyByUnit": 1000000,
　　　　　　"supplyMutable": true,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 1000000,
　　　　　　"mosaic": {
　　　　　　　　"creator": "47900452f5843f391e6485c5172b0e1332bf0032b04c4abe23822754214caec3",
　　　　　　　　"description": "もってるといいことがある....はず、FaucetのDonationのお返しに送金されるよ",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "namuyan",
　　　　　　　　　　"name": "namu"
　　　　　　　　},
　　　　　　　　"levy": []
　　　　　　},
　　　　　　"thumbnail": "http://i.imgur.com/P3HsCbp.png"
　　　　},
　　　　{
　　　　　　"divisibility": 2,
　　　　　　"initialSupply": 100000,
　　　　　　"initialSupplyByUnit": 10000000,
　　　　　　"supplyMutable": true,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 100000,
　　　　　　"mosaic": {
　　　　　　　　"creator": "2ee729d7b2f377fac9948a7c9e87ccb7132453f17afdee9f3d74615fffa74657",
　　　　　　　　"description": "みなりんのテストねっとモザイクです。",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "mizunashi_t",
　　　　　　　　　　"name": "minarin_t"
　　　　　　　　},
　　　　　　　　"levy": []
　　　　　　},
　　　　　　"thumbnail": "http://i.imgur.com/5yIYS0m.png"
　　　　},
　　　　{
　　　　　　"divisibility": 0,
　　　　　　"initialSupply": 10000,
　　　　　　"initialSupplyByUnit": 10000,
　　　　　　"supplyMutable": true,
　　　　　　"transferable": true,
　　　　　　"quantityByUnit" 10000,
　　　　　　"mosaic": {
　　　　　　　　"creator": "efcd2960eca6eeac6091a1324c6e5b4d196a00f77e9962a9ead33bea02c8a722",
　　　　　　　　"description": "／^o^＼ﾌｯｼﾞｯｻｰﾝ　ﾌｯｼﾞｯｻｰﾝ　　＼＼(^o^) ﾀｶｲｿﾞ　　(^o^)／／　ﾀｶｲｿﾞ　　／^o^＼ﾌｯｼﾞｯｻｰﾝ",
　　　　　　　　"id": {
　　　　　　　　　　"namespaceId": "namutest",
　　　　　　　　　　"name": "fujicoin"
　　　　　　　　},
　　　　　　　　"levy": []
　　　　　　},
　　　　　　"thumbnail": "http://i.imgur.com/l3wvYwB.png"
　　　　}
　　]
}</span></p>
EOS;
        $str .= str_replace("\n", "<BR>", $str_tmp);
        echo $str;
        InsertApiUser($ipaddr, $token, 4, $label);
    }
    
        DeleteApiUser();