<?php

/* =========================
   ジャンルIDを決める
========================= */

$genre_id = null;


/*
    「＋ 新規ジャンル」が選択された場合
*/
if (
    isset($_POST["genre_id"]) &&
    $_POST["genre_id"] === "new"
) {

    $newGenreName =
        isset($_POST["new_genre_name"])
            ? preg_replace(
                '/^[\s　]+|[\s　]+$/u',
                '',
                $_POST["new_genre_name"]
            )
            : "";


    /*
        新しいジャンル名が入力されていれば
        既存ジャンルを探す
        → なければ新規作成
    */
    if ($newGenreName !== "") {

        $genre_id =
            getOrCreateGenreId(
                $pdo,
                $newGenreName
            );
    }


/*
    既存ジャンルが選択された場合
*/
} elseif (
    isset($_POST["genre_id"]) &&
    $_POST["genre_id"] !== ""
) {

    $genre_id =
        (int) $_POST["genre_id"];
}


/*
    何も選択されていない場合
    → $genre_id は null のまま
*/



/* =========================
   内容を整える
   半角・全角スペースだけなら空文字
========================= */

$content =
    isset($_POST["content"])
        ? preg_replace(
            '/^[\s　]+|[\s　]+$/u',
            '',
            $_POST["content"]
        )
        : "";



/* =========================
   メモ保存
========================= */

if (isset($_POST["memo_button"])) {


    /*
        空欄・スペースだけの場合は
        保存しない
    */
    if ($content === "") {
        return;
    }


    $sql = "
        INSERT INTO memos (
            memo_text,
            genre_id
        )
        VALUES (
            :memo_text,
            :genre_id
        )
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->bindValue(
        ":memo_text",
        $content,
        PDO::PARAM_STR
    );


    /* ジャンル */

    if ($genre_id === null) {

        $stmt->bindValue(
            ":genre_id",
            null,
            PDO::PARAM_NULL
        );

    } else {

        $stmt->bindValue(
            ":genre_id",
            $genre_id,
            PDO::PARAM_INT
        );
    }


    $stmt->execute();
}



/* =========================
   タスク保存
========================= */

if (isset($_POST["task_button"])) {


    /*
        空欄・スペースだけの場合は
        保存しない
    */
    if ($content === "") {
        return;
    }


    $sql = "
        INSERT INTO tasks (
            task_text,
            due_date,
            genre_id
        )
        VALUES (
            :task_text,
            :due_date,
            :genre_id
        )
    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->bindValue(
        ":task_text",
        $content,
        PDO::PARAM_STR
    );


    /* =========================
       期限
    ========================= */

    if (
        !isset($_POST["due_date"]) ||
        $_POST["due_date"] === ""
    ) {

        $stmt->bindValue(
            ":due_date",
            null,
            PDO::PARAM_NULL
        );

    } else {

        $stmt->bindValue(
            ":due_date",
            $_POST["due_date"],
            PDO::PARAM_STR
        );
    }


    /* =========================
       ジャンル
    ========================= */

    if ($genre_id === null) {

        $stmt->bindValue(
            ":genre_id",
            null,
            PDO::PARAM_NULL
        );

    } else {

        $stmt->bindValue(
            ":genre_id",
            $genre_id,
            PDO::PARAM_INT
        );
    }


    $stmt->execute();
}

?>
