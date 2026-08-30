<?php

include "db.php";


/* 保存・更新処理 */
include "genre_update.php";
include "save.php";
include "memo_update.php";


/* =========================
   ピン留めメモ一覧を取得
========================= */

$sql = "
    SELECT
        memos.*,
        genres.genre_name
    FROM memos
    LEFT JOIN genres
        ON memos.genre_id = genres.id
    WHERE memos.memo_text IS NOT NULL
        AND memos.is_deleted = 0
        AND memos.is_pinned = 1
    ORDER BY
        memos.created_at ASC
";

$stmt = $pdo->query($sql);

$pinnedMemos =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================
   通常メモ一覧を取得
========================= */

$sql = "
    SELECT
        memos.*,
        genres.genre_name
    FROM memos
    LEFT JOIN genres
        ON memos.genre_id = genres.id
    WHERE memos.memo_text IS NOT NULL
        AND memos.is_deleted = 0
        AND memos.is_pinned = 0
    ORDER BY
        memos.created_at ASC
";

$stmt = $pdo->query($sql);

$memos =
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

$stmt = $pdo->query($sql);

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

    <title>メモ</title>

    <link
        rel="stylesheet"
        href="style.css"
    >

</head>


<body>


<div class="page-container">


    <!-- =========================
         ヘッダー
    ========================= -->

    <header class="header">

        <div class="header-top">

            <h1>メモ</h1>

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

            <a
                href="memos.php"
                class="active"
            >
                メモ
            </a>

            <a href="tasks.php">
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
                🔍 メモを検索
            </div>


            <form
                method="get"
                action="search.php"
                class="search-form"
            >

                <input
                    type="hidden"
                    name="type"
                    value="memo"
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
             ピン留め
        ========================= -->

        <section class="reminder-area">

            <div class="reminder-header">

                <span>
                    📌 ピン留め
                </span>

            </div>


            <div id="pinned-content">


                <?php if (empty($pinnedMemos)): ?>


                    <div class="reminder-row">

                        <div class="reminder-empty">
                            ピン留めされたメモはありません
                        </div>

                    </div>


                <?php else: ?>


                    <?php foreach ($pinnedMemos as $memo): ?>


                        <div
                            class="reminder-row"

                            data-id="<?php
                                echo $memo["id"];
                            ?>"

                            data-memo-text="<?php
                                echo htmlspecialchars(
                                    $memo["memo_text"],
                                    ENT_QUOTES
                                );
                            ?>"

                            data-detail-text="<?php
                                echo htmlspecialchars(
                                    $memo["detail_text"] ?? "",
                                    ENT_QUOTES
                                );
                            ?>"

                            data-created-at="<?php
                                echo date(
                                    "n/j H:i",
                                    strtotime(
                                        $memo["created_at"]
                                    )
                                );
                            ?>"

                            data-genre-id="<?php
                                echo htmlspecialchars(
                                    $memo["genre_id"] ?? "",
                                    ENT_QUOTES
                                );
                            ?>"

                            onclick="openMemoModal(this)"
                        >


                            <div class="item-body">


                                <div class="item-content">

                                    <?php
                                    echo htmlspecialchars(
                                        $memo["memo_text"]
                                    );
                                    ?>

                                </div>


                                <div class="item-meta">


                                    <span class="memo-label">
                                        メモ
                                    </span>


                                    <?php if (!empty($memo["genre_name"])): ?>

                                        <span class="genre-label">

                                            <?php
                                            echo htmlspecialchars(
                                                $memo["genre_name"]
                                            );
                                            ?>

                                        </span>

                                    <?php endif; ?>


                                    <span class="pin-label">
                                        📌 ピン留め
                                    </span>


                                </div>

                            </div>


                            <div class="item-right">


                                <span class="item-date">

                                    <?php
                                    echo date(
                                        "n/j H:i",
                                        strtotime(
                                            $memo["created_at"]
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
                                        name="pinned_id"
                                        value="<?php
                                            echo $memo["id"];
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="small-action"
                                    >
                                        ピン解除
                                    </button>

                                </form>


                                <form
                                    method="post"
                                    onclick="event.stopPropagation();"
                                >

                                    <input
                                        type="hidden"
                                        name="delete_id"
                                        value="<?php
                                            echo $memo["id"];
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="small-action"
                                    >
                                        削除
                                    </button>

                                </form>


                            </div>

                        </div>


                    <?php endforeach; ?>


                <?php endif; ?>


            </div>

        </section>



        <!-- =========================
             メモ一覧
        ========================= -->

        <section class="records-area">


            <div class="section-title">
                メモ一覧
            </div>


            <?php if (empty($memos)): ?>

                <p class="empty-message">
                    メモはありません。
                </p>

            <?php endif; ?>


            <?php foreach ($memos as $memo): ?>


                <div
                    class="item memo-item"

                    data-id="<?php
                        echo $memo["id"];
                    ?>"

                    data-memo-text="<?php
                        echo htmlspecialchars(
                            $memo["memo_text"],
                            ENT_QUOTES
                        );
                    ?>"

                    data-detail-text="<?php
                        echo htmlspecialchars(
                            $memo["detail_text"] ?? "",
                            ENT_QUOTES
                        );
                    ?>"

                    data-created-at="<?php
                        echo date(
                            "n/j H:i",
                            strtotime(
                                $memo["created_at"]
                            )
                        );
                    ?>"

                    data-genre-id="<?php
                        echo htmlspecialchars(
                            $memo["genre_id"] ?? "",
                            ENT_QUOTES
                        );
                    ?>"

                    onclick="openMemoModal(this)"
                >


                    <div class="item-body">


                        <div class="item-content">

                            <?php
                            echo htmlspecialchars(
                                $memo["memo_text"]
                            );
                            ?>

                        </div>


                        <div class="item-meta">


                            <span class="memo-label">
                                メモ
                            </span>


                            <?php if (!empty($memo["genre_name"])): ?>

                                <span class="genre-label">

                                    <?php
                                    echo htmlspecialchars(
                                        $memo["genre_name"]
                                    );
                                    ?>

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
                                    $memo["created_at"]
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
                                name="pinned_id"
                                value="<?php
                                    echo $memo["id"];
                                ?>"
                            >

                            <button
                                type="submit"
                                class="small-action"
                            >
                                ピン
                            </button>

                        </form>


                        <form
                            method="post"
                            onclick="event.stopPropagation();"
                        >

                            <input
                                type="hidden"
                                name="delete_id"
                                value="<?php
                                    echo $memo["id"];
                                ?>"
                            >

                            <button
                                type="submit"
                                class="small-action"
                            >
                                削除
                            </button>

                        </form>


                    </div>

                </div>


            <?php endforeach; ?>


        </section>


    </main>



    <?php include "calendar.php"; ?>

    <?php include "input_form.php"; ?>


</div>



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

    <div id="modal-memo-text"></div>


    <p>記録日時</p>

    <div id="modal-created-at"></div>


    <form method="post">


        <input
            type="hidden"
            name="memo_edit_id"
            id="modal-edit-id"
        >


        <p>詳細</p>


        <textarea
            name="detail_text"
            id="modal-detail-text"
            placeholder="詳細を入力"
        ></textarea>


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
   メモ詳細
========================= */

function openMemoModal(memo) {

    document
        .getElementById(
            "modal-edit-id"
        )
        .value =
            memo.dataset.id;


    document
        .getElementById(
            "modal-memo-text"
        )
        .textContent =
            memo.dataset.memoText;


    document
        .getElementById(
            "modal-created-at"
        )
        .textContent =
            memo.dataset.createdAt;


    document
        .getElementById(
            "modal-detail-text"
        )
        .value =
            memo.dataset.detailText;


    document
        .getElementById(
            "modal-genre-id"
        )
        .value =
            memo.dataset.genreId;


    document
        .getElementById(
            "modal-new-genre-name"
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


</script>


</body>
</html>