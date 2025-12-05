document.addEventListener("DOMContentLoaded", () => {
    // File upload label + name update
    const fileInput = document.getElementById("image");
    const fileName = document.getElementById("file-name");

    if (fileInput && fileName) {
        fileInput.addEventListener("change", () => {
            const name = fileInput.files.length > 0 ? fileInput.files[0].name : "No file chosen";
            fileName.textContent = name;
        });
    }

    const imgs = document.querySelectorAll(".gallery-grid img");
    imgs.forEach((img) => {
        img.style.opacity = 0;
        img.onload = () => {
            img.style.transition = "opacity 0.6s ease";
            img.style.opacity = 1;
        };
    });
});