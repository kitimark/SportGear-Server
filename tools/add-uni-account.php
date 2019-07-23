<?php
$arr = json_decode(file_get_contents('uniMap.json'),true);
$i=0;
foreach ($arr as $key => $value) {
    $i++;
    $data = array("uni" => $key);                                                                    
    $data_string = json_encode($data);                                                                                   
    $api_url = 'localhost:8000/mail';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_HEADER, 0);                                                             
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($data_string))                                                                       
    );                                                                                                                                                                                                                             
    $result = curl_exec($ch);
    curl_close($ch);
    echo $result . '<br>';
}