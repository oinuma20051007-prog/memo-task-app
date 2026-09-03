<?php

require_once __DIR__ . "/config/db.php";

include __DIR__ . "/actions/genre_update.php";
include __DIR__ . "/actions/memo_update.php";
include __DIR__ . "/actions/task_update.php";

$type = $_GET["type"] ?? "all";

$keyword = trim(
    $_GET["keyword"] ?? ""
);

$genre_id =
    $_GET["genre_id"] ?? "";

$start_date =
    $_GET["start_date"] ?? "";

$end_date =
    $_GET["end_date"] ?? "";


/* =========================
   日付チェック
========================= */

$date_error = "";

if (
    $start_date !== "" &&
    $end_date !== "" &&
    $start_date > $end_date
) {

    $date_error =
        "開始日は終了日以前の日付を指定してください。";
}


/* =========================
   検索条件
========================= */

$conditions = [];

$params = [];


/* キーワード */
/* 本文＋詳細を検索 */
if ($keyword !== "") {

    $conditions[] = "
        (
            content LIKE :keyword
            OR detail_text LIKE :keyword
        )
    ";

    $params[":keyword"] =
        "%" . $keyword . "%";
}


/* ジャンル */
if ($genre_id === "none") {

    $conditions[] = "
        genre_id IS NULL
    ";

} elseif ($genre_id !== "") {

    $conditions[] = "
        genre_id = :genre_id
    ";

    $params[":genre_id"] =
        $genre_id;
}


/* この日以降に入力 */
if ($start_date !== "") {

    $conditions[] = "
        created_at >= :start_date
    ";

    $params[":start_date"] =
        $start_date . " 00:00:00";
}


/* この日までに入力 */
if ($end_date !== "") {

    $conditions[] = "
        created_at <= :end_date
    ";

    $params[":end_date"] =
        $end_date . " 23:59:59";
}


/* =========================
   全情報
========================= */

if ($type === "all") {

    $sql = "

        SELECT *

        FROM (

            SELECT
                memos.id,
                memos.memo_text AS content,
                memos.detail_text,
                memos.created_at,
                NULL AS due_date,
                memos.genre_id,
                genres.genre_name,
                'memo' AS type

            FROM memos

            LEFT JOIN genres
            ON memos.genre_id = genres.id

            WHERE memos.is_deleted = 0


            UNION ALL


            SELECT
                tasks.id,
                tasks.task_text AS content,
                tasks.detail_text,
                tasks.created_at,
                tasks.due_date,
                tasks.genre_id,
                genres.genre_name,
                'task' AS type

            FROM tasks

            LEFT JOIN genres
            ON tasks.genre_id = genres.id

            WHERE tasks.is_completed = 0

        ) AS all_items

    ";


    if (!empty($conditions)) {

        $sql .= "
            WHERE " .
            implode(
                " AND ",
                $conditions
            );
    }


    $sql .= "
        ORDER BY created_at DESC
    ";
}


/* =========================
   メモだけ
========================= */

elseif ($type === "memo") {

    $sql = "

        SELECT *

        FROM (

            SELECT
                memos.id,
                memos.memo_text AS content,
                memos.detail_text,
                memos.created_at,
                NULL AS due_date,
                memos.genre_id,
                genres.genre_name,
                'memo' AS type

            FROM memos

            LEFT JOIN genres
            ON memos.genre_id = genres.id

            WHERE memos.is_deleted = 0

        ) AS memo_items

    ";


    if (!empty($conditions)) {

        $sql .= "
            WHERE " .
            implode(
                " AND ",
                $conditions
            );
    }


    $sql .= "
        ORDER BY created_at DESC
    ";
}


/* =========================
   タスクだけ
========================= */

elseif ($type === "task") {

    $sql = "

        SELECT *

        FROM (

            SELECT
                tasks.id,
                tasks.task_text AS content,
                tasks.detail_text,
                tasks.created_at,
                tasks.due_date,
                tasks.genre_id,
                genres.genre_name,
                'task' AS type

            FROM tasks

            LEFT JOIN genres
            ON tasks.genre_id = genres.id

            WHERE tasks.is_completed = 0

        ) AS task_items

    ";


    if (!empty($conditions)) {

        $sql .= "
            WHERE " .
            implode(
                " AND ",
                $conditions
            );
    }


    $sql .= "
        ORDER BY created_at DESC
    ";
}


/* =========================
   不正なtype対策
========================= */

