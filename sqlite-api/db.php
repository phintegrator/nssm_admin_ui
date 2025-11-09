<?php
// db.php

$db = new PDO('sqlite:database.sqlite');

// Create table if not exists
$db->exec("CREATE TABLE IF NOT EXISTS servers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    ip TEXT NOT NULL,
    description TEXT
)");
