<?php

include "db.php";


/* 保存・更新処理 */
include "genre_update.php";

include "save.php";

include "task_update.php";


/* =========================
   今日の日付
========================= */

$today =
    date("Y-m-d");


/* =========================
   今日が期限のタスクを取得
========================= */

$sql = "
    SELECT
        tasks.*,
        genres.genre_name
    FROM tasks
    LEFT JOIN genres
        ON tasks.genre_id = genres.id
    WHERE tasks.is_completed = 0
        AND tasks.due_date = :today
    ORDER BY
        tasks.created_at ASC
";


$stmt =
    $pdo->prepare($sql);


$stmt->bindValue(
    ":today",
    $today,
    PDO::PARAM_STR
);


$stmt->execute();


$todayTasks =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================
   今日以外の未完了タスクを取得
========================= */

$sql = "
    SELECT
        tasks.*,
        genres.genre_name
    FROM tasks
    LEFT JOIN genres
        ON tasks.genre_id = genres.id
    WHERE tasks.is_completed = 0
        AND (
            tasks.due_date IS NULL
            OR tasks.due_date <> :today
        )
    ORDER BY
        tasks.due_date IS NULL DESC,
        tasks.due_date ASC,
        tasks.created_at DESC
";


$stmt =
    $pdo->prepare($sql);


$stmt->bindValue(
    ":today",
    $today,
    PDO::PARAM_STR
);


$stmt->execute();


$tasks =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================
   ジャンル一覧を取得
========================= */

$sql = "
    SELECT *
    FROM genres
    ORDER BY id ASC
";


$stmt =
    $pdo->query($sql);


