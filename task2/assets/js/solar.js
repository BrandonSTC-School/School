document.addEventListener('DOMContentLoaded', function () {

    const solar = document.querySelector('.solar-visual');
    const tooltip = document.getElementById('cursor-tooltip');

    if (!solar || !tooltip) {
        console.error("❌ Tooltip or solar container not found.");
        return;
    }

    const hoverTargets = solar.querySelectorAll('.sun, .planet, .moon');

    // Original listeners for real hover
    function runOriginalMouseEnter(item) {
        solar.classList.add('solar-paused');

        const name = item.dataset.name || "Unknown Object";
        const size = item.dataset.size || null;
        const mass = item.dataset.mass || null;
        const age = item.dataset.age || null;
        const temp = item.dataset.temp || null;
        const wind = item.dataset.wind || null;
        const dist = item.dataset.distance || null;

        tooltip.innerHTML = `
            <strong>${name}</strong>
            <ul>
                ${size ? `<li><b>Size:</b> ${size}</li>` : ""}
                ${mass ? `<li><b>Mass:</b> ${mass}</li>` : ""}
                ${age ? `<li><b>Age:</b> ${age}</li>` : ""}
                ${temp ? `<li><b>Temperature:</b> ${temp}</li>` : ""}
                ${wind ? `<li><b>Wind:</b> ${wind}</li>` : ""}
                ${dist ? `<li><b>Distance:</b> ${dist}</li>` : ""}
            </ul>
        `;

        tooltip.style.opacity = "1";
    }

    function runOriginalMouseLeave() {
        tooltip.style.opacity = "0";
        solar.classList.remove('solar-paused');
    }

    function moveTooltip(e) {
        tooltip.style.left = (e.clientX + 20) + "px";
        tooltip.style.top  = (e.clientY + 20) + "px";
    }

    // Bind original events
    hoverTargets.forEach(item => {
        item.addEventListener('mouseenter', () => runOriginalMouseEnter(item));
        item.addEventListener('mouseleave', runOriginalMouseLeave);
        item.addEventListener('mousemove', moveTooltip);
    });

    // PROXIMITY HOVER
    const PROXIMITY = 50; // px around object
    let active = null;

    window.addEventListener('mousemove', (e) => {
        let nearest = null;

        hoverTargets.forEach(item => {
            const rect = item.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;

            const dx = e.clientX - cx;
            const dy = e.clientY - cy;
            const dist = Math.sqrt(dx * dx + dy * dy);

            if (dist <= PROXIMITY) {
                nearest = item;
            }
        });

        // If cursor moves away completely
        if (!nearest) {
            if (active) {
                runOriginalMouseLeave();
                active = null;
            }
            return;
        }

        // If hovering same item, just move tooltip
        if (nearest === active) {
            moveTooltip(e);
            return;
        }

        // New item detected
        active = nearest;
        runOriginalMouseEnter(nearest);
        moveTooltip(e);
    });

    // SCALE SYSTEM (unchanged)
    function autoScaleSolar() {
        const wrapper = document.querySelector('.solar-visual-wrapper');
        if (!wrapper) return;

        let baseWidth = 1000;
        let scale = wrapper.clientWidth / baseWidth;

        scale = Math.min(scale, 1);
        scale = Math.max(scale, 0.35);

        solar.style.transform = `scale(${scale})`;
        solar.style.transformOrigin = 'top center';
    }

    autoScaleSolar();
    window.addEventListener('resize', autoScaleSolar);

});
