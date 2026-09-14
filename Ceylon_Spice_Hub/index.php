<?php
require 'db.php';

$categories = $pdo->query("SELECT * FROM categories ORDER BY category_id")->fetchAll();
$products = $pdo->query("
 SELECT p.*, c.name AS category_name, c.slug AS category_slug,
 COALESCE(AVG(r.rating), p.default_rating) AS rating,
 COUNT(r.review_id) AS review_count
 FROM products p
 JOIN categories c ON c.category_id=p.category_id
 LEFT JOIN reviews r ON r.product_id=p.product_id
 GROUP BY p.product_id
 ORDER BY p.featured DESC, p.product_id
")->fetchAll();

$stmt=$pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items ci JOIN carts c ON c.cart_id=ci.cart_id WHERE c.session_token=?");
$stmt->execute([$cartToken]);
$cartCount=(int)$stmt->fetchColumn();

$featured = array_slice($products,0,4);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Ceylon Spice Hub - authentic Sri Lankan spices delivered to your doorstep.">
<title>Ceylon Spice Hub | Authentic Sri Lankan Spices</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="announcement"><span>🌿 100% Pure Ceylon Spices</span><span>🚚 Islandwide Delivery</span><span>🔒 Secure & Simple Checkout</span></div>

<header class="header">
  <div class="container nav-wrap">
    <a class="brand" href="#home"><span>Ceylon</span><small>SPICE HUB</small></a>
    <button class="mobile-toggle" onclick="toggleMenu()" aria-label="Open menu">☰</button>
    <nav id="navMenu">
      <a class="active" href="#home">Home</a><a href="#shop">Shop</a><a href="#categories">Categories</a><a href="#about">Our Story</a><a href="#contact">Contact</a>
    </nav>
    <div class="nav-actions">
      <div class="search"><span>⌕</span><input id="searchInput" placeholder="Search spices..." autocomplete="off"></div>
      <button class="icon-btn" onclick="document.getElementById('shop').scrollIntoView({behavior:'smooth'})" aria-label="Search">🔎</button>
      <a class="cart-link" href="cart.php" aria-label="Cart">🛒<b id="cartCount"><?=$cartCount?></b></a>
    </div>
  </div>
</header>

<main>
<section class="hero" id="home">
 <div class="container hero-inner">
  <div class="hero-copy">
   <p class="eyebrow">AUTHENTIC • NATURAL • PREMIUM</p>
   <h1>The true taste of <em>Ceylon.</em></h1>
   <p>Discover carefully selected Sri Lankan spices, packed fresh and delivered from our island to your kitchen.</p>
   <div class="hero-buttons"><a class="btn btn-primary" href="#shop">SHOP SPICES <span>→</span></a><a class="btn btn-light" href="#about">OUR STORY</a></div>
   <div class="mini-trust"><span>✓ Farm-sourced</span><span>✓ Freshly packed</span><span>✓ Islandwide delivery</span></div>
  </div>
 </div>
</section>

<section class="feature-strip">
 <div class="container feature-grid">
  <div><strong>🌿 Pure & Authentic</strong><small>Real Ceylon spices</small></div>
  <div><strong>🌱 Carefully Selected</strong><small>Quality first, always</small></div>
  <div><strong>📦 Fresh Packaging</strong><small>Sealed for freshness</small></div>
  <div><strong>🚚 Islandwide Delivery</strong><small>Delivered to your door</small></div>
 </div>
</section>

<section class="section" id="categories">
 <div class="section-head"><div><p class="eyebrow">EXPLORE</p><h2>Shop by Category</h2></div><a class="text-link" href="#shop">View all →</a></div>
 <div class="category-grid">
 <?php foreach($categories as $c): ?>
 <a class="category-card" href="#shop" onclick="selectCategory('<?=e($c['slug'])?>')">
  <div class="category-img"><img src="<?=e($c['image'])?>" alt="<?=e($c['name'])?>"><span>Shop →</span></div>
  <h3><?=e($c['name'])?></h3><p><?=e($c['tagline'])?></p>
 </a>
 <?php endforeach; ?>
 </div>
</section>

<section class="section shop-section" id="shop">
 <div class="section-head"><div><p class="eyebrow">OUR COLLECTION</p><h2>Fresh from Ceylon</h2><p class="muted">Premium spices for everyday cooking and special recipes.</p></div>
 <select id="sortSelect" onchange="sortProducts()"><option value="relevance">Featured</option><option value="low">Price: Low to High</option><option value="high">Price: High to Low</option><option value="new">Newest</option></select></div>
 <div class="shop-toolbar">
  <div class="filter-buttons"><button class="filter-pill active" onclick="setQuickFilter('all',this)">All</button><?php foreach($categories as $c): ?><button class="filter-pill" onclick="setQuickFilter('<?=e($c['slug'])?>',this)"><?=e($c['short_name'])?></button><?php endforeach; ?></div>
  <span id="resultCount"><?=count($products)?> products</span>
 </div>
 <div class="products" id="productList">
 <?php foreach($products as $p): $rating=max(0,min(5,(int)round((float)$p['rating']))); ?>
 <article class="product" data-name="<?=e(strtolower($p['name'].' '.$p['category_name']))?>" data-category="<?=e($p['category_slug'])?>" data-price="<?=$p['price']?>">
   <div class="product-image">
    <?php if($p['badge']): ?><span class="badge"><?=e($p['badge'])?></span><?php endif; ?>
    <button class="wishlist" onclick="toggleWishlist(this)" aria-label="Add to wishlist">♡</button>
    <img src="<?=e($p['image'])?>" alt="<?=e($p['name'])?>">
    <div class="quick"><button onclick="addToCart(<?=$p['product_id']?>,'<?=e($p['name'])?>')">Add to Cart</button></div>
   </div>
   <div class="product-info">
    <small class="category-label"><?=e($p['category_name'])?></small>
    <h3><?=e($p['name'])?></h3>
    <div class="rating"><?=str_repeat('★',$rating).str_repeat('☆',5-$rating)?> <span>(<?=$p['review_count']?>)</span></div>
    <div class="product-bottom"><div><strong><?=money((float)$p['price'])?></strong><small><?=e($p['weight'])?> · <b class="stock"><?=((int)$p['stock']>0?'In stock':'Out of stock')?></b></small></div>
    <?php if($p['stock']>0): ?><button class="add-cart" onclick="addToCart(<?=$p['product_id']?>,'<?=e($p['name'])?>')">＋</button><?php else: ?><button class="add-cart disabled" disabled>—</button><?php endif; ?>
    </div>
   </div>
 </article>
 <?php endforeach; ?>
 </div>
 <div id="noResults" class="empty-search" hidden><div>🌿</div><h3>No spices found</h3><p>Try another search or choose a different category.</p></div>
</section>

<section class="story" id="about">
 <div class="container story-grid"><div class="story-image"><img src="images/highquality/hero.jpg" alt="Sri Lankan spices"></div>
 <div class="story-copy"><p class="eyebrow">OUR STORY</p><h2>A little island.<br>A world of flavour.</h2><p>Ceylon Spice Hub brings the character of Sri Lanka to your kitchen. We focus on authentic spices, careful selection and simple packaging that keeps every product fresh.</p><div class="story-stats"><div><b>100%</b><span>Ceylon focused</span></div><div><b>6+</b><span>Spice varieties</span></div><div><b>Fresh</b><span>Quality packed</span></div></div><a class="btn btn-primary" href="#shop">DISCOVER THE COLLECTION →</a></div></div>
</section>

<section class="confidence"><div class="container confidence-grid"><div><p class="eyebrow">SHOP WITH CONFIDENCE</p><h2>Quality you can feel good about.</h2><p>Transparent pricing, secure ordering and customer-friendly support make every order simple.</p></div><div class="confidence-cards"><div>🔐<b>Secure checkout</b><span>Protected order process</span></div><div>📦<b>Freshly packed</b><span>Quality-focused packaging</span></div><div>↩<b>Customer care</b><span>Friendly support</span></div></div></div></section>

<section class="newsletter" id="contact"><div class="container newsletter-inner"><div><p class="eyebrow">JOIN OUR COMMUNITY</p><h2>Recipes, new spices & special offers.</h2><p>Subscribe for occasional updates from Ceylon Spice Hub.</p></div><form onsubmit="subscribe(event)"><input id="email" type="email" placeholder="Your email address" required><button>SUBSCRIBE</button></form></div></section>
</main>

<footer><div class="container footer-grid"><div class="footer-brand"><a class="brand" href="#home"><span>Ceylon</span><small>SPICE HUB</small></a><p>Authentic Sri Lankan spices, selected with care and delivered to your kitchen.</p><div class="socials"><span>f</span><span>◎</span><span>in</span></div></div><div><h4>Explore</h4><a href="#shop">Shop</a><a href="#categories">Categories</a><a href="#about">Our Story</a></div><div><h4>Help</h4><a href="cart.php">Shopping Cart</a><a href="#contact">Contact Us</a><a href="#contact">FAQs</a></div><div><h4>Contact</h4><p>Badulla, Sri Lanka</p><p>hello@ceylonspicehub.lk</p><p>+94 77 123 4567</p></div></div><div class="copyright">© <?=date('Y')?> Ceylon Spice Hub. All rights reserved.</div></footer>
<div id="message" class="toast"></div>
<script src="script.js"></script>
</body></html>
