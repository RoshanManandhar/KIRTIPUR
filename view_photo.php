<?php
include('connect.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch current photo
$res = $conn->query("SELECT * FROM gallery WHERE id = $id");
$current_photo = $res->fetch_assoc();

if (!$current_photo) { header("Location: index.php"); exit(); }

// Fetch ID of Next and Previous photos for navigation
$prev_res = $conn->query("SELECT id FROM gallery WHERE id < $id ORDER BY id DESC LIMIT 1");
$prev = $prev_res->fetch_assoc();

$next_res = $conn->query("SELECT id FROM gallery WHERE id > $id ORDER BY id ASC LIMIT 1");
$next = $next_res->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Photo | Kirtipur Gallery</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
   <link rel = "Stylesheet" href="viewer.css">
</head>
<body>

    <div class="viewer-container">
        <a href="index.php#gallery" class="close-btn">&times;</a>
        
        <img src="<?php echo $current_photo['image_path']; ?>">

        <?php if($prev): ?>
            <a href="view_photo.php?id=<?php echo $prev['id']; ?>" class="nav-btn prev"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>

        <?php if($next): ?>
            <a href="view_photo.php?id=<?php echo $next['id']; ?>" class="nav-btn next"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
    </div>

    <p style="margin-top: 20px;">Viewing Photo ID: <?php echo $id; ?></p>
    <a href="index.php#gallery" style="color: #e67e22; text-decoration: none;">Back to Gallery</a>

</body>
</html>