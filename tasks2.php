<?php
$pdo = new PDO(
    "mysql:host=localhost;dbname=task2_app;charset=utf8mb4",
    "root",
    ""
);

include "save2.php";


/* タスクを完了する */
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


/* 詳細画面で変更した内容を保存する */
if (isset($_POST["edit_id"])) {

    $sql = "
        UPDATE tasks
        SET
            detail_text = :detail_text,
            due_date = :due_date
        WHERE id = :edit_id
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
        ":edit_id",
        $_POST["edit_id"],
        PDO::PARAM_INT
    );

    $stmt->execute();
}


/* 未完了のタスクを取得する */
$sql = "
    SELECT *
    FROM tasks
    WHERE is_completed = 0
    ORDER BY
        due_date IS NULL DESC,
        due_date ASC,
        created_at DESC
";

$stmt = $pdo->query($sql);

$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<h2>タスクリスト</h2>


<?php foreach ($tasks as $task): ?>

    <div
        class="task-item"

        data-id="<?php echo $task["id"]; ?>"

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
                strtotime($task["created_at"])
            );
        ?>"

        data-due-date="<?php
            echo htmlspecialchars(
                $task["due_date"] ?? "",
                ENT_QUOTES
            );
        ?>"

        onclick="openTaskModal(this)"
    >


        <div>
            <span>
                <?php
                echo htmlspecialchars(
                    $task["task_text"]
                );
                ?>
            </span>
        </div>


        <?php
        echo date(
            "n/j H:i",
            strtotime($task["created_at"])
        );
        ?>


        <span>
            期限：

            <?php

            if ($task["due_date"] === null) {

                echo "なし";

            } else {

                echo htmlspecialchars(
                    $task["due_date"]
                );
            }

            ?>

        </span>


        <form
            method="post"
            style="display: inline;"
            onclick="event.stopPropagation();"
        >

            <input
                type="hidden"
                name="complete_id"
                value="<?php echo $task["id"]; ?>"
            >

            <button type="submit">
                完了
            </button>

        </form>

    </div>

    <br>

<?php endforeach; ?>



<!-- タスク詳細画面 -->
<div id="task-modal" style="display: none;">


    <button
        type="button"
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
            name="edit_id"
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
            type="date"
            name="due_date"
            id="modal-due-date"
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

function openTaskModal(task) {

    document
        .getElementById("modal-edit-id")
        .value = task.dataset.id;


    document
        .getElementById("modal-task-text")
        .textContent = task.dataset.taskText;


    document
        .getElementById("modal-created-at")
        .textContent = task.dataset.createdAt;


    document
        .getElementById("modal-detail-text")
        .value = task.dataset.detailText;


    document
        .getElementById("modal-due-date")
        .value = task.dataset.dueDate;


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

