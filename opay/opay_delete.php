<?php
require_once "../db/connect.php";

$id = intval($_POST['id'] ?? 0);

if ($id > 0) {
    mysqli_query($con, "DELETE FROM opay_deposit WHERE id = $id");
    echo "success";
} else {
    echo "error";
}
