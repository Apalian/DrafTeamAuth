<?php
$password = "monMotDePasse";
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo ($hashedPassword);