$genres =
    $stmt->fetchAll(
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

    <title>タスク</title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>


<body>


<div class="page-container">


    <header class="header">

        <div class="header-top">

            <h1>タスク</h1>

            <button
                type="button"
                class="search-toggle-button"
                onclick="toggleSearch()"
                aria-label="検索を開閉"
            >
                🔍
            </button>

        </div>


        <nav class="top-tabs">

            <a href="index.php">
                ホーム
            </a>

            <a href="memos.php">
                メモ
            </a>

            <a
                href="tasks.php"
                class="active"
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
             検索
        ========================= -->

        <section
            class="search-area"
            id="search-area"
            style="display: none;"
        >

            <div class="section-title">
                🔍 タスクを検索
            </div>


            <form
                method="get"
                action="search.php"
                class="search-form"
            >

                <input
                    type="hidden"
                    name="type"
                    value="task"
                >


                <input
                    type="text"
                    name="keyword"
                    placeholder="キーワードを入力"
                >


                <select name="genre_id">

                    <option value="">
                        すべてのジャンル
                    </option>

                    <option value="none">
                        ジャンル未選択
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


                <input
                    type="text"
                    name="start_date"
                    id="search-start-date"
                    placeholder="開始日"
                    readonly
                    onclick="openCalendar(this)"
                >


                <span>～</span>


                <input
                    type="text"
                    name="end_date"
                    id="search-end-date"
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

        </section>



        <!-- =========================
             今日のタスク
        ========================= -->

        <section class="reminder-area">

            <div class="reminder-header">

                <span>
                    📅 今日のタスク
                </span>

            </div>


            <div id="today-task-content">


                <?php if (empty($todayTasks)): ?>


                    <div class="reminder-row">

                        <div class="reminder-empty">
                            今日が期限のタスクはありません
                        </div>

                    </div>


                <?php else: ?>


                    <?php foreach ($todayTasks as $task): ?>


                        <div
                            class="reminder-row"

                            data-id="<?php
                                echo $task["id"];
                            ?>"

                            data-task-text="<?php
                                echo htmlspecialchars(
                                    $task["task_text"],
                                    ENT_QUOTES
                                );
                            ?>"

                            data-detail-text="<?php
                                echo htmlspecialchars(
                                    $task["detail_text"] ?? "",
                                    ENT_QUOTES
                                );
                            ?>"

                            data-created-at="<?php
                                echo date(
                                    "n/j H:i",
                                    strtotime(
                                        $task["created_at"]
                                    )
                                );
                            ?>"

                            data-due-date="<?php
                                echo htmlspecialchars(
                                    $task["due_date"] ?? "",
                                    ENT_QUOTES
                                );
                            ?>"

                            data-genre-id="<?php
                                echo htmlspecialchars(
                                    $task["genre_id"] ?? "",
                                    ENT_QUOTES
                                );
                            ?>"

                            onclick="openTaskModal(this)"
                        >


                            <div class="item-body">


                                <div class="item-content">

                                    <?php
                                    echo htmlspecialchars(
                                        $task["task_text"]
                                    );
                                    ?>

                                </div>


                                <div class="item-meta">


                                    <span class="task-label">
                                        タスク
                                    </span>


                                    <?php if (!empty($task["genre_name"])): ?>

                                        <span class="genre-label">

                                            <?php
                                            echo htmlspecialchars(
                                                $task["genre_name"]
                                            );
                                            ?>

                                        </span>

                                    <?php endif; ?>


                                    <span class="due-date">
                                        期限：今日
                                    </span>


                                </div>

                            </div>


                            <div class="item-right">


                                <span class="item-date">

                                    <?php
                                    echo date(
                                        "n/j H:i",
                                        strtotime(
                                            $task["created_at"]
                                        )
                                    );
                                    ?>

                                </span>


                                <form
                                    method="post"
                                    onclick="event.stopPropagation();"
                                >

                                    <input
                                        type="hidden"
                                        name="complete_id"
                                        value="<?php
                                            echo $task["id"];
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="small-action complete-action"
                                    >
                                        完了
                                    </button>

                                </form>


                            </div>


                        </div>


                    <?php endforeach; ?>


                <?php endif; ?>


            </div>

        </section>



        <!-- =========================
             通常タスク一覧
        ========================= -->

        <section class="records-area">

            <div class="section-title">
                タスク一覧
            </div>


            <?php if (empty($tasks)): ?>

                <p class="empty-message">
                    その他の未完了タスクはありません。
                </p>

            <?php endif; ?>


            <?php foreach ($tasks as $task): ?>


                <div
                    class="item task-item"

                    data-id="<?php
                        echo $task["id"];
                    ?>"

                    data-task-text="<?php
                        echo htmlspecialchars(
                            $task["task_text"],
                            ENT_QUOTES
                        );
                    ?>"

                    data-detail-text="<?php
                        echo htmlspecialchars(
                            $task["detail_text"] ?? "",
                            ENT_QUOTES
                        );
                    ?>"

                    data-created-at="<?php
                        echo date(
                            "n/j H:i",
                            strtotime(
                                $task["created_at"]
                            )
                        );
                    ?>"

                    data-due-date="<?php
                        echo htmlspecialchars(
                            $task["due_date"] ?? "",
                            ENT_QUOTES
                        );
                    ?>"

                    data-genre-id="<?php
                        echo htmlspecialchars(
                            $task["genre_id"] ?? "",
                            ENT_QUOTES
                        );
                    ?>"

                    onclick="openTaskModal(this)"
                >


                    <div class="item-body">


                        <div class="item-content">

                            <?php
                            echo htmlspecialchars(
                                $task["task_text"]
                            );
                            ?>

                        </div>


                        <div class="item-meta">


                            <span class="task-label">
                                タスク
                            </span>


                            <?php if (!empty($task["genre_name"])): ?>

                                <span class="genre-label">

                                    <?php
                                    echo htmlspecialchars(
                                        $task["genre_name"]
                                    );
                                    ?>

                                </span>

                            <?php endif; ?>


                           <span class="due-date">

    <?php if (!empty($task["due_date"])): ?>

        期限：

        <?php
        echo date(
            "n/j",
            strtotime(
                $task["due_date"]
            )
        );
        ?>

        <?php if (
            $task["due_date"] < date("Y-m-d")
        ): ?>

            <span class="overdue-label">
                期限切れ
            </span>

        <?php endif; ?>

    <?php else: ?>

        期限なし

    <?php endif; ?>

</span>


                        </div>

                    </div>


                    <div class="item-right">


                        <span class="item-date">

                            <?php
                            echo date(
                                "n/j H:i",
                                strtotime(
                                    $task["created_at"]
                                )
                            );
                            ?>

                        </span>


                        <form
                            method="post"
                            onclick="event.stopPropagation();"
                        >

                            <input
                                type="hidden"
                                name="complete_id"
                                value="<?php
                                    echo $task["id"];
                                ?>"
                            >

                            <button
                                type="submit"
                                class="small-action complete-action"
                            >
                                完了
                            </button>

                        </form>


                    </div>


                </div>


            <?php endforeach; ?>


        </section>


    </main>


    <?php include "input_form.php"; ?>


</div>


<?php include "calendar.php"; ?>



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

    <div id="modal-task-text"></div>


    <p>記録日時</p>

    <div id="modal-created-at"></div>


    <form method="post">


        <input
            type="hidden"
            name="task_edit_id"
            id="modal-edit-id"
        >


        <p>詳細</p>

        <textarea
            name="detail_text"
            id="modal-detail-text"
            placeholder="詳細を入力"
        ></textarea>


        <p>期限</p>

        <input
            type="text"
            name="due_date"
            id="modal-due-date"
            readonly
            onclick="openCalendar(this, { noneText: '期限なし' })"
        >


        <p>ジャンル</p>

        <select
            name="genre_id"
            id="modal-genre-id"
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
            id="modal-new-genre-name"
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


function toggleSearch() {

    const searchArea =
        document.getElementById(
            "search-area"
        );


    const isHidden =
        window.getComputedStyle(
            searchArea
        ).display === "none";


    searchArea.style.display =
        isHidden
        ? "block"
        : "none";
}



function openTaskModal(task) {

    document
        .getElementById(
            "modal-edit-id"
        )
        .value =
            task.dataset.id;


    document
        .getElementById(
            "modal-task-text"
        )
        .textContent =
            task.dataset.taskText;


    document
        .getElementById(
            "modal-created-at"
        )
        .textContent =
            task.dataset.createdAt;


    document
        .getElementById(
            "modal-detail-text"
        )
        .value =
            task.dataset.detailText;


    document
        .getElementById(
            "modal-due-date"
        )
        .value =
            task.dataset.dueDate;


    document
        .getElementById(
            "modal-genre-id"
        )
        .value =
            task.dataset.genreId;


    document
        .getElementById(
            "modal-new-genre-name"
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
