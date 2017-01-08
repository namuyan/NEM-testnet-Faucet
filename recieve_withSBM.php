<!DOCTYPE html>
<?php
    require_once './config.php';
    require_once './function.php';
    require_once './class.php';
    require_once './NEMApiLibrary.php';
?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="description" content="NEM testnet faucet">
        <meta name="viewport" content="width=1000">
        <link rel="shortcut icon" href="./img/iconnemu_32.png" type="image/vnd.microsoft.icon">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <link rel="stylesheet" href="./css/default.css">
        <!--
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        -->
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
          ga('create', 'UA-62148063-3', 'auto');
          ga('send', 'pageview');
        </script>
        <title id="l_title">NEM testnet Faucet</title>
    </head>
    <body>
        <div id="header_main"><img src="./img/iconnemu_64.png" style="margin-top:10px;margin-left:10px;">NEM Testnet Faucet</div>
        <div id="body_main">
            <?php
            // 受け取り処理
            $address = str_replace('-', '', common::getPost('address'));
            $message = common::getPost('message');
            if(isset($_POST['address']) AND ( reCAPTCHA($ipaddr) OR imlocal() )  AND CheckUserHistory($ipaddr, $address) === FALSE){
                // 受け取れる
                $digit = pow(10, $nem_Divisibility);
                $amount = mt_rand($nem_Minimum * $digit, $nem_Maximum * $digit) ; // 生の値
                $nem = new TransactionBuilder('testnet');
                $nem->setting($NEMpubkey, $NEMprikey, $baseurl);
                $nem->ImportAddr($address);
                $nem->InputMosaic('nem', 'xem', $amount);
                
                $mosaic = new MosaicData();
                $mosaic->dbname = 'sbmfdb';
                $where = "WHERE `ended` = 0 "; // 変数を入れてはいけない
                $stmh = $mosaic->Get($where);
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
                        $mosaic->Update($coin, $data);
                        //die("sbmdbにエラー発生中");
                    }
                    $distribute = min( mt_rand($minimum, $maximum)  ,$quantity);
                    $nem->InputMosaic($namespace, $name, $distribute);
                    
                }// Mosaic送金処理
                
                    $fee = $nem->EstimateFee();
                    $tmp = $nem->SendMosaicVer2();
                    $reslt = $nem->analysis($tmp);
                    if($reslt['status']){
                        // 送金成功
                        $txid = $reslt['txid'];
                    ?><span id="ok_recieve"><em2>送金が完了しました！</em2></span><br>
                    <ul>
                        <li>Address : <?php echo $address;?></li>
                        <li>TXID    : <?php echo $txid;?></li>
                        <li>Amount  : <?php echo $amount / $digit ."XEM($fee XEM)";?></li>
                        <li>Mosaic  : <?php echo $distribute .' '. strtoupper($name) .' GET!! :)';?></li>
                        <li>Message : <?php echo $message;?></li>
                    </ul>
                    <span id="message_recieve">※10秒後にメイン画面へ自動的に飛びます。</span>
                <script>onload = function() { setTimeout(function() {location.href = './index.php';},10000); };</script>
                        <?php
                    // 送金をDBに記録
                        $withdraw = new WithdrawHistory();
                        $withdraw->Insert($ipaddr, $address, $distribute, $txid, $message, "$namespace:$name");
                        $withdraw->Insert($ipaddr, $address ,$amount ,$txid ,'');
                    }else{
                        // 送金失敗
                    ?><span id="error_recieve"><em2>送金エラーが発生しました</em2></span><br>
                    <ul>
                        <li>Address : <?php echo $address;?></li>
                        <li>Amount  : <?php echo $amount / $digit ."XEM($fee XEM)";?></li>
                        <!--<li>Status  : <?php //echo $Details['status'];?></li>-->
                        <li>Message : <?php echo $Details['message'];?></li>
                    </ul>
                        <?php
                    }
                ?><p style="text-align:center;"><button id="button_recieve" onclick="location.href = './index.php';">メイン画面へ戻る</button></p><?php
            }else{
                // 受け取れない
                ?><span id="no_recieve"><em2>残念ですが受け取れません</em2><br>アドレスが未入力、reCAPTCHAの認証失敗、前回から時間が経っていない などの理由が挙げられます。</span><?php
            }
            ?>
        </div>
            
            <div id="foot_sub">
        <div id="google_translate_element"></div>
        <!-- 翻訳機能 -->
        <script type="text/javascript">function googleTranslateElementInit() {new google.translate.TranslateElement({pageLanguage: 'ja', includedLanguages: 'en,ja', layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL, gaTrack: true, gaId: 'UA-62148063-3'}, 'google_translate_element');}
        </script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
            </div>
    </body>
</html>