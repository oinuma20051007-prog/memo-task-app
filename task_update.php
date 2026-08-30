<?php

/* タスク完了 */
if (isset($_POST["complete_id"])) {

    $sql = "
        UPDATE tasks
        SET is_completed = 1
        WHERE id = :complete_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":complete_id",
        $_POST["complete_id"],
        PDO::PARAM_INT
    );

    $stmt->execute();
}


/* 詳細・期限・ジャンルを保存 */
if (isset($_POST["task_edit_id"])) {


    /*
        新しいジャンル名が入力されている場合
        → 共通ジャンル処理を使う
    */
    if (
        isset($_POST["new_genre_name"]) &&
        trim($_POST["new_genre_name"]) !== ""
    ) {

        $genreId = getOrCreateGenreId(
            $pdo,
            $_POST["new_genre_name"]
        );

    } else {

        /*
            新しいジャンル名がない場合
            → 選択された既存ジャンルを使う
        */
        if (
            isset($_POST["genre_id"]) &&
            $_POST["genre_id"] !== ""
        ) {

            $genreId = $_POST["genre_id"];

        } else {

            /* ジャンルなし */
            $genreId = null;
        }
    }


    /* タスク本体を更新 */
    $sql = "
        UPDATE tasks
        SET
            detail_text = :detail_text,
            due_date = :due_date,
            genre_id = :genre_id
        WHERE id = :edit_id
    ";

    $stmt = $pdo->prepare($sql);


    /* 詳細 */
    $stmt->bindValue(
        ":detail_text",
        $_POST["detail_text"]
    );


    /* 期限 */
    if ($_POST["due_date"] === "") {

        $stmt->bindValue(
            ":due_date",
            null,
            PDO::PARAM_NULL
        );

    } else {

        $stmt->bindValue(
            ":due_date",
            $_POST["due_date"]
        );
    }


    /* ジャンル */
    if ($genreId === null) {

        $stmt->bindValue(
            ":genre_id",
            null,
            PDO::PARAM_NULL
        );

    } else {

        $stmt->bindValue(
            ":genre_id",
            $genreId,
            PDO::PARAM_INT
        );
    }


    /* どのタスクを変更するか */
    $stmt->bindValue(
        ":edit_id",
        $_POST["task_edit_id"],
        PDO::PARAM_INT
    );


    $stmt->execute();
}

?>