<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: index.php"); exit(); }
include(__DIR__ . '/../connect.php');

$msg = "";
if (isset($_GET['success'])) { $msg = "Site settings updated successfully!"; }
if (isset($_GET['msg'])) { $msg = htmlspecialchars($_GET['msg']); }

// --- 1. HANDLE SETTINGS UPDATE (Hero, About & Logo) ---
if (isset($_POST['update_settings'])) {
    // Update Text Settings
    foreach ($_POST['settings'] as $key => $value) {
        $clean_value = mysqli_real_escape_string($conn, $value);
        $conn->query("UPDATE site_settings SET meta_value = '$clean_value' WHERE meta_key = '$key'");
    }
    
    // Ensure uploads folder exists
    if (!is_dir('../uploads')) { mkdir('../uploads', 0777, true); }

    // Handle Image Uploads for Settings
    foreach (['hero_image', 'about_image', 'site_logo'] as $img_key) {
        if (!empty($_FILES[$img_key]['name'])) {
            $filename = time() . "_" . basename($_FILES[$img_key]['name']);
            $path = "uploads/" . $filename;
            if(move_uploaded_file($_FILES[$img_key]['tmp_name'], "../" . $path)) {
                $conn->query("UPDATE site_settings SET meta_value = '$path' WHERE meta_key = '$img_key'");
            }
        }
    }
    header("Location: admin_dashboard.php?success=1");
    exit();
}

