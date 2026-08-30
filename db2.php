<?php

// PHPの時刻を日本時間にする
date_default_timezone_set("Asia/Tokyo");

if (
    $_SERVER["HTTP_HOST"] === "localhost" ||
    $_SERVER["HTTP_HOST"] === "127.0.0.1"
) {

    // ローカル環境
    $pdo = new PDO(
        "mysql:host=localhost;dbname=task2_app;charset=utf8mb4",
        "root",
        ""
    );

} else {

    // InfinityFree
    $pdo = new PDO(
        "mysql:host=sql204.infinityfree.com;dbname=if0_42773380_memo_task;charset=utf8mb4",
        "if0_42773380",
        "fZNDNPitIum"
    );
}

$pdo->exec("SET time_zone = '+09:00'");



