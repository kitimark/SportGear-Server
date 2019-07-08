<?php
function clean($string) {
    $string = str_replace(' ', '', $string); // Replaces all spaces with hyphens.
 
    return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
 }

$uni = json_decode(file_get_contents(__DIR__.'/uni.json'),true);
$keys = array_keys($uni);
$settings['host'] = "172.19.0.3";
$settings['dbname'] = "gearsport";
$settings['user'] = "root";
$settings['pass'] = "1234";
echo '(' . count($keys) . ')= ';
for($index = 0 ; $index < count($keys) ; $index++){
    $pdo = new PDO("mysql:host=" . $settings['host'] . ";dbname=" . $settings['dbname'],$settings['user'], $settings['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $name = clean($uni[$keys[$index]]);
    $email = clean(str_replace(' ', '', strtolower($name))) . '@localhost.com';
    $hash = password_hash(clean(str_replace(' ', '', strtolower($name))), PASSWORD_DEFAULT);
    $sql = "INSERT INTO account_uni(uni,email,uni_full_name,uni_pwd) VALUES ('$keys[$index]','$email','$name','$hash')";
    //$pdo->query($sql);
    try{
        $pdo->query($sql);
        echo $index . '-> ';
    }catch(PDOException $e){
        echo "Error: " . $keys[$index] . "<br>" . $e->getMessage();
    }
    //echo 'INSERT LINE :' . $index . '</br>';
}