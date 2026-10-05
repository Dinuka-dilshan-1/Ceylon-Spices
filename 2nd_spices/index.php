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

$activeCartId=get_active_cart_id();
$cartCount=0;
if($activeCartId){
    $stmt=$pdo->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart_items WHERE cart_id=?");
    $stmt->execute([$activeCartId]);
    $cartCount=(int)$stmt->fetchColumn();
}
$featured=array_slice($products,0,4);
$user=current_user();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="Ceylon Spice Hub — premium Sri Lankan spices, carefully selected and freshly packed.">
<title>Ceylon Spice Hub | The Soul of Ceylon</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="announcement"><div class="container announcement-inner"><span>✦ AUTHENTIC CEYLON SPICES</span><span>Islandwide delivery</span><span>Freshly packed for every order</span><span class="announcement-right">Free delivery on selected orders · <a href="#shop">Shop now →</a></span></div></div>

<header class="header">
 <div class="container nav-wrap">
  <a class="brand" href="#home"><span>Ceylon</span><small>SPICE HUB</small></a>
  <button class="mobile-toggle" onclick="toggleMenu()" aria-label="Open menu">☰</button>
  <nav id="navMenu">
   <a class="active" href="#home">Home</a><a href="#shop">Shop</a><a href="#categories">Collections</a><a href="#about">Our Story</a><a href="#contact">Contact</a>
  </nav>
  <div class="nav-actions">
   <div class="search"><span>⌕</span><input id="searchInput" placeholder="Search our spices..." autocomplete="off"></div>
   <button class="icon-btn" onclick="document.getElementById('searchInput').focus()" aria-label="Search">⌕</button>
   <a class="account-link" href="<?=$user?'profile.php':'login.php'?>"><span class="nav-user-icon">◯</span><?=$user?e($user['name']):'Account'?></a>
   <a class="cart-link" href="cart.php" aria-label="Shopping cart"><span>Bag</span><b id="cartCount"><?=$cartCount?></b></a>
  </div>
 </div>
</header>

<main>
<section class="hero" id="home">
 <div class="hero-overlay"></div>
 <div class="container hero-inner">
  <div class="hero-copy">
   <div class="hero-kicker"><span></span> FROM THE ISLAND OF CEYLON</div>
   <h1>Bring the <em>heart</em><br>of Sri Lanka home.</h1>
   <p>Pure, aromatic spices selected from the island's rich growing regions and packed to preserve the flavour you love.</p>
   <div class="hero-buttons"><a class="btn btn-primary" href="#shop">Explore spices <span>↗</span></a><a class="btn btn-ghost" href="#about">Discover our story</a></div>
   <div class="hero-proof"><div><strong>6+</strong><span>signature spices</span></div><i></i><div><strong>100%</strong><span>Ceylon focused</span></div><i></i><div><strong>Fresh</strong><span>quality packed</span></div></div>
  </div>
  <div class="hero-card"><span class="hero-card-label">HANDPICKED</span><strong>From our island<br>to your table.</strong><a href="#shop">View collection →</a></div>
 </div>
</section>

<section class="trust-bar"><div class="container trust-grid"><div><span class="trust-icon">✦</span><div><b>Authentic Ceylon</b><small>Island-sourced flavour</small></div></div><div><span class="trust-icon">◇</span><div><b>Quality Selected</b><small>Carefully chosen products</small></div></div><div><span class="trust-icon">□</span><div><b>Freshly Packed</b><small>Sealed for freshness</small></div></div><div><span class="trust-icon">→</span><div><b>Islandwide Delivery</b><small>From us to your door</small></div></div></div></section>

<section class="section" id="categories">
 <div class="container">
  <div class="section-head"><div><p class="eyebrow">THE COLLECTION</p><h2>Explore the flavours<br><em>of Ceylon.</em></h2></div><p class="section-intro">A considered collection of Sri Lankan favourites — from warm cinnamon to bold black pepper.</p></div>
  <div class="category-grid">
  <?php foreach($categories as $i=>$c): ?>
   <a class="category-card" href="#shop" onclick="selectCategory('<?=e($c['slug'])?>')">
    <div class="category-img"><img src="<?=e($c['image'])?>" alt="<?=e($c['name'])?>"><span class="category-number">0<?=($i+1)?></span><span class="category-arrow">↗</span></div>
    <div class="category-copy"><div><h3><?=e($c['name'])?></h3><p><?=e($c['tagline'])?></p></div><span>Shop</span></div>
   </a>
  <?php endforeach; ?>
  </div>
 </div>
</section>

