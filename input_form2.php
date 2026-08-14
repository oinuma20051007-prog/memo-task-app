
<form method="post">
    <textarea
        name="content"
        placeholder="内容を入力"
        required
    ></textarea>

    <button type="submit" name="memo_button" >
        送信
    </button>

    <button type="button" id="open-task-button">
        タスク
    </button>

    <div id="task-area" hidden>
        <label for="due-date">
            期限を入力（未選択でも送信できます）
        </label>

        <input
            type="date"
            id="due-date"
            name="due_date"
        >

        <button type="submit" name="task_button" >
            タスクとして送信
        </button>
    </div>
</form>

<script>
const openTaskButton =
    document.getElementById("open-task-button");

const taskArea =
    document.getElementById("task-area");

const dueDate =
    document.getElementById("due-date");

openTaskButton.addEventListener("click", function () {
    taskArea.hidden = false;

    if (dueDate.showPicker) {
        dueDate.showPicker();
    } else {
        dueDate.focus();
    }
});
</script>