<?php 
session_start();
include(__DIR__ . '/connect.php');

// 1. Fetch Site Settings (Hero and About)
$settings = [];
$settings_res = $conn->query("SELECT * FROM site_settings");
while($row = $settings_res->fetch_assoc()) {
    $settings[$row['meta_key']] = $row['meta_value'];
}

// 2. Set default values if database is empty to prevent errors
// 2. Set variables based on DB, with fallbacks
$hero_caption = $settings['hero_caption'] ?? 'Experience Kirtipur';
// This line is key! If DB has a value, use it, otherwise use the URL
$hero_bg = !empty($settings['hero_image']) ? $settings['hero_image'] : 'https://images.unsplash.com/photo-1623492701902-47dc207df5dc?auto=format&fit=crop&w=1350&q=80';

$about_text = $settings['about_text'] ?? 'Kirtipur is an ancient city of glory...';
$about_img = !empty($settings['about_image']) ? $settings['about_image'] : 'https://images.unsplash.com/photo-1541418950054-c1baead332ca?auto=format&fit=crop&w=600&q=80';
?>

<!-- // HTML section -->

<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirtipur Tourism | Ancient City of Glory</title>
   <link rel = "Stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
<nav>
    <div class="logo-container">
       <?php 
            // 1. Check if a custom logo exists in the settings array we fetched at the top
            // 2. If it exists, use it. If not, use the default local path.
            $logo_path = !empty($settings['site_logo']) ? $settings['site_logo'] : 'assets/logo.png';
        ?>
        
        <a href="#home" style="display: flex; align-items: center; text-decoration: none; gap: 15px;">
            <img src="<?php echo $logo_path; ?>" alt="Kirtipur Logo" class="nav-logo">
            <h2 style="color: #e67e22; margin: 0;">KIRTIPUR</h2>
        </a>
    </div>
   
   <ul>
    <li><a href="#home">Home</a></li>
    <li><a href="#about">About</a></li>
    <li><a href="#gallery">Gallery</a></li>
    <li><a href="#places">Places</a></li>
    <li><a href="#contact">Contact</a></li>

    <?php if(isset($_SESSION['user_id'])): ?>
        <li style="display:flex; align-items:center; gap:10px;">
            <span style="font-size: 0.9rem; color: #555;">Hi, <?php echo $_SESSION['user_name']; ?></span>
            <a href="logout.php" title="Sign Out">
                <i class="fa-solid fa-right-from-bracket" style="color: #e74c3c; font-size: 1.3rem;"></i>
            </a>
        </li>
    <?php else: ?>
        <li>
            <a href="login.php" title="Sign In">
                <i class="fa-solid fa-circle-user" style="color: #e67e22; font-size: 1.5rem;"></i>
            </a>
        </li>
        <li>
            <a href="register.php" style="background: #e67e22; color: white !important; padding: 8px 20px; border-radius: 5px; font-weight: bold;">
                Register
            </a>
        </li>
    <?php endif; ?>
</ul>
</nav>

<section id="home" style="background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('<?php echo $settings['hero_image']; ?>') center/cover no-repeat;">
    <h1><?php echo htmlspecialchars($settings['hero_caption']); ?></h1>
    <p>Discover the ancient beauty and cultural heritage of Kirtipur.</p>
    <a href="#places" class="btn">Explore Now</a>
</section>

<section id="about">
    <div class="about-content">
        <h2>About Kirtipur</h2>
        <p><?php echo nl2br(htmlspecialchars($about_text)); ?></p>
    </div>
    <img src="<?php echo $about_img; ?>" alt="About Kirtipur" class="about-img">
</section>

<section id="gallery">
    <h2>Photo Gallery</h2>
    <div class="grid">
        <?php
        $gallery_res = $conn->query("SELECT * FROM gallery ORDER BY id DESC");
        if ($gallery_res && $gallery_res->num_rows > 0) {
            while($img = $gallery_res->fetch_assoc()) {
                ?>
                <a href="view_photo.php?id=<?php echo $img['id']; ?>" class="gallery-link">
                    <img src="<?php echo $img['image_path']; ?>" alt="Kirtipur View">
                </a>
                <?php
            }
        } else {
            echo "<p>Gallery is coming soon!</p>";
        }
        ?>
    </div>
</section>


<section id="places">
    <h2>Must-Visit Places</h2>
    <div class="card-container">
        <?php
        $places_res = $conn->query("SELECT * FROM places ORDER BY id DESC");
        if ($places_res && $places_res->num_rows > 0) {
            while($place = $places_res->fetch_assoc()) {
                ?>
                <a href="place_details.php?id=<?php echo $place['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="card">
                        <img src="<?php echo $place['image_path']; ?>" alt="<?php echo htmlspecialchars($place['place_name']); ?>">
                        <div class="card-info">
                            <h3><?php echo htmlspecialchars($place['place_name']); ?></h3>
                            <p><?php echo substr(htmlspecialchars($place['description']), 0, 100) . '...'; ?></p>
                            <span style="color: #e67e22; font-weight: bold; font-size: 0.9rem;">View Details →</span>
                        </div>
                    </div>
                </a>
                <?php
            }
        } else {
            echo "<p>No places listed yet.</p>";
        }
        ?>
    </div>
</section>

<section id="contact">
    <h2 style="text-align: center; margin-bottom: 30px; font-size: 2.5rem;">Get In Touch</h2>
    <form action="admin/process_contact.php" method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="tel" name="phone" placeholder="Phone Number">
        <textarea name="message" rows="5" placeholder="Your Message" required></textarea>
        <button type="submit">Send Message To Us</button>
    </form>
</section>

</body>
<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>KIRTIPUR</h3>
            <p>Preserving the rich Newari heritage and ancient glory of the Kathmandu Valley. Visit us to experience history in every corner.</p>
            <div class="social-icons">
                <a href="#"><i class="fa-brands fa-facebook"></i></a>
                <a href="#"><i class="fa-brands fa-instagram"></i></a>
                <a href="#"><i class="fa-brands fa-twitter"></i></a>
                <a href="#"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>

        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About Kirtipur</a></li>
                <li><a href="#gallery">Gallery</a></li>
                <li><a href="#places">Must Visit</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h4>Contact Us</h4>
            <p><i class="fa-solid fa-location-dot"></i> Kirtipur, Kathmandu, Nepal</p>
            <p><i class="fa-solid fa-phone"></i> +977 01-4XXXXXX</p>
            <p><i class="fa-solid fa-envelope"></i> info@visitkirtipur.com</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> Kirtipur Tourism. All Rights Reserved.</p>
    </div>
</footer>
</html>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php if (isset($_GET['login']) && $_GET['login'] == 'success'): ?>
<script>
    Swal.fire({
        title: 'Logged In!',
        text: 'Welcome, <?php echo $_SESSION['user_name']; ?>',
        icon: 'success',
        width: '320px',             // Makes the popup smaller
        padding: '1.5rem',          // Reduces internal spacing
        showConfirmButton: false,   // Removes the "OK" button for a cleaner look
        timer: 2000,                // Closes faster (2 seconds)
        timerProgressBar: true,
        toast: false,               // Keep it as a popup, but small
        customClass: {
            title: 'small-title-font',
            popup: 'compact-popup'
        }
    }).then(() => {
        // Removes the success flag from URL
        window.history.replaceState(null, null, window.location.pathname);
    });
</script>

<style>
    /* Optional: Fine-tune the font sizes if they still feel too big */
    .swal2-title {
        font-size: 1.2rem !important;
    }
    .swal2-html-container {
        font-size: 1rem !important;
    }
</style>
<?php endif; ?>
