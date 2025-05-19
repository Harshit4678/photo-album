<?php
$targetDir = "images/";
$maxSize = 5 * 1024 * 1024;
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];

if (!empty($_FILES["image"]["name"])) {
    $file = $_FILES["image"];
    $fileName = basename($file["name"]);
    $targetFile = $targetDir . uniqid() . "_" . $fileName;
    $fileType = mime_content_type($file["tmp_name"]);

    if (!in_array($fileType, $allowedTypes)) {
        echo "Only JPG, JPEG, PNG files are allowed.";
        exit;
    }

    if ($file["size"] > $maxSize) {
        echo "File size must be less than 5MB.";
        exit;
    }

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        echo "Image uploaded successfully!";
    } else {
        echo "Failed to upload image.";
    }
} else {
    echo "No file selected.";
}
