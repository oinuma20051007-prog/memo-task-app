<?php

$pdo = new PDO(
    "mysql:host=localhost;dbname=task2_app;charset=utf8mb4",
    "root",
    ""
);

include "save2.php";


/* メモ削除 */
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


/* メモ詳細を保存 */
if (isset($_POST["memo_edit_id"])) {

    $sql = "
        UPDATE memos
        SET detail_text = :detail_text
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":detail_text",
        $_POST["detail_text"]
    );

    $stmt->bindValue(
        ":id",
        $_POST["memo_edit_id"],
        PDO::PARAM_INT
    );

    $stmt->execute();
}


/* タスク詳細・期限を保存 */
if (isset($_POST["task_edit_id"])) {

    $sql = "
        UPDATE tasks
        SET
            detail_text = :detail_text,
            due_date = :due_date
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":detail_text",
        $_POST["detail_text"]
    );


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


    $stmt->bindValue(
        ":id",
        $_POST["task_edit_id"],
        PDO::PARAM_INT
    );

    $stmt->execute();
}


/* メモとタスクをまとめて取得 */
$sql = "

    SELECT
        id,
        memo_text AS content,
        detail_text,
        created_at,
        NULL AS due_date,
        is_pinned,
        'memo' AS type

    FROM memos

    WHERE is_deleted = 0


    UNION ALL


    SELECT
        id,
        task_text AS content,
        detail_text,
        created_at,
        due_date,
        NULL AS is_pinned,
        'task' AS type

    FROM tasks

    WHERE is_completed = 0


    ORDER BY created_at DESC
";


$stmt = $pdo->query($sql);

$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<h1>全情報</h1>


<?php foreach ($items as $item): ?>

    <div
        class="item"

        data-id="<?php echo $item["id"]; ?>"

        data-type="<?php echo $item["type"]; ?>"

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
                strtotime($item["created_at"])
            );
        ?>"

        data-due-date="<?php
            echo htmlspecialchars(
                $item["due_date"] ?? "",
                ENT_QUOTES
            );
        ?>"

        onclick="openDetail(this)"
    >


        <div>
            <?php
            echo htmlspecialchars(
                $item["content"]
            );
            ?>
        </div>


        <?php
        echo date(
            "n/j H:i",
            strtotime($item["created_at"])
        );
        ?>


        <?php if ($item["type"] === "memo"): ?>

            <span>
                メモ
            </span>

        <?php else: ?>

            <span>
                タスク
            </span>

        <?php endif; ?>


        <?php if ($item["type"] === "memo"): ?>


            <form
                method="post"
                style="display: inline;"
                onclick="event.stopPropagation();"
            >

                <input
                    type="hidden"
                    name="delete_id"
                    value="<?php echo $item["id"]; ?>"
                >

                <button type="submit">
                    削除
                </button>

            </form>


            


        <?php else: ?>


            <form
                method="post"
                style="display: inline;"
                onclick="event.stopPropagation();"
            >

                <input
                    type="hidden"
                    name="complete_id"
                    value="<?php echo $item["id"]; ?>"
                >

                <button type="submit">
                    完了
                </button>

            </form>


        <?php endif; ?>


    </div>

    <br>

<?php endforeach; ?>



<!-- メモ詳細 -->
<div
    id="memo-modal"
    style="display: none;"
>

    <button
        type="button"
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


        <br><br>


        <button type="submit">
            保存
        </button>

    </form>

</div>



<!-- タスク詳細 -->
<div
    id="task-modal"
    style="display: none;"
>

    <button
        type="button"
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
            type="date"
            name="due_date"
            id="task-due-date"
        >


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

function openDetail(item) {

    if (item.dataset.type === "memo") {

        openMemoModal(item);

    } else {

        openTaskModal(item);
    }
}



function openMemoModal(item) {

    document
        .getElementById("memo-edit-id")
        .value = item.dataset.id;


    document
        .getElementById("memo-content")
        .textContent = item.dataset.content;


    document
        .getElementById("memo-created-at")
        .textContent = item.dataset.createdAt;


    document
        .getElementById("memo-detail-text")
        .value = item.dataset.detailText;


    document
        .getElementById("memo-modal")
        .style.display = "block";
}



function closeMemoModal() {

    document
        .getElementById("memo-modal")
        .style.display = "none";
}



function openTaskModal(item) {

    document
        .getElementById("task-edit-id")
        .value = item.dataset.id;


    document
        .getElementById("task-content")
        .textContent = item.dataset.content;


    document
        .getElementById("task-created-at")
        .textContent = item.dataset.createdAt;


    document
        .getElementById("task-detail-text")
        .value = item.dataset.detailText;


    document
        .getElementById("task-due-date")
        .value = item.dataset.dueDate;


    document
        .getElementById("task-modal")
        .style.display = "block";
}



function closeTaskModal() {

    document
        .getElementById("task-modal")
        .style.display = "none";
}

</script>