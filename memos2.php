<?php
$pdo = new PDO(
    "mysql:host=localhost;dbname=task2_app;charset=utf8mb4",
    "root",
    ""
);

include "save2.php";


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


/* 詳細を保存 */
if (isset($_POST["edit_id"])) {

    $sql = "
        UPDATE memos
        SET detail_text = :detail_text
        WHERE id = :edit_id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":detail_text",
        $_POST["detail_text"]
    );

    $stmt->bindValue(
        ":edit_id",
        $_POST["edit_id"],
        PDO::PARAM_INT
    );

    $stmt->execute();
}


/* メモを取得 */
$sql = "
    SELECT *
    FROM memos
    WHERE memo_text IS NOT NULL
    AND is_deleted = 0
    ORDER BY
        is_pinned DESC,
        created_at ASC
";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$memos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<h1>メモリスト</h1>


<?php foreach ($memos as $memo): ?>

    <div
        class="memo-item"

        data-id="<?php echo $memo["id"]; ?>"

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
            echo htmlspecialchars(
                $memo["created_at"],
                ENT_QUOTES
            );
        ?>"

        onclick="openMemoModal(this)"
    >


        <div>
            <?php
            echo htmlspecialchars(
                $memo["memo_text"]
            );
            ?>
        </div>


        記録日時：

        <?php
        echo htmlspecialchars(
            $memo["created_at"]
        );
        ?>


        <!-- 削除 -->
        <form
            method="post"
            style="display: inline;"
            onclick="event.stopPropagation();"
        >

            <input
                type="hidden"
                name="delete_id"
                value="<?php echo $memo["id"]; ?>"
            >

            <button type="submit">
                削除
            </button>

        </form>


        <!-- ピン留め -->
        <form
            method="post"
            style="display: inline;"
            onclick="event.stopPropagation();"
        >

            <input
                type="hidden"
                name="pinned_id"
                value="<?php echo $memo["id"]; ?>"
            >

            <button type="submit">

                <?php

                if ($memo["is_pinned"] == 1) {

                    echo "ピン解除";

                } else {

                    echo "ピン留め";
                }

                ?>

            </button>

        </form>


    </div>

    <br>

<?php endforeach; ?>



<!-- メモ詳細 -->
<div id="memo-modal" style="display: none;">

    <button
        type="button"
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
            name="edit_id"
            id="modal-edit-id"
        >


        <p>詳細</p>

        <textarea
            name="detail_text"
            id="modal-detail-text"
            placeholder="詳細を入力"
        ></textarea>


        <br><br>


        <button type="submit">
            保存
        </button>

    </form>

</div>



<nav>
    <a href="zen2.php">全情報</a>
    <a href="memos2.php">メモ</a>
    <a href="tasks2.php">タスク</a>
    <a href="kioku2.php">リマインド</a>
</nav>


<?php include "input_form2.php"; ?>



<script>

function openMemoModal(memo) {

    document
        .getElementById("modal-edit-id")
        .value = memo.dataset.id;


    document
        .getElementById("modal-memo-text")
        .textContent = memo.dataset.memoText;


    document
        .getElementById("modal-created-at")
        .textContent = memo.dataset.createdAt;


    document
        .getElementById("modal-detail-text")
        .value = memo.dataset.detailText;


    document
        .getElementById("memo-modal")
        .style.display = "block";
}


function closeMemoModal() {

    document
        .getElementById("memo-modal")
        .style.display = "none";
}

</script>