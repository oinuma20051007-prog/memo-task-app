<?php

require_once __DIR__ . "/config/db.php";


/* =========================
   ジャンル名変更
========================= */

if (
    isset($_POST["rename_genre_id"]) &&
    isset($_POST["genre_name"])
) {

    $genre_id =
        (int) $_POST["rename_genre_id"];

    $genre_name =
        trim($_POST["genre_name"]);


    if ($genre_name !== "") {

        $sql = "
            UPDATE genres
            SET genre_name = :genre_name
            WHERE id = :genre_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(
            ":genre_name",
            $genre_name,
            PDO::PARAM_STR
        );

        $stmt->bindValue(
            ":genre_id",
            $genre_id,
            PDO::PARAM_INT
        );

        $stmt->execute();
    }
}


/* =========================
   ジャンル削除
========================= */

if (isset($_POST["delete_genre_id"])) {

    $genre_id =
        (int) $_POST["delete_genre_id"];


    /*
        このジャンルを使っている
        メモ・タスクを
        「ジャンルなし」に戻してから削除
    */

    $pdo->beginTransaction();


    try {

        /* メモ */
        $sql = "
            UPDATE memos
            SET genre_id = NULL
            WHERE genre_id = :genre_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(
            ":genre_id",
            $genre_id,
            PDO::PARAM_INT
        );

        $stmt->execute();


        /* タスク */
        $sql = "
            UPDATE tasks
            SET genre_id = NULL
            WHERE genre_id = :genre_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(
            ":genre_id",
            $genre_id,
            PDO::PARAM_INT
        );

        $stmt->execute();


        /* ジャンル自体を削除 */
        $sql = "
            DELETE FROM genres
            WHERE id = :genre_id
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(
            ":genre_id",
            $genre_id,
            PDO::PARAM_INT
        );

        $stmt->execute();


        $pdo->commit();


    } catch (Exception $e) {

        $pdo->rollBack();

        throw $e;
    }
}


/* =========================
   ジャンル一覧
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

    <title>ジャンル</title>

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

            <h1>
                ジャンル
            </h1>

        </div>


        <!-- =========================
             上部タブ
        ========================= -->

        <nav class="top-tabs">


            <a href="index.php">
                ホーム
            </a>


            <a href="memos.php">
                メモ
            </a>


            <a href="tasks.php">
                タスク
            </a>


            <a
                href="genre_manage.php"
                class="active"
            >
                ジャンル
            </a>


        </nav>


    </header>



    <main class="main-content">


        <!-- =========================
             ジャンル一覧
        ========================= -->

        <section class="records-area">


            <div class="section-title">
                ジャンル管理
            </div>


            <?php if (empty($genres)): ?>


                <p class="empty-message">
                    登録されているジャンルはありません。
                </p>


            <?php endif; ?>



            <?php foreach ($genres as $genre): ?>


                <div class="genre-manage-item">


                    <!-- =========================
                         名前変更
                    ========================= -->

                    <form
                        method="post"
                        class="genre-rename-form"
                    >


                        <input
                            type="hidden"
                            name="rename_genre_id"
                            value="<?php
                                echo $genre["id"];
                            ?>"
                        >


                        <input
                            type="text"
                            name="genre_name"
                            value="<?php
                                echo htmlspecialchars(
                                    $genre["genre_name"]
                                );
                            ?>"
                            required
                        >


                        <button
                            type="submit"
                            class="genre-change-button"
                        >
                            変更
                        </button>


                    </form>



                    <!-- =========================
                         削除
                    ========================= -->

                    <form
                        method="post"
                        class="genre-delete-form"
                        onsubmit="
                            return confirm(
                                'このジャンルを削除しますか？'
                            );
                        "
                    >


                        <input
                            type="hidden"
                            name="delete_genre_id"
                            value="<?php
                                echo $genre["id"];
                            ?>"
                        >


                        <button
                            type="submit"
                            class="genre-delete-button"
                        >
                            削除
                        </button>


                    </form>


                </div>


            <?php endforeach; ?>


        </section>


    </main>


</div>


</body>

</html>
