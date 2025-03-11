<?php
require '_base.php';
//-----------------------------------------------------------------------------

$arr = $_db->query('SELECT * FROM product')->fetchAll();

// ----------------------------------------------------------------------------
include '_head.php';
?>

</head>
<body>

<div class="hero-image">
  <div class="hero-text">
    <h1 style="font-size:50px">All Books You Need</h1>
    <p>A online book store</p>
    <button>Select some</button>
  </div>
</div>

<div class="responsive">
    <?php foreach ($arr as $product): ?>
        <?php if (empty($product)): ?>
            <p>No product available</p>
        <?php else: ?>    
            <div class="gallery">
                <div class="desc" style="display: none;"><?= htmlspecialchars($product->product_id) ?></div>
                
                <?php
                // Fetch photos for the current product
                $stmt = $_db->prepare('SELECT * FROM product_photo WHERE product_id = ? LIMIT 1');
                $stmt->execute([$product->product_id]);
                $photos = $stmt->fetchAll();
                ?>

                <?php if (empty($photos)): ?>
                    <p>No image available</p>
                <?php else: ?>
                    <?php foreach ($photos as $productPhoto): ?>
                        <?php
                        // Convert BLOB to base64
                        $imageData = base64_encode($productPhoto->product_photo);
                        $imageSrc = "data:image/jpeg;base64," . $imageData;
                        ?>
                        <a target="_blank" href="<?= $imageSrc ?>">
                            <img src="<?= $imageSrc ?>" 
                                alt="Product Image" 
                                onerror="this.onerror=null; this.src='default-image.jpg';">
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="desc"><strong><?= $product->name ?></strong></div>
                <div class="desc" style="color: orangered; margin-top: 10px;">RM<?= $product->price ?></div>
            </div>
        <?php endif; ?>
    <?php endforeach;?>
</div>

<?php
include "_foot.php";
