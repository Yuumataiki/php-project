<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  </head>
  <h1>mission5-1</h1>
  <body>
      <?php
      //データベースへの接続
      $dsn = 'データベース名';
      $user = 'ユーザー名';
      $password = 'パスワード';
      $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

      //投稿のテーブル作成 
      $sql = "CREATE TABLE IF NOT EXISTS mission5" //テーブル名:mission5
      ." ("
      . "id INT AUTO_INCREMENT PRIMARY KEY,"
      . "name char(32),"
      . "comment TEXT,"
      . "time TEXT"
      .");";
      $stmt = $pdo->query($sql);

      //パスワードのテーブル作成
      $sql = "CREATE TABLE IF NOT EXISTS pass" //テーブル名:pass
      ." ("
      . "id INT AUTO_INCREMENT PRIMARY KEY,"
      . "pass char(32)"
      .");";
      $stmt = $pdo->query($sql);

      if (!empty($_POST["name"]) && !empty($_POST["com"]) && !empty($_POST["pass"])) { //新規追加機能 and 編集機能
        if (empty($_POST["edit"])) {//新規投稿モード
          //フォームから名前とコメントとパスワードを取得
          $name = $_POST["name"];
          $comment = $_POST["com"];
          $pass = $_POST["pass"];

          //現在時刻を取得
          $date = date("Y年m月d日H時i分s秒");

          //新規追加処理
          //mission4-5
          //テーブル:mission5に投稿を追加
          $sql = $pdo -> prepare("INSERT INTO mission5 (name, comment, time) VALUES (:name, :comment, :time)");
          $sql -> bindParam(':name', $name, PDO::PARAM_STR);
          $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
          $sql -> bindParam(':time', $date, PDO::PARAM_STR);
          $sql -> execute();

          //テーブル:passにパスワードを追加          
          $sql = $pdo -> prepare("INSERT INTO pass (pass) VALUES (:pass)");
          $sql -> bindParam(':pass', $pass, PDO::PARAM_STR);
          $sql -> execute();
        } else {//編集モード
          //フォームから変更したい投稿の番号と名前とコメントの内容を取得
          $id = $_POST["edit"];
          $name = $_POST["name"];
          $comment = $_POST["com"];

          //データベースからパスワードの取得
          $stmt = $pdo->prepare("SELECT pass FROM pass WHERE id = :id");
          $stmt->bindParam( ':id', $id, PDO::PARAM_INT);
          $results = $stmt->execute();
          if( $results ) {
            $pass = $stmt->fetch();
          }

          if ($pass[0] == $_POST["pass"]) {
            //編集処理
            //mission4-7
            $sql = 'UPDATE mission5 SET name=:name,comment=:comment WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
          }
        }
      }

      if (!empty($_POST["delete"])) { //削除機能
        //フォームから編集対象番号を取得
        $id = $_POST["delete"];

        //データベースからパスワードの取得
        $stmt = $pdo->prepare("SELECT pass FROM pass WHERE id = :id");
        $stmt->bindParam( ':id', $id, PDO::PARAM_INT);
        $results = $stmt->execute();
        if( $results ) {
          $pass = $stmt->fetch();
        }

        if ($pass[0] == $_POST["pass"]) {
          //削除処理
          //mission4-8
          //投稿の削除
          $sql = 'delete from mission5 where id=:id';
          $stmt = $pdo->prepare($sql);
          $stmt->bindParam(':id', $id, PDO::PARAM_INT);
          $stmt->execute();

          //パスワードの削除
          $sql = 'delete from pass where id=:id';
          $stmt = $pdo->prepare($sql);
          $stmt->bindParam(':id', $id, PDO::PARAM_INT);
          $stmt->execute();
        }
      }

      if (!empty($_POST["edit_number"])) {//編集番号指定機能
        //初期化
        $edit_num = "";
        $edit_name = "";
        $edit_com = "";

        //フォームから編集対象番号を取得
        $id = $_POST["edit_number"];

        //データベースからパスワードの取得
        $stmt = $pdo->prepare("SELECT pass FROM pass WHERE id = :id");
        $stmt->bindParam( ':id', $id, PDO::PARAM_INT);
        $results = $stmt->execute();
        if( $results ) {
          $pass = $stmt->fetch();
        }

        //データベースから編集対象番号と一致する投稿の全情報を取得
        $stmt = $pdo->prepare("SELECT * FROM mission5 WHERE id = :id");
        $stmt->bindParam( ':id', $id, PDO::PARAM_INT);
        $results = $stmt->execute();
        if( $results ) {
          $edit = $stmt->fetch();
        }

        if ($pass[0] == $_POST["pass"]) {
          //編集対象のidと名前とコメントを取得
          $edit_number = $edit['id'];
          $edit_name = $edit['name'];
          $edit_com = $edit['comment'];
        }
      }
      ?>

      <form action="" method= "post">
          <input type="text" name="name" placeholder="名前"
          value="<?php
          if(!empty($edit_name)){ //既存の投稿フォームに、上記で取得した「名前」の内容が既に入っている状態で表示させる
            echo $edit_name;
          }
          ?>"><br>
          <input type="text" name="com" placeholder="コメント" 
          value="<?php
          if(!empty($edit_com)){ //既存の投稿フォームに、上記で取得した「コメント」の内容が既に入っている状態で表示させる
              echo $edit_com;
          }
          ?>">
          <input type="hidden" name="edit"
          value="<?php
          if(!empty($edit_number)){ //ブラウザから見えてしまう場合は、type属性をhiddenに変更して見えなくする
            echo $edit_number;
          }
          ?>"><br>
          <input type="text" name="pass" placeholder="password">
          <input type="submit" name="submit">
      </form>
      
      <form action="" method="post">
          <input type="number" name="delete" placeholder="削除対象番号"><br>
          <input type="text" name="pass" placeholder="password">
          <input type="submit" name="submit" value="削除">
      </form><br>

      <form action="" method="post">
          <input type="number" name="edit_number" placeholder="編集対象番号"><br>
          <input type="text" name="pass" placeholder="password">
          <input type="submit" name="submit" value="編集">
      </form>
      <hr>

      <?php
      //表示機能
      //mission4-6
      $sql = 'SELECT * FROM mission5';
      if (!empty($pdo->query($sql))) {
        //表示処理
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll();
        foreach ($results as $row){
          //$rowの中にはテーブルのカラム名が入る
          echo $row['id'].', ';
          echo $row['name'].', ';
          echo $row['comment'].', ';
          echo $row['time'].'<br>';
          echo "<hr>";
        }
      }
      ?>
  </body>
</html>