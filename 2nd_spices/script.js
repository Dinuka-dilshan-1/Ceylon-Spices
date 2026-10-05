function toast(text){
  const m=document.getElementById('message');
  if(!m){console.log(text);return;}
  m.textContent=text;m.classList.add('show');
  clearTimeout(window.toastTimer);window.toastTimer=setTimeout(()=>m.classList.remove('show'),2300);
}

function submitAddToCart(form,event){
  // IMPORTANT: return false synchronously. An async function returns a Promise,
  // which is truthy to an inline onsubmit handler and causes navigation to
  // add_to_cart.php instead of keeping the user on the shop page.
  if(event && event.preventDefault) event.preventDefault();
  if(!window.fetch){return true;}

  const button=form.querySelector('button[type="submit"]');
  if(button) button.disabled=true;
  (async function(){
    try{
      const body=new URLSearchParams(new FormData(form));
      const response=await fetch(form.action,{method:'POST',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin',body});
      const text=await response.text();
      let data;
      try{data=JSON.parse(text);}catch(err){throw new Error('Cart server response is not valid. Check the database connection and import database.sql.');}
      if(!response.ok || !data.success) throw new Error(data.message || 'Unable to add item to cart.');
      const c=document.getElementById('cartCount');if(c)c.textContent=data.count;
      toast(data.message || 'Product added to your cart ✓');
    }catch(err){
      console.error('Add to cart error:',err);
      toast(err.message || 'Could not add this product.');
    }finally{if(button)button.disabled=false;}
  })();
  return false;
}

function addToCart(id,name){
  const form=document.querySelector('.add-cart-form input[name="product_id"][value="'+CSS.escape(String(id))+'"]')?.closest('form');
  if(form)return submitAddToCart(form);
  const body=new URLSearchParams({product_id:String(id),quantity:'1',ajax:'1'});
  fetch('add_to_cart.php',{method:'POST',headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},credentials:'same-origin',body})
    .then(r=>r.json()).then(d=>{if(!d.success)throw new Error(d.message||'Unable to add item.');const c=document.getElementById('cartCount');if(c)c.textContent=d.count;toast((d.message||name+' added to your cart.')+' ✓');})
    .catch(e=>toast(e.message||'Could not add this product.'));
  return false;
}

function toggleMenu(){document.getElementById('navMenu')?.classList.toggle('show')}
function toggleWishlist(btn){btn.classList.toggle('liked');btn.textContent=btn.classList.contains('liked')?'♥':'♡';toast(btn.classList.contains('liked')?'Saved to wishlist':'Removed from wishlist')}
let activeCategory='all';
function setQuickFilter(cat,button){activeCategory=cat;document.querySelectorAll('.filter-pill').forEach(b=>b.classList.remove('active'));if(button)button.classList.add('active');applyFilters()}
function selectCategory(cat){setTimeout(()=>{document.getElementById('shop')?.scrollIntoView({behavior:'smooth'});const btn=[...document.querySelectorAll('.filter-pill')].find(x=>x.textContent.toLowerCase().includes(cat.replace('-',' ')));if(btn)setQuickFilter(cat,btn)},60)}
function applyFilters(){const q=(document.getElementById('searchInput')?.value||'').toLowerCase().trim();let visible=0;document.querySelectorAll('.product').forEach(p=>{const okQ=!q||p.dataset.name.includes(q);const okC=activeCategory==='all'||p.dataset.category===activeCategory;const ok=okQ&&okC;p.style.display=ok?'':'none';if(ok)visible++});const count=document.getElementById('resultCount');if(count)count.textContent=visible+' product'+(visible===1?'':'s');const empty=document.getElementById('noResults');if(empty)empty.hidden=visible!==0}
function sortProducts(){const val=document.getElementById('sortSelect').value,list=document.getElementById('productList');const items=[...list.children];if(val==='low')items.sort((a,b)=>+a.dataset.price-+b.dataset.price);else if(val==='high')items.sort((a,b)=>+b.dataset.price-+a.dataset.price);items.forEach(x=>list.appendChild(x));applyFilters()}
function subscribe(e){e.preventDefault();const input=e.target.querySelector('input');fetch('subscribe.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'email='+encodeURIComponent(input.value)}).then(async r=>{const t=await r.text();return JSON.parse(t)}).then(d=>{toast(d.message);if(d.success)e.target.reset()}).catch(()=>toast('Subscription could not be completed.'))}
document.addEventListener('DOMContentLoaded',()=>{document.getElementById('searchInput')?.addEventListener('input',applyFilters);document.querySelectorAll('#navMenu a').forEach(a=>a.addEventListener('click',()=>document.getElementById('navMenu')?.classList.remove('show')))});
