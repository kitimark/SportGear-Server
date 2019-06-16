<?php
/**
 * Moves the uploaded file to the upload directory and assigns it a unique name
 * to avoid overwriting an existing uploaded file.
 *
 * @param string $directory directory to which the file is moved
 * @param UploadedFile $uploadedFile file uploaded file to move
 * @return string filename of moved file
 */
function moveUploadedFile($directory, UploadedFile $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}

function groupArray($arr, $group, $preserveGroupKey = false, $preserveSubArrays = false) {
    $temp = array();
    foreach($arr as $key => $value) {
        $groupValue = $value[$group];
        if(!$preserveGroupKey)
        {
            unset($arr[$key][$group]);
        }
        if(!array_key_exists($groupValue, $temp)) {
            $temp[$groupValue] = array();
        }

        if(!$preserveSubArrays){
            $data = count($arr[$key]) == 1? array_pop($arr[$key]) : $arr[$key];
        } else {
            $data = $arr[$key];
        }
        $temp[$groupValue][] = $data;
    }
    return $temp;
}