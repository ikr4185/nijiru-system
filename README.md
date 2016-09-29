# nijiru frame02

nijiruフレームワークは、MVC+LIモデルです。

* /application
    - /controllers
    - /logics
    - /inputs
    - /models
    - /views

## 各役割

* Controller
    - アプリケーション層です。ユーザのリクエスト(Input)を受け取り、適切なLogicに渡します。返って来た結果を、Viewに渡します。
* Logic
    - アプリケーション層です。Controllerからの命令を受け取り、Modelからの結果を返します。つまり処理の実体です。Controllerは複数のLogicを取る事ができます。
* Input
    - アプリケーション層です。ViewまたはSessionからの入力、データ保持を請け負います。
* Model
    - ドメイン層です。MySQLデータベースの値を保持します。DB構造と1対1の関係でなくてはなりません。また、全てシングルトン構成になっています。
* View
    - ユーザインタフェース層です。リクエストの入力と、レスポンスの表示を行います。Smartyを利用します。


企業に例えると、各機能の役割分担がわかりやすくなるかも知れません。

* Controller
    - ディレクターです。
    - 各社員に指示を出し、プロジェクトを進行します。
    - 細かい実際の仕事のやり方は知りません。
* Logic
    - 実際に業務を行う社員です。
    - 各メンバーは、それぞれ自分の仕事についての知識を持っています。
* Input
    - 外部からの電話や来客を取り次いでくれる、受付のお姉さんです。
    - 担当者不在時は、要件をメモしておいてくれたりします。
* Model
    - 資料庫です。
    - 各社員が必要に応じて資料を出し入れします。
* View
    - 実店舗の売り場と、アルバイトです。
    - 細かい事務処理などはしませんが、季節ごとに看板を差し替えるぐらいの仕事はしてくれます。
    
更に、各職種は「その職種のリーダー(=抽象クラス)」がおり、各人で重複する仕事を取りまとめてやってくれたりします。

---

## _cores について

* デザインパターンの抽象化クラス、ヘルパー等、汎用性の高いクラス群を格納します。
* /home/njr-sys/public_html/application/_cores/config/config.ini にコンフィグファイルを設置しています。
    - 上記は Config::load("path.app") で呼び出すことが出来ます。
    
---

## composer

* オートロード機能と外部のライブラリ利用の為、composerを導入しています。
    - 2016-07-18 現在、smartyが読み込まれています。
* オートロードはPSR-4準拠になっています。フレームワーク構造に追加/修正がある場合、/home/njr-sys/public_html/application/composer.json を修正してください。

### Nijiru Command Line Interfaces Loader

* Composer::Script の機能を使い、Cliを呼び出すことが出来ます
* php composer.phar ncl CliUserGetter
* /application/cli 内に設置したスクリプトをコントローラーのように扱い、NijiruSystem内のクラスをオートロードすることが可能です。
    - CLI駆動の都合で、AbstractControllerやinput、一部のhelperは使用できません。

---

## 仕様詳細

### URL 

* URLは、勿論大文字小文字は無視されます

### Controller

* Controller_Prefix, Action_Prefix は、キャメルケースを用います
* Controller のファイル名は、Prefixの先頭と"Controller"のCだけ大文字で、後は全て小文字にする必要があります
* クラス名はキャメルで問題ありません

### views

* 各ページのテンプレートファイルは、/views/templates/[Controller_Prefix]/[getViewの第一引数].tpl にする必要があります。

---

## 手順書

### composer

* /home/njr-sys/public_html/application/composer.json を修正
* application に移動して
* php composer.phar install