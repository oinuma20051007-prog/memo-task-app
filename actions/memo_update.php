<?php

/* 削除 */
if (isset($_POST["delete_id"])) {

    $sql = "
        UPDATE memos
        SET is_deleted = 1
        WHERE id = :delete_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":delete_id",
        $_POST["delete_id"],
        PDO::PARAM_INT
    );

    $stmt->execute();
}


/* ピン留め・解除 */
if (isset($_POST["pinned_id"])) {

    $sql = "
        UPDATE memos
        SET is_pinned = NOT is_pinned
        WHERE id = :pinned_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":pinned_id",
        $_POST["pinned_id"],
        PDO::PARAM_INT
    );

    $stmt->execute();
}

/* 詳細・ジャンルを保存 */
if (isset($_POST["memo_edit_id"])) {

    /*
        新しいジャンル名が入力されていたら
        genre_update.php の共通処理で
        genre_id を取得する
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
            新しいジャンル名がなければ
            選択されている既存ジャンルを使う
        */
        if (
            isset($_POST["genre_id"]) &&
            $_POST["genre_id"] !== ""
        ) {

            $genreId = $_POST["genre_id"];

        } else {

            $genreId = null;
        }
    }


    /* メモ本体を更新 */
    $sql = "
        UPDATE memos
        SET
            detail_text = :detail_text,
            genre_id = :genre_id
        WHERE id = :edit_id
    ";

    $stmt = $pdo->prepare($sql);


    /* 詳細 */
    $stmt->bindValue(
        ":detail_text",
        $_POST["detail_text"]
    );


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


    /* どのメモを更新するか */
    $stmt->bindValue(
        ":edit_id",
        $_POST["memo_edit_id"],
        PDO::PARAM_INT
    );


    $stmt->execute();
}
