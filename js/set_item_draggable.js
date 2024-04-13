
let item_default_layout_json = {
    cover_title: {
        left: "60px",
        top: "250px",
        degree: 0,
        scale: 1,
    },
    cover_image: {
        left: "60px",
        top: "250px",
        degree: 0,
        scale: 1,
    },
    image: {
        left: "60px",
        top: "60px",
        degree: 0,
        scale: 1,
    },
    datetime: {
        left: "60px",
        top: "300px",
        degree: 0,
        scale: 1,
    },
    author: {
        left: "60px",
        top: "330px",
        degree: 0,
        scale: 1,
    },
    storybook: {
        left: "60px",
        top: "360px",
        degree: 0,
        scale: 1,
    },
    child_name: {
        left: "60px",
        top: "390px",
        degree: 0,
        scale: 1,
    },
    progress: {
        left: "60px",
        top: "420px",
        degree: 0,
        scale: 1,
    },
    child_feedback: {
        left: "60px",
        top: "450px",
        degree: 0,
        scale: 1,
    },
    comment: {
        left: "60px",
        top: "480px",
        degree: 0,
        scale: 1,
    },
    icon: {
        left: "50px",
        top: "50px",
        degree: 0,
        scale: 1,
    }
};

// 從json裡取得元件的屬性
function getItemAttr(layout_json, item_name, attr_name, default_value) {
    if (layout_json) {
        item_layout = layout_json[item_name];
        if (item_layout) {
            item_attr = item_layout[attr_name];
            if (item_attr) {
                return item_attr;
            }
        }
    }
    // 如果從db讀進來的json沒有對應的資料，就會用defult_value回傳
    return default_value;
}

// 追加可以移動, 旋轉, 縮放的物件
function addOneDraggableItem(
    parent_id,
    layout_json,
    item_name,
    html_content,
    draggable_item_name,
    draggable_trigger_name,
    rotate_item_name,
    rotate_trigger_name,
    add_to_first
) {
    $("#" + parent_id).append(
        "<div id='" + draggable_item_name + "' style='position: absolute; left: " + getItemAttr(layout_json, item_name, 'left', '100px') + "; top: " + getItemAttr(layout_json, item_name, 'top', '50px') + ";'></div>"
    );;

    $("#" + draggable_item_name).append(
        "<div id='" + rotate_item_name + "' class='view_record_item_rotateable_back_style' degree='" + getItemAttr(layout_json, item_name, 'degree', 0) + "' scale='" + getItemAttr(layout_json, item_name, 'scale', 1) + "' style='transform: rotate(" + getItemAttr(layout_json, item_name, 'degree', 0) + "deg) scale(" + getItemAttr(layout_json, item_name, 'scale', 1) + ");'></div>"
    );

    $("#" + rotate_item_name).append(html_content);

    $("#" + rotate_item_name).prepend(
        "<div id='" + draggable_trigger_name + "' class='record_drag_trigger_style'></div>"
    );

    $("#" + rotate_item_name).prepend(
        "<div class='view_item_empty_back_style'>" +
        "   <div id='" + rotate_trigger_name + "' class='view_item_rotate_btn_style'></div>" +
        "</div>"
    );
}

function addOneDraggableItemWithPage(parent_id, layout_json_str, item_name, page, html_content) {
    let layout_json = JSON.parse(layout_json_str);
    if (!layout_json[item_name]) {
        layout_json = item_default_layout_json;
    }

    let draggable_item_name = "record_" + item_name + '_' + page;
    let draggable_trigger_name = "record_" + item_name + '_drag_' + page;
    let rotate_item_name = "record_" + item_name + "_rotateable_" + page;
    let rotate_trigger_name = "record_" + item_name + '_rotate_' + page;

    addOneDraggableItem(
        parent_id,
        layout_json,
        item_name,
        html_content,
        draggable_item_name,
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name
    );
}

function addOneDraggableItemWithPageAndIndex(parent_id, layout_json_str, item_name, page, index, html_content) {
    let layout_json = JSON.parse(layout_json_str);
    if (!layout_json[item_name]) {
        layout_json = item_default_layout_json;
    }
    let draggable_item_name = "record_" + item_name + '_' + page + "_" + index;
    let draggable_trigger_name = "record_" + item_name + '_drag_' + page + "_" + index;
    let rotate_item_name = "record_" + item_name + "_rotateable_" + page + "_" + index;
    let rotate_trigger_name = "record_" + item_name + '_rotate_' + page + "_" + index;

    addOneDraggableItem(
        parent_id,
        layout_json,
        item_name,
        html_content,
        draggable_item_name,
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name
    );
}

function addOneDraggableItemWithoutPage(parent_id, layout_json_str, item_name, html_content) {
    let layout_json = JSON.parse(layout_json_str);
    if (!layout_json[item_name]) {
        layout_json = item_default_layout_json;
    }
    let draggable_item_name = "record_" + item_name;
    let draggable_trigger_name = "record_" + item_name + "_drag";
    let rotate_item_name = "record_" + item_name + "_rotateable";
    let rotate_trigger_name = "record_" + item_name + "_rotate";

    addOneDraggableItem(
        parent_id,
        layout_json,
        item_name,
        html_content,
        draggable_item_name,
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name
    );
}


