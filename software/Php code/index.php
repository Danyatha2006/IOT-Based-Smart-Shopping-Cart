<?php include 'db_config.php'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Retail Store - Frontend Demo</title>
  <style>
    :root{
      --bg:#0f1724; /* dark navy */
      --panel:#0b1220;
      --muted:#94a3b8;
      --accent:#06b6d4; /* teal */
      --accent-2:#7c3aed; /* purple */
      --card:#071029;
      --glass:rgba(255,255,255,0.04);
      --success:#10b981;
      --danger:#ef4444;
      --radius:14px;
      --card-padding:18px;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      background: linear-gradient(180deg, #071427 0%, #051126 100%);
      color:#e6eef7;
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      padding:28px;
    }

    .app{
      max-width:1200px;
      margin:0 auto;
      display:grid;
      grid-template-columns: 1fr 360px;
      gap:22px;
    }

    header{
      grid-column:1/-1;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:16px;
      margin-bottom:6px;  }
    .brand{
      display : flex ;
      align-items : center ;
      gap : 14px
    }
    .logo{
      width:56px;height:56px;border-radius:12px;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:flex;align-items:center;justify-content:center;font-weight:700;color:#051124;font-size:20px
    }
    h1{margin:0;font-size:20px}
    .controls{display:flex;align-items:center;gap:8px}

    .search{
      display:flex;align-items:center;background:var(--glass);padding:8px 12px;border-radius:12px;gap:8px;color:var(--muted)
    }
    .search input{background:transparent;border:0;outline:0;color:inherit;width:220px}
    .filters{display:flex;gap:8px}
    .btn{
      background:linear-gradient(90deg,var(--accent),var(--accent-2));border:0;padding:9px 12px;border-radius:10px;color:#061022;cursor:pointer;font-weight:600
    }
    .ghost{background:transparent;border:1px solid rgba(255,255,255,0.06);padding:8px 10px;border-radius:10px;color:var(--muted);cursor:pointer}

    .main{
      background:linear-gradient(180deg, rgba(255,255,255,0.02), transparent);padding:18px;border-radius:var(--radius);box-shadow:0 10px 30px rgba(2,6,23,0.6)
    }

    .grid{
      display:grid;grid-template-columns:repeat(3,1fr);gap:14px
    }
    .card{
      background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));padding:14px;border-radius:12px;border:1px solid rgba(255,255,255,0.03);min-height:160px;display:flex;flex-direction:column;justify-content:space-between
    }
    .product-head{display:flex;gap:12px;align-items:center}
    .thumb{width:64px;height:64px;border-radius:10px;background:linear-gradient(135deg,#0b1220,#091424);display:flex;align-items:center;justify-content:center;font-weight:700;color:var(--muted)}
    .meta{flex:1}
    .name{font-weight:700;margin:0}
    .brandSmall{font-size:12px;color:var(--muted);margin-top:6px}
    .priceRow{display:flex;align-items:center;justify-content:space-between;margin-top:12px}
    .final{font-weight:800;font-size:18px}
    .mrp{font-size:13px;color:var(--muted);text-decoration:line-through;margin-left:8px}
    .discountTag{background:rgba(255,255,255,0.04);padding:6px;border-radius:8px;font-weight:700;color:var(--success);font-size:13px}

    /* right column / cart */
    .sidebar{
      background:linear-gradient(180deg, rgba(255,255,255,0.02), transparent);padding:18px;border-radius:var(--radius);height:calc(100vh - 80px);position:sticky;top:28px;display:flex;flex-direction:column;gap:12px;overflow:auto
    }
    .cartItem{display:flex;gap:12px;align-items:center;padding:10px;background:rgba(255,255,255,0.02);border-radius:10px}
    .qty{display:flex;align-items:center;gap:8px}
    .qty button{background:transparent;border:1px solid rgba(255,255,255,0.04);padding:6px;border-radius:6px;color:var(--muted);cursor:pointer}
    .totals{margin-top:auto;padding-top:10px;border-top:1px dashed rgba(255,255,255,0.03)}
    .totals .row{display:flex;justify-content:space-between;margin-top:8px}

    /* Bundles & Promotions */
    .sectionTitle{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
    .bundleList{display:flex;flex-direction:column;gap:12px}
    .bundleCard{display:flex;flex-direction:column;gap:6px;padding:12px;background:rgba(255,255,255,0.015);border-radius:10px;border:1px solid rgba(255,255,255,0.02)}
    .promo{padding:10px;border-radius:10px;background:linear-gradient(90deg, rgba(124,58,237,0.1), rgba(6,182,212,0.06));display:flex;align-items:center;justify-content:space-between}.promo small{color:var(--muted)}

    footer{grid-column:1/-1;margin-top:18px;color:var(--muted);font-size:13px}

    /* responsive */
    @media (max-width:980px){
      .app{grid-template-columns:1fr;}
      .grid{grid-template-columns:repeat(2,1fr)}
      .sidebar{height:auto;position:relative;top:0}
    }
    @media (max-width:640px){
      .grid{grid-template-columns:1fr}
      .search input{width:120px}
    }

    /* small helpers */
    .muted{color:var(--muted)}
    .pill{padding:6px 8px;border-radius:8px;background:rgba(255,255,255,0.02);font-weight:600}
    .empty{opacity:0.6;color:var(--muted);text-align:center;padding:28px;border-radius:10px}
  </style>
</head>
<body>
  <div class="app">
    <header>
      <div class="brand">
        <div class="logo">RS</div>
        <div>
          <h1>Retail Store — Demo UI</h1>
          <div class="muted" style="font-size:13px">Products, Bundles, Promotions & Cart simulation</div>
        </div>
      </div>

      <div class="controls">
        <div class="search">
          🔎
          <input id="q" placeholder="Search products or brand..." />
        </div>
        <div class="filters">
          <select id="categoryFilter" class="ghost">
            <option value="">All categories</option>
          </select>
        </div>
        <button class="btn" id="clearBtn">Clear Cart</button>
      </div>
    </header>

    <main class="main">
      <section style="margin-bottom:18px;display:flex;gap:12px;align-items:flex-start">
        <div style="flex:1">
          <div class="sectionTitle">
            <h2 style="margin:0">Products</h2>
            <div class="muted">Showing <span id="count">0</span> products</div>
          </div>

          <div id="products" class="grid">
            <!-- product cards injected here -->
          </div>
        </div>

        <aside style="width:320px;display:flex;flex-direction:column;gap:12px">
          <div>
            <div class="sectionTitle"><h3 style="margin:0">Bundles</h3><div class="muted">Combos & offers</div></div>
            <div id="bundles" class="bundleList"></div>
          </div>

          <div>
            <div class="sectionTitle"><h3 style="margin:0">Promotions</h3><div class="muted">Active promotions</div></div>
            <div id="promos"></div>
          </div>
        </aside>
      </section>

      <section>
        <div class="sectionTitle"><h3 style="margin:0">Selected Cart</h3><div class="muted">Temporary simulation cart</div></div>
        <div id="cartPreview"></div>
      </section>

    </main>

    <aside class="sidebar">
      <div style="display:flex;justify-content:space-between;align-items:center">
        <div>
          <div class="muted">Your Cart</div>
          <div style="font-weight:800;font-size:20px">Checkout</div>
        </div>
        <div class="pill" id="cartCount">0 items</div>
      </div>

      <div id="cartList">
        <!-- cart items -->
      </div>

      <div class="totals">
        <div class="row"><div class="muted">Subtotal</div><div id="subtotal">₹0.00</div></div>
        <div class="row"><div class="muted">Discounts</div><div id="discountTotal">-₹0.00</div></div>
        <div class="row"><div class="muted">Tax (GST 0%)</div><div id="tax">₹0.00</div></div>
        <div class="row" style="margin-top:12px;font-weight:900;font-size:18px"><div>Total</div><div id="grand">₹0.00</div></div>

        <div style="margin-top:14px;display:flex;gap:8px">
          <button class="btn" id="checkoutBtn">Proceed</button>
          <button class="ghost" id="exportBtn">Export JSON</button>
        </div>
      </div>
    </aside>

    <footer>Built for demo & education — Data is local to this page (no backend).</footer>
  </div>

  <script>
    /* ---------- Sample Data matching your schema ---------- */
    const products = [
      { product_id:1, barcode:'B10001', name:'AquaFresh Shampoo 200ml', brand:'CleanCo', category:'Shampoo', mrp:199.00, discount:10.0, final_price:179.10, pack_size:'200ml', expiry_date:'2026-08-01', sponsored_flag:false, store_margin:10},
      { product_id:2, barcode:'B10002', name:'CareSoap Bar 100g', brand:'SoftSkin', category:'Soap', mrp:49.00, discount:0, final_price:49.00, pack_size:'100g', expiry_date:'2027-01-15', sponsored_flag:true, store_margin:15},
      { product_id:3, barcode:'B10003', name:'Ultra Toothpaste 150g', brand:'SmileX', category:'Toothpaste', mrp:129.00, discount:20, final_price:103.20, pack_size:'150g', expiry_date:'2026-04-22', sponsored_flag:false, store_margin:12},
      { product_id:4, barcode:'B10004', name:'Herbal Conditioner 200ml', brand:'GreenLeaf', category:'Conditioner', mrp:249.00, discount:15, final_price:211.65, pack_size:'200ml', expiry_date:'2026-11-01', sponsored_flag:false, store_margin:11},
      { product_id:5, barcode:'B10005', name:'Kids Bubble Bath 500ml', brand:'HappyKids', category:'Bath', mrp:349.00, discount:5, final_price:331.55, pack_size:'500ml', expiry_date:'2027-06-01', sponsored_flag:true, store_margin:20},
      { product_id:6, barcode:'B10006', name:'Economy Dishwash 1L', brand:'HomeSafe', category:'Cleaning', mrp:99.00, discount:0, final_price:99.00, pack_size:'1L', expiry_date:'2028-01-01', sponsored_flag:false, store_margin:8}
    ];

    const bundles = [
      { bundle_id:1, bundle_name:'Haircare Combo', total_price:349.00, discount_text:'Save ₹100', items:[1,4], description:'Shampoo + Conditioner combo' },
      { bundle_id:2, bundle_name:'Kids Bath Set', total_price:599.00, discount_text:'Buy 2 save ₹50', items:[5], description:'Bubble Bath + Toys' }
    ];

    const promotions = [
      { promo_id:1, product_id:1, promo_type:'Festival', discount_percent:5, valid_till:'2026-12-31' },
      { promo_id:2, product_id:3, promo_type:'Expiry Clearance', discount_percent:30, valid_till:'2025-12-31' }
    ];

    /* ---------- Application State ---------- */
    let cart = [];
    let activePromo = null;

    /* ---------- Utilities ---------- */
    function money(n){ return '₹'+Number(n).toFixed(2); }

    function findProduct(pid){ return products.find(p=>p.product_id===pid); }

    function calcFinalPrice(p){
      // compute from mrp and discount field; promotions applied separately
      const afterDiscount = p.mrp * (1 - (p.discount||0)/100);
      return Math.round(afterDiscount*100)/100;
    }

    function applyPromotionsToPrice(product){
      let price = calcFinalPrice(product);
      // check active promo that applies to this product and still valid
      const now = new Date();
      const applicable = promotions.filter(pr=>pr.product_id===product.product_id && new Date(pr.valid_till) >= now);
      if(applicable.length){
        // choose highest discount
        const max = Math.max(...applicable.map(a=>a.discount_percent));
        price = price * (1 - max/100);
      }
      return Math.round(price*100)/100;
    }

    /* ---------- Rendering ---------- */
    function renderCategoryFilter(){
      const cat = [...new Set(products.map(p=>p.category))];
      const sel = document.getElementById('categoryFilter');
      cat.forEach(c=>{
        const o = document.createElement('option'); o.value=c; o.textContent=c; sel.appendChild(o);
      });
    }

    function renderProducts(filterQ=''){
      const container = document.getElementById('products'); container.innerHTML='';
      const q = (document.getElementById('q').value || '').toLowerCase();
      const cat = document.getElementById('categoryFilter').value;
      let list = products.slice();
      if(cat) list = list.filter(p=>p.category===cat);
      if(q) list = list.filter(p=> (p.name+ ' '+p.brand+' '+p.category).toLowerCase().includes(q));
      document.getElementById('count').textContent = list.length;

      if(list.length===0){ container.innerHTML='<div class="empty">No products found</div>'; return; }

      for(const p of list){
        const card = document.createElement('div'); card.className='card';
        card.innerHTML = `
          <div class="product-head">
            <div class="thumb">${p.brand[0]||'P'}</div>
            <div class="meta">
              <p class="name">${p.name}</p>
              <div class="brandSmall">${p.brand} • ${p.pack_size}</div>
            </div>
            <div style="text-align:right">
              <div class="discountTag">${p.discount}%</div>
            </div>
          </div>
          <div class="priceRow">
            <div>
              <div class="final">${money(applyPromotionsToPrice(p))}</div>
              <div class="muted" style="font-size:13px">MRP <span class="mrp">${money(p.mrp)}</span></div>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;align-items:flex-end">
              <button class="btn" data-pid="${p.product_id}">Add</button>
              <div class="muted" style="font-size:12px">${p.sponsored_flag? 'Sponsored' : ''}</div>
            </div>
          </div>
        `;
        container.appendChild(card);
      }

      // attach handlers
      container.querySelectorAll('.btn').forEach(b=>b.addEventListener('click', e=>{
        const pid = Number(e.currentTarget.dataset.pid);
        addToCart(pid,1);
      }));
    }

    function renderBundles(){
      const el = document.getElementById('bundles'); el.innerHTML='';
      for(const b of bundles){
        const node = document.createElement('div'); node.className='bundleCard';
        const items = b.items.map(id=>findProduct(id)).filter(Boolean).map(p=>p.name).join(', ');
        node.innerHTML = `
          <div style="display:flex;justify-content:space-between;align-items:center">
            <strong>${b.bundle_name}</strong>
            <div class="muted">${money(b.total_price)}</div>
          </div>
          <div class="muted" style="font-size:13px">${b.description}</div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px">
            <div class="muted" style="font-size:13px">${items}</div>
            <div style="display:flex;gap:8px"><button class="btn" data-bid="${b.bundle_id}">Add Bundle</button></div>
          </div>
        `;
        el.appendChild(node);
      }
      el.querySelectorAll('.btn').forEach(b=>b.addEventListener('click',e=>{
        const bid = Number(e.currentTarget.dataset.bid);
        const bundle = bundles.find(x=>x.bundle_id===bid);
        bundle.items.forEach(id=>addToCart(id,1));
      }));
    }

    function renderPromotions(){
      const el = document.getElementById('promos'); el.innerHTML='';
      const now = new Date();
      const active = promotions.filter(p=> new Date(p.valid_till) >= now);
      if(active.length===0){ el.innerHTML='<div class="empty">No active promotions</div>'; return; }
      for(const p of active){
        const prod = findProduct(p.product_id);
        const node = document.createElement('div'); node.className='promo';
        node.innerHTML = `
          <div>
            <div style="font-weight:700">${prod?prod.name:'Product'} — ${p.discount_percent}%</div>
            <small class="muted">${p.promo_type} • valid till ${p.valid_till}</small>
          </div>
          <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end">
            <button class="ghost" data-pid="${p.product_id}">Apply</button>
            <div class="muted" style="font-size:12px">Promo ID: ${p.promo_id}</div>
          </div>
        `;
        el.appendChild(node);
      }

      el.querySelectorAll('.ghost').forEach(b=>b.addEventListener('click', e=>{
        const pid = Number(e.currentTarget.dataset.pid);
        // set activePromo to promo for that product (highest percent if multiple)
        const now = new Date();
        const applicable = promotions.filter(pr=>pr.product_id===pid && new Date(pr.valid_till)>=now);
        if(applicable.length){
          activePromo = applicable.reduce((a,b)=> a.discount_percent>b.discount_percent? a:b);
          alert('Applied promotion: ' + activePromo.discount_percent + '% for product id '+ pid);
          renderProducts(); renderCart();
        }
      }));
    }

    /* ---------- Cart Logic ---------- */
    function addToCart(product_id, qty=1){
      const existing = cart.find(c=>c.product_id===product_id);
      if(existing){ existing.quantity += qty; }
      else cart.push({ product_id, quantity:qty });
      renderCart(); renderCartPreview();
    }

    function removeFromCart(product_id){ cart = cart.filter(c=>c.product_id!==product_id); renderCart(); renderCartPreview(); }

    function updateQuantity(product_id, q){ const it = cart.find(c=>c.product_id===product_id); if(!it) return; it.quantity = Math.max(0, Number(q)||0); if(it.quantity===0) removeFromCart(product_id); else renderCart(); renderCartPreview(); }

    function clearCart(){ cart = []; activePromo = null; renderCart(); renderCartPreview(); }

    function calculateTotals(){
      let subtotal = 0; let discountTotal = 0;
      for(const c of cart){
        const prod = findProduct(c.product_id);
        if(!prod) continue;
        const base = calcFinalPrice(prod) * c.quantity;
        let final = applyPromotionsToPrice(prod) * c.quantity;
        subtotal += base;
        discountTotal += (base - final);
      }
      subtotal = Math.round(subtotal*100)/100;
      discountTotal = Math.round(discountTotal*100)/100;
      const tax = 0;
      const grand = Math.round((subtotal - discountTotal + tax)*100)/100;
      return {subtotal, discountTotal, tax, grand};
    }

    function renderCart(){
      const el = document.getElementById('cartList'); el.innerHTML='';
      if(cart.length===0){ el.innerHTML='<div class="empty">Cart is empty. Add products on the left.</div>'; document.getElementById('cartCount').textContent='0 items'; return; }
      document.getElementById('cartCount').textContent = cart.reduce((s,i)=>s+i.quantity,0) + ' items';
      for(const c of cart){
        const p = findProduct(c.product_id);
        const node = document.createElement('div'); node.className='cartItem';
        node.innerHTML = `
          <div style="flex:1">
            <div style="font-weight:700">${p.name}</div>
            <div class="muted" style="font-size:13px">${p.brand} • ${p.pack_size}</div>
          </div>
          <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px">
            <div style="font-weight:800">${money(applyPromotionsToPrice(p) * c.quantity)}</div>
            <div class="qty">
              <button data-op="dec" data-pid="${p.product_id}">-</button>
              <div style="padding:6px 10px;background:rgba(255,255,255,0.02);border-radius:8px">${c.quantity}</div>
              <button data-op="inc" data-pid="${p.product_id}">+</button>
            </div>
            <button class="ghost" data-remove="${p.product_id}">Remove</button>
          </div>
        `;
        el.appendChild(node);
      }

      // attach events
      el.querySelectorAll('[data-op]').forEach(b=>b.addEventListener('click',e=>{
        const pid = Number(e.currentTarget.dataset.pid);
        const op = e.currentTarget.dataset.op;
        const it = cart.find(x=>x.product_id===pid);
        if(!it) return;
        if(op==='inc') updateQuantity(pid, it.quantity+1);
        else updateQuantity(pid, it.quantity-1);
      }));

      el.querySelectorAll('[data-remove]').forEach(b=>b.addEventListener('click',e=>{
        const pid = Number(e.currentTarget.dataset.remove); removeFromCart(pid);
      }));

      // update totals
      const t = calculateTotals();
      document.getElementById('subtotal').textContent = money(t.subtotal);
      document.getElementById('discountTotal').textContent = '-'+money(t.discountTotal).replace('₹','');
      document.getElementById('tax').textContent = money(t.tax);
      document.getElementById('grand').textContent = money(t.grand);

      // checkout/export handlers
      document.getElementById('exportBtn').onclick = ()=>{
        const payload = { cart:cart.map(ci=> ({...ci, product: findProduct(ci.product_id)})), totals: t };
        const data = JSON.stringify(payload, null, 2);
        const blob = new Blob([data], {type:'application/json'});
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a'); a.href = url; a.download = 'cart.json'; a.click(); URL.revokeObjectURL(url);
      };

      document.getElementById('checkoutBtn').onclick = ()=>{
        alert('Checkout simulated. Total: ' + document.getElementById('grand').textContent);
      };
    }

    function renderCartPreview(){
      const el = document.getElementById('cartPreview'); el.innerHTML='';
      if(cart.length===0){ el.innerHTML='<div class="empty">No items selected for simulation.</div>'; return; }
      const ul = document.createElement('div'); ul.style.display='grid'; ul.style.gridTemplateColumns='repeat(3,1fr)'; ul.style.gap='8px';
      for(const c of cart){ const p = findProduct(c.product_id); const block = document.createElement('div'); block.className='card';block.style.padding='8px'; 
      block.innerHTML = `<div style="font-weight:700">${p.name}</div><div class="muted" style="font-size:12px">${c.quantity}${money(applyPromotionsToPrice(p))}</div>; ul.appendChild(block)`; }
      el.appendChild(ul);
    }

    /* ---------- Wiring & events ---------- */
    document.getElementById('q').addEventListener('input', ()=> renderProducts());
    document.getElementById('categoryFilter').addEventListener('change', ()=> renderProducts());
    document.getElementById('clearBtn').addEventListener('click', ()=> { if(confirm('Clear cart?')) clearCart(); });

    document.getElementById('checkoutBtn').addEventListener('click', ()=>{ /* handled in renderCart to ensure totals exist */ });

    // initial render
    renderCategoryFilter(); renderProducts(); renderBundles(); renderPromotions(); renderCart();

  </script>
</body>
</html>