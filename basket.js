function updateQuantity(productId, quantity) {
    fetch('update_basket_quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_id: productId, quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh the page to update the total price
        } else {
            alert('Failed to update quantity.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the quantity.');
    });
}

function removeFromBasket(productId) {
    fetch('remove_from_basket.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh the page to update the basket
        } else {
            alert('Failed to remove product from basket.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while removing the product from the basket.');
    });
}

function addToBasket(productId) {
    fetch('add_to_basket.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added to basket successfully!');
            location.reload(); // Refresh the page after adding to the basket
        } else {
            alert('An error occurred: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
}