<!-- Database Imformation Input-->
<?php

include 'connect_to_db.php';

// Connect to db
$pdo = connect_to_db();
if($pdo == null) {
    exit();
}


// Set the name of book, edit the book, and store the content to write.php
// user_info.php 會查詢好該使用者所創作的書籍id及書籍名稱
if (isset($_POST["book_id"])) {

    $book_id = $_POST["book_id"];
    $book_name = $_POST["book_name"];
}
else {
    $book_id="";
    $book_name="";
}

$author = $_POST["author"];

// 只要使用者之前有登錄過書名的話，語法如下：
// book_group =: xxxx (自行喜好填寫)
if ($book_id != "") {
    $sql = "SELECT * FROM `book_page_table` WHERE book_id=:target_book_id;";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':target_book_id', $book_id, PDO::PARAM_STR);  //Integer（数値の場合 PDO::PARAM_INT)
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }

    //之前登錄過的資料(表單的)都會從資料庫裡面讀到$Values的變數裡面 
    $values = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 取得這本書的封面資料
    $sql = "SELECT * FROM `book_table` WHERE id=:book_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":book_id", $book_id, PDO::PARAM_STR);
    $status = $stmt->execute();

    if ($status === false) {
        //SQL実行時にエラーがある場合（エラーオブジェクト取得して表示）
        $error = $stmt->errorInfo();
        exit("SQL_ERROR: " . $error[2]);
    }
    // 封面資料取得成功的話，從資料庫裡面讀到$cover_values的變數裡
    $cover_values = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 取得封面的版面設計json
    $cover_layout = json_decode($cover_values[0]["cover_layout"], true);
}
else {
    // 書籍id是空白的話，Values=Page的資料會顯示空白
    $values = [];
    $cover_values = null;
    $cover_layout = null;
}

include "svg_icons.php";

$icons_in_db = getSvgIcons($pdo);

