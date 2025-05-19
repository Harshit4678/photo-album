<?php
$imagesPerPage = 6;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$images = array_diff(scandir("images"), array(".", ".."));
$images = array_values(array_reverse($images));
$totalImages = count($images);
$offset = ($page - 1) * $imagesPerPage;
$currentImages = array_slice($images, $offset, $imagesPerPage);

$left = "";
$right = "";

for ($i = 0; $i < count($currentImages); $i++) {
    $imgPath = "images/" . $currentImages[$i];
    $imgTag = "<img src='$imgPath' alt='Image'>";
    if ($i < 3) {
        $left .= $imgTag;
    } else {
        $right .= $imgTag;
    }
}

echo json_encode([
    "left" => $left,
    "right" => $right,
    "page" => $page,
    "all" => $images // send all for lightbox nav
]);
