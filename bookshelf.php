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

// 搜尋所有該使用者所製作的書
$sql = "SELECT * FROM `book_table` WHERE author=:author";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(":author", $author, PDO::PARAM_STR);
$status = $stmt->execute();

if ($status === false) {
    //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
    $error = $stmt->errorInfo();
    exit("SQL_ERROR: " . $error[2]);
}

// 成功從DB取得資料的話，從資料庫裡面讀到$books的變數裡
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "svg_icons.php";

$icons_in_db = getSvgIcons($pdo);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Storybook Memory Maker</title>
    <link rel="stylesheet" href="./css/bookshelf_style.css">
    <link rel="stylesheet" href="./css/view_and_bookshelf.css">
</head>
<body>
<!-- 一開始網頁還沒讀取完之前看起來版面會很亂所以先把畫面遮起來等到一切整理好之後再秀出來 -->
<div id="blank_curtain" class="blank_curtain_style"></div>
<!-- 左邊的選單 -->
<div class="left_bar_back_bottom"></div>
<div class="left_bar_back_top"></div>
    <div class="left_bar_back_style">
        <button id="buy_this_book" class="edit_btn_style"> About Us</button>    
        <button id="see_bookshelf" class="edit_btn_style"> Bookshelf </button>
        <button id="news_btn" class="edit_btn_style"> News </button>
        <button id="contact_btn" class="edit_btn_style"> Contact </button>
        <button id="log_out_btn" class="edit_btn_style"> Log Out </button>
        
    </div>
    <div class="view_item_empty_back_style">
        <svg xmlns="http://www.w3.org/2000/svg">
            <?php
            printSvgIconDefInHtml($icons_in_db);
            ?>
        </svg>
    </div>
    <!-- 右半邊選擇書本的畫面 -->
    <div class="view_main_back_style">
        <div class="bookshelf_book_list_style">
            <div class="bookshelf_prev_book_style">
                <div class="prev_book_btn_style" onclick="javascript: movePrev('my_book');">◀</div>
            </div>
            <div class="bookshelf_all_books_style">
                <?php
                $i = 0;
                foreach ($books as $book) {
                ?>

                <div id="my_book_<?=$i?>" class="one_book_cover_style">
                    <div id="book_<?=$i?>" onclick="javascript: viewBook(<?= $book["id"] ?>, '<?= $book["book_name"] ?>');" class="view_right_page_back_style">
                        <!-- 用if語法確認封面照片有沒有被設定過 -->
                        <div id="cover_page_<?=$i?>" class="first_page_all_style">
                        </div>
                    </div>
                </div>

                <?php
                $i++;
                }
                ?>

                <div id="my_book_new" class="new_book_cover_style" onclick="javascript: viewBook('', '');">
                    <div id="new_book" class="view_new_right_page_back_style">
                        <div class="new_book_icon_back_style">
                            <div class="new_book_icon_circle_style"></div>
                            <span class="new_book_icon_horizontal_line_style"></span>
                            <span class="new_book_icon_verticle_line_style"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bookshelf_next_book_style">
                <div class="next_book_btn_style" onclick="moveNext('my_book');">▶</div>
            </div>
        </div>
        
        <!-- 移動去書本畫面 -->
        <form id="move_to_view_form" method="post" action="view.php">
            <input type="text" id="view_book_id" name="book_id" value="<?= $book_id ?>" hidden />
            <input type="text" id="view_book_name" name="book_name" value="<?= $book_name ?>" hidden />
            <input type="text" id="view_author" name="author" value="<?=$author?>" hidden />
        </form>
    </div>
</body>

<!-- JS Library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script> 
<script src="./js/set_item_draggable.js"></script>


