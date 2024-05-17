function addToWishlist(productId) {
    fetch('add_to_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added to wishlist successfully.');
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the product to the wishlist.');
    });
}
function removeFromWishlist(productId) {
    fetch('remove_from_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product removed from wishlist successfully.');
            location.reload(); // Refresh the page
        } else {
            alert('Failed to remove product from wishlist.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // alert('An error occurred while removing the product from the wishlist.');
    });
}