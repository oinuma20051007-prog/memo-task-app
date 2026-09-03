<?php

/* 
    ジャンル名を受け取って、
    既存ならそのIDを返す
    なければ新しく作って、そのIDを返す
*/
function getOrCreateGenreId($pdo, $genreName) {

    /*
        半角スペース
        全角スペース
        改行
        タブ
        を前後から削除
    */
    $genreName =
        preg_replace(
            '/^[\s　]+|[\s　]+$/u',
            '',
            $genreName
        );


    /* 空ならジャンルなし */
    if ($genreName === "") {
        return null;
    }


    /* すでに同じジャンルがあるか確認 */
    $sql = "
        SELECT id
        FROM genres
        WHERE genre_name = :genre_name
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":genre_name",
        $genreName,
        PDO::PARAM_STR
    );

    $stmt->execute();

    $genre =
        $stmt->fetch(
            PDO::FETCH_ASSOC
        );


    /* 既存ジャンルなら、そのIDを返す */
    if ($genre) {

        return $genre["id"];
    }


    /* なければ新しいジャンルを追加 */
    $sql = "
        INSERT INTO genres (
            genre_name
        )
        VALUES (
            :genre_name
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":genre_name",
        $genreName,
        PDO::PARAM_STR
    );

    $stmt->execute();


    /* 今作ったジャンルのIDを返す */
    return $pdo->lastInsertId();
}



/* =========================
   ジャンル削除
========================= */

if (isset($_POST["delete_genre_id"])) {

    $genreId =
        (int) $_POST["delete_genre_id"];


    /* そのジャンルを使っているメモをジャンルなしにする */
    $sql = "
        UPDATE memos
        SET genre_id = NULL
        WHERE genre_id = :genre_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":genre_id",
        $genreId,
        PDO::PARAM_INT
    );

    $stmt->execute();


    /* そのジャンルを使っているタスクをジャンルなしにする */
    $sql = "
        UPDATE tasks
        SET genre_id = NULL
        WHERE genre_id = :genre_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":genre_id",
        $genreId,
        PDO::PARAM_INT
    );

    $stmt->execute();


    /* genresテーブルからジャンル自体を削除 */
    $sql = "
        DELETE FROM genres
        WHERE id = :genre_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":genre_id",
        $genreId,
        PDO::PARAM_INT
    );

    $stmt->execute();
}

?>
