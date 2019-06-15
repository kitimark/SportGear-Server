<?php
$uni = json_decode(file_get_contents(__DIR__.'/uni.json'),true);
$keys = array_keys($uni);
$mysqli = new mysqli("localhost", "root", "", "gearsport");
for($index = 0 ; $index < count($keys) - 1 ; $index++){
    $name = $uni[$keys[$index]];
    $email = $name . '@localhost.com';
    $hash = password_hash($name, PASSWORD_DEFAULT);
    $sql = "INSERT INTO account_uni(uni,email,uni_full_name,uni_pwd) VALUES ('$keys[$index]','$email','$name','$hash')";
    $mysqli->query($sql);
}