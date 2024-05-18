function liveSearch() {
    let query = document.getElementById('search').value;
    if (query.length > 0) {
        fetch('live_search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ query: query })
        })
        .then(response => response.json())
        .then(data => {
            let searchResults = document.getElementById('search-results');
            searchResults.innerHTML = '';
            if (data.success) {
                data.products.forEach(product => {
                    let item = document.createElement('a');
                    item.href = `product_detail.php?id=${product.PRODUCTID}`;
                    item.textContent = `${product.NAME} - $${product.PRICE}`;
                    searchResults.appendChild(item);
                });
                searchResults.style.display = 'block';
            } else {
                searchResults.style.display = 'none';
            }
        });
    } else {
        document.getElementById('search-results').style.display = 'none';
    }
}
