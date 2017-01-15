# NEM-testnet-Faucet

![NEM logo](https://upload.wikimedia.org/wikipedia/commons/thumb/0/0a/Nem_logo.svg/1000px-Nem_logo.svg.png)

#Overview
**NEM testnet Faucet** は testnet の XEM を Faucet形式 で手に入れることができるサイトの全ソースです。  
**testnet** と付いているのは、現実のNEM(mainnet)ではこのような形式では既に配布するのが現実的ではないほど値が高いからです。  
百聞は一見に如かず、以下のリンクに設置してありますので触ってみて下さい。  
<http://namuyan.dip.jp/nem/main/index.php>

I recomend [NEM-Api-Library](https://github.com/namuyan/NEM-Api-Library) ,it help you constract contents with PHP!

## Requirement
####作者の環境  
* XAMPP (1.8.3 include PHP Ver 5.5.6 and Mysql Ver 14.14)  
* NIS (NEM Beta 0.6.82)  
* [NEM-Api-Library (既に含まれています)](https://github.com/namuyan/NEM-Api-Library)  
これより新しければ問題ないはずです。  
PHP7でも動くはずですが未確認です。


## Install (in Japanese)
1. ソースをDLします。　`git clone https://github.com/namuyan/NEM-testnet-Faucet.git`  
Download sorce.　`git clone https://github.com/namuyan/NEM-testnet-Faucet.git`

2. Apacheのルートフォルダ以下に*main*ファイルを作成し、  
*NEM-testnet-Faucet* 内の全ファイルをコピーします。copyできない場合は権限の違いによるものです。  
Make *main* folder and copy all data of *NEM-testnet-Faucet* to the folder.  
`mkdir main` and `cp -r /patt/to/NEM-testnet-Faucet/* /passs/too/main/`

3. *htdocs*と権限を一致させます。`chown -R daemon:daemon htdocs`  
Match the authority with * htdocs *. `chown -R daemon:daemon htdocs`


4. *cron/.htaccess* and *example/.htaccess* をローカル環境と一致させます。これらは他人にアクセスされないように  
あなたのローカルが `192.168.1.*` であるならば `allow from 192.168.3.0/24` → `allow from 192.168.1.0/24`  


5. NEMの *PriKey,PubKey,Address* を入手  
`curl http://localhost:7890/account/generate` **重要:必ず記録して誰にも見せない事**

6. *config.php* の *$NEMAddress $NEMprikey $NEMpubkey* に書き込む。

7. データベースを作成する。**パラメータは任意の文字列にする事、例ではconfig.phpのパラメータを使用**  
１、root権限でログイン　`mysql -u root -p`  
２、データベースを作成　`CREATE DATABASE nemdb CHARACTER SET utf8 COLLATE utf8_general_ci;`  
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

## Install(in English)
1. Download sorce.　`git clone https://github.com/namuyan/NEM-testnet-Faucet.git`

2. Make *main* folder and copy all data of *NEM-testnet-Faucet* to the folder.  
`mkdir main` and `cp -r /patt/to/NEM-testnet-Faucet/* /passs/too/main/`

3. Match the authority with * htdocs *. `chown -R daemon:daemon htdocs`


4. To fit local environment *cron/.htaccess* and *example/.htaccess*. DO NOT ALLOW OTHERS TO ACCESS!   
If your local ip is `192.168.1.12` ,set `allow from 192.168.3.0/24` → `allow from 192.168.1.12/24`


5. Create NEM account, `curl http://localhost:7890/account/generate` **DO NOT SHOW OTHERS**

6. Write the three account data (*$NEMAddress $NEMprikey $NEMpubkey*) to *config.php*.

7. Create DB account **Use diffarent parameter to Example、I use same parameter of config.php as example**  
１、Login as root　`mysql -u root -p`  
２、Create database　`CREATE DATABASE nemdb CHARACTER SET utf8 COLLATE utf8_general_ci;`  
３、Create user　`CREATE USER 'nember'@'localhost' IDENTIFIED BY 'obama';`  
４、Grant the user access to the DB　`GRANT ALL PRIVILEGES ON nemdb.* TO 'nember'@'localhost';`  
５、Logout　`exit`


8. Create tables **After write down to config.php**  
example  
If makedb.php on *htdocs/main/cron/makedb.php*, access to `http://localhost/main/cron/makedb.php`  
Check no error output.

9. Run regularly by crontab.  
１、To make a pass of *$root_dir* of *Deposit.php ImageReg.php SBMFaucet.php* in *main/cron*.  
２、Get pass to type `pwd` at main folder.  
３、`crontab -e`  
４、Write down followings. (pass is original)  
５、`*/4 * * * * /opt/lampp/bin/php /opt/lampp/htdocs/main/cron/Deposit.php >/dev/null 2>&1`  
　　`*/5 * * * * /opt/lampp/bin/php /opt/lampp/htdocs/main/cron/ImageReg.php >/dev/null 2>&1`  
　　`*/6 * * * * /opt/lampp/bin/php /opt/lampp/htdocs/main/cron/SBMFaucet.php >/dev/null 2>&1`  

## Usage
`http://localhost/main/index.php`にアクセスしてみましょう。  
NEM-testnet-Faucet は [NEM-Api-Library](https://github.com/namuyan/NEM-Api-Library) のサンプルプログラムみたいな扱いです。  
使うならば *NEM-Api-Library* の方を使用したプログラムを作成する方がｽｯｷﾘしています。

## Licence

[MIT](https://github.com/tcnksm/tool/blob/master/LICENCE)

## Author

[namuyan](http://namuyan.dip.jp)  
Twitter @namuyan_mine

DonationCPaddress： 1BvRTmPCe47vee2CyrLi9AGeSEcrR2ciM4  
DonationNEMaddress： NAN7XFG52NL3V5AW3NTSYO77AVR6X5LYRJKXWKHY  
DonationMonacoin： MSYTEF7t62b9sjXt3oN9JokSjnYkvtcPFx