// HTML
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title></title>
    <link rel="stylesheet" href="./css/view_style.css">
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
    <!-- 右半邊的書本編輯 -->
    <div class="view_main_back_style">
        <div id="book" class="view_two_pages_back_style">
            <div id="page_0" class="view_right_page_back_style">
                
                <div id="cover_page" class="first_page_all_style">
                    <div id="page_bottom_bar_0" class="page_bottom_bar">
                        <button onclick="javascript: showIconMenu(0);" class="page_btn_style page_layout_btn_style">Add Icon</button>
                        <button onclick="javascript: resetOnePageLayout(0);" class="page_btn_style page_layout_rightmost_btn_style">Reset</button>
                    </div>
                </div>
            </div>

            <!-- 用for迴圈跑每一頁的資料設定 -->
            <?php
            // i = 頁數
            $i = 1;
            foreach ($values as $value) {
            ?>
            <!-- 每一頁的資料庫都有設定ID, 可以根據ID更新 -->
            <!-- 不想給PHP處理的部分 -->
            <div id="page_<?=$i?>" class="<?php
            // 單數頁設在左邊，偶數頁的右邊，利用php的$i去找
                                            if(($i % 2) == 1)
                                                echo "view_left_page_back_style";
                                            else 
                                                echo "view_right_page_back_style";
                                            ?>">
                <div id="record_id_<?=$i?>" hidden><?= $value['id'] ?></div>
                <div id="page_bottom_bar_<?=$i?>" class="page_bottom_bar">
                    <button onclick="javascript: showIconMenu(<?=$i?>);" class="page_btn_style page_layout_btn_style">Add Icon</button>
                    <button onclick="javascript: resetOnePageLayout(<?= $i ?>);" class="page_btn_style page_layout_btn_style">Reset</button>
                    <button onclick="javascript: editText(<?= $i ?>, true);" class="page_btn_style page_layout_btn_style">Edit Page</button>
                    <button onclick="javascript: deleteOnePage(<?=$value["id"]?>)" class="page_btn_style page_layout_rightmost_btn_style">Del Page</button>
                </div>
                <div id="update_form_back_<?=$i?>" class="empty">
                    <!--表單的語法-->
                    <form id="upload_form_<?= $i ?>" action="update_page_content.php" method="post" enctype="multipart/form-data">
                    
                        <div id="image_preview_<?= $i ?>" class="image_preview_style">
                            <img id="record_image_content_<?=$i?>" src="<?=$value["image_filename"]?>" class="view_record_image_style" />
                        </div>
                        <div class="form_one_item_back_style">
                            <input type="file" id="image_file_chooser_<?= $i ?>" name="image_file_chooser" onchange="changeImage(<?=$i?>);" hidden />
                            <input type="text" id="image_file_updated_<?= $i ?>" name="image_file_updated" value="0" hidden />
                        </div>
                        <!-- 隱藏 -->
                        <div class="empty">
                            <input type="text" name="page_id" hidden value="<?=$value["id"]?>" />
                            <div class="form_label_style">
                                Book: 
                            </div>
                            <input id="book_name_<?= $i ?>" type="text" name="book_name" class="form_text_style" value="<?= $book_name ?>" />
                            <input id="book_id_<?= $i ?>" type="text" name="book_id" value="<?= $book_id ?>" />
                        </div>
                        <div class="form_one_item_back_style">
                            <div class="form_label_style">
                                Author: 
                            </div>
                            <input type="text" name="author" class="form_text_style" value="<?= $author ?>" />
                        </div>
                        <!-- 顯示 -->
                        <div class="form_one_item_back_style">
                            <div class="form_label_style">
                                Storybook:
                            </div>
                            <input type="text" name="storybook" class="form_text_style" value="<?=$value["storybook_name"]?>" />
                        </div>
                        <div class="form_one_item_back_style">
                            <div class="form_label_style">
                                Child's name:
                            </div>
                            <input type="text" name="child_name" class="form_text_style" value="<?= $value["child_name"] ?>"/>
                        </div>
                        <div class="form_one_item_back_style">
                            <div class="form_label_style">
                                Progress:
                            </div>
                            <input type="text" name="progress" class="form_text_style" value="<?= $value["progress"] ?>" />
                        </div>
                        <div class="form_one_item_back_style">
                            <div class="form_label_style">
                                Child feedback:
                            </div>
                            <input type="text" name="child_feedback" class="form_text_style" value="<?= $value["child_feedback"] ?>" />
                        </div>
                        <div class="form_one_item_back_style">
                            <div class="form_label_style">
                                Comment: 
                            </div>
                            <textarea name="comments" rows="5" cols="40" class="input_comments_style"><?= $value["input_comment"]  ?></textarea>
                        </div>
                    </form>
                    <div class="form_button_back_style">
                        <button class="page_btn_style select_image_btn_style" onclick="selectImage(<?=$i?>);"> Select Image </button>
                        <button class="page_btn_style send_btn_style" onclick="sendForm(<?=$i?>);"> Update </button>
                        <button class="page_btn_style cancel_btn_style" onclick="editText(<?=$i?>, false)"> Cancel </button>
                    </div>
                </div>
            </div>
            
            <?php
            // 每處理完1頁，頁數+1
                $i++;
            }
            // 因為中間有穿插不想被PHP處理的HTML語法，所以用好幾個PHP引號
            ?>

            <!-- 最後一頁的輸入表單 -->
            <!-- 表單有可能在頁數的左右兩邊，所以要再寫一次if語法 -->
            <div id="page_<?=$i?>" class="<?php
                                          if(($i % 2) == 1)
                                                echo "view_left_page_back_style";
                                            else 
                                                echo "view_right_page_back_style";
                                          ?>">
                <!--表單的語法-->
                <form id="upload_form" action="write.php" method="post" enctype="multipart/form-data">
                    
                    <div id="image_preview" class="image_preview_style"></div>
                    <div class="form_one_item_back_style">
                        <input type="file" id="image_file_chooser" name="image_file_chooser" onchange="changeImage();" hidden>
                        <input type="text" id="image_file_updated" name="image_file_updated" value="0" hidden />
                    </div>
                    <!-- 隱藏 -->
                    <div class="empty">
                        <div class="form_label_style">
                            Book: 
                        </div>
                        <input id="book_name" type="text" name="book_name" class="form_text_style" value="<?=$book_name?>" />
                        <input id="book_id" type="text" name="book_id" value="<?=$book_id?>" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Author: 
                        </div>
                        <input type="text" name="author" class="form_text_style" value="<?=$author?>" />
                    </div>
                    <!-- 顯示 -->
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Storybook:
                        </div>
                        <input type="text" name="storybook" class="form_text_style" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Child's name:
                        </div>
                        <input type="text" name="child_name" class="form_text_style" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Progress:
                        </div>
                        <input type="text" name="progress" class="form_text_style" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Child feedback:
                        </div>
                        <input type="text" name="child_feedback" class="form_text_style" />
                    </div>
                    <div class="form_one_item_back_style">
                        <div class="form_label_style">
                            Comment: 
                        </div>
                        <textarea name="comments" rows="5" cols="40" class="input_comments_style"></textarea>
                    </div>
                </form>
                <div class="form_button_back_style">
                    <button id="select_image" class="page_btn_style select_image_btn_style" onclick="selectImage();"> Select Image </button>
                    <button id="send_btn" class="page_btn_style send_btn_style" onclick="sendForm();"> Send </button>
                </div>
            </div>

            <!-- 為了不讓表單放到最後一頁, 插入空白頁，讓它變成一本書可以合起來 -->
            <?php
            if(($i % 2) == 1){
            ?>
            <div id="page_<?=($i+1)?>" class="view_right_page_back_style">
            </div>
            <?php
                $i++;
            }
            ?>

            <!-- 封底 -->
            <div id="page_<?=($i+1)?>" class="view_left_page_back_style">
                <div class="first_page_all_style">
                    <div class="first_page_title_style">
                    </div>
                </div>
            </div>
        </div>

        <!-- 選擇icon的表單 -->
        <div id="icon_list" target_page="" class="empty">
            <div class="icon_list_prev_icon_style">
                <div class="prev_icon_btn_style" onclick="movePrev('sample_icons');">◀</div>
            </div>
            <div class="icon_list_all_icons_style">
                <?php
                // 把db裡的icon都秀出來
                $i = 0;
                foreach ($icons_in_db as $one_icon) {
                ?>
                <div id="sample_icons_<?=$i?>" class="svg_sample_back_style" onclick="addIconToPage(current_icon_page, '<?=$one_icon["icon_name"]?>');">
                    <svg class="svg_icon_style" viewBox="<?= $one_icon["icon_x"] ?> <?= $one_icon["icon_y"] ?> <?= $one_icon["icon_width"] ?> <?=$one_icon["icon_height"]?>">
                        <use xlink:href="#<?=$one_icon["icon_name"]?>" ref_target="<?= $one_icon["icon_name"] ?>" x="0" y="0"></use>
                    </svg>
                </div>
                <?php
                $i++;
                }
                ?>
                <div id="sample_icons_new" class="svg_sample_new_button_back_style">
                    <div class="svg_sample_new_button_circle_style"></div>
                    <span class="new_svg_sample_horizontal_line_style"></span>
                    <span class="new_svg_sample_verticle_line_style"></span>
                </div>
            </div>
            <div class="icon_list_next_icon_style">
                <div class="next_icon_btn_style" onclick="moveNext('sample_icons');">▶</div>
            </div>
        </div>

        <!-- 新增icon的表單 -->
        <div id="create_icon_form_back" class="empty">
            <div class="create_icon_form_back_style">
                <form id="create_icon_form" method="post" action="write_icon.php">
                    <input type="text" id="create_icon_book_id" name="book_id" value="<?= $book_id ?>" hidden />
                    <input type="text" id="create_icon_book_name" name="book_name" value="<?= $book_name ?>" hidden />
                    <input type="text" id="create_icon_author" name="author" value="<?= $author ?>" hidden />
                    <div class="create_icon_form_one_row_style">
                        <div class="create_icon_form_title_style">
                            Create your own icon!
                        </div>
                    </div>
                    <div class="create_icon_form_one_row_style">
                        <div class="create_icon_form_label_style">
                            Nname
                        </div>
                        <input id="create_icon_name" type="text" name="icon_name" class="create_icon_form_text_style" />
                    </div>
                    <div class="create_icon_form_one_row_style">
                        <div class="create_icon_form_label_style">
                            Viewbox
                        </div>
                        <input id="create_icon_x" type="text" name="icon_x" class="create_icon_form_viewbox_style" value="0" />
                        <input id="create_icon_y" type="text" name="icon_y" class="create_icon_form_viewbox_style" value="0"/>
                        <input id="create_icon_width" type="text" name="icon_width" class="create_icon_form_viewbox_style" value="1024"/>
                        <input id="create_icon_height" type="text" name="icon_height" class="create_icon_form_viewbox_style" value="1024" />
                    </div>
                    <div class="create_icon_form_one_row_style">
                        <div class="create_icon_form_label_style">
                            Svg code
                        </div>
                        <textarea id="create_icon_html" name="icon_html" class="create_icon_form_textarea_style"></textarea>
                    </div>
                </form>
                <div class="create_icon_btn_back_style">
                    <button id="create_icon_submit_btn" class="create_icon_btn_style" style="margin-right: 20px;">Create</button>
                    <button id="create_icon_cancel_btn" class="create_icon_btn_style">Cancel</button>
                </div>
            </div>
            <div id="create_icon_preview" class="create_icon_preview_back_style">
                
            </div>
        </div>
        
        <!-- 編輯書籍的按鈕 -->
        <div class="book_buttons_back_style">
        <button id="edit_layout" class="book_btn_style"> Edit Pages </button>
        <button id="change_cover" class="book_btn_style"> Change Cover </button>
        <button id="save_layout" class="book_btn_style"> Save Design </button>        
        </div>
        
        <!-- 上傳每一頁和封面封底影像的表單到Write_layout_PHP -->
        <form id="layout_form" method="post" action="write_layout.php"  enctype="multipart/form-data">
            <input type="text" id="cover_image_is_chosen" name="cover_image_is_chosen" value="0" hidden>
            <input type="file" id="cover_image_file_chooser" name="cover_image_file_chooser" hidden>
            <input type="text" id="layout_book_id" name="book_id" value="<?=$book_id?>" hidden />
            <input type="text" id="layout_book_name" name="book_name" value="<?=$book_name?>" hidden />
            <input type="text" id="layout_author" name="author" value="<?=$author?>" hidden />
            <input id="layout_json" type="text" name="layout_json" hidden/>
            <input id="cover_layout_json" type="text" name="cover_layout_json" hidden/>
        </form>

        <!-- 刪除某一頁 -->
        <form id="delete_page_form" method="post" action="delete_page.php">
            <input type="text" id="delete_page_id" name="page_id" hidden />
            <input type="text" id="delete_book_id" name="book_id" value="<?= $book_id ?>" hidden />
            <input type="text" id="delete_book_name" name="book_name" value="<?= $book_name ?>" hidden />
            <input type="text" id="delete_author" name="author" value="<?=$author?>" hidden />

        </form>

        <!-- 移動到書架 -->
        <form id="move_to_bookshelf_form" method="post" action="bookshelf.php">
            <input type="text" id="bookshelf_book_id" name="book_id" value="<?= $book_id ?>" hidden />
            <input type="text" id="bookshelf_book_name" name="book_name" value="<?= $book_name ?>" hidden />
            <input type="text" id="bookshelf_author" name="author" value="<?=$author?>" hidden />
        </form>
    </div>    