else {

    $type = "all";

    $sql = "

        SELECT *

        FROM (

            SELECT
                memos.id,
                memos.memo_text AS content,
                memos.detail_text,
                memos.created_at,
                NULL AS due_date,
                memos.genre_id,
                genres.genre_name,
                'memo' AS type

            FROM memos

            LEFT JOIN genres
            ON memos.genre_id = genres.id

            WHERE memos.is_deleted = 0


            UNION ALL


            SELECT
                tasks.id,
                tasks.task_text AS content,
                tasks.detail_text,
                tasks.created_at,
                tasks.due_date,
                tasks.genre_id,
                genres.genre_name,
                'task' AS type

            FROM tasks

            LEFT JOIN genres
            ON tasks.genre_id = genres.id

            WHERE tasks.is_completed = 0

        ) AS all_items

        ORDER BY created_at DESC
    ";
}


/* =========================
   SQL実行
========================= */

$items = [];


/*
    日付に問題がない場合だけ
    検索を実行する
*/
if ($date_error === "") {

    $stmt =
        $pdo->prepare($sql);


    foreach ($params as $key => $value) {

        $stmt->bindValue(
            $key,
            $value
        );
    }


    $stmt->execute();


    $items =
        $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );
}


/* =========================
   ジャンル一覧取得
========================= */

$genreSql = "
    SELECT *
    FROM genres
    ORDER BY id ASC
";

$genreStmt =
    $pdo->query($genreSql);


$genres =
    $genreStmt->fetchAll(
        PDO::FETCH_ASSOC
    );

?>


<!DOCTYPE html>
<html lang="ja">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>検索</title>

    <link
    rel="stylesheet"
    href="assets/css/style.css"
>

</head>


<body>


<div class="page-container">


    <!-- =========================
         ヘッダー
    ========================= -->

    <header class="header">


        <div class="header-top">

            <h1>検索</h1>

        </div>


        <nav class="top-tabs">


            <a
                href="index.php"
                <?php
                if ($type === "all") {
                    echo 'class="active"';
                }
                ?>
            >
                ホーム
            </a>


            <a
                href="memos.php"
                <?php
                if ($type === "memo") {
                    echo 'class="active"';
                }
                ?>
            >
                メモ
            </a>


            <a
                href="tasks.php"
                <?php
                if ($type === "task") {
                    echo 'class="active"';
                }
                ?>
            >
                タスク
            </a>


            <a href="genre_manage.php">
                ジャンル
            </a>


        </nav>


    </header>



    <main class="main-content">


        <!-- =========================
             検索条件
        ========================= -->

        <section
            class="search-area search-results-search"
        >


            <div class="section-title">
                🔍 検索条件
            </div>


            <form
                method="get"
                action="search.php"
                class="search-form"
            >


                <input
                    type="hidden"
                    name="type"
                    value="<?php
                        echo htmlspecialchars(
                            $type
                        );
                    ?>"
                >


                <input
                    type="text"
                    name="keyword"
                    placeholder="キーワードを入力"
                    value="<?php
                        echo htmlspecialchars(
                            $keyword
                        );
                    ?>"
                >


                <select name="genre_id">


                    <option value="">
                        すべてのジャンル
                    </option>


                    <option
                        value="none"
                        <?php
                        if ($genre_id === "none") {
                            echo "selected";
                        }
                        ?>
                    >
                        ジャンル未選択
                    </option>


                    <?php foreach ($genres as $genre): ?>


                        <option
                            value="<?php
                                echo $genre["id"];
                            ?>"

                            <?php
                            if (
                                $genre_id ==
                                $genre["id"]
                            ) {
                                echo "selected";
                            }
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $genre["genre_name"]
                            );
                            ?>

                        </option>


                    <?php endforeach; ?>


                </select>


                <input
                    type="text"
                    name="start_date"
                    id="search-start-date"
                    value="<?php
                        echo htmlspecialchars(
                            $start_date
                        );
                    ?>"
                    placeholder="開始日"
                    readonly
                    onclick="openCalendar(this)"
                >


                <span>〜</span>


                <input
                    type="text"
                    name="end_date"
                    id="search-end-date"
                    value="<?php
                        echo htmlspecialchars(
                            $end_date
                        );
                    ?>"
                    placeholder="終了日"
                    readonly
                    onclick="openCalendar(this)"
                >


                <button
                    type="submit"
                    class="search-button"
                >
                    検索
                </button>


            </form>


            <!-- =========================
                 日付エラー
            ========================= -->

            <?php if ($date_error !== ""): ?>


                <p class="search-error">

                    <?php
                    echo htmlspecialchars(
                        $date_error
                    );
                    ?>

                </p>


            <?php endif; ?>


            <div class="search-clear-row">


               <a
    href="search.php?type=<?php
        echo urlencode($type);
    ?>"
    class="search-clear-link"
