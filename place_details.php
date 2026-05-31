<?php
include('connect.php');

// 1. Get the ID from the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

// 2. Fetch only the selected place
$stmt = $conn->prepare("SELECT * FROM places WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$place = $result->fetch_assoc();

if (!$place) {
    die("Place not found!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($place['place_name']); ?> | Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="details.css">
</head>
<body class="details-page">

<div class="container">
    <a href="index.php#places" class="back-link">← Back to Places</a>
    
    <img src="<?php echo $place['image_path']; ?>" class="detail-img" alt="<?php echo $place['place_name']; ?>">
    
    <h1><?php echo htmlspecialchars($place['place_name']); ?></h1>
    
    <div class="description">
        <?php echo nl2br(htmlspecialchars($place['description'])); ?>
    </div>

    <div class="location-section">
        <h3><i class="fa-solid fa-location-dot"></i> Location</h3>
        <p><?php echo htmlspecialchars($place['location'] ?? 'Kirtipur, Nepal'); ?></p>
        
        <?php if(!empty($place['map_link'])): ?>
            <a href="<?php echo htmlspecialchars($place['map_link']); ?>" target="_blank" class="map-btn">
                View on Google Maps
            </a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>