// 設定文字跟影像可以縮放和拖拉的函式
function setDraggableItem(
    draggable_item_name,
    draggable_trigger_name,
    rotate_item_name,
    rotate_trigger_name) {
    // For draggable 拖拉
    // handle: 指定文字跟影像可以拖拉
    $("#" + draggable_item_name).draggable({ handle: "#" + draggable_trigger_name });

    // For rotate 旋轉和縮放
    // rotate_trigger 要點選才能會有縮放功能
    $("#" + rotate_item_name).draggable({
        handle: "#" + rotate_trigger_name,
        // 待查詢
        opacity: 0.001,
        helper: 'clone',

        // 利用滑鼠座標去做角度，三角函數
        drag: function (event) {
            var // get center of div to rotate
                pw = document.getElementById(rotate_item_name);
            pwBox = pw.getBoundingClientRect();
            center_x = (pwBox.left + pwBox.right) / 2;
            center_y = (pwBox.top + pwBox.bottom) / 2;

            // get mouse position 計算縮放角度和比例
            mouse_x = event.pageX;
            mouse_y = event.pageY;
            radians = Math.atan2(mouse_x - center_x, mouse_y - center_y);
            degree = Math.round((radians * (180 / Math.PI) * -1) + 100);

            origin_size = pwBox.width / 2 + 30;
            delta_x = mouse_x - center_x;
            delta_y = mouse_y - center_y;
            new_size = Math.sqrt(delta_x * delta_x + delta_y * delta_y);
            new_scale = new_size / origin_size;

            // 計算完的角度記錄在HTML的文件裡
            $("#" + rotate_item_name).attr('degree', (degree + 170));
            $("#" + rotate_item_name).attr('scale', new_scale);

            // 用CSS控制大小和旋轉方向
            var rotateCSS = 'rotate(' + (degree + 170) + 'deg) scale(' + new_scale + ')';
            $("#" + rotate_item_name).css({
                '-moz-transform': rotateCSS,
                '-webkit-transform': rotateCSS
            });
        }
    });
}

// 決定每一頁div的ID，讓Line 424的函數處理
function setDraggableWithPage(item_name, page) {

    let draggable_item_name = "record_" + item_name + '_' + page;
    let draggable_trigger_name = "record_" + item_name + '_drag_' + page;
    let rotate_item_name = "record_" + item_name + "_rotateable_" + page;
    let rotate_trigger_name = "record_" + item_name + '_rotate_' + page;

    setDraggableItem(
        draggable_item_name,
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name
    );
}

// 決定每一頁div的ID，讓Line 424的函數處理
function setDraggableWithPageAndIndex(item_name, page, index) {

    let draggable_item_name = "record_" + item_name + '_' + page + "_" + index;
    let draggable_trigger_name = "record_" + item_name + '_drag_' + page + "_" + index;
    let rotate_item_name = "record_" + item_name + "_rotateable_" + page + "_" + index;
    let rotate_trigger_name = "record_" + item_name + '_rotate_' + page + "_" + index;

    setDraggableItem(
        draggable_item_name,
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name
    );
}

// 決定封面和封底的div的ID，讓Line 424的函數處理
function setDraggableWithoutPage(item_name) {
    let draggable_item_name = "record_" + item_name;
    let draggable_trigger_name = "record_" + item_name + '_drag';
    let rotate_item_name = "record_" + item_name + "_rotateable";
    let rotate_trigger_name = "record_" + item_name + '_rotate';

    setDraggableItem(
        draggable_item_name,
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name
    );
}

function setEditOneItemStatus(
    draggable_item_name,
    draggable_trigger_name,
    rotate_item_name,
    rotate_trigger_name,
    edit_enabled) {
    $("#" + draggable_item_name).draggable({ disabled: !edit_enabled });

    if (edit_enabled) {
        $("#" + rotate_trigger_name).attr("class", "view_item_rotate_btn_style");
        $("#" + draggable_trigger_name).attr("class", "record_drag_trigger_style");
    }
    else {
        $("#" + rotate_trigger_name).attr("class", "empty");
        $("#" + draggable_trigger_name).attr("class", "empty");
    }
}

function setEditOneItemWithPage(item_name, page, edit_enabled) {
    let draggable_item_name = "record_" + item_name + '_' + page;
    let draggable_trigger_name = "record_" + item_name + '_drag_' + page;
    let rotate_item_name = "record_" + item_name + "_rotateable_" + page;
    let rotate_trigger_name = "record_" + item_name + '_rotate_' + page;

    setEditOneItemStatus(
        draggable_item_name,
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name,
        edit_enabled
    );
}

function setEditOneItemWithPageAndIndex(item_name, page, index, edit_enabled) {
    let draggable_item_name = "record_" + item_name + '_' + page + "_" + index;
    let draggable_trigger_name = "record_" + item_name + '_drag_' + page + "_" + index;
    let rotate_item_name = "record_" + item_name + "_rotateable_" + page + "_" + index;
    let rotate_trigger_name = "record_" + item_name + '_rotate_' + page + "_" + index;

    setEditOneItemStatus(
        draggable_item_name,
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name,
        edit_enabled
    );
}

function setEditOneItemWithoutPage(item_name, edit_enabled) {
    let draggable_item_name = "record_" + item_name;
    let draggable_trigger_name = "record_" + item_name + '_drag';
    let rotate_item_name = "record_" + item_name + "_rotateable";
    let rotate_trigger_name = "record_" + item_name + '_rotate';

    setEditOneItemStatus(
        draggable_item_name,
        draggable_trigger_name,
        rotate_item_name,
        rotate_trigger_name,
        edit_enabled
    );
}

// 顯示或隱藏每個頁面下方的按鈕
function setPageBottomButtonEnabled(page, edit_enabled) {
    if (edit_enabled) {
        $("#page_bottom_bar_" + page).attr("class", "page_bottom_bar");
    }
    else {
        $("#page_bottom_bar_" + page).attr("class", "empty");
    }
}