<?php
require __DIR__ . '/db_connect.php';

$pdo = get_db_connection();
if ($pdo) {
    echo "Connection successful";
} else {
    echo "Connection failed — check logs at logs/db_errors.log for details.";
}
