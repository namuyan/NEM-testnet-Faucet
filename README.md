# NEM-testnet-Faucet

![NEM logo](https://upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Nem_logo.svg/1000px-Nem_logo.svg.png)

#Overview
**NEM testnet Faucet** は testnet の XEM を Faucet形式 で手に入れることができるサイトの全ソースです。  
**testnet** と付いているのは、現実のNEM(mainnet)ではこのような形式では既に配布するのが現実的ではないほど値が高いからです。  
百聞は一見に如かず、以下のリンクに設置してありますので触ってみて下さい。  
<http://namuyan.dip.jp/nem/main/index.php>

## Requirement
####作者の環境  
* XAMPP (1.8.3 include PHP Ver 5.5.6 and Mysql Ver 14.14)  
* NIS (NEM Beta 0.6.82)  
* [NEM-Api-Library (既に含まれています)](https://github.com/namuyan/NEM-Api-Library)  
これより新しければ問題ないはずです。  
PHP7でも動くはずですが未確認です。


## Install
1. ソースをDLします。　`git clone https://github.com/namuyan/NEM-testnet-Faucet.git`

2. Apacheのルートフォルダ以下に*main*ファイルを作成し  
*distribute-file* 内の全ファイルをコピーします。

3. *htdocs*と権限を一致させます。`chown -R daemon:daemon htdocs`

4. *cron/.htaccess* *example/.htaccess* をローカル環境と一致させます。  
あなたのローカルが `192.168.1.*` であるならば `allow from 192.168.3.0/24` → `allow from 192.168.1.0/24`

5. NEMの *PriKey,PubKey,Address* を入手  
`curl http://localhost:7890/account/generate` **重要:必ず記録して誰にも見せない事**

6. *config.php* の *$NEMAddress $NEMprikey $NEMpubkey* に書き込む。

7. データベースを作成する。**パラメータは任意の文字列にする事、例ではconfig.phpのパラメータを使用**  
１、root権限でログイン　`mysql -u root -p`  
２、データベースを作成　`CREATE DATABASE nemdb SET utf8 COLLATE utf8_general_ci;`  
３、ユーザー作成　`CREATE USER 'nember'@'localhost' IDENTIFIED BY 'obama';`  
４、ユーザーに作成したDBの権限を与える　`GRANT ALL PRIVILEGES ON nemdb.* TO 'nember'@'localhost';`  
５、mysqlよりログアウト　`exit`

8. 必要なDBを作成する。 **config.phpにパラメータを書き込んだ後**  
example  
*htdocs/main/cron/makedb.php* に makedb.php があるならば  
`http://localhost/main/cron/makedb.php` へアクセスしエラーが出なければ完了。

9. crontab にて定期実行させる。  
１、*main/cron* 内の *Deposit.php ImageReg.php SBMFaucet.php* の *$root_dir* を直します。  
２、mainファイル内で`pwd`と打てばパスが出ます。  
３、cronに登録　`crontab -e`  
４、以下を書き込みます。  
５、`*/4 * * * * /opt/lampp/bin/php /opt/lampp/htdocs/main/cron/Deposit.php >/dev/null 2>&1`  
　　`*/5 * * * * /opt/lampp/bin/php /opt/lampp/htdocs/main/cron/ImageReg.php >/dev/null 2>&1`  
　　`*/6 * * * * /opt/lampp/bin/php /opt/lampp/htdocs/main/cron/SBMFaucet.php >/dev/null 2>&1`  
８、パスは適宜直して下さい。  

10. reCAPTCHAへの登録などは他のサイトを見て下さい。 `function.php`の17行目の`secret`が空の為エラーが出ます。

11. あとは適宜 index.php config.php を修正して下さい。  


## Usage
`http://localhost/main/index.php`にアクセスしてみましょう。  
NEM-testnet-Faucet は [NEM-Api-Library](https://github.com/namuyan/NEM-Api-Library) のサンプルプログラムみたいな扱いです。  
使うならば *NEM-Api-Library* の方を使用したプログラムを作成する方がｽｯｷﾘしています。

## Licence

[MIT](https://github.com/tcnksm/tool/blob/master/LICENCE)

## Author

[namuyan](http://namuyan.dip.jp)  
Twitter @namuyan_mine