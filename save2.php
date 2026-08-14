<?php
if (isset($_POST["memo_button"])) {

    $sql = "INSERT INTO memos (memo_text)
            VALUES (:memo_text)";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":memo_text",
        $_POST["content"]
    );

    $stmt->execute();
}

if (isset($_POST["task_button"])) {

    $sql = "
        INSERT INTO tasks (task_text, due_date)
        VALUES (:task_text, :due_date)
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(
        ":task_text",
        $_POST["content"]
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

    $stmt->execute();
}
?>
