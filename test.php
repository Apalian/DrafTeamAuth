<?php
$password = '$iutinfo';
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo ($hashedPassword);