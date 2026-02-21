// Sample data
const dishes = [
    { id: 1, name: 'Milk', price: 50, image: '🥛', category: 'Perishable' },
    { id: 2, name: 'YogurtCup', price: 30, image: '🥣', category: 'Perishable' },
    { id: 3, name: 'BoiledEggs', price: 40, image: '🥚', category: 'Perishable' },
    { id: 4, name: 'Paneer', price: 80, image: '🧀', category: 'Perishable' },
    { id: 5, name: 'Bread', price: 35, image: '🍞', category: 'SemiPerishable' },
    { id: 6, name: 'CakeSlice', price: 60, image: '🍰', category: 'dessert' },
    { id: 7, name: 'Pickles', price: 120, image: '🥒', category: 'SemiPerishable' },
    { id: 8, name: 'DryFruitsMix', price: 200, image: '🥜', category: 'SemiPerishable' },
    { id: 9, name: 'Cooked Rice', price: 90, image: '🍚', category: 'Staple' },
    { id: 10, name: 'Chapati', price: 40, image: '🫓', category: 'Staple' },
    { id: 11, name: 'VegetablePulao', price: 120, image: '🥘', category: 'Staple' },
    { id: 13, name: 'CerealBox', price: 150, image: '🥣', category: 'NonPerishable' },
    { id: 14, name: 'TeaBags', price: 180, image: '🍵', category: 'NonPerishable' },
    { id: 15, name: 'CannedSoup', price: 100, image: '🥫', category: 'NonPerishable' },
    { id: 16, name: 'FrozenPizza', price: 250, image: '🍕', category: 'FrozenFoods' },
    { id: 17, name: 'IceCreamTub', price: 220, image: '🍨', category: 'FrozenFoods' },
    { id: 18, name: 'FrozenChickenNuggets', price: 280, image: '🍗', category: 'FrozenFoods' },
    { id: 19, name: 'Frozen Parathas', price: 150, image: '🫓', category: 'FrozenFoods' },
    { id: 20, name: 'PotatoChips', price: 50, image: '🥔', category: 'Processed' },
];

// Function to render dishes in a given container
function renderDishes(dishesArray, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = ''; // Clear existing items

    dishesArray.forEach(dish => {
        const dishDiv = document.createElement('div');
        dishDiv.classList.add('dish-card');

        dishDiv.innerHTML = `
        <form method="POST" action="">
            <div class="dish-image">${dish.image}</div>
            <div class="dish-info">
                <h3 class="dish-name">${dish.name}</h3>
                <p class="dish-price">₹${dish.price}</p>
            </div>
            <input type="hidden" name="dishId" value="${dish.id}">
            <input type="hidden" name="dishCategory" value="${dish.category}">
            <div class="dish-actions">
                <input type="number" name="qty" value="1" min="1" class="qty-input">
                <button class="add-to-cart-btn" type="submit" name="addToCart">Add to Cart</button>
            </div>
         </form> `;

        container.appendChild(dishDiv);
    });
}

// Render dishes on different pages
renderDishes(dishes, 'dashboard-dishes'); // Dashboard page
renderDishes(dishes, 'food-order-dishes'); // Food Order page
renderDishes(dishes.filter(d => d.category === 'dessert'), 'cake-dishes'); // Birthday Cakes

window.addEventListener('DOMContentLoaded', (event) => {
    const emptyDiv = document.getElementById('emptyIndicator');
    if (emptyDiv) {
        setTimeout(() => {
            // Fade out smoothly
            emptyDiv.style.transition = 'opacity 1s ease';
            emptyDiv.style.opacity = '0';
            // After fade-out, remove from DOM
            setTimeout(() => {
                emptyDiv.remove();
            }, 1000);
        }, 3000); // wait 3 seconds before starting fade-out
    }
});
