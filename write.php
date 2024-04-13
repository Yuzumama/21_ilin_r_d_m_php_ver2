

<?php
// Put Client's content from write.html to db

include 'connect_to_db.php';

// Connect to db
$pdo = connect_to_db();
if($pdo == null) {
    exit();
}

// Deal with Client's photo (line 18 - line 38)
// Get a number that will not be same as any files in before
$num_images = 0;
$image_counts_filename = "./images/image_counts.txt";

// Load current number of files from file
if(file_exists($image_counts_filename)) {
    $json_str = file_get_contents($image_counts_filename);

    $image_counts_json = json_decode($json_str, true);

    $num_images = $image_counts_json["num_images"];
}

// Increase the number of images
$num_images++;

// Get image info from submit form
// 先把照片放到Server暫存 Put photo on the server temperately
$filename = $_FILES["image_file_chooser"]["name"];
$tempname = $_FILES["image_file_chooser"]["tmp_name"];
$file_ext = pathinfo($filename, PATHINFO_EXTENSION);



// New file name of image to be stored on server
// sprintf 8=8位數
$new_filename = "./images/" . sprintf("image_%08d", $num_images) . "." . $file_ext;

// Now let's move the uploaded image into the folder: images
// Server上的暫存檔轉換成新的檔名
if(move_uploaded_file($tempname, $new_filename)) {

    // Save the new number of images to file
    // num image +1 存檔
    $new_image_counts_json = json_encode(array("num_images" => $num_images));
    $file = fopen($image_counts_filename, "w");
    fwrite($file, $new_image_counts_json);
    fclose($file);

    // Move back to index.php
    //header("Location: index.php");

    // Deal with Client's text information (line 41 - line 49)
    // Get text info from submit form
    $author = $_POST["author"];
    $book_id = $_POST["book_id"];
    $book_name = $_POST["book_name"];
    $storybook = $_POST["storybook"];
    $child_name = $_POST["child_name"];
    $progress = $_POST["progress"];
    $child_feedback = $_POST["child_feedback"];
    $comments = $_POST["comments"];

    // 如果書本id為空白就代表這是一本新創作的書, 必須先儲存到book_table裡
    if($book_id == ""){
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

    // For bug check
/*    echo "image: " . $new_filename . "<br>";
    echo "book_id: " . $book_id . "<br>";
    echo "author: " . $author . "<br>";
    echo "group: " . $book_name . "<br>";
    echo "storybook: " . $storybook ."<br>";
    echo "child_name: " . $child_name . "<br>";
    echo "progress: " . $progress . "<br>";
    echo "child_feedback: " . $child_feedback . "<br>";
    echo "comments: " . $comments . "<br>";*/

    
    // 把Text和image的資料存到SQL/DB
    //    $sql = "INSERT INTO `book_page_table`(`book_group`, `image_filename`, `input_author`, `input_date`, `input_comment`, 'storybook_name', 'child_name', 'progress', 'child_feedback') VALUES (:book_group,:image_filename,:input_author,sysdate(),:input_comment, :storybook_name, :child_name, :progress, :child_feedback);";
    $sql = "INSERT INTO `book_page_table`(`book_id`, `image_filename`, `input_author`, `input_date`, `input_comment`, `storybook_name`, `child_name`, `progress`, `child_feedback`) VALUES " .
                                        "(:book_id , :image_filename , :input_author , sysdate()   , :input_comment , :storybook_name , :child_name , :progress , :child_feedback)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':book_id', $book_id, PDO::PARAM_INT);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':image_filename', $new_filename, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':input_author', $author, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':input_comment', $comments, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':storybook_name', $storybook, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':child_name', $child_name, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':progress', $progress, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $stmt->bindValue(':child_feedback', $child_feedback, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }
}
else {
    echo "Failed to upload image!!";
    exit("");
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
</head>
<!-- Server的Write.php → Client的Write.html → Server的view.php -->
<body>
    <form id="back_to_view_form" method="post" action="view.php">
        <input type="text" name="author" value="<?=$author?>" hidden />
        <input type="text" name="book_id" value="<?=$book_id?>" hidden />
        <input type="text" name="book_name" value="<?=$book_name?>" hidden />
    </form>
</body>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>

    $(document).ready(function() {
        $("#back_to_view_form").submit();
    });

</script>
</html>