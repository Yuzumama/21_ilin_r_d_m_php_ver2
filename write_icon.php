<?php

include 'connect_to_db.php';

// Connect to db
$pdo = connect_to_db();
if ($pdo == null) {
    exit();
}

$book_id = $_POST["book_id"];
$book_name = $_POST["book_name"];
$author = $_POST["author"];

$icon_name = $_POST["icon_name"];
$icon_x = $_POST["icon_x"];
$icon_y = $_POST["icon_y"];
$icon_w = $_POST["icon_width"];
$icon_h = $_POST["icon_height"];
$icon_html = $_POST["icon_html"];

// 首先先檢查 icon name 有沒有被使用過
$sql = "SELECT * FROM `icon_table` WHERE icon_name=:icon_name";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":icon_name", $icon_name, PDO::PARAM_STR);
$status = $stmt->execute();

if ($status === false) {
    //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    exit("SQL_ERROR: " . $error[2]);
}

// 取得db的搜尋結果
$icons_in_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 如果icon name已經被使用過了的話那就秀一個alert視窗
if(count($icons_in_db) > 0) {
    $icon_name_exists = true;
}
else {
    $icon_name_exists = false;

    $sql = "INSERT INTO `icon_table`(`icon_name`, `icon_x`, `icon_y`, `icon_width`, `icon_height`, `icon_html`) VALUES 
                                    (:icon_name , :icon_x , :icon_y , :icon_width , :icon_height , :icon_html)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":icon_name", $icon_name, PDO::PARAM_STR);
    $stmt->bindParam(":icon_x", $icon_x, PDO::PARAM_INT);
    $stmt->bindParam(":icon_y", $icon_y, PDO::PARAM_INT);
    $stmt->bindParam(":icon_width", $icon_h, PDO::PARAM_INT);
    $stmt->bindParam(":icon_height", $icon_w, PDO::PARAM_INT);
    $stmt->bindParam(":icon_html", $icon_html, PDO::PARAM_STR);
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
</head>
<body>
    <form id="back_to_view_form" method="post" action="view.php">
        <input type="text" name="author" value="<?= $author ?>" hidden />
        <input type="text" name="book_id" value="<?= $book_id ?>" hidden />
        <input type="text" name="book_name" value="<?= $book_name ?>" hidden />
    </form>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

<?php
    if($icon_name_exists) {
        echo "alert('The icon name \"" . $icon_name . "\" already exists. Please use another icon name!');";
    }
?>
    $(document).ready(function() {
        $("#back_to_view_form").submit();
    });

</script>
</html>