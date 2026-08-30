 <?php

session_start();


include "db2.php";


/* =========================
   保存・更新処理
========================= */

include "genre_update2.php";
include "save2.php";
include "memo_update2.php";
include "task_update2.php";


/* =========================
   すべての記録を取得
========================= */

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

    ORDER BY created_at ASC

";


$stmt =
    $pdo->query($sql);


$items =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================
   リマインド用
========================= */

$reminderSql = "

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

    ) AS reminder_items

    ORDER BY created_at DESC

";


$reminderStmt =
    $pdo->query($reminderSql);


$allReminderItems =
    $reminderStmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================
   日付別に振り分け
========================= */

$reminders = [

    "1日前" => [],
    "3日前" => [],
    "1週間前" => [],
    "1か月前" => []

];


$today =
    new DateTime("today");


foreach ($allReminderItems as $item) {

    $created =
        new DateTime(
            date(
                "Y-m-d",
                strtotime(
                    $item["created_at"]
                )
            )
        );


    $interval =
        $created->diff(
            $today
        );


    $days =
        (int) $interval->format(
            "%r%a"
        );


    if ($days === 1) {

        $reminders["1日前"][] =
            $item;

    } elseif ($days === 3) {

        $reminders["3日前"][] =
            $item;

    } elseif ($days === 7) {

        $reminders["1週間前"][] =
            $item;

    } elseif (
        $days >= 28 &&
        $days <= 31
    ) {

        $reminders["1か月前"][] =
            $item;
    }
}


/* =========================
   各期間からランダム1件
========================= */

$randomReminders = [];


foreach (
    $reminders as $label => $group
) {

    if (count($group) > 0) {

        $randomKey =
            array_rand(
                $group
            );


        $randomReminders[$label] =
            $group[$randomKey];

    } else {

        $randomReminders[$label] =
            null;
    }
}


/* =========================
   ジャンル一覧
========================= */

$genreSql = "
    SELECT *
    FROM genres
    ORDER BY id ASC
";


$genreStmt =
    $pdo->query(
        $genreSql
    );


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

    <title>ホーム</title>

    <link
        rel="stylesheet"
        href="style2.css?v=2"
    >

</head>


<body>


