<?php

include 'connect_to_db.php';

// Connect to db
$pdo = connect_to_db();
if($pdo == null) {
    exit();
}

$user_id = $_POST["author"];
$user_pw = $_POST["password"];
$user_email = $_POST["email"];
$is_new_user = $_POST["is_new_user"];

//echo $user_id;
//echo $user_pw;
//echo $user_email;
//echo $is_new_user;

// Create new user
if($is_new_user == "1"){
    // 檢查該id是否已經存在, 如果已經存在就回傳錯誤到index.php
    $sql = "SELECT * FROM `user_table` WHERE user_id=:user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR when checking new user id: " . $error[2]);
    }

    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If the user id is used, then send an error message to index.php
    if(count($values)>0){
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
</head>
<body>
    <form id="back_to_index_form" method="post" action="index.php">
        <input type="text" name="user_id" value="<?= $user_id ?>" hidden />
        <input type="text" name="user_email" value="<?=$user_email?>" hidden />
        <input type="text" name="error_message" value="<?= $user_id ?> is already used" hidden />
        <input type="text" name="is_new_user" value="<?=$is_new_user?>" hidden />
    </form>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

    $(document).ready(function() {
        $("#back_to_index_form").submit();
    });

</script>
</html>

<?php
    exit(); // Stop php
    }

    // If the user id is not used, then create a new one in database
    else {
        $sql = "INSERT INTO `user_table`(`user_id`,`user_pw`,`user_email`) VALUES " .
                                       "(:user_id ,:user_pw ,:user_email)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
        $stmt->bindParam(":user_pw", $user_pw, PDO::PARAM_STR);
        $stmt->bindParam(":user_email", $user_email, PDO::PARAM_STR);
        $status = $stmt->execute();

        if ($status === false) {
            //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
            $error = $stmt->errorInfo();
            exit("SQL_ERROR when create new user: " . $error[2]);
        }
    }
}

// Log in
else {
    // 檢查該id與pw是否已經存在, 如果不存在就回傳錯誤到index.php
    $sql = "SELECT * FROM `user_table` WHERE user_id=:user_id AND user_pw=:user_pw";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_STR);
    $stmt->bindValue(":user_pw", $user_pw, PDO::PARAM_STR);
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR when checking id and pw: " . $error[2]);
    }

    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no user id and pw are found, then send an error message to index.php
    if (count($values) == 0) {
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
</head>
<body>
    <form id="back_to_index_form" method="post" action="index.php">
        <input type="text" name="user_id" value="<?= $user_id ?>" hidden />
        <input type="text" name="error_message" value="Wrong id and password" hidden />
        <input type="text" name="is_new_user" value="<?= $is_new_user ?>" hidden />
    </form>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

    $(document).ready(function() {
        $("#back_to_index_form").submit();
    });

</script>
</html>
<?php
        exit(); // Stop php
    }
}

// If everything is all right when checking/creating user account, then find the book records of the user
// 如果使用者順利建立帳號或者是成功登入了, 那就去book_page_table裡找該使用者的最新一本創作的書
$sql = "SELECT * FROM `book_page_table` where input_author=:author";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":author", $user_id, PDO::PARAM_STR);
$status = $stmt->execute();

if ($status === false) {
    //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    exit("SQL_ERROR when checking id and pw: " . $error[2]);
}

$values = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 如果該使用者尚未創作任何一本書就把書的id跟名稱留白
if(count($values)==0){
    $latest_book_id = "";
    $latest_book_name = "";
}

// 如果該使用者有創作過書就去book_table裡面找該書籍的名稱
else {
    $latest_book_id = $values[count($values) - 1]["book_id"];

    $sql = "SELECT * FROM `book_table` where id=:book_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":book_id", $latest_book_id, PDO::PARAM_INT);
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR when checking id and pw: " . $error[2]);
    }

    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $latest_book_name = $values[0]["book_name"];
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
</head>
<body>
    <form id="to_view_form" method="post" action="view.php">
        <input type="text" name="author" value="<?= $user_id ?>" hidden />
        <input type="text" name="book_id" value="<?= $latest_book_id?>" hidden />
        <input type="text" name="book_name" value="<?=$latest_book_name?>" hidden />
    </form>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

    $(document).ready(function() {
        $("#to_view_form").submit();
    });

</script>