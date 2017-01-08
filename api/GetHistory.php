<?php

/* 
 * DepositHistory,WithdrawHistoryをさらに読み込む
 */

    require_once '../config.php';
    require_once '../function.php';
    require_once '../class.php';
    require_once '../NEMApiLibrary.php';

$page = common::getGET('page');
$which = common::getGET('which');

    
    if($which === 'deposit'){
    $stmh = GetDepositHistory(null,$page);
    if($stmh ->rowCount() > 0){
        echo '<table class="HistoryTable">';
        echo '<tr> <th>ID</th> <th>Amount</th> <th>Address</th> <th>TXID</th> <th>Message</th> <th>Date</th></tr>';
        while($fetch = $stmh ->fetch(PDO::FETCH_ASSOC)){
            preg_match("/([^:]*):([^:]*)/", $fetch['coin'], $CoinMatches);
            $txid = $fetch['txid'];$fetch['done'];
            $namespace = $CoinMatches[1];
            $name = $CoinMatches[2];
            $MosaicData = SerchMosaicInfo($baseurl,$namespace,$name);
            $imgurl = get_mosaic_imgurl($namespace, $name);
            switch ($fetch['done']) {
                case 0: $done = '';break;
                case 1: $done = '<span style="color:red;">完了!</done>';break;
                default:$done = '';break;
            }
            $digit = pow(10, 6);
            
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
    }else{
        $stmh = GetUserHistory(null,$page);
        if($stmh ->rowCount() > 0){
            echo '<table class="HistoryTable">';
            echo '<tr> <th>ID</th> <th>Amount</th> <th>Address</th> <th>TXID</th> <th>Message</th> <th>Date</th></tr>';
            while($fetch = $stmh ->fetch(PDO::FETCH_ASSOC)){
                preg_match("/([^:]*):([^:]*)/", $fetch['coin'], $CoinMatches);
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
    }