document.addEventListener("DOMContentLoaded", () => {

    let offset = 12;
    let loading = false;
    let endReached = false;

    const loader = document.getElementById("loader");
    const container = document.getElementById("news-container");
    const filterForm = document.getElementById("news-filters");

    async function loadMoreNews() {
        if (loading || endReached) return;
        loading = true;

        loader.style.display = "block";

        const params = new URLSearchParams(new FormData(filterForm));
        params.append("offset", offset);

        const response = await fetch(`/task2/phps/newsLoad.php?${params.toString()}`);
        const data = await response.json();

        if (data.length === 0) {
            loader.textContent = "No more articles.";
            endReached = true;
            return;
        }

        data.forEach(article => {
            const card = document.createElement("div");
            card.className = "news-card";

            card.innerHTML = `
                ${article.imageUrl ? `<img src="${article.imageUrl}" />` : ""}
                <h3>${article.title}</h3>
                <p class="news-date">${article.publishedAt.substring(0,10)} — ${article.source}</p>
                <p>${article.summary.substring(0,160)}...</p>
                <a href="${article.url}" target="_blank" class="btn btn-secondary" style="margin-top:10px;">
                    🔗 Read More
                </a>
            `;

            container.appendChild(card);
        });

        offset += data.length;
        loading = false;
        loader.style.display = "none";
    }

    // Infinite scroll trigger
    window.addEventListener("scroll", () => {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 300) {
            loadMoreNews();
        }
    });

});
