<?php

include 'connect_to_db.php';

// Connect to db
$pdo = connect_to_db();
if($pdo == null) {
    exit();
}

$book_id = $_POST["book_id"];
$book_name = $_POST["book_name"];
$author = $_POST["author"];
$layout_json = json_decode($_POST["layout_json"], true);
$cover_image_is_chosen = $_POST["cover_image_is_chosen"];

// 如果書本id為空白就代表這是一本新創作的書, 必須先儲存到book_table裡
if ($book_id == "") {
    $sql = "INSERT INTO `book_table`(`book_name`, `author`) VALUES " .
                                   "(:book_name , :author)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':book_name', $book_name, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':author', $author, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }

    $book_id = $pdo->lastInsertId();
}

foreach ($layout_json as $layout) {

    $layout_str = json_encode($layout["layout"]);

    $sql = "UPDATE `book_page_table` SET `page_layout`=:layout WHERE id=:id;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':layout', $layout_str, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':id', $layout["id"], PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();

//    echo "id " . $layout["id"] . "'s layout: " . $layout_str;
//    echo "<br>";

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }
}

// If the cover image is uploaded
if($cover_image_is_chosen == "1") {
    // Get image info from submit form
    $filename = $_FILES["cover_image_file_chooser"]["name"];
    $tempname = $_FILES["cover_image_file_chooser"]["tmp_name"];
    $file_ext = pathinfo($filename, PATHINFO_EXTENSION);

    // Get a number that will not be same as any files in before
    $num_images = 0;
    $image_counts_filename = "./covers/image_counts.txt";

    // Load current number of files from file
    if (file_exists($image_counts_filename)) {
        $json_str = file_get_contents($image_counts_filename);

        $image_counts_json = json_decode($json_str, true);

        $num_images = $image_counts_json["num_images"];
    }

    // Increase the number of images
    $num_images++;



    // New file name of image to be stored on server
    $new_filename = "./covers/" . sprintf("image_%08d", $num_images) . "." . $file_ext;

    // Now let's move the uploaded image into the folder: images
    if(move_uploaded_file($tempname, $new_filename)) {

        // Save the new number of images to file
        $new_image_counts_json = json_encode(array("num_images" => $num_images));
        $file = fopen($image_counts_filename, "w");
        fwrite($file, $new_image_counts_json);
        fclose($file);

    } else {
        echo "Failed to upload image!!";
        exit("");
    }

//    echo $new_filename;
} 
else {
    $new_filename = "";
}

$cover_layout = $_POST["cover_layout_json"];

// 如果封面影像沒有上傳那就只單純更新各個頁面的版面設計
if($new_filename == ""){

    $sql = "UPDATE `book_table` SET`cover_layout`=:cover_layout WHERE id=:book_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":book_id", $book_id, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(":cover_layout", $cover_layout, PDO::PARAM_STR);

    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }
}

// 如果封面影像也有上傳那就同時更新各個頁面的版面設計以及封面影像
else {

    // 更新之前要先取得舊的封面影像檔案名稱
    $sql = "SELECT * FROM `book_table` where id=:id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue("id", $book_id, PDO::PARAM_INT);
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }

    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $old_cover_image_file = $values[0]["cover_filename"];

    // 更新封面影像以及版面設計
    $sql = "UPDATE `book_table` SET `cover_filename`=:cover_filename,`cover_layout`=:cover_layout WHERE id=:book_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":book_id", $book_id, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(":cover_filename", $new_filename, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(":cover_layout", $cover_layout, PDO::PARAM_STR);

    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }

    // 更新成功了的話就把舊的封面檔案刪除
    // 先確認舊的封面檔案還在, 若該檔案還在就刪除
    if(file_exists($old_cover_image_file)) {
        unlink($old_cover_image_file);
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

    $(document).ready(function() {
        $("#back_to_view_form").submit();
    });

</script>
</html>