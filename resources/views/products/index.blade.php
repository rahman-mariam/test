@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <button id="addProductBtn" class="btn btn-primary">Add Product</button>
    </div>

    <div class="w-50">
        <input id="search" type="text" class="form-control" placeholder="Search products...">
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th style="width:60px">#</th>
                <th>Title</th>
                <th>Description</th>
                <th style="width:120px">Price</th>
                <th style="width:120px">Action</th>
            </tr>
        </thead>
        <tbody id="productsBody">
            <tr><td colspan="5" class="text-center">Loading...</td></tr>
        </tbody>
    </table>
</div>

<nav>
    <ul id="pagination" class="pagination"></ul>
</nav>

<!-- Simple modal for add/update (uses prompt for brevity) -->

@push('scripts')
<script>
const perPage = 8;
let currentPage = 1;
let currentSearch = '';

function debounce(fn, wait){
    let t;
    return (...args)=>{ clearTimeout(t); t = setTimeout(()=>fn(...args), wait); };
}

async function fetchProducts(page = 1, search = ''){
    currentPage = page;
    currentSearch = search;
    const tbody = document.getElementById('productsBody');
    try {
        const res = await fetch(`/products?page=${page}&search=${encodeURIComponent(search)}`, { headers: { 'Accept': 'application/json' }});
        if (!res.ok) {
            const text = await res.text();
            tbody.innerHTML = `<tr><td colspan="5" class="text-danger">Error: ${escapeHtml(text.substring(0,200))}</td></tr>`;
            document.getElementById('pagination').innerHTML = '';
            return;
        }
        const json = await res.json();
        renderTable(json.data, json.meta);
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-danger">Fetch error: ${escapeHtml(err.message)}</td></tr>`;
        document.getElementById('pagination').innerHTML = '';
    }
}

function renderTable(items, meta){
    const tbody = document.getElementById('productsBody');
    tbody.innerHTML = '';
    if(!items.length){
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">No products</td></tr>';
        document.getElementById('pagination').innerHTML = '';
        return;
    }

    items.forEach((p, idx)=>{
        const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>${(meta.current_page -1)*meta.per_page + idx + 1}</td>
            <td>${escapeHtml(p.title)}</td>
            <td>${escapeHtml(p.description ?? '')}</td>
            <td>${Number(p.price).toFixed(2)}</td>
            <td>
                <button class="btn btn-sm p-0 me-2" onclick="handleEdit(${p.id})" title="Edit" style="background:transparent;border:none;">
                    <span style="color:#ffc107;font-size:1.1rem;line-height:1;">&#9998;</span>
                </button>
                <button class="btn btn-sm p-0" onclick="handleDelete(${p.id})" title="Delete" style="background:transparent;border:none;">
                    <span style="color:#dc3545;font-size:1.1rem;line-height:1;">&#128465;</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    renderPagination(meta);
}

function renderPagination(meta){
    const ul = document.getElementById('pagination');
    ul.innerHTML = '';
    for(let i=1;i<=meta.last_page;i++){
        const li = document.createElement('li');
        li.className = 'page-item' + (i===meta.current_page? ' active' : '');
        li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        li.querySelector('a').addEventListener('click', (e)=>{ e.preventDefault(); fetchProducts(i,currentSearch); });
        ul.appendChild(li);
    }
}

function escapeHtml(unsafe){ return unsafe.replace(/[&<"'>]/g, function(m){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;"})[m]; }); }

document.getElementById('search').addEventListener('input', debounce((e)=>{ fetchProducts(1,e.target.value); }, 300));

document.getElementById('addProductBtn').addEventListener('click', async ()=>{
    const title = prompt('Title'); if(!title) return;
    const description = prompt('Description') || '';
    const price = prompt('Price', '0');
    await fetch('/products', { method: 'POST', headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }, body: JSON.stringify({ title, description, price }) });
    fetchProducts(currentPage,currentSearch);
});

window.handleEdit = async function(id){
    const title = prompt('New title'); if(!title) return;
    const description = prompt('New description') || '';
    const price = prompt('New price', '0');
    await fetch(`/products/${id}`, { method: 'PUT', headers: { 'Content-Type':'application/json', 'Accept':'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content }, body: JSON.stringify({ title, description, price }) });
    fetchProducts(currentPage,currentSearch);
}

window.handleDelete = async function(id){
    if(!confirm('Delete this product?')) return;
    await fetch(`/products/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content, 'Accept':'application/json' } });
    fetchProducts(currentPage,currentSearch);
}

// initial load
fetchProducts();
</script>
@endpush

@endsection
