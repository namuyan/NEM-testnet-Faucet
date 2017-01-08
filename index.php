<!DOCTYPE html>
<?php
    require_once './config.php';
    require_once './function.php';
    require_once './class.php';
    require_once './NEMApiLibrary.php';
    $error = GreenOrRed();
    $url = $baseurl.'/account/get?address='.$NEMAddress;
    $datatmp = get_json_array($url);
    $balance = display_shousuuten($datatmp['account']['balance'] / pow(10 ,$nem_Divisibility));
?>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="description" content="NEM testnet faucet">
        <meta name="viewport" content="width=1200">
        <link rel="shortcut icon" href="./img/iconnemu_32.png" type="image/vnd.microsoft.icon">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <link rel="stylesheet" href="./css/default.css">
        <!--
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
        -->
        <script src="https://www.google.com/recaptcha/api.js"></script>
        <script  >
            function syncerRecaptchaCallback( code ){ // recaptchaで使用
                if( code != "" ){
		$( '#syncer-recaptcha-form input , #syncer-recaptcha-form button' ).removeAttr( 'disabled' ) ;
                }
            }
        </script>
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
          ga('create', 'UA-62148063-3', 'auto');
          ga('send', 'pageview');
        </script>
        <script>
        function hyoji(id){
            var num = document.getElementById( id ).style.display;
            if (num === 'none'){
                document.getElementById( id ).style.display="block";
            }else{
                document.getElementById( id ).style.display="none";
            }
        }function CheckRegimgMessage(){
            var message = document.getElementById('CheckRegimgMessageInput').value;
            var div = document.getElementById('CheckRegimgMessageDiv');
            var matches = message.match(/^regimg,([a-z0-9]+?),([a-zA-Z0-9]+?),([^:]+?):([^:]+?)$/);
            // 1=imgur or others,2=ID,3=namespace,4=name
            if(Array.isArray(matches)){
                var url = './api/mosaic_definition.php';
                console.log('url:' + url);
                console.log('namespace :' + matches[3] +', name :'+ matches[4]);
                $.ajax({
                    type:"get",
                    url: url,
                    data: { namespace : matches[3], name : matches[4] },
                    timeout: 5000,
                    dataType: "json",
                }).done(function(data, status, xhr) {
                    // 通信成功時の処理
                    console.log( data );
                    console.log("status:" + status);
                    console.log("xhr:" + xhr);
                    if(data['status'] === true){
                        var address = data['detail']['detail']['mosaic']['address'];
                        var description = data['detail']['detail']['mosaic']['description'];
                        if(matches[1] === 'imgur'){
                            div.innerHTML = 'Messageをデコードできました。<br>Address：' + address + '<br>Description：' + description + '<br>Image　<img src="http://i.imgur.com/'+ matches[2] +'.png">';
                        }else{
                            // その他のうｐロダまだない
                           　div.innerHTML = 'Error:第二要素が"imgur"ではありません。';
                        }
                    }else{
                        div.innerHTML = 'Error:Mosaicの定義を見つける事ができませんでした。記入をご確認ください。';
                    }
                }).fail(function(xhr, status, error) {
                    // 通信失敗時の処理
                    console.log("xhr:" + xhr);
                    console.log("status:" + status);
                    console.log("error:" + error);
                    div.innerHTML = 'Error:APIにアクセスしましたがエラーになりました。';
                });
            }else{
                // Arrayでない
                div.innerHTML = 'Error:構文エラーです、もう一度説明をよく読んで下さい。';
            }
        }
        var rowd = 2;
        var roww = 2;
        function GetHistory(which){
        var row_num,id;
        if(which === 'deposit'){
            row_num = rowd;
            rowd ++;
            id = '#foot_main1';
        }else{
            row_num = roww;
            roww ++;
            id = '#foot_main2';
        }
            $.ajax({
                url: './api/GetHistory.php',
                data: {"page" : row_num ,'which' : which},
                type: "GET",
                timeout: 5000
                }).done(function(data, status, xhr) {
                    console.log(data);
                    $(id).append(data);
                }).fail(function(xhr, status, error) {
                    console.log("Error:fail");
                    $(id).append("<span style='color:red;'>Error</span>");
                });
        }
        </script>
        <title id="l_title">NEM testnet Faucet</title>
    </head>
    <body>
            <?php
            //echo '<div style="margin:10px;">';
            //echo '<select name="SelectLanguage"onchange="ChangeLanguage();">';
            //foreach ($languages as $key => $value) {
            //    echo '<option value="'.$key.'" '.($languages === common::getCookie('languages'))?"selected ":"" .$value.'</option>';
            //}
            //echo '</select>';
            //echo '</div>';
            ?>
                
        <div id="header_main"><img src="./img/iconnemu_64.png" style="margin-top:10px;margin-left:10px;">NEM Testnet Faucet</div>
            
            <div id="header_sub1"><em2>NEM とは、</em2><br>平等な分散型プラットフォームとして新たなデジタル通貨による経済圏を作り出すことを目標とした仮想通貨のことです。
                POS(Proof of stake)を改良した<em>POI(Proof of importance)</em>という、ネットワークに対してより大きな「重要度」を持つユーザーがブロックを生成し、報酬として基軸通貨であるコイン(XEM)を得るシステムを採用しています。
            また、NEMには独自通貨発行機能があり<em>Mosaic</em>と呼ばれます。Mosaicには送金する際の最小単位を決めたり、送金額に応じて徴税したり、ユーザー間の送金を制限したりできるなど、コミュニティ内通貨として利用しやすくなっています。
            さらに、NEMは独自の安全性が保障されたネットワークを構築しています。</div>
            
            <div id="header_sub2"><em2>NEM Testnet Faucet とは、</em2><br>開発中のプログラムを本番環境に限りなく近い状態で<em>テストをする為の</em>NEMの蛇口です。
                testnetのNEMには<em>価値は無い</em>とされ、タダで入手できます。使用方法は簡単です、下記の入力欄にNEMアドレスを入れて送信ボタンを押すだけです。テストが完了しましたら、
                蛇口のアドレスにNEMを<em>戻していただけるとありがたいです。</em>またtestnetのNEMを用いてMosaicを作成してみたり、友達と送金しあいImportanceの増加を楽しんだり、ハーベストを試してみたり
                するのに使用することができます。NEMを面白いと感じましたら是非mainnetの方でご活用下さい。
            </div>
            
            <div id="body_main">
                <em2>提供中のサービス</em2><br>
                <ol>
                    <li>
                        <span style="font-size:20px;">Testnet NEM Faucet</span>
                    </li>
                    <li>
                        <span style="font-size:20px;">SBMF (Small Business Mosaic Faucet)</span><button onclick="hyoji('SBMFDescription');">Click!</button>
                        <div id="SBMFDescription" style="display: none;margin:0px 10px;padding:10px;background-color:#FFBD2C;">
                            SBM限定ですがFaucet形式で入手することができます。<br>
                            そもそもSBMとはSmall Business Mosaicの略で供給量１万分割無しのMosaicのことです。送金手数料が定額１XEMであることが特徴です。<br>
                            【SBMFへの登録方法】
                            <ol>
                                <li>登録には条件があります。SBMの作成者であること、SmoallBusinessMosaicの定義を満たすこと。</li>
                                <li>やり方は簡単です、<?php echo "{$NEMAddress} 宛てに {$SBMFaucetFee}　{$SBMFaucetMosaic}";?> と 撒きたいSBM を同梱して特定のMessageを付けて送るだけです。</li>
                                <li>例えば namuyan:namu を 1000 撒きたい、そして一回に撒かれる量は最大10で最小30にしたいならば sbmf,namuyan:namu,10,30 が送るMessageになります。</li>
                                <li>ただし、最小値は０より大きく最大値は最大供給量以下であることが条件です。もしも撒く条件を変えたい場合は <?php echo "{$NEMAddress} 宛てに {$SBMFaucetFee}　{$SBMFaucetMosaic}";?> をMessage付きで送ることになります。</li>
                                <li>条件を変える場合のmessageの構文は登録時と同じものです。ただし、FaucetからMosaicをすべて引き上げる...のようなことは今の所できません。</li>
                            </ol>
                            【SBMFの使い方】・以下の使用欄を見て下さい。<br>
                        </div>
                    </li>
                    <li>
                        <span style="font-size:20px;">Mosaic API</span>　<button onclick="hyoji('MosaicApiDescription');">Click!</button>
                        <div id="MosaicApiDescription" style="display: none;margin:0px 10px;padding:10px;background-color:#FFBD2C;">
                            <strong>APIのアクセスし過ぎにご注意ください。1時間当たり3600回に制限されています。</strong><br>
                            Mosaic定義　<a href="http://namuyan.dip.jp/nem/main/api/mosaic_definition.php">http://namuyan.dip.jp/nem/main/api/mosaic_definition.php</a><br>
                            アドレス所有Mosaic　<a href="http://namuyan.dip.jp/nem/main/api/own_mosaic.php">http://namuyan.dip.jp/nem/main/api/own_mosaic.php</a><br>
                        </div>
                    </li>
                    <li>
                        <span style="font-size:20px;">Mosaic Thumbnail 登録</span>　<button onclick="hyoji('MosaicThumbnailDescription');">Click!</button>
                        <div id="MosaicThumbnailDescription" style="display: none;margin:0px 10px;padding:10px;background-color:#FFBD2C;">
                            このサイトではMosaicのサムネイルを登録することができます。<br>
                            【手順】<br>
                            <ol>
                                <li>例として、ネームスペースがnamuyan、モザイク名がnamuのサムネイルを登録するとします。</li>
                                <li>imgur.comにサムネイルをアップロードします。128x128ｻｲｽﾞが最適ですが制限はありません。</li>
                                <li>リンクをGET(http://imgur.com/P3HsCbp)しましたら、<?php echo "{$NEMAddress} 宛てに{$ImageRegFee}　{$ImageRegMosaic}　を";?> message 付きで送ります。<span style="color:red;">Messageの内容が重要</span>ですので間違えないようにして下さい。また、モザイク発行者と送金者のアドレスが異なると弾かれますので注意してください。</li>
                                <li>Messageには　regimg,imgur,P3HsCbp,namuyan:namu　と書き込み送金します。Messageは適宜変えて下さい。</li>
                                <li>DepositHistoryのMessageに<span style="color:red;">完了！</span>の文字がありましたら登録完了です！(確定までに30分かかる場合があります、それ以上待っても完了にならない場合は教えて下さい。)</li>
                                <li><input id="CheckRegimgMessageInput" style="width: 400px;height: 20px;" placeholder="messageが正しいかお試し下さい"><button onclick="CheckRegimgMessage();">Click!</button><div id="CheckRegimgMessageDiv" style="background-color:white;margin:10px;"></div></li>
                            </ol>
                        </div>
                    </li>
                    <li><span style="font-size:20px;">tomotomoさんのFaucet(外部)：</span><a href="http://tomotomo9696.xyz/nem/faucet/">てすとねっと蛇口</a></li>
                </ol>
            </div>
            
            <div id="body_sub1">
                <em2>XEM Faucet (<?php echo " $nem_Minimum ～ $nem_Maximum ";?> XEM /1h)</em2>いまならMosaic付き！<br>
                <?php if( CheckUserHistory($ipaddr) ){?>
                <em2><?php echo floor(($SpanNEM + strtotime(CheckUserHistory($ipaddr)) - time() ) /60);?></em2><em2 id="AlreadyUsedMessage"> 分お待ち下さい。連続でFaucetを使用できません。</em2>
                <?php }elseif($error){ ?>
                <form action="./recieve_withSBM.php" method="POST">
                    <table style="width:auto;padding:5px;" id="syncer-recaptcha-form">
                        <tr><th><?php echo 'Faucet Balance：'.$balance.' XEM';?></th></tr>
                        <tr><td><input name="address" style="width: 500px;height: 20px;" placeholder="NEM Address"></td></tr>
                        <tr><td><input name="message" style="width: 400px;height: 20px;" placeholder="message"></td></tr>
                        <tr><td><input type="submit" value="Click!" id="NEMButton" <?php echo (imlocal())?"":"disabled";?>></td></tr>
                    </table>
                    <div class="g-recaptcha" data-callback="syncerRecaptchaCallback" data-sitekey="6LfR-SITAAAAAMUjiJtJJoDFqKuXSDguwF54_crI"></div>
                </form>
                <?php }else{ ?>
                    Error has occurred on NIS. Prease wait....
                <?php } ?>
            </div>
            
            <div id="body_sub2">
                <em2>SBMF(Small Business Mosaic Faucet)</em2><button onclick="hyoji('SBMFaucetDescription');">Click!</button><br>
                
                <?php if( true OR imlocal()){?>
                <?php
                $where = '';
                $dbname = 'sbmfdb';
                $stmh = GetMosaicData($where, $dbname);
                if($stmh ->rowCount() > 0){
                    ?>
                    SBM を手に入れることができるFaucetです。発行数1万以下、小数点分割無しのMosaicです。
                    <div style="display: inline-block;">
                    【 使用方法 】
                    <ol>
                        <li><?php echo "$NEMAddress 宛てに $SBMFaucetUserFee $SBMFaucetUserMosaic をmessage付きで送ります。";?></li>
                        <li><span style="color:red;">messageの内容が重要です。必ず”twistSBMF”と記入して下さい。</span></li>
                        <li>5～20分するとDepositHistoryに送金履歴が表示され <span style="color:red;">完了！</span> の文字が付加されます。</li>
                        <li>ランダムで選ばれたMosaicが送られます。SBMFを使用する間隔に制限はありませんがMosaicの指定はできません。</li>
                        <li>配布されるMosaicは以下の中から選ばれます。一部は在庫切れかもしれません。</li>
                    </ol>
                    【 How to use 】
                    <ol>
                        <li><?php echo "You send $SBMFaucetUserFee $SBMFaucetUserMosaic to $NEMAddress with a specific message.";?></li>
                        <li><span style="color:red;">You MUST set the message with "twistSBMF".</span></li>
                        <li>Your transaction is set on DepositHistory. And You find a following Japanese message "<span style="color:red;">完了！</span>" after a message of the transaction in 5～20 minutes.</li>
                        <li>We send a SBMosaic at randum. There is no limit on use ,but you can't choose it.</li>
                        <li>We send a Mosaic in the bottom column. Some Mosaic may not exist already.Check by mouseover.</li>
                    </ol>
                    <div id="SBMFaucetDescription" style="display: block;">
                    <?php
                    while ($fetch = $stmh ->fetch(PDO::FETCH_ASSOC)){
                        preg_match('/^([^:]+?):([^:]+?)$/', $fetch['coin'], $matches);
                        $namespace = $matches[1];
                        $name = $matches[2];
                        $img = get_mosaic_imgurl($namespace, $name);
                        echo "<div class='mask1'>";
                        echo     "<img src='{$img}' style='width:100%;height:100%;' alt='Mosaic'>";
                        echo     "<div class='mask2'>";
                        if($fetch['ended'] === 0 ){
                        echo         "<div class='caption_str'>You can GET {$fetch['minimum']}～{$fetch['maximum']}</div>";
                        }else{
                        echo         "<div class='caption_img'><img src='./img/icon_ended.png' style='position:absolute;width:100%;height:100%;' alt='終了Mosaic'></div>";
                        }
                        echo     "</div>";
                        echo "</div>";
                    }
                    echo '</div></div>';
                }else{
                    echo '配布中のSBMは存在しません。';
                }
                ?>
                <style>
.mask1 {
	width:			128px;
        height:                 128px;
	overflow:		hidden;
	margin:			10px;
	position:		relative;	/* 相対位置指定 */
        float: left;
}
.mask1 .caption_str {
	font-size:		130%;
	text-align: 		center;
	padding-top:		80px;
	color:			#fff;
}
.mask1 .caption_img {
	font-size:		130%;
	color:			#fff;
}
.mask1 .mask2 {
	width:			100%;
	height:			100%;
	position:		absolute;	/* 絶対位置指定 */
	top:			0;
	left:			0;
	opacity:		0;	/* マスクを表示しない */
	background-color:	rgba(0,0,0,0.4);	/* マスクは半透明 */
	-webkit-transition:	all 0.2s ease;
	transition:		all 0.2s ease;
}
.mask1:hover .mask2 {
	opacity:		1;	/* マスクを表示する */
}
                </style>
                <?php }else{ ?>
                    工事中...
                    <!--Error has occurred on NIS. Prease wait....-->
                <?php } ?>
            </div>
            
            <div id="foot_main1" style="overflow-x:auto;"><em2>Deposit History</em2><button onclick="GetHistory('deposit');">さらに読み込む</button>
                <?php
                $stmh = GetDepositHistory();
                if($stmh ->rowCount() > 0){
                    echo '<table class="HistoryTable">';
                    echo '<tr> <th>　</th> <th>Amount</th> <th>Address</th> <th>TXID</th> <th>Message</th> <th>Date</th></tr>';
                    while($fetch = $stmh ->fetch(PDO::FETCH_ASSOC)){
                        if(!preg_match("/([^:]+):([^:]+)/", $fetch['coin'], $CoinMatches)){
                            print_r($fetch);
                            continue;
                        }
                        $txid = $fetch['txid'];
                        $namespace = $CoinMatches[1];
                        $name = $CoinMatches[2];
                        $MosaicData = SerchMosaicInfo($baseurl,$namespace,$name);
                        $imgurl = get_mosaic_imgurl($namespace, $name);
                        switch ($fetch['done']) {
                            case 0: $done = '';break;
                            case 1: $done = '<span style="color:red;">完了!</done>';break;
                            default:$done = '';break;
                        }
                        $digit = pow(10, $MosaicData['divisibility']);
                        
                        echo '<tr>';
                        echo "<td><img src='$imgurl' alt='NEM mosaic' style='height:32px;'></td>";
                        echo "<td>".display_shousuuten($fetch['amount'] /$digit)." <span style='font-size:small;'>{$CoinMatches[1]}:</span><span style='font-weight:bold;'>".strtoupper($CoinMatches[2])."</span></td>";
                        echo "<td>{$fetch['nem_address']}</td>";
                        echo "<td><a href='{$BlockExplorer}/#/transfer/{$txid}' alt='nem blockexplorer'>".  substr($txid, 0, 12)."...</a></td>";
                        echo "<td>{$fetch['message']} {$done}</td>";
                        echo "<td>".substr($fetch['i_date'], 0, 16)."</td>";
                        echo '</tr>';
                    }
                    echo '</table>';
                }else{
                    // 履歴なし
                    echo "No recording";
                }
                ?>
            </div>

                    <div id="foot_main2" style="overflow-x:auto;"><em2>Withdrow History</em2><button onclick="GetHistory('withdraw');">さらに読み込む</button>
                <?php
                $stmh = GetUserHistory(null, 1);
                if($stmh ->rowCount() > 0){
                    echo '<table class="HistoryTable">';
                    echo '<tr> <th>ID</th> <th>Amount</th> <th>Address</th> <th>TXID</th> <th>Message</th> <th>Date</th></tr>';
                    while($fetch = $stmh ->fetch(PDO::FETCH_ASSOC)){
                        if(!preg_match("/([^:]+):([^:]+)/", $fetch['coin'], $CoinMatches)){
                            continue;
                        }
                        $txid = $fetch['txid'];
                        $MosaicData = SerchMosaicInfo($baseurl,$CoinMatches[1], $CoinMatches[2]);
                        $imgurl = get_mosaic_imgurl($CoinMatches[1], $CoinMatches[2]);
                        $digit = pow(10, $MosaicData['divisibility']);
                        
                        echo '<tr>';
                        echo "<td><img src='$imgurl' alt='NEM mosaic' style='height:32px;'></td>";
                        echo "<td>".display_shousuuten($fetch['amount'] /$digit)." <span style='font-size:small;'>{$CoinMatches[1]}:</span><span style='font-weight:bold;'>".strtoupper($CoinMatches[2])."</span></td>";
                        echo "<td>{$fetch['nem_address']}</td>";
                        echo "<td><a href='{$BlockExplorer}/#/transfer/{$txid}' alt='nem blockexplorer'>".  substr($txid, 0, 12)."...</a></td>";
                        echo "<td>{$fetch['message']}</td>";
                        echo "<td>".substr($fetch['i_date'], 0, 16)."</td>";
                        echo '</tr>';
                    }
                    echo '</table>';
                }else{
                    // 履歴なし
                    echo "No recording";
                }
                ?>
            </div>
            
            <div id="foot_sub">
        <div id="google_translate_element"></div>
        <!-- 翻訳機能 -->
        <script type="text/javascript">function googleTranslateElementInit() {new google.translate.TranslateElement({pageLanguage: 'ja', includedLanguages: 'en,ja', layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL, gaTrack: true, gaId: 'UA-62148063-3'}, 'google_translate_element');}
        </script><script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
        <ul>
            <li>Faucet Address　：　<?php echo $NEMAddress;?></li>
            <li>Donation Address：　NAN7XFG52NL3V5AW3NTSYO77AVR6X5LYRJKXWKHY</li>
            <li>Twitter　：　@namuyan_mine</li>
        </ul>
            </div>
        <?php if(imlocal()){print ""; } ?>
    </body>
</html>