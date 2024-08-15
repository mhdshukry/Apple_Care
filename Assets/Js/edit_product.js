document.addEventListener('DOMContentLoaded', function () {
    // Get the file input and filename span elements
    const fileInput = document.getElementById('image_url');
    const filenameSpan = document.getElementById('file-upload-filename');

    // Add an event listener for the change event on the file input
    fileInput.addEventListener('change', function () {
        // Check if a file was selected
        if (fileInput.files.length > 0) {
            // Display the name of the selected file
            filenameSpan.textContent = fileInput.files[0].name;
        } else {
            // If no file was selected, show the existing image URL name
            filenameSpan.textContent = '<?php echo basename($existing_image_url); ?>';
        }
    });
});

document.getElementById('add-storage').addEventListener('click', function() {
    var container = document.getElementById('storage-container');
    var input = document.createElement('input');
    input.type = 'text';
    input.name = 'storage[]';
    input.required = true;
    container.appendChild(input);
});