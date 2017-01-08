<?php

/*
 * 設定値格納
 * 
 */

        // 根幹の設定
        // コマンドラインでcurl http://localhost:7890/account/generate　と打ち込んでAddress,PriKey,Pubkeyを取得
        $SpanNEM = 60*60 ; //3600秒、一時間間隔開けなければFaucetを回せない、秒単位
        // cronフォルダにある$root_dirも個別に設定しなければならないのに注意
        $baseurl = 'http://localhost:7890'; // NISのURL
        $BlockExplorer = 'http://bob.nem.ninja:8765'; // NEMのブロックエクスプローラ
        $languages = array('jp'=>'日本語','en'=>'english');
        $ipaddr = isset($_SERVER['HTTP_X_FORWARDED_FOR'])? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR']; // $_SERVER['REMOTE_ADDR']=リバースプロキシ使用しない場合、$_SERVER['HTTP_X_FORWARDED_FOR']=リバースプロキシ使用する場合
        
        // サイトAddress設定
        $NEMAddress = ''; // Faucetのアドレス
        $NEMprikey = ''; //NEMのPriKey、送金時に必要
        $NEMpubkey = ''; //NEMのPubkey、送金時に必要
        
        // 配布量設定
        $nem_Minimum = 20 ; // NEMの配布される最大量、最小量、小数点以下桁数
        $nem_Maximum = 200 ;
        $nem_Divisibility = 6 ;
        
        // サムネイル登録設定
        $ImageRegFee = 10;  // NEMのサムネイルを登録するのに $ImageRegMosaic が $ImageRegFee 必要
        $ImageRegDivisibility = 6;
        $ImageRegMosaic = 'nem:xem';
        
        // SBMF(SmallBusinessMosaicFaucet)の設定
        $SBMFaucetFee = 10;  // SBMFに登録するには $SBMFaucetMosaic が $SBMFaucetFee 必要
        $SBMFaucetDivisibility = 6;
        $SBMFaucetMosaic = 'nem:xem';
        $SBMFaucetUserFee = 10;  // SBMFをひねるには $SBMFaucetUserMosaic が $SBMFaucetUserFee 必要であり $SBMFaucetUserIncome がFaucet設置主のポケット(ヾﾉ･∀･`)ﾅｲﾅｲ
        $SBMFaucetUserIncome = 1;
        $SBMFaucetUserDivisibility = 6; // NEM以外対応せず
        $SBMFaucetUserMosaic = 'nem:xem'; // NEM以外対応せず
        
    
    
        // mysql設定
        $db_user = "nember";
        $db_pass = "obama";
        $db_host = "localhost";
        $db_name = "nemdb";
        $db_type = "mysql";
        
        
        // API用変数
        $ApiFrequency = 60*60;
        $ApiSpan = 60*60;  // $ApiSpan秒に$ApiFrequency回以上アクセス禁止
        $ApiLimit = 3600;  // $ApiSpan秒に$ApiFrequency + $ApiLimit回以上アクセスするとブラックリスト入り
        $ApiWhiteList = array('192.168.3.16','192.168.3.3'); //API制限を課さないIPリスト　未実装
        
        
        
/* MIT
http://wisdommingle.com/mit-license/
Copyright (c) 2013 namuyan
http://wisdommingle.com/

Permission is hereby granted, free of charge, to any person obtaining a 
copy of this software and associated documentation files (the 
"Software"), to deal in the Software without restriction, including 
without limitation the rights to use, copy, modify, merge, publish, 
distribute, sublicense, and/or sell copies of the Software, and to 
permit persons to whom the Software is furnished to do so, subject to 
the following conditions:

The above copyright notice and this permission notice shall be 
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, 
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF 
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND 
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE 
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION 
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
         */