>
                 
                >
                    条件をクリア
                </a>


            </div>


        </section>



        <!-- =========================
             検索結果
        ========================= -->

        <section class="records-area">


            <div class="section-title search-result-title">

                検索結果


                <?php if ($date_error === ""): ?>

                    <span class="search-result-count">

                        <?php
                        echo count($items);
                        ?>件

                    </span>

                <?php endif; ?>


            </div>


            <?php if ($date_error !== ""): ?>


                <p class="empty-message">

                    日付を修正してから、
                    もう一度検索してください。

                </p>


            <?php elseif (empty($items)): ?>


                <p class="empty-message">

                    検索条件に一致するデータは
                    見つかりませんでした。

                </p>


            <?php endif; ?>



            <?php if ($date_error === ""): ?>


                <?php foreach ($items as $item): ?>


                    <div
                        class="item"

                        data-id="<?php
                            echo $item["id"];
                        ?>"

                        data-type="<?php
                            echo $item["type"];
                        ?>"

                        data-content="<?php
                            echo htmlspecialchars(
                                $item["content"],
                                ENT_QUOTES
                            );
                        ?>"

                        data-detail-text="<?php
                            echo htmlspecialchars(
                                $item["detail_text"] ?? "",
                                ENT_QUOTES
                            );
                        ?>"

                        data-created-at="<?php
                            echo date(
                                "n/j H:i",
                                strtotime(
                                    $item["created_at"]
                                )
                            );
                        ?>"

                        data-due-date="<?php
                            echo htmlspecialchars(
                                $item["due_date"] ?? "",
                                ENT_QUOTES
                            );
                        ?>"

                        data-genre-id="<?php
                            echo htmlspecialchars(
                                $item["genre_id"] ?? "",
                                ENT_QUOTES
                            );
                        ?>"

                        onclick="openDetail(this)"
                    >


                        <div class="item-body">


                            <div class="item-content">

                                <?php
                                echo htmlspecialchars(
                                    $item["content"]
                                );
                                ?>

                            </div>


                            <div class="item-meta">


                                <span
                                    class="<?php
                                        echo
                                            $item["type"] === "memo"
                                            ? "memo-label"
                                            : "task-label";
                                    ?>"
                                >

                                    <?php
                                    echo
                                        $item["type"] === "memo"
                                        ? "メモ"
                                        : "タスク";
                                    ?>

                                </span>


                                <?php if (!empty($item["genre_name"])): ?>


                                    <span class="genre-label">

                                        <?php
                                        echo htmlspecialchars(
                                            $item["genre_name"]
                                        );
                                        ?>

                                    </span>


                                <?php endif; ?>

<?php if ($item["type"] === "task"): ?>

    <span class="due-date">

        <?php if (!empty($item["due_date"])): ?>

            期限：

            <?php
            echo date(
                "n/j",
                strtotime(
                    $item["due_date"]
                )
            );
            ?>

        <?php else: ?>

            期限なし

        <?php endif; ?>

    </span>

<?php endif; ?>


                                  


                            </div>


                        </div>



                        <div class="item-right">


                            <span class="item-date">

                                <?php
                                echo date(
                                    "n/j H:i",
                                    strtotime(
                                        $item["created_at"]
                                    )
                                );
                                ?>

                            </span>


                            <?php if ($item["type"] === "memo"): ?>


                                <form
                                    method="post"
                                    onclick="event.stopPropagation();"
                                >


                                    <input
                                        type="hidden"
                                        name="delete_id"
                                        value="<?php
                                            echo $item["id"];
                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="small-action"
                                    >
                                        削除
                                    </button>


                                </form>


                            <?php else: ?>


                                <form
                                    method="post"
                                    onclick="event.stopPropagation();"
                                >


                                    <input
                                        type="hidden"
                                        name="complete_id"
                                        value="<?php
                                            echo $item["id"];
                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="small-action complete-action"
                                    >
                                        完了
                                    </button>


                                </form>


                            <?php endif; ?>


                        </div>


                    </div>


                <?php endforeach; ?>


            <?php endif; ?>


        </section>


    </main>


</div>


<?php include "calendar.php"; ?>



<!-- =========================
     メモ詳細
========================= -->

<div
    id="memo-modal"
    class="detail-modal"
    style="display: none;"
