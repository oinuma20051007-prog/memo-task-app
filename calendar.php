<!-- =========================
     共通カレンダー
========================= -->

<div
    id="calendar-modal"
    class="calendar-modal"
    style="display:none;"
>

    <div class="calendar-box">

        <div class="calendar-top">

            <button
                type="button"
                id="calendar-prev"
                class="calendar-arrow"
            >
                ‹
            </button>

            <div
                id="calendar-title"
                class="calendar-title"
            ></div>

            <button
                type="button"
                id="calendar-next"
                class="calendar-arrow"
            >
                ›
            </button>

        </div>


        <div class="calendar-week">

            <span>日</span>
            <span>月</span>
            <span>火</span>
            <span>水</span>
            <span>木</span>
            <span>金</span>
            <span>土</span>

        </div>


        <div
            id="calendar-days"
            class="calendar-days"
        ></div>


        <div class="calendar-actions">

            <button
                type="button"
                id="calendar-none"
                class="calendar-sub-button"
            >
                未選択
            </button>

            <button
                type="button"
                id="calendar-cancel"
                class="calendar-sub-button"
            >
                キャンセル
            </button>

            <button
                type="button"
                id="calendar-confirm"
                class="calendar-confirm-button"
            >
                決定
            </button>

        </div>

    </div>

</div>


<script>

const calendarModal =
    document.getElementById(
        "calendar-modal"
    );

const calendarTitle =
    document.getElementById(
        "calendar-title"
    );

const calendarDays =
    document.getElementById(
        "calendar-days"
    );

const calendarNone =
    document.getElementById(
        "calendar-none"
    );


let currentDate =
    new Date();

let selectedDate =
    null;


/*
    現在どのinputを
    カレンダーで変更しているか
*/
let calendarTarget =
    null;


/*
    カレンダーを閉じたあとに
    実行する処理
*/
let calendarCallback =
    null;



/* =========================
   カレンダーを開く
========================= */

function openCalendar(
    target,
    options = {}
) {

    /*
        動作確認用
        期限を押したときに
        このアラートが出るか確認
    */
   


    calendarTarget =
        typeof target === "string"
        ? document.getElementById(
            target
        )
        : target;


    if (!calendarTarget) {

        alert(
            "カレンダー対象が見つかりません"
        );

        return;
    }


    calendarCallback =
        options.onSelect ?? null;


    /*
        「期限なし」など
        ボタンの文字を変更可能
    */
    calendarNone.textContent =
        options.noneText ??
        "未選択";


    /*
        すでに日付が入っていたら
        その日を最初から選択
    */
    if (calendarTarget.value) {

        const parts =
            calendarTarget.value.split(
                "-"
            );


        if (parts.length === 3) {

            selectedDate =
                new Date(
                    Number(
                        parts[0]
                    ),
                    Number(
                        parts[1]
                    ) - 1,
                    Number(
                        parts[2]
                    )
                );


            currentDate =
                new Date(
                    selectedDate.getFullYear(),
                    selectedDate.getMonth(),
                    1
                );

        } else {

            selectedDate =
                null;

            currentDate =
                new Date();
        }

    } else {

        selectedDate =
            null;

        currentDate =
            new Date();
    }


    renderCalendar();


    calendarModal.style.display =
        "flex";
}



/* =========================
   カレンダー表示
========================= */

function renderCalendar() {

    calendarDays.innerHTML =
        "";


    const year =
        currentDate.getFullYear();

    const month =
        currentDate.getMonth();


    calendarTitle.textContent =
        year +
        "年" +
        (month + 1) +
        "月";


    const firstDay =
        new Date(
            year,
            month,
            1
        ).getDay();


    const lastDate =
        new Date(
            year,
            month + 1,
            0
        ).getDate();



    /* 月初までの空白 */

    for (
        let i = 0;
        i < firstDay;
        i++
    ) {

        const blank =
            document.createElement(
                "div"
            );

        blank.className =
            "calendar-empty";

        calendarDays.appendChild(
            blank
        );
    }



    const today =
        new Date();



    /* 日付 */

    for (
        let day = 1;
        day <= lastDate;
        day++
    ) {

        const button =
            document.createElement(
                "button"
            );


        button.type =
            "button";

        button.className =
            "calendar-day";

        button.textContent =
            day;


        const dateValue =
            new Date(
                year,
                month,
                day
            );


        /* 今日 */

        if (
            dateValue.getFullYear() ===
                today.getFullYear() &&
            dateValue.getMonth() ===
                today.getMonth() &&
            dateValue.getDate() ===
                today.getDate()
        ) {

            button.classList.add(
                "today"
            );
        }


        /* 選択中 */

        if (
            selectedDate &&
            dateValue.getFullYear() ===
                selectedDate.getFullYear() &&
            dateValue.getMonth() ===
                selectedDate.getMonth() &&
            dateValue.getDate() ===
                selectedDate.getDate()
        ) {

            button.classList.add(
                "selected"
            );
        }


        button.addEventListener(
            "click",
            function () {

                selectedDate =
                    dateValue;

                renderCalendar();
            }
        );


        calendarDays.appendChild(
            button
        );
    }
}



/* =========================
   前月
========================= */

document
    .getElementById(
        "calendar-prev"
    )
    .addEventListener(
        "click",
        function () {

            currentDate =
                new Date(
                    currentDate.getFullYear(),
                    currentDate.getMonth() - 1,
                    1
                );

            renderCalendar();
        }
    );



/* =========================
   翌月
========================= */

document
    .getElementById(
        "calendar-next"
    )
    .addEventListener(
        "click",
        function () {

            currentDate =
                new Date(
                    currentDate.getFullYear(),
                    currentDate.getMonth() + 1,
                    1
                );

            renderCalendar();
        }
    );



/* =========================
   未選択
========================= */

calendarNone.addEventListener(
    "click",
    function () {

        if (calendarTarget) {

            calendarTarget.value =
                "";
        }


        calendarModal.style.display =
            "none";


        if (calendarCallback) {

            calendarCallback(
                ""
            );
        }
    }
);



/* =========================
   キャンセル
========================= */

document
    .getElementById(
        "calendar-cancel"
    )
    .addEventListener(
        "click",
        function () {

            calendarModal.style.display =
                "none";
        }
    );



/* =========================
   決定
========================= */

document
    .getElementById(
        "calendar-confirm"
    )
    .addEventListener(
        "click",
        function () {

            if (!selectedDate) {

                alert(
                    "日付を選択してください"
                );

                return;
            }


            const year =
                selectedDate.getFullYear();


            const month =
                String(
                    selectedDate.getMonth() + 1
                ).padStart(
                    2,
                    "0"
                );


            const day =
                String(
                    selectedDate.getDate()
                ).padStart(
                    2,
                    "0"
                );


            const value =
                year +
                "-" +
                month +
                "-" +
                day;


            calendarTarget.value =
                value;


            calendarModal.style.display =
                "none";


            if (calendarCallback) {

                calendarCallback(
                    value
                );
            }
        }
    );

</script>