<script>

    function viewBook(book_id, book_name) {
        $("#view_book_id").val(book_id);
        $("#view_book_name").val(book_name);
        $("#move_to_view_form").submit();
    }

    let num_icons_on_page = Array(<?= (count($books)) ?>);
    function setLayoutEdit(edit_enabled) {
        <?php
        $i = 0;
        foreach ($books as $book) {
        ?>
        var page = <?= $i ?>;
        setEditOneItemWithPage("cover_title", page, edit_enabled);
        setEditOneItemWithPage("cover_image", page, edit_enabled);

        // 封面上所有icon
        for (let icon_id = 0; icon_id < num_icons_on_page[page]; ++icon_id) {
            setEditOneItemWithPageAndIndex("icon", page, icon_id, edit_enabled);
        }
        <?php
        $i++;
        }
        ?>
    }

    $(function () {
        <?php
        
        $i = 0;
        foreach ($books as $book) {

            // 取得封面照片檔名
            $first_page_image = null;
            if(isset($book["cover_filename"])) {
                $first_page_image = $book["cover_filename"];
            }

            // 空的封面格式json
            $cover_title_layout = "{}";

            // 取得封面格式json
            // 書名的放大、縮小、位置移動(編輯中)
            if (isset($book["cover_layout"])) {
                $temp_cover_layout = json_decode($book["cover_layout"], true);
                if(isset($temp_cover_layout["first_page_layout"])) {
                    $cover_title_layout = $temp_cover_layout["first_page_layout"];
                }
            }

            // 如果封面照片存在才會追加img物件
            if ($first_page_image) {
            ?>
            // 用javascript去生成封面的照片的html語法
            addOneDraggableItemWithPage(
                "cover_page_<?=$i?>",
                '<?= json_encode($cover_title_layout) ?>',
                "cover_image",
                <?=$i?>,
                '<img id="record_cover_image" src="<?= $first_page_image ?>" class="view_record_cover_image_style" />'
            );
            <?php
            } 

            // 生成封面的書名
            $cover_title_html_content = 
                "<div class='first_page_title_style'>" . 
                $book["book_name"] .
                "</div>";
            ?>
            // 用javascript去生成封面的書名的html語法
            addOneDraggableItemWithPage(
                "cover_page_<?=$i?>",
                '<?= json_encode($cover_title_layout) ?>',
                "cover_title",
                <?=$i?>,
                "<?=$cover_title_html_content?>"
            );

            // 生成封面的icon
            <?php
            if (isset($cover_title_layout["icons"])) {

                $icon_id = 0;
                foreach ($cover_title_layout["icons"]["icon_layout"] as $icon_layout) {
                    $icon_layout_str = json_encode($icon_layout);
                    $icon_name = $icon_layout["icon_name"];
                ?>
                // 用javascript去生成封面的icon
                var svgContent = createSvgContent("use_icon_<?=$i?>_<?= $icon_id ?>", "<?= $icon_name ?>");
                addOneDraggableItemWithPageAndIndex("book_<?=$i?>", '<?= $icon_layout_str ?>', "icon", <?=$i?>, <?= $icon_id ?>, svgContent, true);

                <?php
                    ++$icon_id;
                }
                ?>
                num_icons_on_page[<?=$i?>] = <?= $cover_title_layout["icons"]["num_icons"] ?>;
            <?php
            } else {
                ?>
            num_icons_on_page[<?= $i ?>] = 0;
            <?php
            }
            ?>

        <?php
            $i++;
        }
        ?>
    });

    // 停止變更版面的功能
    $(document).ready(function () {
        setLayoutEdit(false);
        updateBookButtons("my_book");

        // 等到一切都就緒了再來把白色簾幕掀開
        $("#blank_curtain").css({ opacity: 0.0 });
        setTimeout(() => {
            $("#blank_curtain").attr("class", "empty");
        }, 2000);
    });

    // 從鋼琴作業裡copy過來的
    function updateBookButtons(book_prefix) {
        $("#" + book_prefix + "_new").css({
            left: (25 + (bookListLength[book_prefix] - currBookIndex) * 235) + "px",
        });
        for (let i = 0; ; ++i) {
            let book_id = book_prefix + "_" + i;
            if ($("#" + book_id).length) {
                let book_btn = $("#" + book_id);
                book_btn.css({
                    left: (25 + (i - currBookIndex) * 235) + "px",
                });
            }
            else {
                break;
            }
        }
    }

    let currBookIndex = 0;
    function movePrev(book_prefix) {
        if (currBookIndex > 0) {
            currBookIndex--;
            updateBookButtons(book_prefix);
        }
    }

    let bookListLength = [];
    bookListLength['my_book'] = <?=count($books)?>;
    function moveNext(book_prefix) {
        
        if (currBookIndex < bookListLength[book_prefix] - 2) {
            currBookIndex++;
            updateBookButtons(book_prefix);
        }
    }

    // 生成指定的svg icon html語法
    function createSvgContent(svg_item_id, svg_ref_id) {
        return '<svg class="svg_icon_style" viewBox="' + $("#" + svg_ref_id).attr("bounding-box") + '">' +
               '<use id="' + svg_item_id + '" xlink:href="#' + svg_ref_id + '" ref_target="' + svg_ref_id + '" x="0" y="0"></use>' +
               '</svg>';
    }

</script>

</html>