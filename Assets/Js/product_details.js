document.querySelectorAll('.storage-button').forEach(button => {
    button.addEventListener('click', function () {
        const storageId = this.getAttribute('data-storage-id');

        // Use AJAX to fetch the price for the selected storage option
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_storage_price.php?storage_id=' + storageId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                document.getElementById('price').innerText = 'Price: Rs' + xhr.responseText;
            }
        };
        xhr.send();
    });
});

document.addEventListener('DOMContentLoaded', (event) => {
    const colorButtons = document.querySelectorAll('.color-button');
    const clearColorButton = document.getElementById('clear-color');

    colorButtons.forEach(button => {
        button.addEventListener('click', () => {
            colorButtons.forEach(btn => btn.classList.remove('checked'));
            button.classList.add('checked');
            const selectedColor = button.getAttribute('data-color-name');
            console.log('Selected Color:', selectedColor);
            // You can add code here to handle the color selection, like updating a hidden input or making an AJAX request
        });
    });

    clearColorButton.addEventListener('click', (e) => {
        e.preventDefault();
        colorButtons.forEach(btn => btn.classList.remove('checked'));
        console.log('Color selection cleared.');
        // You can add code here to handle clearing the selection, like resetting a hidden input or making an AJAX request
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const stars = document.querySelectorAll('.star');
    let selectedRating = 0;

    stars.forEach(star => {
        star.addEventListener('click', () => {
            selectedRating = parseInt(star.getAttribute('data-rating'));
            stars.forEach(s => {
                s.classList.toggle('checked', parseInt(s.getAttribute('data-rating')) <= selectedRating);
            });
            document.querySelector('input[name="rating"]').value = selectedRating;
        });
    });

    // Set hidden input for rating
    const ratingInput = document.createElement('input');
    ratingInput.type = 'hidden';
    ratingInput.name = 'rating';
    ratingInput.value = '0';
    document.querySelector('.review-form').appendChild(ratingInput);
});