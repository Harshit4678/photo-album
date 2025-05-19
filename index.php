<?php
$imagesDir = "images/";
$allowedFormats = ['jpg', 'jpeg', 'png'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

$success = '';
$error = '';

// Metadata file for titles/descriptions
$metaFile = $imagesDir . 'meta.json';
if (!file_exists($metaFile)) file_put_contents($metaFile, '{}');
$meta = json_decode(file_get_contents($metaFile), true);

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $desc = isset($_POST['desc']) ? trim($_POST['desc']) : '';

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedFormats)) {
        $error = "Unsupported file format. Only JPG, JPEG, PNG allowed.";
    } elseif ($file['size'] > $maxFileSize) {
        $error = "File size exceeds 5MB limit.";
    } elseif ($file['error'] !== 0) {
        $error = "Error uploading file.";
    } else {
        $newFileName = uniqid('img_', true) . "." . $ext;
        if (move_uploaded_file($file['tmp_name'], $imagesDir . $newFileName)) {
            $meta[$newFileName] = [
                'title' => $title,
                'desc' => $desc
            ];
            file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
            header("Location: index.php?success=1");
            exit;
        } else {
            $error = "Failed to upload image.";
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $deleteFile = basename($_GET['delete']);
    $deletePath = $imagesDir . $deleteFile;
    $ext = strtolower(pathinfo($deleteFile, PATHINFO_EXTENSION));
    if (in_array($ext, $allowedFormats) && is_file($deletePath)) {
        if (unlink($deletePath)) {
            if (isset($meta[$deleteFile])) {
                unset($meta[$deleteFile]);
                file_put_contents($metaFile, json_encode($meta, JSON_PRETTY_PRINT));
            }
            header("Location: index.php?deleted=1");
            exit;
        } else {
            $error = "Failed to delete image.";
        }
    } else {
        $error = "Invalid image selected for deletion.";
    }
}

// Show messages based on URL
if (isset($_GET['success'])) $success = "Image uploaded successfully!";
if (isset($_GET['deleted'])) $success = "Image deleted successfully!";

// Get images for display and pagination
$allImages = array_values(array_filter(scandir($imagesDir), function($f) use ($allowedFormats, $imagesDir) {
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    return in_array($ext, $allowedFormats) && is_file($imagesDir . $f);
}));

// Pagination params
$imagesPerPage = 6;
$totalPages = max(1, ceil(count($allImages) / $imagesPerPage));
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;

$startIndex = ($page - 1) * $imagesPerPage;
$imagesOnPage = array_slice($allImages, $startIndex, $imagesPerPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Photo Album</title>
    <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <header>
  <div class="title-container">
    <img src="https://cdn-icons-png.flaticon.com/512/2922/2922561.png" alt="Photo Album Icon" class="logo" />
    <div>
      <h1 style="margin:0;">Photo Album</h1>
      <div style="font-size:1.1rem;font-weight:400;color:#555;letter-spacing:0.5px;">
        Your beautiful moments, organized.
      </div>
    </div>
  </div>
  <button id="darkModeToggle" aria-label="Toggle Dark Mode">🌙</button>
</header>

    <?php if (!empty($error)): ?>
      <div class="error-msg"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
      <div class="success-msg"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="upload-form">
      <input type="file" name="image" accept=".jpg,.jpeg,.png" required />
      <input type="text" name="title" placeholder="Title (optional)" maxlength="100" />
      <input type="text" name="desc" placeholder="Description (optional)" maxlength="200" />
      <button type="submit">Upload Image</button>
    </form>

    <div class="album">
      <?php foreach ($imagesOnPage as $i => $imgFile):
        $imgSrc = $imagesDir . htmlspecialchars($imgFile);
        $imgMeta = isset($meta[$imgFile]) ? $meta[$imgFile] : ['title'=>'','desc'=>''];
      ?>
        <div class="img-card">
          <img src="<?= $imgSrc ?>" alt="Photo" class="album-img" onclick="openModal(<?= $startIndex + $i ?>)" />
          <?php if ($imgMeta['title']): ?>
            <div class="img-title"><?= htmlspecialchars($imgMeta['title']) ?></div>
          <?php endif; ?>
          <?php if ($imgMeta['desc']): ?>
            <div class="img-desc"><?= htmlspecialchars($imgMeta['desc']) ?></div>
          <?php endif; ?>
          <div class="img-actions">
            <a href="<?= $imgSrc ?>" download class="action-btn download" title="Download"><span>⬇️</span></a>
            <a href="?delete=<?= urlencode($imgFile) ?>" class="action-btn delete" title="Delete" onclick="return confirm('Delete this image?')"><span>🗑️</span></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>" class="btn">&laquo; Previous</a>
      <?php else: ?>
        <span class="btn disabled">&laquo; Previous</span>
      <?php endif; ?>

      <span class="page-info">Page <?= $page ?> of <?= $totalPages ?></span>

      <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>" class="btn">Next &raquo;</a>
      <?php else: ?>
        <span class="btn disabled">Next &raquo;</span>
      <?php endif; ?>
    </div>
    
    <footer class="footer">
  <div>
    Made with ❤️ by <strong>Harshit</strong>
    <a href="mailto:harshitkumar2045@gmail.com" class="footer-mail">harshitkumar2045@gmail.com</a>
  </div>
</footer>
  </div>

  <!-- Modal for image viewer -->
  <div class="image-modal" id="modal" onclick="if(event.target === this) closeModal()">
    <span class="close-btn" onclick="closeModal()">&times;</span>
    <span class="arrow left" onclick="prevImage()">&#10094;</span>
    <div class="modal-slider">
      <div class="slides"></div>
    </div>
    <span class="arrow right" onclick="nextImage()">&#10095;</span>

  </div>

  <script>
    const allImages = <?= json_encode($allImages) ?>;
    let currentIndex = 0;

    function openModal(index) {
      currentIndex = index;
      const modal = document.getElementById("modal");
      modal.classList.add('show');

      const slidesContainer = document.querySelector('.slides');
      slidesContainer.innerHTML = "";

      allImages.forEach(img => {
        const imageElement = document.createElement("img");
        imageElement.src = "images/" + img;
        slidesContainer.appendChild(imageElement);
      });

      updateSlidePosition();
    }

    function closeModal() {
      const modal = document.getElementById("modal");
      modal.classList.remove('show');
    }

    function nextImage() {
      currentIndex = (currentIndex + 1) % allImages.length;
      updateSlidePosition();
    }

    function prevImage() {
      currentIndex = (currentIndex - 1 + allImages.length) % allImages.length;
      updateSlidePosition();
    }

    function updateSlidePosition() {
      const slidesContainer = document.querySelector('.slides');
      slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    // Dark mode toggle
    const toggleBtn = document.getElementById('darkModeToggle');
    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark-mode');
      // Change icon accordingly
      if(document.body.classList.contains('dark-mode')) {
        toggleBtn.textContent = '☀️';
      } else {
        toggleBtn.textContent = '🌙';
      }
    });

    // Hide messages after 3 seconds with fade out
    setTimeout(() => {
      const successMsg = document.querySelector('.success-msg');
      if (successMsg) successMsg.style.opacity = '0';
      const errorMsg = document.querySelector('.error-msg');
      if (errorMsg) errorMsg.style.opacity = '0';
    }, 2000);
  </script>
</body>
</html>