<section class="section shop-section" id="shop">
 <div class="container">
  <div class="section-head shop-heading"><div><p class="eyebrow">OUR FAVOURITES</p><h2>Best of Ceylon</h2><p class="muted">Everyday essentials, chosen for aroma, character and versatility.</p></div><a class="outline-link" href="#shop">View all products <span>↗</span></a></div>
  <div class="shop-toolbar"><div class="filter-buttons"><button class="filter-pill active" onclick="setQuickFilter('all',this)">All</button><?php foreach($categories as $c): ?><button class="filter-pill" onclick="setQuickFilter('<?=e($c['slug'])?>',this)"><?=e($c['short_name'])?></button><?php endforeach; ?></div><select id="sortSelect" onchange="sortProducts()"><option value="relevance">Featured</option><option value="low">Price: Low to High</option><option value="high">Price: High to Low</option></select></div>
  <div class="products" id="productList">
  <?php foreach($products as $p): $rating=max(0,min(5,(int)round((float)$p['rating']))); ?>
   <article class="product" data-name="<?=e(strtolower($p['name'].' '.$p['category_name']))?>" data-category="<?=e($p['category_slug'])?>" data-price="<?=$p['price']?>">
    <div class="product-image">
     <?php if($p['badge']): ?><span class="badge"><?=e($p['badge'])?></span><?php endif; ?>
     <button class="wishlist" onclick="toggleWishlist(this)" aria-label="Add to wishlist">♡</button>
     <img src="<?=e($p['image'])?>" alt="<?=e($p['name'])?>" loading="lazy">
     <div class="quick"><form class="add-cart-form" action="add_to_cart.php" method="post" onsubmit="return submitAddToCart(this,event)"><input type="hidden" name="product_id" value="<?=$p['product_id']?>"><input type="hidden" name="quantity" value="1"><input type="hidden" name="ajax" value="1"><button type="submit">Add to bag <span>+</span></button></form></div>
    </div>
    <div class="product-info"><div class="product-meta"><small><?=e($p['category_name'])?></small><span><?=$p['review_count']?> reviews</span></div><h3><?=e($p['name'])?></h3><div class="rating"><?=str_repeat('★',$rating).str_repeat('☆',5-$rating)?></div><div class="product-bottom"><div><strong><?=money((float)$p['price'])?></strong><small><?=e($p['weight'])?> · <b class="stock"><?=((int)$p['stock']>0?'In stock':'Out of stock')?></b></small></div><?php if($p['stock']>0): ?><form class="add-cart-form-circle" action="add_to_cart.php" method="post" onsubmit="return submitAddToCart(this,event)"><input type="hidden" name="product_id" value="<?=$p['product_id']?>"><input type="hidden" name="quantity" value="1"><input type="hidden" name="ajax" value="1"><button type="submit" class="add-cart" aria-label="Add <?=e($p['name'])?> to cart">+</button></form><?php else: ?><button class="add-cart disabled" disabled>—</button><?php endif; ?></div></div>
   </article>
  <?php endforeach; ?>
  </div>
  <div id="noResults" class="empty-search" hidden><div>✦</div><h3>Nothing found</h3><p>Try another spice, category or search term.</p></div>
 </div>
</section>

<section class="story" id="about"><div class="container story-grid"><div class="story-visual"><img src="images/highquality/hero.jpg" alt="Sri Lankan spices" loading="lazy"><div class="story-stamp"><span>CEYLON</span><b>Since</b><strong>2026</strong></div></div><div class="story-copy"><p class="eyebrow">OUR STORY</p><h2>One small island.<br><em>A world of flavour.</em></h2><p>There is something unmistakable about Ceylon — warm, fragrant and full of character. Ceylon Spice Hub was created to make that character easier to bring into everyday cooking.</p><p>We keep our collection focused, our presentation simple and our promise clear: quality spices that feel at home in every Sri Lankan kitchen.</p><div class="story-signature"><span>Selected with care</span><b>CEYLON SPICE HUB</b></div></div></div></section>

<section class="editorial"><div class="container editorial-grid"><div><p class="eyebrow">THE CEYLON DIFFERENCE</p><h2>Simple ingredients.<br><em>Extraordinary aroma.</em></h2><p>From morning tea to a slow-cooked curry, the right spice changes everything. Our collection is designed around the flavours people reach for again and again.</p><a class="text-link" href="#shop">Shop the collection →</a></div><div class="editorial-list"><div><span>01</span><b>Character</b><p>Distinctive aroma and flavour inspired by Sri Lankan spice traditions.</p></div><div><span>02</span><b>Care</b><p>Thoughtful selection and packaging to keep each product ready for your kitchen.</p></div><div><span>03</span><b>Simplicity</b><p>A clean shopping experience from discovery to checkout.</p></div></div></div></section>

<section class="quote-section"><div class="container quote-inner"><div class="quote-mark">“</div><blockquote>Good food starts with good ingredients — and a little bit of Ceylon.</blockquote><span>THE CEYLON SPICE HUB JOURNAL</span></div></section>

<section class="newsletter" id="contact"><div class="container newsletter-inner"><div><p class="eyebrow">STAY IN THE LOOP</p><h2>A little more Ceylon<br><em>in your inbox.</em></h2><p>New arrivals, recipes and occasional offers. No noise, just the good stuff.</p></div><form onsubmit="subscribe(event)"><div class="newsletter-input"><span>✉</span><input type="email" placeholder="Your email address" required></div><button>Subscribe <span>↗</span></button></form></div></section>
</main>

<footer><div class="container footer-grid"><div class="footer-brand"><a class="brand" href="#home"><span>Ceylon</span><small>SPICE HUB</small></a><p>The soul of Ceylon, selected for your table.</p><div class="socials"><span>f</span><span>◎</span><span>in</span></div></div><div><h4>Explore</h4><a href="#shop">Shop spices</a><a href="#categories">Collections</a><a href="#about">Our story</a></div><div><h4>Your account</h4><a href="<?=$user?'profile.php':'login.php'?>"><?=$user?'My profile':'Login'?></a><a href="cart.php">Shopping bag</a><a href="checkout.php">Checkout</a></div><div><h4>Get in touch</h4><p>Badulla, Sri Lanka</p><p>hello@ceylonspicehub.lk</p><p>+94 77 123 4567</p></div></div><div class="copyright"><div class="container"><span>© <?=date('Y')?> Ceylon Spice Hub</span><span>Authentic Sri Lankan spices · Made for modern kitchens</span></div></div></footer>
<div id="message" class="toast"></div>
<script src="script.js"></script>
</body></html>