// --- 2. HANDLE EDIT PLACE ---
if (isset($_POST['edit_place'])) {
  $id = (int)$_POST['place_id'];
    $name = mysqli_real_escape_string($conn, $_POST['place_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['place_desc']);
    $loc  = mysqli_real_escape_string($conn, $_POST['location']); // Matches your col 5
    $map  = mysqli_real_escape_string($conn, $_POST['map_link']); // Matches your col 6
    
    $conn->query("UPDATE places SET place_name = '$name', description = '$desc', location = '$loc', map_link = '$map' WHERE id = $id");

    if (!empty($_FILES['place_img']['name'])) {
        $path = "uploads/place_" . time() . "_" . $_FILES['place_img']['name'];
        if (move_uploaded_file($_FILES['place_img']['tmp_name'], "../" . $path)) {
            // Delete old image file
            $old_res = $conn->query("SELECT image_path FROM places WHERE id = $id");
            $old_img = $old_res->fetch_assoc();
            if(!empty($old_img['image_path']) && file_exists("../".$old_img['image_path'])) {
                unlink("../".$old_img['image_path']);
            }
            $conn->query("UPDATE places SET image_path = '$path' WHERE id = $id");
        }
    }
    header("Location: admin_dashboard.php?msg=Place updated successfully!");
    exit();
}

// --- 3. HANDLE ADD NEW PLACE ---
if (isset($_POST['add_place'])) {
   $name = mysqli_real_escape_string($conn, $_POST['place_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['place_desc']);
    $loc  = mysqli_real_escape_string($conn, $_POST['location']);
    $map  = mysqli_real_escape_string($conn, $_POST['map_link']);
    $path = "uploads/place_" . time() . "_" . $_FILES['place_img']['name'];
    
    if (move_uploaded_file($_FILES['place_img']['tmp_name'], "../" . $path)) {
        $conn->query("INSERT INTO places (place_name, description, location, map_link, image_path) 
                      VALUES ('$name', '$desc', '$loc', '$map', '$path')");
        header("Location: admin_dashboard.php?msg=New place added!");
        exit();
    }
}

// --- 4. HANDLE MESSAGE ACTIONS ---
// if (isset($_GET['mark_read'])) {
//     $msg_id = (int)$_GET['mark_read'];
//     $conn->query("UPDATE messages SET status = 'Read' WHERE id = $msg_id");
//     header("Location: admin_dashboard.php#messages");
//     exit();
// }

if (isset($_GET['delete_msg'])) {
    $msg_id = (int)$_GET['delete_msg'];
    $conn->query("DELETE FROM messages WHERE id = $msg_id");
    header("Location: admin_dashboard.php#messages");
    exit();
}

// --- 5. HANDLE GALLERY & DELETE LOGIC ---
if (isset($_POST['upload_gallery'])) {
    $path = "uploads/gallery_" . time() . "_" . $_FILES['gallery_img']['name'];
    if (move_uploaded_file($_FILES['gallery_img']['tmp_name'], "../" . $path)) {
        $conn->query("INSERT INTO gallery (image_path) VALUES ('$path')");
        header("Location: admin_dashboard.php?msg=Photo added to gallery!");
        exit();
    }
}

if (isset($_GET['delete_gallery'])) {
    $id = (int)$_GET['delete_gallery'];
    $res = $conn->query("SELECT image_path FROM gallery WHERE id = $id");
    $img = $res->fetch_assoc();
    if(file_exists("../".$img['image_path'])) unlink("../".$img['image_path']);
    $conn->query("DELETE FROM gallery WHERE id = $id");
    header("Location: admin_dashboard.php#gallery");
    exit();
}

if (isset($_GET['delete_place'])) {
    $id = (int)$_GET['delete_place'];
    $res = $conn->query("SELECT image_path FROM places WHERE id = $id");
    $p = $res->fetch_assoc();
    if(file_exists("../".$p['image_path'])) unlink("../".$p['image_path']);
    $conn->query("DELETE FROM places WHERE id = $id");
    header("Location: admin_dashboard.php#visit_places");
    exit();
}

// --- 6. DATA FETCHING (Runs every time page loads) ---
$edit_data = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $res = $conn->query("SELECT * FROM places WHERE id = $edit_id");
    $edit_data = $res->fetch_assoc();
}

$settings = [];
$s_res = $conn->query("SELECT * FROM site_settings");
while($row = $s_res->fetch_assoc()) { $settings[$row['meta_key']] = $row['meta_value']; }

$places = $conn->query("SELECT * FROM places ORDER BY id DESC");
$messages = $conn->query("SELECT * FROM messages ORDER BY id DESC");
$gallery = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kirtipur Admin Panel</title>
   <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; display: flex; margin: 0; }
        .sidebar { width: 240px; background: #2c3e50; color: white; height: 100vh; padding: 20px; position: fixed; }
        .sidebar h2 { color: #e67e22; margin-bottom: 30px; }
        .sidebar a { display: block; color: #bdc3c7; text-decoration: none; padding: 12px 0; border-bottom: 1px solid #34495e; transition: 0.3s; }
        .sidebar a:hover { color: #fff; padding-left: 5px; }
        
        .main { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        .card { background: white; padding: 25px; margin-bottom: 40px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        h2 { margin-bottom: 20px; color: #34495e; border-left: 5px solid #e67e22; padding-left: 15px; }
        
        label { font-weight: bold; color: #555; display: block; margin-top: 15px; }
        input, textarea { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        button { background: #e67e22; color: white; border: none; padding: 12px 25px; cursor: pointer; border-radius: 6px; font-weight: bold; margin-top: 10px; }
        button:hover { background: #d35400; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 15px; border-bottom: 1px solid #eee; }
        
        .admin-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; }
        .gallery-item img { width: 100%; height: 100px; object-fit: cover; border-radius: 4px; }
        .status-badge { color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Kirtipur Admin</h2>
    <a href="#hero_about">General Settings</a>
    <a href="#visit_places">Must Visit Places</a>
    <a href="#gallery">Gallery</a>
    <a href="#messages">Messages</a>
    <a href="logout.php" style="margin-top: 50px; color: #ff7675;">Logout</a>
</div>

<div class="main">
    <?php if(isset($msg)) echo "<div style='background:#d4edda; color:#155724; padding:15px; margin-bottom:20px; border-radius:8px;'>$msg</div>"; ?>

   <div class="card" id="hero_about">
        <h2>General Site Settings</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Current Site Logo</label>
            <?php if(!empty($settings['site_logo'])): ?>
                <img src="../<?php echo $settings['site_logo']; ?>" height="50" style="display:block; margin: 10px 0; background:#eee; padding:5px;">
            <?php endif; ?>
            <input type="file" name="site_logo">
            
            <hr style="border:0; border-top:1px solid #eee; margin:20px 0;">

            <label>Hero Title Caption</label>
            <input type="text" name="settings[hero_caption]" value="<?php echo $settings['hero_caption'] ?? ''; ?>">
            
            <label>Hero Background Image</label>
            <input type="file" name="hero_image">
            
            <label>About Section Description</label>
            <textarea name="settings[about_text]" rows="5"><?php echo $settings['about_text'] ?? ''; ?></textarea>
            
            <label>About Side Image</label>
            <input type="file" name="about_image">
            
            <button type="submit" name="update_settings">Update All Site Settings</button>
        </form>
    </div>

    <div class="card" id="visit_places">
    <h2><?php echo $edit_data ? "Edit Place: " . $edit_data['place_name'] : "Add Must Visit Place"; ?></h2>
    
    <form method="POST" enctype="multipart/form-data">
        <?php if($edit_data): ?>
            <input type="hidden" name="place_id" value="<?php echo $edit_data['id']; ?>">
        <?php endif; ?>

       <input type="text" name="place_name" placeholder="Name of Place" value="<?php echo $edit_data['place_name'] ?? ''; ?>" required>

<textarea name="place_desc" placeholder="Short description..."><?php echo $edit_data['description'] ?? ''; ?></textarea>

<input type="text" name="location" placeholder="Location (e.g. Kirtipur, Nepal)" 
       value="<?php echo $edit_data['location'] ?? 'Kirtipur, Nepal'; ?>">

<input type="text" name="map_link" placeholder="Google Maps URL" 
       value="<?php echo $edit_data['map_link'] ?? ''; ?>">
<label>Image <?php echo $edit_data ? "(Leave blank to keep current)" : ""; ?></label>
<input type="file" name="place_img" <?php echo $edit_data ? "" : "required"; ?>>
        
        <?php if($edit_data): ?>
            <button type="submit" name="edit_place" style="background: #2ecc71;">Update Place Details</button>
            <a href="admin_dashboard.php#visit_places" style="margin-left:10px; color: #7f8c8d;">Cancel Edit</a>
        <?php else: ?>
            <button type="submit" name="add_place">Add Place</button>
        <?php endif; ?>
    </form>

    <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

    <h3>Existing Places</h3>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Place Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Reset pointer for places query
            $places_list = $conn->query("SELECT * FROM places ORDER BY id DESC");
            while($p = $places_list->fetch_assoc()): ?>
            <tr>
                <td><img src="../<?php echo $p['image_path']; ?>" width="60" style="border-radius:4px;"></td>
                <td><strong><?php echo $p['place_name']; ?></strong></td>
                <td>
                    <a href="?edit_id=<?php echo $p['id']; ?>#visit_places" style="color: #3498db; text-decoration:none; margin-right:10px;">Edit</a>
                    <a href="?delete_place=<?php echo $p['id']; ?>" style="color: #e74c3c; text-decoration:none;" onclick="return confirm('Delete this place?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
    <div class="card" id="gallery">
        <h2>Photo Gallery Control</h2>
        <form method="POST" enctype="multipart/form-data" style="margin-bottom:20px;">
            <input type="file" name="gallery_img" required>
            <button type="submit" name="upload_gallery">Upload to Gallery</button>
        </form>
        <div class="admin-gallery">
            <?php while($img = $gallery->fetch_assoc()): ?>
                <div class="gallery-item">
                    <img src="../<?php echo $img['image_path']; ?>">
                    <a href="?delete_gallery=<?php echo $img['id']; ?>" style="color:red; font-size:12px; text-decoration:none;" onclick="return confirm('Delete?')">Delete</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

   <div class="card" id="messages">
    <h2>User Inquiries</h2>
    <table>
        <thead>
            <tr>
                <th>User Details</th>
                <th>Message</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $messages = $conn->query("SELECT * FROM messages ORDER BY id DESC");
            while($m = $messages->fetch_assoc()): 
            ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($m['name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($m['email']); ?></small><br>
                        <small><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($m['phone'] ?? 'N/A'); ?></small>
                    </td>
                    <td><?php echo nl2br(htmlspecialchars($m['message'])); ?></td>
                    <td>
                        <a href="?delete_msg=<?php echo $m['id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this message?')" 
                           style="color: #e74c3c; text-decoration: none; font-weight: bold;">
                           <i class="fa-solid fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</div>

</body>
</html>