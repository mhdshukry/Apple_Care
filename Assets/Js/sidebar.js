document.querySelectorAll('.sidebar ul li a:not(.social-media-link)').forEach(link => {
    link.addEventListener('click', function () {
        document.querySelector('.sidebar ul li.active').classList.remove('active');
        this.parentElement.classList.add('active');
    });
});


document.getElementById('image_url').addEventListener('change', function () {
    var fileName = this.files[0].name;
    document.getElementById('file-upload-filename').textContent = fileName;
});
