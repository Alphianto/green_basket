<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poetsen+One&family=Inter:wght@400;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Lemon&display=swap" rel="stylesheet">

  <title>GreenBasket</title>
  <link rel="icon" type="image/png" href="style/imgs/gb.png">
  <link rel="stylesheet" href="style/style.css">

</head>
<body>

    <?php include __DIR__ . '/layout/header.php';?>

  <!-- Hero Section -->
    <section class="hero">
         <video autoplay muted loop playsinline class="background-video">
            <source src="style/imgs/vegivideo.mp4" type="video/mp4">
        </video>
        <div class="body-sect">
            <div class="subbody-sect">
                    <div class="subbody21">
                        <div class="text-hero">Fresh Home Grown</div>
                        <div class="text-hero1"> veggies & fruits</div>
                        <p>100% Home-Grown | No Harmful Chemicals | Trusted Local Sellers</p>
                        <p>Real People. Real Farms</p>
                    </div>
            </div>
        </div>
    </section>
    <section class="category-section">
    <div class="category-header">
        <h2 class="category-title">Explore Our Categories</h2>
        <p class="category-subtitle">Freshness organized just for you</p>
    </div>

    <div class="category-grid">
        
        <!-- VEGETABLES CARD -->
        <a href="shop/veggies.php" class="category-card image-card">
            <!-- <img src="https://placehold.co/800x1000/6A994E/FFFFFF?text=Vegetables+Section" 
                 alt="Fresh Vegetables" 
                 class="card-image"> -->
            <img src="style/imgs/vegi.jpg" alt="Fresh Vegetables" class="card-image" class="card-overlay">

            <div class="card-overlay">
                <h3 class="card-title">Vegetables</h3>
                <p class="card-description">A daily harvest of fresh, organic vegetables grown locally organic vegetables no harmful chemicals.</p>
                <div class="card-button">Shop Now &rarr;</div>
            </div>
        </a>

        <!-- FRUITS CARD -->
        <a href="shop/fruits.php" class="category-card image-card">
            <img src="style/imgs/fruits.jpg" alt="Delicious Fruits" class="card-image" class="card-overlay">

            <div class="card-overlay">
                <h3 class="card-title">Fruits</h3>
                <p class="card-description">Juicy, sweet, and seasonal fruits perfect for snacking fresh fruits seasonal specials.</p>
                <div class="card-button">Shop Now &rarr;</div>
            </div>
        </a>
    </div>
</section>
<?php include __DIR__ . '/layout/footer.php';?>

</body>
</html>