</body>

<!-- JS Library -->
<script src='//cdnjs.cloudflare.com/ajax/libs/gsap/1.18.0/TweenMax.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/gsap/1.18.2/utils/Draggable.min.js'></script>
<script src='//s3-us-west-2.amazonaws.com/s.cdpn.io/16327/MorphSVGPlugin.min.js?r=185'></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script> 
<script src="./js/turn.js"></script>
<script src="./js/set_item_draggable.js"></script>

<script>
    // Chat App的照片上傳Code再利用
    let selected_image = new Image();
    let selected_image_file = new File([""], "");

    function changeImage(page) {

        // 如果page為空白就代表是新的一頁
        if (!page) {
            item_name_postfix = "";
        }
        // 如果page非空白就代表是要更新已經存在的某一頁
        else {
            item_name_postfix = "_" + page;
        }

        $("#image_file_updated" + item_name_postfix).val("1");

        // The image is stored in memory
        selected_image_file = $("#image_file_chooser" + item_name_postfix)[0].files[0];

        let reader = new FileReader();
        reader.onloadend = function () {

            selected_image.src = reader.result;
            selected_image.onload = function () {
                $("#record_image_content" + item_name_postfix).remove();
                $("#image_preview" + item_name_postfix).css("background-image", "url('" + reader.result + "')");
            }
        };
        reader.readAsDataURL(selected_image_file);
    }

    function selectImage(page) {
        // 如果page為空白就代表是新的一頁
        if (!page) {
            item_name_postfix = "";
        }
        // 如果page非空白就代表是要更新已經存在的某一頁
        else {
            item_name_postfix = "_" + page;
        }
        $("#image_file_chooser" + item_name_postfix).click();
    }

    function sendForm(page) {
        // 如果page為空白就代表是新的一頁
        if (!page) {
            item_name_postfix = "";
        }
        // 如果page非空白就代表是要更新已經存在的某一頁
        else {
            item_name_postfix = "_" + page;
        }

        // Submit之前會確認Title是否空白
        if ($("#book_name" + item_name_postfix).val() == "") {
            alert("Must input the book's name on cover page!");
            return;
        }
        // Submit之前會確認author是否空白
        if ($("#author" + item_name_postfix).val() == "") {
            alert("Must input author!");
            return;
        }
        // 如果是新的一頁, submit之前檢查有沒有選好影像檔案
        if (!page) {
            if (selected_image_file.name == "") {
                alert("Must select an image!");
                return;
            }
        }
        
        // 沒問題的話就允許submit
        $("#upload_form" + item_name_postfix).submit();
    };

    // author的名稱有更改的話，表單上面的內容也會跟著變更
    $("#author").on("change", function () {
        $("#book_author").val($("#author").val());
    });

    // 登出帳號, 回到登入畫面的index.php
    $("#log_out_btn").on("click", function () {
        if (confirm("Are you sure to log-out?")) {
            window.location.href = "index.php";
        }
    });

    // 各頁面上的icon的數量
    let num_icons_on_page = Array(<?=(count($values) + 1)?>);
    <?php
    // 設定封面上的icon數量
    $num_icons = 0;
    ?>
    num_icons_on_page[0] = <?= $num_icons ?>;
    <?php
    $i = 1;
    foreach ($values as $value) {
        $num_icons = 0;
    ?>
    num_icons_on_page[<?=$i?>] = <?= $num_icons ?>;
    <?php
    $i++;
    }
    ?>

    // 用javascript去生成每一頁的照片、日期、繪本名字等的html語法
    $(function () {  
        <?php

        // 取得封面照片檔名
        $first_page_image = null;
        if ($cover_values) {
            if (count($cover_values) > 0) {
                $first_page_image = $cover_values[0]["cover_filename"];
            }
        }

        // 取得封面格式json
        // 書名的放大、縮小、位置移動(編輯中)
        if ($cover_layout) {
            $cover_title_layout = $cover_layout["first_page_layout"];
        } else {
            $cover_title_layout = "{}";
        }

        ?>
        // 用javascript去生成封面的照片的html語法
        addOneDraggableItemWithoutPage(
            "cover_page",
            '<?=json_encode($cover_title_layout)?>',
            "cover_image",
            '<img id="cover_image_item" src="<?=$first_page_image?>" class="view_record_cover_image_style" />'
        );
        <?php

        // 如果使用者沒有上傳過封面照片那就把封面照片的物件隱藏起來
        if(!$first_page_image) {
        ?>
        $("#record_cover_image").attr("class", "empty");
        <?php
        }

        // 生成封面的書名
        $cover_title_html_content = "<div class='first_page_title_style'>";
        if ($book_name == "") {
            $cover_title_html_content = $cover_title_html_content .
                "<input id='input_book_title' type='text' class='input_book_title_style' />";
        } else {
            // 如果書名存在就直接秀出書名
            $cover_title_html_content = $cover_title_html_content .
                $book_name;
        }
        $cover_title_html_content = $cover_title_html_content .
            "</div>";
        ?>
        // 用javascript去生成封面的書名的html語法
        addOneDraggableItemWithoutPage(
            "cover_page",
            '<?= json_encode($cover_title_layout) ?>',
            "cover_title",
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
        var svgContent = createSvgContent("use_icon_0_<?= $icon_id ?>", "<?=$icon_name?>");
        addOneDraggableItemWithPageAndIndex("page_0", '<?= $icon_layout_str ?>', "icon", 0, <?= $icon_id ?>, svgContent);

        <?php
                ++$icon_id;
            }
        ?>
        num_icons_on_page[0] = <?=$cover_title_layout["icons"]["num_icons"]?>;
        <?php
        }
        else {
        ?>
        num_icons_on_page[0] = 0;
        <?php
        }
        ?>

        // 用javascript去啟動封面的照片跟書名等的html語法
        setDraggableWithoutPage('cover_image');
        setDraggableWithoutPage('cover_title');

        // 用javascript去啟動封面的icon的html語法
        for (icon_id = 0; icon_id < num_icons_on_page[0]; ++icon_id) {
            setDraggableWithPageAndIndex("icon", 0, icon_id);
        }
        <?php
        $i = 1;
        foreach ($values as $value) {
            // $page_layout讀目前頁面的layout
            if (isset($value["page_layout"])) {
                $page_layout_str = $value["page_layout"];
            }
            else {
                $page_layout_str = "{}";
            }

            if ($value["input_comment"]) {
                $comment_content = $value["input_comment"];
                $comment_content_array = str_split($comment_content, 30);


                $comment_str = "<div class='view_record_comment_multiple_lines_style'>";
                foreach ($comment_content_array as $curr_comment_content) {
                    $comment_str = $comment_str . "<div class='view_record_comment_one_line_style'>";
                    $comment_str = $comment_str . $curr_comment_content;
                    $comment_str = $comment_str . "</div>";
                }
                $comment_str = $comment_str .
                    "</div>";
            }
            else {
                $comment_str = null;
            }
            ?>

            // 生成第i頁上的元件
            addOneDraggableItemWithPage("page_<?=$i?>", '<?=$page_layout_str?>', "image",          <?=$i?>, '<img id="record_image_content_' + (<?=$i?>) + '" src="<?=$value["image_filename"]?>" class="view_record_image_style" />');
            addOneDraggableItemWithPage("page_<?=$i?>", '<?=$page_layout_str?>', "datetime",       <?=$i?>, '<?= DateTime::createFromFormat('Y-m-d H:i:s', $value["input_date"])->format('Y/m/d H:i') ?>');
            addOneDraggableItemWithPage("page_<?=$i?>", '<?=$page_layout_str?>', "author",         <?=$i?>, '<?= $value["input_author"] ?>');
            addOneDraggableItemWithPage("page_<?=$i?>", '<?=$page_layout_str?>', "storybook",      <?=$i?>, '<?= $value["storybook_name"] ?>');
            addOneDraggableItemWithPage("page_<?=$i?>", '<?=$page_layout_str?>', "child_name",     <?=$i?>, '<?= $value["child_name"] ?>');
            addOneDraggableItemWithPage("page_<?=$i?>", '<?=$page_layout_str?>', "progress",       <?=$i?>, '<?= $value["progress"] ?>');
            addOneDraggableItemWithPage("page_<?=$i?>", '<?=$page_layout_str?>', "child_feedback", <?=$i?>, '<?= $value["child_feedback"] ?>');
            <?php
            // 用if語法去設定Comment的生成，如果沒有填寫就不會生成
            if($comment_str){
            ?>
            addOneDraggableItemWithPage("page_<?=$i?>", '<?=$page_layout_str?>', "comment",        <?=$i?>, "<?= $comment_str ?>");
            <?php
            }
            ?>

            // 把文字設在最上層
            $("#record_datetime_<?=$i?>").css({ "z-index": 2 });
            $("#record_author_<?=$i?>").css({ "z-index": 2 });
            $("#record_storybook_<?=$i?>").css({ "z-index": 2 });
            $("#record_child_name_<?=$i?>").css({ "z-index": 2 });
            $("#record_progress_<?=$i?>").css({ "z-index": 2 });
            $("#record_child_feedback_<?=$i?>").css({ "z-index": 2 });
            $("#record_comment_<?=$i?>").css({ "z-index": 2 });

            // 生成第i頁上的icon
            <?php
            $page_layout = json_decode($page_layout_str, true);
            if (isset($page_layout["icons"])) {                
                $icon_id = 0;
                $icon_layout = $page_layout["icons"]["icon_layout"];
                foreach ($icon_layout as $one_icon) {
                    $icon_item_id = "icon_" . $i . "_" . $icon_id;
                    $icon_layout_str = json_encode($icon_layout[$icon_item_id]);
                    $icon_name = $one_icon["icon_name"];
                    ?>

                var svgContent = createSvgContent("use_icon_<?= $i ?>_<?= $icon_id ?>", "<?=$icon_name?>");
                addOneDraggableItemWithPageAndIndex("page_<?= $i ?>", '<?= $icon_layout_str ?>', "icon", <?= $i ?>, <?= $icon_id ?>, svgContent, true);

                <?php
                ++$icon_id;
                }
                ?>
                num_icons_on_page[<?= $i ?>] = <?= $page_layout["icons"]["num_icons"] ?>;
        <?php
            }
            else {
        ?>
                num_icons_on_page[<?= $i ?>] = 0;
        <?php
            }
            $i++;
        }
        ?>

        for (page = 1; page <= <?= count($values) ?> ; ++page) {
            // 將第i頁的元件追加拖拉的功能
            setDraggableWithPage("image", page);
            setDraggableWithPage("datetime", page);
            setDraggableWithPage("author", page);
            setDraggableWithPage("storybook", page);
            setDraggableWithPage("child_name", page);
            setDraggableWithPage("progress", page);
            setDraggableWithPage("child_feedback", page);
            setDraggableWithPage("comment", page);

            // 將每一頁的icon追加拖拉的功能
            for (icon_id = 0; icon_id < num_icons_on_page[page]; ++icon_id) {
                setDraggableWithPageAndIndex("icon", page, icon_id);
            }
        }

        // 封面輸入書名時一併跟表單連動
        $("#input_book_title").on("change", function () {
            $("#book_name").val($("#input_book_title").val());
            $("#layout_book_name").val($("#input_book_title").val());
            $("#create_icon_book_name").val($("#input_book_title").val());
        });
    });

    // 參考同期的翻頁效果
    $(document).ready(function () {
        $('#book').turn({
            width: 840,
            height: 600,
            autoCenter: true
        });

        // 把所有元件的拖曳功能都先關掉
        setLayoutEdit(false);

        // 把icon選單上的所有icon排列好
        updateIconSamples("sample_icons");

        // 等到一切都就緒了再來把白色簾幕掀開
        $("#blank_curtain").css({ opacity: 0.0 });
        setTimeout(() => {
            $("#blank_curtain").attr("class", "empty");
        }, 2000);
    });

    // 把每一個元件的X, Y座標的旋轉角度和縮放比例，轉成JSON格式，才能透過PHP存到DB去
    function createOneItemLayoutJson(pos_item_name, rot_item_name) {
        let pos_item = $("#"+pos_item_name);
        let rot_item = $("#"+rot_item_name);

        let item_left = pos_item.css("left");
        if (!item_left) item_left = "0px";

        let item_top = pos_item.css("top");
        if (!item_top) item_top = "0px";

        // 角度
        let item_degree = rot_item.attr("degree");
        if (!item_degree) item_degree = 0;

        // 縮放比例
        let item_scale = rot_item.attr("scale");
        if (!item_scale) item_scale = 1.0;

        // 存成json格式
        let item_json = {
            left: item_left,
            top: item_top,
            degree: item_degree,
            scale: item_scale,
        };

        return item_json;
    }

    function createOneItemJsonWithPage(item_name, page) {
        let pos_item_name = "record_" + item_name + '_' + page;
        let rot_item_name = "record_" + item_name + "_rotateable_" + page;

        return createOneItemLayoutJson(pos_item_name, rot_item_name);
    }

    function createOneItemJsonWithPageAndIndex(item_name, page, index) {
        let pos_item_name = "record_" + item_name + '_' + page + '_' + index;
        let rot_item_name = "record_" + item_name + "_rotateable_" + page + '_' + index;

        return createOneItemLayoutJson(pos_item_name, rot_item_name);
    }

    function createOneItemJsonWithoutPage(item_name) {
        let pos_item_name = "record_" + item_name;
        let rot_item_name = "record_" + item_name + "_rotateable";

        return createOneItemLayoutJson(pos_item_name, rot_item_name);
    }

    // 把每一頁的縮放、角度等變數存成json格式，僅存在Client端
    $("#save_layout").on("click", function () {

        // Submit之前會確認Title是否空白
        if ($("#layout_book_name").val() == "") {
            alert("Must input the book's name on cover page!");
            return;
        }
        // Submit之前會確認author是否空白
        if ($("#layout_author").val() == "") {
            alert("Must input author!");
            return;
        }

        let all_pages_json = [];
        let one_page_json = null;

        for (page = 1; page <= <?=count($values)?> ; ++page) {

            one_page_json = {
                id: parseInt($("#record_id_" + page).text()),
                layout: {
                    image: createOneItemJsonWithPage("image", page),
                    datetime: createOneItemJsonWithPage("datetime", page),
                    author: createOneItemJsonWithPage("author", page),
                    storybook: createOneItemJsonWithPage("storybook", page),
                    child_name: createOneItemJsonWithPage("child_name", page),
                    progress: createOneItemJsonWithPage("progress", page),
                    child_feedback: createOneItemJsonWithPage("child_feedback", page),
                    comment: createOneItemJsonWithPage("comment", page),
                    icons: {
                        num_icons: num_icons_on_page[page],
                        icon_layout: {},
                    },
                },
            };
            // 追加每一頁裡的icon到json
            for (icon_id = 0; icon_id < num_icons_on_page[page]; ++icon_id) {
                let icon_name = "icon_" + page + "_" + icon_id;
                let icon_json = {};
                icon_json["icon_name"] = $("#use_" + icon_name).attr("ref_target");
                icon_json["icon"] = createOneItemJsonWithPageAndIndex("icon", page, icon_id);
                one_page_json["layout"]["icons"]["icon_layout"][icon_name] = icon_json;
            }


            // 每一頁處理完的layout的資料，統整成整本書的內容(Client端)
            all_pages_json.push(one_page_json);
        }

        // 把封面的版面設計轉成json
        let cover_layout_json = {
            first_page_layout: {
                cover_title: createOneItemJsonWithoutPage("cover_title"),
                cover_image: createOneItemJsonWithoutPage("cover_image"),
                icons: {
                    num_icons: num_icons_on_page[0],
                    icon_layout: {},
                },
            },
        };
        // 把封面的icon追加到json裡去
        for (icon_id = 0; icon_id < num_icons_on_page[0]; ++icon_id) {
            let icon_name = "icon_0_" + icon_id;
            let icon_json = {};
            icon_json["icon_name"] = $("#use_" + icon_name).attr("ref_target");
            icon_json["icon"] = createOneItemJsonWithPageAndIndex("icon", 0, icon_id);
            cover_layout_json["first_page_layout"]["icons"]["icon_layout"][icon_name] = icon_json;
        }
//        for (icon_id = 0; icon_id < num_icons_on_page[0]; ++icon_id) {
//            cover_layout_json["first_page_layout"]["icons"]["icon_layout"]["icon_0_" + icon_id] = createOneItemJsonWithPageAndIndex("icon", 0, icon_id);
//        }

        // 把json轉成文字，透過form將文字資料傳到DB去，因為DB上面只能儲存文字
        all_pages_json_str = JSON.stringify(all_pages_json);
        cover_pages_json_str = JSON.stringify(cover_layout_json);

        // 把文字填入表單
        $("#layout_json").val(all_pages_json_str);
        $("#cover_layout_json").val(cover_pages_json_str);

        // Line 346 表單Submit出去
        $("#layout_form").submit();
    });

    // 編輯Layout
    let edit_layout_enabled = false;

    
    // 設定版面上的元件是否可以編輯
    function setLayoutEdit(edit_enabled) {

        // 封面上的元件
        setEditOneItemWithoutPage("cover_title", edit_enabled);
        setEditOneItemWithoutPage("cover_image", edit_enabled);
        setPageBottomButtonEnabled(0, edit_enabled);

        // 封面上所有icon
        for (let icon_id = 0; icon_id < num_icons_on_page[0]; ++icon_id) {
            setEditOneItemWithPageAndIndex("icon", 0, icon_id, edit_enabled);
        }

        for (page = 1; page <= <?=count($values)?>; ++page) {

            // 各頁上的元件
            setEditOneItemWithPage("image", page, edit_enabled);
            setEditOneItemWithPage("datetime", page, edit_enabled);
            setEditOneItemWithPage("author", page, edit_enabled);
            setEditOneItemWithPage("storybook", page, edit_enabled);
            setEditOneItemWithPage("child_name", page, edit_enabled);
            setEditOneItemWithPage("progress", page, edit_enabled);
            setEditOneItemWithPage("child_feedback", page, edit_enabled);
            setEditOneItemWithPage("comment", page, edit_enabled);
            setPageBottomButtonEnabled(page, edit_enabled);

            // 各頁上所有icon
            for (let icon_id = 0; icon_id < num_icons_on_page[page]; ++icon_id) {
                setEditOneItemWithPageAndIndex("icon", page, icon_id, edit_enabled);
            }
        }
    }

    // 編輯Layout的按鈕功能設定
    $("#edit_layout").on("click", function () {
        edit_layout_enabled = !edit_layout_enabled;
        if (edit_layout_enabled) {
            $("#edit_layout").text("Stop Edit");
        }
        else {
            $("#edit_layout").text("Edit Page");
        }
        setLayoutEdit(edit_layout_enabled);
    });

    // Chat風 等同Select Image功能，只適用在封面
    let selected_cover_file = new File([""], "");

    $("#cover_image_file_chooser").on("change", function () {
        selected_cover_file = this.files[0];

        let reader = new FileReader();
        reader.onloadend = function () {

            // 如果因為之前沒有上傳過封面影像而把封面影像物件隱藏起來的話就把封面影像再秀出來
            let no_empty_class = $("#record_cover_image").attr("class").replace("empty ", "");
            $("#record_cover_image").attr("class", no_empty_class);

            // 把img物件的影像網址設為新的
            $("#cover_image_item").attr("src", reader.result);
            $("#cover_image_is_chosen").val("1");
        };
        reader.readAsDataURL(selected_cover_file);
    })

    $("#change_cover").on("click", function () {
        $("#cover_image_file_chooser").click();
    });

    // 把每一個元件的X, Y座標的旋轉角度和縮放比例都還原成預設值
    function resetOneItemLayout(pos_item_name, rot_item_name, item_name) {
        let pos_item = $("#"+pos_item_name);
        let rot_item = $("#"+rot_item_name);

        pos_item.css("left", item_default_layout_json[item_name]["left"]);
        pos_item.css("top" , item_default_layout_json[item_name]["top"]);

        // 角度
        $("#"+rot_item_name).attr('degree', 0);

        // 縮放比例
        $("#"+rot_item_name).attr('scale', 1.0);

        // 用CSS控制大小和旋轉方向
        var rotateCSS = 'rotate(0deg) scale(1.0)';
        $("#"+rot_item_name).css({
            '-moz-transform': rotateCSS,
            '-webkit-transform': rotateCSS
        });
    }

    function resetOneItemLayoutWithPage(item_name, page) {
        let pos_item_name = "record_" + item_name + '_' + page;
        let rot_item_name = "record_" + item_name + "_rotateable_" + page;

        resetOneItemLayout(pos_item_name, rot_item_name, item_name);
    }

    function resetOneItemLayoutWithoutPage(item_name) {
        let pos_item_name = "record_" + item_name;
        let rot_item_name = "record_" + item_name + "_rotateable";

        resetOneItemLayout(pos_item_name, rot_item_name, item_name);
    }

    // 每一頁是否在編輯狀態
    let page_on_edit = new Array(<?=count($values) + 1?>).fill(false);

    // 秀出指定頁面的修改表單
    function editText(page, is_editting) {
        if (page) {

            // 紀錄該頁面的編輯狀態
            page_on_edit[page] = is_editting;

            // 如果是在編輯狀態則秀出編輯畫面
            if (is_editting) {
                if ((page % 2) == 1) {
                    $("#update_form_back_" + page).attr("class", "view_left_page_update_form_back_style");
                }
                else {
                    $("#update_form_back_" + page).attr("class", "view_right_page_update_form_back_style");
                }
            }

            // 如果是非編輯狀態則隱藏編輯畫面
            else {
                $("#update_form_back_" + page).attr("class", "empty");
            }
        }
    }

    // 把指定頁的版面設定還原為預設值
    function resetOnePageLayout(page) {

        // 再次確認是否要把設定還原為預設值
        if (!confirm("Are you sure to reset the design of page " + page + "?")) {
            return;
        }

        // 確認是否要移除所有icon
        let remove_icons = confirm("Do you want to remove all icons?");

        // 封面的版面設定
        if (page == 0) {
            resetOneItemLayoutWithoutPage("cover_title");
            resetOneItemLayoutWithoutPage("cover_image");
        }

        // 內頁的版面設定
        else {
            resetOneItemLayoutWithPage("image", page);
            resetOneItemLayoutWithPage("datetime", page);
            resetOneItemLayoutWithPage("author", page);
            resetOneItemLayoutWithPage("storybook", page);
            resetOneItemLayoutWithPage("child_name", page);
            resetOneItemLayoutWithPage("progress", page);
            resetOneItemLayoutWithPage("child_feedback", page);
            resetOneItemLayoutWithPage("comment", page);
        }

        if (remove_icons) {
            // 移除掉所有的icon
            for (icon_id = 0; icon_id < num_icons_on_page[page]; icon_id++) {
                let icon_item_name = "record_icon_" + page + "_" + icon_id;
                $("#" + icon_item_name).remove();
            }
            num_icons_on_page[page] = 0;
        }
    }

    // 把指定的頁面刪除
    function deleteOnePage(page_id) {
        if (confirm("Are you sure to delete this page?")) {
            $("#delete_page_id").val(page_id);
            $("#delete_page_form").submit();
        }
    }

    $("#see_bookshelf").on("click", function(){
        $("#move_to_bookshelf_form").submit();
    });

    // 生成指定的svg icon html語法
    function createSvgContent(svg_item_id, svg_ref_id) {
        return '<svg class="svg_icon_style" viewBox="' + $("#" + svg_ref_id).attr("bounding-box") + '">' +
               '<use id="' + svg_item_id + '" xlink:href="#' + svg_ref_id + '" ref_target="' + svg_ref_id + '" x="0" y="0"></use>' +
               '</svg>';
    }

    // 追加指定的icon到指定頁面
    function addIconToPage(page, icon_name) {
        const icon_id = num_icons_on_page[page];
        ++num_icons_on_page[page];

        let svgContent = createSvgContent('use_icon_' + page + '_' + icon_id, icon_name);        

        // 生成icon
        addOneDraggableItemWithPageAndIndex("page_" + page, '{}', "icon", page, icon_id, svgContent, true);

        // Icon生成後將其擺在預設位置的最左上角
        $("#record_icon_" + page + "_" + icon_id).css({
            left: "100px",
            top: "100px",
        });

        // 設定icon
        setDraggableWithPageAndIndex("icon", page, icon_id);

        // 啟動icon的拖拉功能
        setEditOneItemWithPageAndIndex("icon", page, icon_id, edit_layout_enabled);

        // 把icon列表隱藏起來
        showIconMenu(-1);
    }

    // 把新增icon的視窗秀出來
    let show_icon_list = false;
    let current_icon_page = 0;
    function showIconMenu(page) {
        current_icon_page = page;

        if (page == -1) {
            show_icon_list = false;
        } else {
            show_icon_list = !show_icon_list;
        }

        if (show_icon_list) {
            $("#icon_list").attr("taget_page", page);
            $("#icon_list").attr("class", "icon_list_back_style");
            $("#create_icon_form_back").attr("class", "empty");
        }
        else {
            $("#icon_list").attr("class", "empty");
        }
    }

    // 從鋼琴作業裡copy過來的
    let num_items_per_column = [];
    num_items_per_column['sample_icons'] = 3;

    function updateIconSamples(icon_prefix) {
        {
            let x_pos = Math.floor((0 - currIconSampleIndex) / num_items_per_column[icon_prefix]);
            let y_pos = 0;
            $("#" + icon_prefix + "_new").css({
                left: (20 + x_pos * 130) + "px",
                top: (30 + y_pos * 120) + "px",
            });
        }
        for (let i = 0; ; ++i) {
            let icon_id = icon_prefix + "_" + i;
            if ($("#" + icon_id).length) {
                let icon_btn = $("#" + icon_id);

                let x_pos = Math.floor(((i + 1) - currIconSampleIndex) / num_items_per_column[icon_prefix]);
                let y_pos = (i + 1) % num_items_per_column[icon_prefix];

                icon_btn.css({
                    left: (20 + x_pos * 130) + "px",
                    top: (30 + y_pos * 120) + "px",
                });
            }
            else {
                break;
            }
        }
    }

    let currIconSampleIndex = 0;
    function movePrev(icon_prefix) {
        if (currIconSampleIndex > 0) {
            currIconSampleIndex-= num_items_per_column[icon_prefix];
            updateIconSamples(icon_prefix);
        }
    }

    let iconSampleListLength = [];
    iconSampleListLength['sample_icons'] = <?= count($icons_in_db) ?>;
    function moveNext(icon_prefix) {
        
        if (currIconSampleIndex < iconSampleListLength[icon_prefix] - 11) {
            currIconSampleIndex += num_items_per_column[icon_prefix];
            updateIconSamples(icon_prefix);
        }
    }

    $("#sample_icons_new").on("click", function () {

        // 把icon列表隱藏起來
        showIconMenu(-1);

        // 把生成icon的表單秀出來
        $("#create_icon_form_back").attr("class", "create_icon_back_style");
    });

    function showPreviewSvgIcon() {

        if ($("#create_icon_html").val() == "") {
            return;
        }

        let icon_x = 0;
        if ($("#create_icon_x").val() != "") {
            icon_x = parseInt($("#create_icon_x").val());
        }

        let icon_y = 0;
        if ($("#create_icon_y").val() != "") {
            icon_y = parseInt($("#create_icon_y").val());
        }

        let icon_w = 1024;
        if ($("#create_icon_width").val() != "") {
            icon_w = parseInt($("#create_icon_width").val());
        }

        let icon_h = 1024;
        if ($("#create_icon_height").val() != "") {
            icon_h = parseInt($("#create_icon_height").val());
        }
        

        // 刪掉之前preview的結果
        $("#create_icon_preview").empty();

        // 按照使用者輸入的資訊生成新的svg
        let svg_viewbox = icon_x + " " + icon_y + " " + icon_w + " " + icon_h;

        let svg_content = 
            '<svg class="create_icon_preview_svg_style" viewBox="' + svg_viewbox + '">' +
                $("#create_icon_html").val() +
            '</svg>';

        $("#create_icon_preview").append(svg_content);
    };

    $("#create_icon_x").on("change", function () {
        if (isNaN(parseInt($("#create_icon_x").val())) && $("#create_icon_x").val() != "") {
            alert("Icon viewbox must be integer!");
            $("#create_icon_x").val("");
            return;
        }
        showPreviewSvgIcon();
    })

    $("#create_icon_y").on("change", function () {
        if (isNaN(parseInt($("#create_icon_y").val())) && $("#create_icon_y").val() != "") {
            alert("Icon viewbox must be integer!");
            $("#create_icon_y").val("");
            return;
        }
        showPreviewSvgIcon();
    })

    $("#create_icon_width").on("change", function () {
        if (isNaN(parseInt($("#create_icon_width").val())) && $("#create_icon_width").val() != "") {
            alert("Icon viewbox must be integer!");
            $("#create_icon_width").val("");
            return;
        }
        showPreviewSvgIcon();
    })

    $("#create_icon_height").on("change", function () {
        if (isNaN(parseInt($("#create_icon_height").val())) && $("#create_icon_height").val() != "") {
            alert("Icon viewbox must be integer!");
            $("#create_icon_height").val("");
            return;
        }
        showPreviewSvgIcon();
    })

    $("#create_icon_html").on("change", function () {
        showPreviewSvgIcon();
    })

    $("#create_icon_submit_btn").on("click", function () {
        if ($("#create_icon_name").val() == "") {
            alert("Must input icon's name!")
            return;
        }

        if ($("#create_icon_x").val() == "") {
            alert("Must input icon's x!")
            return;
        }
        if ($("#create_icon_y").val() == "") {
            alert("Must input icon's y!")
            return;
        }
        if ($("#create_icon_width").val() == "") {
            alert("Must input icon's width!")
            return;
        }
        if ($("#create_icon_height").val() == "") {
            alert("Must input icon's height!")
            return;
        }

        if ($("#create_icon_html").val() == "") {
            alert("Must input icon's svg code!")
            return;
        }

        $("#create_icon_form").submit();
    });

    $("#create_icon_cancel_btn").on("click", function () {
        $("#create_icon_form_back").attr("class", "empty");
    });
    
</script>

</html>