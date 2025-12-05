// AstroGallery Lightbox with Zoom
document.addEventListener("DOMContentLoaded", () => {
    const lightbox = document.createElement("div");
    lightbox.className = "lightbox";
    document.body.appendChild(lightbox);

    const imgEl = document.createElement("img");
    lightbox.appendChild(imgEl);

    let zoomed = false;
    let isDragging = false;
    let startX, startY, scrollLeft, scrollTop;

    // Open lightbox
    document.querySelectorAll(".gallery-grid img").forEach(img => {
        img.addEventListener("click", () => {
            imgEl.src = img.src;
            imgEl.classList.remove("zoomed");
            lightbox.style.display = "flex";
            zoomed = false;
        });
    });

    // Zoom toggle
    imgEl.addEventListener("click", (e) => {
        e.stopPropagation();
        zoomed = !zoomed;
        imgEl.classList.toggle("zoomed", zoomed);
    });

    // Enable drag to move when zoomed
    imgEl.addEventListener("mousedown", (e) => {
        if (!zoomed) return;
        isDragging = true;
        startX = e.pageX - imgEl.offsetLeft;
        startY = e.pageY - imgEl.offsetTop;
        imgEl.style.cursor = "grabbing";
        e.preventDefault();
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
        imgEl.style.cursor = zoomed ? "zoom-out" : "zoom-in";
    });

    document.addEventListener("mousemove", (e) => {
        if (!isDragging || !zoomed) return;
        e.preventDefault();
        const moveX = e.movementX;
        const moveY = e.movementY;
        imgEl.style.transform = `scale(1.6) translate(${moveX}px, ${moveY}px)`;
    });

    // Close when clicking outside
    lightbox.addEventListener("click", (e) => {
        if (e.target === lightbox) {
            lightbox.style.display = "none";
            imgEl.src = "";
            imgEl.classList.remove("zoomed");
            zoomed = false;
        }
    });

    // Close on ESC key
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            lightbox.style.display = "none";
            imgEl.src = "";
            imgEl.classList.remove("zoomed");
            zoomed = false;
        }
    });
});