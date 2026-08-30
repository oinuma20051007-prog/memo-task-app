<form
    method="post"
    class="input-area"
>

    <!-- ジャンル -->
    <select
        name="genre_id"
        id="genre-select"
    >

        <option value="">
            ジャンルなし
        </option>

        <?php foreach ($genres as $genre): ?>

            <option
                value="<?php echo $genre["id"]; ?>"
            >
                <?php
                echo htmlspecialchars(
                    $genre["genre_name"]
                );
                ?>
            </option>

        <?php endforeach; ?>


        <!-- 新規ジャンル -->
        <option value="new">
            ＋ 新規ジャンル
        </option>

    </select>


    <!-- 新しいジャンル名 -->
    <input
        type="text"
        name="new_genre_name"
        id="new-genre-name"
        placeholder="新しいジャンル名"
        style="display:none;"
    >


    <!-- 内容 -->
    <textarea
        name="content"
        class="main-input"
        placeholder="内容を入力..."
        required
        rows="1"
    ></textarea>


    <!-- メモ -->
    <button
        type="submit"
        name="memo_button"
        class="memo-button"
    >
        メモ
    </button>


    <!-- タスク -->
    <button
        type="button"
        id="open-task-button"
        class="task-button"
    >
        タスク
    </button>


    <!-- PHPへ送る期限 -->
    <input
        type="hidden"
        name="due_date"
        id="due-date"
        value=""
    >


    <!-- タスク送信 -->
    <button
        type="submit"
        name="task_button"
        id="task-submit-button"
        class="task-submit-button"
        style="display:none;"
    >
        保存
    </button>

</form>


<script>

const openTaskButton =
    document.getElementById(
        "open-task-button"
    );

const taskSubmitButton =
    document.getElementById(
        "task-submit-button"
    );

const dueDate =
    document.getElementById(
        "due-date"
    );

const genreSelect =
    document.getElementById(
        "genre-select"
    );

const newGenreName =
    document.getElementById(
        "new-genre-name"
    );

const inputForm =
    document.querySelector(
        ".input-area"
    );


/* =========================
   新規ジャンル
========================= */

genreSelect.addEventListener(
    "change",
    function () {

        if (
            genreSelect.value === "new"
        ) {

            newGenreName.style.display =
                "block";

            newGenreName.focus();

        } else {

            newGenreName.style.display =
                "none";

            newGenreName.value =
                "";
        }

    }
);


/* =========================
   タスクボタン
========================= */

openTaskButton.addEventListener(
    "click",
    function () {

        openCalendar(
            dueDate,
            {
                noneText: "期限なし",

                onSelect: function () {

                    openTaskButton.style.display =
                        "none";

                    taskSubmitButton.style.display =
                        "inline-block";
                }
            }
        );

    }
);


/* =========================
   送信したことを記録
========================= */

inputForm.addEventListener(
    "submit",
    function () {

        sessionStorage.setItem(
            "scrollToBottomAfterSubmit",
            "1"
        );

    }
);


/* =========================
   送信後は最初から一番下へ
========================= */

window.addEventListener(
    "pageshow",
    function () {

        const shouldScroll =
            sessionStorage.getItem(
                "scrollToBottomAfterSubmit"
            );

        if (
            shouldScroll !== "1"
        ) {
            return;
        }

        window.scrollTo(
            0,
            document.documentElement.scrollHeight
        );

        sessionStorage.removeItem(
            "scrollToBottomAfterSubmit"
        );

    }
);

</script>