<div class="page-container">


    <!-- =========================
         ヘッダー
    ========================= -->

    <header class="header">


        <div class="header-top">

            <h1>ホーム</h1>


            <button
                type="button"
                class="search-toggle-button"
                onclick="toggleSearch()"
                aria-label="検索を開閉"
            >
                🔍
            </button>

        </div>
    </header>

        <nav class="top-tabs">


            <a
                href="kioku2.php"
                class="active"
            >
                ホーム
            </a>


            <a href="memos2.php">
                メモ
            </a>


            <a href="tasks2.php">
                タスク
            </a>


            <a href="genre_manage2.php">
                ジャンル
            </a>


        </nav>


  



    <main class="main-content">


        <!-- =========================
             検索
        ========================= -->

        <section
            class="search-area"
            id="search-area"
            style="display:none;"
        >


            <div class="section-title">
                🔍 検索
            </div>


            <form
                method="get"
                action="search2.php"
                class="search-form"
            >


                <input
                    type="hidden"
                    name="type"
                    value="all"
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


                <span>
                    〜
                </span>


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
             リマインド
        ========================= -->

        <section class="reminder-area">


            <button
                type="button"
                class="reminder-header"
                onclick="toggleReminder()"
            >


                <span>
                    🔔 リマインド
                </span>


                <span id="reminder-arrow">
                    ▲
                </span>


            </button>



            <div id="reminder-content">


                <?php

                $reminderIndex = 0;

                ?>


                <?php foreach (
                    $reminders
                    as $label => $group
                ): ?>


                    <?php

                    $item =
                        $randomReminders[
                            $label
                        ];

                    $groupId =
                        "reminder-group-" .
                        $reminderIndex;

                    $randomId =
                        "reminder-random-" .
                        $reminderIndex;

                    $arrowId =
                        "reminder-group-arrow-" .
                        $reminderIndex;

                    ?>


                    <div class="reminder-group">


                        <div class="reminder-row">


                            <div
                                class="reminder-period"

                                <?php if (
                                    count($group) > 0
                                ): ?>

                                    onclick="
                                        toggleReminderGroup(
                                            '<?php
                                            echo $groupId;
                                            ?>',
                                            '<?php
                                            echo $randomId;
                                            ?>',
                                            '<?php
                                            echo $arrowId;
                                            ?>'
                                        )
                                    "

                                    style="
                                        cursor:pointer;
                                        user-select:none;
                                    "

                                <?php endif; ?>
                            >

                                <?php
                                echo $label;
                                ?>


                                <span class="reminder-count">

                                    （全<?php
                                    echo count(
                                        $group
                                    );
                                    ?>件）

                                </span>


                                <?php if (
                                    count($group) > 0
                                ): ?>

                                    <span
                                        id="<?php
                                            echo $arrowId;
                                        ?>"
                                        style="
                                            margin-left:4px;
                                        "
                                    >
                                        ▼
                                    </span>

                                <?php endif; ?>


                            </div>


                            <?php if (
                                $item !== null
                            ): ?>


                                <div
                                    id="<?php
                                        echo $randomId;
                                    ?>"

                                    class="reminder-record"

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
                                            $item["detail_text"]
                                                ?? "",
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
                                            $item["due_date"]
                                                ?? "",
                                            ENT_QUOTES
                                        );
                                    ?>"

                                    data-genre-id="<?php
                                        echo htmlspecialchars(
                                            $item["genre_id"]
                                                ?? "",
                                            ENT_QUOTES
                                        );
                                    ?>"

                                    onclick="openDetail(this)"
                                >


                                    <div class="reminder-main">

                                        <?php
                                        echo htmlspecialchars(
                                            $item["content"]
                                        );
                                        ?>

                                    </div>


                                    <div class="reminder-extra">


                                        <span
                                            class="<?php
                                                echo
                                                    $item["type"]
                                                    === "memo"
                                                    ? "memo-label"
                                                    : "task-label";
                                            ?>"
                                        >

                                            <?php
                                            echo
                                                $item["type"]
                                                === "memo"
                                                ? "メモ"
                                                : "タスク";
                                            ?>

                                        </span>


                                        <?php if (
                                            !empty(
                                                $item["genre_name"]
                                            )
                                        ): ?>


                                            <span>

                                                <?php
                                                echo htmlspecialchars(
                                                    $item["genre_name"]
                                                );
                                                ?>

                                            </span>


                                        <?php endif; ?>


                                        <span>

                                            <?php
                                            echo date(
                                                "n/j H:i",
                                                strtotime(
                                                    $item["created_at"]
                                                )
                                            );
                                            ?>

                                        </span>


                                    </div>


                                </div>


                            <?php else: ?>


                                <div
                                    id="<?php
                                        echo $randomId;
                                    ?>"
                                    class="reminder-empty"
                                >

                                    該当する記録はありません

                                </div>


                            <?php endif; ?>


                        </div>


                        <?php if (
                            count($group) > 0
                        ): ?>


                            <div
                                id="<?php
                                    echo $groupId;
                                ?>"
                                style="display:none;"
                            >


                                <?php foreach (
                                    $group
                                    as $groupItem
                                ): ?>


                                    <div class="reminder-row">


                                        <div class="reminder-period">
                                        </div>


                                        <div
                                            class="reminder-record"

                                            data-id="<?php
                                                echo $groupItem["id"];
                                            ?>"

                                            data-type="<?php
                                                echo $groupItem["type"];
                                            ?>"

                                            data-content="<?php
                                                echo htmlspecialchars(
                                                    $groupItem["content"],
                                                    ENT_QUOTES
                                                );
                                            ?>"

                                            data-detail-text="<?php
                                                echo htmlspecialchars(
                                                    $groupItem["detail_text"]
                                                        ?? "",
                                                    ENT_QUOTES
                                                );
                                            ?>"

                                            data-created-at="<?php
                                                echo date(
                                                    "n/j H:i",
                                                    strtotime(
                                                        $groupItem[
                                                            "created_at"
                                                        ]
                                                    )
                                                );
                                            ?>"

                                            data-due-date="<?php
                                                echo htmlspecialchars(
                                                    $groupItem["due_date"]
                                                        ?? "",
                                                    ENT_QUOTES
                                                );
                                            ?>"

                                            data-genre-id="<?php
                                                echo htmlspecialchars(
                                                    $groupItem["genre_id"]
                                                        ?? "",
                                                    ENT_QUOTES
                                                );
                                            ?>"

                                            onclick="openDetail(this)"
                                        >


                                            <div class="reminder-main">

                                                <?php
                                                echo htmlspecialchars(
                                                    $groupItem["content"]
                                                );
                                                ?>

                                            </div>


                                            <div class="reminder-extra">


                                                <span
                                                    class="<?php
                                                        echo
                                                            $groupItem["type"]
                                                            === "memo"
                                                            ? "memo-label"
                                                            : "task-label";
                                                    ?>"
                                                >

                                                    <?php
                                                    echo
                                                        $groupItem["type"]
                                                        === "memo"
                                                        ? "メモ"
                                                        : "タスク";
                                                    ?>

                                                </span>


                                                <?php if (
                                                    !empty(
                                                        $groupItem[
                                                            "genre_name"
                                                        ]
                                                    )
                                                ): ?>


                                                    <span>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $groupItem[
                                                                "genre_name"
                                                            ]
                                                        );
                                                        ?>

                                                    </span>


                                                <?php endif; ?>


                                                <?php if (
                                                    $groupItem["type"]
                                                        === "task"
                                                ): ?>


                                                    <span>

                                                        <?php if (
                                                            !empty(
                                                                $groupItem[
                                                                    "due_date"
                                                                ]
                                                            )
                                                        ): ?>

                                                            期限：
                                                            <?php
                                                            echo date(
                                                                "n/j",
                                                                strtotime(
                                                                    $groupItem[
                                                                        "due_date"
                                                                    ]
                                                                )
                                                            );
                                                            ?>

                                                        <?php else: ?>

                                                            期限なし

                                                        <?php endif; ?>

                                                    </span>


                                                <?php endif; ?>


                                                <span>

                                                    <?php
                                                    echo date(
                                                        "n/j H:i",
                                                        strtotime(
                                                            $groupItem[
                                                                "created_at"
                                                            ]
                                                        )
                                                    );
                                                    ?>

                                                </span>


                                            </div>


                                        </div>


                                    </div>


                                <?php endforeach; ?>


                            </div>


                        <?php endif; ?>


                    </div>


                    <?php

                    $reminderIndex++;

                    ?>


                <?php endforeach; ?>


            </div>


        </section>



        <!-- =========================
             すべての記録
        ========================= -->

        <section class="records-area">


            <div class="section-title">

                すべての記録

            </div>


            <?php if (empty($items)): ?>


                <p class="empty-message">

                    記録はありません。

                </p>


            <?php endif; ?>



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
                            $item["detail_text"]
                                ?? "",
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
                            $item["due_date"]
                                ?? "",
                            ENT_QUOTES
                        );
                    ?>"

                    data-genre-id="<?php
                        echo htmlspecialchars(
                            $item["genre_id"]
                                ?? "",
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
                                        $item["type"]
                                        === "memo"
                                        ? "memo-label"
                                        : "task-label";
                                ?>"
                            >

                                <?php
                                echo
                                    $item["type"]
                                    === "memo"
                                    ? "メモ"
                                    : "タスク";
                                ?>

                            </span>


                            <?php if (
                                !empty(
                                    $item["genre_name"]
                                )
                            ): ?>


                                <span class="genre-label">

                                    <?php
                                    echo htmlspecialchars(
                                        $item["genre_name"]
                                    );
                                    ?>

                                </span>


                            <?php endif; ?>


                            <!-- タスクの期限 -->
                            <?php if (
                                $item["type"] === "task"
                            ): ?>


                                <span class="due-date">


                                    <?php if (
                                        !empty(
                                            $item["due_date"]
                                        )
                                    ): ?>


                                        期限：

                                        <?php
                                        echo date(
                                            "n/j",
                                            strtotime(
                                                $item["due_date"]
                                            )
                                        );
                                        ?>


                                        <?php if (
                                            $item["due_date"]
                                            <
                                            date("Y-m-d")
                                        ): ?>


                                            <span class="overdue-label">
                                                期限切れ
                                            </span>


                                        <?php endif; ?>


                                    <?php else: ?>


                                        期限なし


                                    <?php endif; ?>


                                </span>


                            <?php endif; ?>


                        </div>


                    </div>



                    <div class="item-right">


                        <!-- 右側は日時だけ -->
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


                        <?php if (
                            $item["type"] === "memo"
                        ): ?>


                            <form
                                method="post"
                                onclick="
                                    event.stopPropagation();
                                "
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
                                onclick="
                                    event.stopPropagation();
                                "
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


        </section>


    </main>



    <?php
    include "calendar2.php";
    ?>


    <?php
    include "input_form2.php";
    ?>


</div>



<!-- =========================
     メモ詳細
========================= -->

<div
    id="memo-modal"
    class="detail-modal"
    style="display:none;"
>


    <button
        type="button"
        class="close-button"
        onclick="closeMemoModal()"
    >
        ×
    </button>


    <h2>
        メモ詳細
    </h2>


    <p>
        内容
    </p>


    <div id="memo-content">
    </div>


    <p>
        記録日時
    </p>


    <div id="memo-created-at">
    </div>


    <form method="post">


        <input
            type="hidden"
            name="memo_edit_id"
            id="memo-edit-id"
        >


        <p>
            詳細
        </p>


        <textarea
            name="detail_text"
            id="memo-detail-text"
        ></textarea>


        <p>
            ジャンル
        </p>


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


        <p>
            新しいジャンル
        </p>


        <input
            type="text"
            name="new_genre_name"
            id="memo-new-genre-name"
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
    style="display:none;"
>


    <button
        type="button"
        class="close-button"
        onclick="closeTaskModal()"
    >
        ×
    </button>


    <h2>
        タスク詳細
    </h2>


    <p>
        内容
    </p>


    <div id="task-content">
    </div>


    <p>
        記録日時
    </p>


    <div id="task-created-at">
    </div>


    <form method="post">


        <input
            type="hidden"
            name="task_edit_id"
            id="task-edit-id"
        >


        <p>
            詳細
        </p>


        <textarea
            name="detail_text"
            id="task-detail-text"
        ></textarea>


        <p>
            期限
        </p>


        <input
            type="text"
            name="due_date"
            id="task-due-date"
            readonly
            onclick="
                openCalendar(
                    this,
                    {
                        noneText: '期限なし'
                    }
                )
            "
        >


        <p>
            ジャンル
        </p>


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


        <p>
            新しいジャンル
        </p>


        <input
            type="text"
            name="new_genre_name"
            id="task-new-genre-name"
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
   検索開閉
========================= */

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



/* =========================
   リマインド開閉
========================= */

let reminderCompact =
    false;


function toggleReminder() {

    const content =
        document.getElementById(
            "reminder-content"
        );


    const arrow =
        document.getElementById(
            "reminder-arrow"
        );


    reminderCompact =
        !reminderCompact;


    if (reminderCompact) {

        content.classList.add(
            "compact"
        );


        arrow.textContent =
            "▼";

    } else {

        content.classList.remove(
            "compact"
        );


        arrow.textContent =
            "▲";
    }
}




/* =========================
   リマインド期間ごとの全件表示
========================= */

function toggleReminderGroup(
    groupId,
    randomId,
    arrowId
) {

    const group =
        document.getElementById(
            groupId
        );

    const randomItem =
        document.getElementById(
            randomId
        );

    const arrow =
        document.getElementById(
            arrowId
        );


    const isHidden =
        window.getComputedStyle(
            group
        ).display === "none";


    if (isHidden) {

        group.style.display =
            "block";

        if (randomItem) {

            randomItem.style.display =
                "none";
        }

        if (arrow) {

            arrow.textContent =
                "▲";
        }

    } else {

        group.style.display =
            "none";

        if (randomItem) {

            randomItem.style.display =
                "";
        }

        if (arrow) {

            arrow.textContent =
                "▼";
        }
    }
}



/* =========================
   詳細画面判定
========================= */

function openDetail(item) {

    if (
        item.dataset.type ===
        "memo"
    ) {

        openMemoModal(
            item
        );

    } else {

        openTaskModal(
            item
        );
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