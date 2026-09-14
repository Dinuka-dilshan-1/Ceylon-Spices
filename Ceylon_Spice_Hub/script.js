function toast(text){
 const m=document.getElementById('message'); if(!m)return;
 m.textContent=text;m.classList.add('show');clearTimeout(window.toastTimer);
 window.toastTimer=setTimeout(()=>m.classList.remove('show'),2200);
}
function addToCart(id,name){
 fetch('add_to_cart.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'product_id='+encodeURIComponent(id)+'&quantity=1'})
 .then(r=>r.json()).then(d=>{if(d.success){const c=document.getElementById('cartCount');if(c)c.textContent=d.count;toast(name+' added to your cart.');}else toast(d.message||'Unable to add item.');})
 .catch(()=>toast('Could not connect to the server. Check XAMPP.'))
}
function toggleMenu(){document.getElementById('navMenu')?.classList.toggle('show')}
function toggleWishlist(btn){btn.classList.toggle('liked');btn.textContent=btn.classList.contains('liked')?'♥':'♡';toast(btn.classList.contains('liked')?'Added to wishlist':'Removed from wishlist')}
let activeCategory='all';
function setQuickFilter(cat,button){
 activeCategory=cat;
 document.querySelectorAll('.filter-pill').forEach(b=>b.classList.remove('active'));button.classList.add('active');
 applyFilters();
}
function selectCategory(cat){
 setTimeout(()=>{const btn=[...document.querySelectorAll('.filter-pill')].find(x=>x.textContent.toLowerCase().includes(cat.replace('-',' ')));if(btn)setQuickFilter(cat,btn)},50);
}
function applyFilters(){
 const q=(document.getElementById('searchInput')?.value||'').toLowerCase().trim();
 let visible=0;
 document.querySelectorAll('.product').forEach(p=>{
  const okQ=!q||p.dataset.name.includes(q);
  const okC=activeCategory==='all'||p.dataset.category===activeCategory;
  const ok=okQ&&okC;p.style.display=ok?'':'none';if(ok)visible++;
 });
 const count=document.getElementById('resultCount');if(count)count.textContent=visible+' product'+(visible===1?'':'s');
 const empty=document.getElementById('noResults');if(empty)empty.hidden=visible!==0;
}
function sortProducts(){
 const val=document.getElementById('sortSelect').value,list=document.getElementById('productList');
 const items=[...list.children];
 items.sort((a,b)=>val==='low'?+a.dataset.price-+b.dataset.price:val==='high'?+b.dataset.price-+a.dataset.price:0);
 items.forEach(x=>list.appendChild(x));applyFilters();
}
function subscribe(e){
 e.preventDefault();
 fetch('subscribe.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'email='+encodeURIComponent(e.target.querySelector('input').value)})
 .then(r=>r.json()).then(d=>{toast(d.message);if(d.success)e.target.reset()}).catch(()=>toast('Subscription could not be completed.'));
}
document.addEventListener('DOMContentLoaded',()=>{
 document.getElementById('searchInput')?.addEventListener('input',applyFilters);
});
