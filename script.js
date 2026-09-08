let cartCount=0;
function toast(text){const m=document.getElementById('message');m.textContent=text;m.classList.add('show');clearTimeout(window.tm);window.tm=setTimeout(()=>m.classList.remove('show'),1800)}
function addToCart(name){cartCount++;document.getElementById('cartCount').textContent=cartCount;toast(name+' added to cart!')}
function showCart(){toast(cartCount?'Your cart has '+cartCount+' item(s).':'Your cart is empty.')}
function toggleMenu(){document.getElementById('navMenu').classList.toggle('show')}
function searchProducts(){applyFilters()}
document.getElementById('searchInput').addEventListener('input',applyFilters);
document.querySelectorAll('.filter,input[name="price"]').forEach(x=>x.addEventListener('change',applyFilters));
function applyFilters(){const q=document.getElementById('searchInput').value.toLowerCase().trim();const cats=[...document.querySelectorAll('.filter:checked')].map(x=>x.value);const price=document.querySelector('input[name="price"]:checked')?.value;let visible=0;document.querySelectorAll('.product').forEach(p=>{const name=p.dataset.name, val=+p.dataset.price;const okQ=!q||name.includes(q);const okCat=!cats.length||cats.includes(name);const okPrice=!price||(price==='under1000'?val<1000:val>=1000);p.style.display=okQ&&okCat&&okPrice?'':'none';if(okQ&&okCat&&okPrice)visible++});document.getElementById('resultCount').textContent=visible+' product'+(visible===1?'':'s');document.getElementById('chips').textContent=[...cats.map(c=>' '+c+' ×'),price?' '+price+' ×':''].join('')}
function clearFilters(){document.querySelectorAll('.filter,input[name="price"]').forEach(x=>x.checked=false);document.getElementById('searchInput').value='';applyFilters()}
function sortProducts(){const val=document.getElementById('sortSelect').value, list=document.getElementById('productList');const items=[...list.children];items.sort((a,b)=>{if(val==='low')return +a.dataset.price-+b.dataset.price;if(val==='high')return +b.dataset.price-+a.dataset.price;return 0});items.forEach(x=>list.appendChild(x))}
document.querySelectorAll('.wishlist').forEach(b=>b.addEventListener('click',()=>{b.textContent=b.textContent==='♡'?'♥':'♡';toast(b.textContent==='♥'?'Added to wishlist':'Removed from wishlist')}));
function subscribe(e){e.preventDefault();toast('Thank you for subscribing!');e.target.reset()}
