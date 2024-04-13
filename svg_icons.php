<?php
    
function getSvgIcons($pdo) {
    // 取得所有db裡有的icon
    $icons_in_db = [];
    $sql = "SELECT * FROM `icon_table` WHERE 1";
    $stmt = $pdo->prepare($sql);
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        echo ("SQL_ERROR: " . $error[2]);
    }

    // Icon資料取得成功的話，從資料庫裡面讀到$icons_in_db的變數裡
    $icons_in_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $icons_in_db;
}

function printSvgIconDefInHtml($icons_in_db) {
    echo "<defs>\n";
    
    // 把db裡的icon都拿出來
    foreach ($icons_in_db as $one_icon) {

        echo '<g id="' . $one_icon["icon_name"] . '" bounding-box="' . $one_icon["icon_x"] . ' ' . $one_icon["icon_x"] . ' ' . $one_icon["icon_width"] . ' ' . $one_icon["icon_height"] . '">';
        echo $one_icon["icon_html"];
        echo "</g>\n";
    }

    echo '</defs>';
}

?>