>


    <button
        type="button"
        class="close-button"
        onclick="closeMemoModal()"
    >
        ×
    </button>


    <h2>メモ詳細</h2>


    <p>内容</p>

    <div id="memo-content"></div>


    <p>記録日時</p>

    <div id="memo-created-at"></div>


    <form method="post">


        <input
            type="hidden"
            name="memo_edit_id"
            id="memo-edit-id"
        >


        <p>詳細</p>


        <textarea
            name="detail_text"
            id="memo-detail-text"
            placeholder="詳細を入力"
        ></textarea>


        <p>ジャンル</p>


        <select
            name="genre_id"
            id="memo-genre-id"
        >


            <option value="">
                ジャンルなし
            </option>


            <?php foreach ($genres as $genre): ?>


                <option
                    value="<?php
                        echo $genre["id"];
                    ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $genre["genre_name"]
                    );
                    ?>

                </option>


            <?php endforeach; ?>


        </select>


        <p>新しいジャンル</p>


        <input
            type="text"
            name="new_genre_name"
            id="memo-new-genre-name"
            placeholder="新しく作る場合のみ入力"
        >


        <br><br>


        <button
            type="submit"
            class="modal-save-button"
        >
            保存
        </button>


    </form>


</div>



<!-- =========================
     タスク詳細
========================= -->

<div
    id="task-modal"
    class="detail-modal"
    style="display: none;"
>


    <button
        type="button"
        class="close-button"
        onclick="closeTaskModal()"
    >
        ×
    </button>


    <h2>タスク詳細</h2>


    <p>内容</p>

    <div id="task-content"></div>


    <p>記録日時</p>

    <div id="task-created-at"></div>


    <form method="post">


        <input
            type="hidden"
            name="task_edit_id"
            id="task-edit-id"
        >


        <p>詳細</p>


        <textarea
            name="detail_text"
            id="task-detail-text"
            placeholder="詳細を入力"
        ></textarea>


        <p>期限</p>


        <input
            type="text"
            name="due_date"
            id="task-due-date"
            readonly
            onclick="openCalendar(this, { noneText: '期限なし' })"
        >


        <p>ジャンル</p>


        <select
            name="genre_id"
            id="task-genre-id"
        >


            <option value="">
                ジャンルなし
            </option>


            <?php foreach ($genres as $genre): ?>


                <option
                    value="<?php
                        echo $genre["id"];
                    ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $genre["genre_name"]
                    );
                    ?>

                </option>


            <?php endforeach; ?>


        </select>


        <p>新しいジャンル</p>


        <input
            type="text"
            name="new_genre_name"
            id="task-new-genre-name"
            placeholder="新しく作る場合のみ入力"
        >


        <br><br>


        <button
            type="submit"
            class="modal-save-button"
        >
            保存
        </button>


    </form>


</div>



<script>


/* =========================
   メモかタスクか判定
========================= */

function openDetail(item) {


    if (item.dataset.type === "memo") {

        openMemoModal(item);

    } else {

        openTaskModal(item);
    }

}



/* =========================
   メモ詳細
========================= */

function openMemoModal(item) {


    document
        .getElementById(
            "memo-edit-id"
        )
        .value =
            item.dataset.id;


    document
        .getElementById(
            "memo-content"
        )
        .textContent =
            item.dataset.content;


    document
        .getElementById(
            "memo-created-at"
        )
        .textContent =
            item.dataset.createdAt;


    document
        .getElementById(
            "memo-detail-text"
        )
        .value =
            item.dataset.detailText;


    document
        .getElementById(
            "memo-genre-id"
        )
        .value =
            item.dataset.genreId;


    document
        .getElementById(
            "memo-new-genre-name"
        )
        .value = "";


    document
        .getElementById(
            "memo-modal"
        )
        .style.display =
            "block";

}



function closeMemoModal() {


    document
        .getElementById(
            "memo-modal"
        )
        .style.display =
            "none";

}



/* =========================
   タスク詳細
========================= */

function openTaskModal(item) {


    document
        .getElementById(
            "task-edit-id"
        )
        .value =
            item.dataset.id;


    document
        .getElementById(
            "task-content"
        )
        .textContent =
            item.dataset.content;


    document
        .getElementById(
            "task-created-at"
        )
        .textContent =
            item.dataset.createdAt;


    document
        .getElementById(
            "task-detail-text"
        )
        .value =
            item.dataset.detailText;


    document
        .getElementById(
            "task-due-date"
        )
        .value =
            item.dataset.dueDate;


    document
        .getElementById(
            "task-genre-id"
        )
        .value =
            item.dataset.genreId;


    document
        .getElementById(
            "task-new-genre-name"
        )
        .value = "";


    document
        .getElementById(
            "task-modal"
        )
        .style.display =
            "block";

}



function closeTaskModal() {


    document
        .getElementById(
            "task-modal"
        )
        .style.display =
            "none";

}


</script>


</body>
</html>
