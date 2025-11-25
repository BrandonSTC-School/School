document.addEventListener('DOMContentLoaded', function () {

    /* ---------------------------------------------------------------------
       1) TOOLTIP + PAUSE LOGIC (your existing working system)
    --------------------------------------------------------------------- */
    const solar = document.querySelector('.solar-visual');
    const tooltip = document.getElementById('global-tooltip');

    if (!solar || !tooltip) return;

    const items = solar.querySelectorAll('.sun, .planet, .moon, .planet-orbit, .moon-orbit');

    items.forEach(item => {
        item.addEventListener('mouseenter', () => {
            const text = item.getAttribute('data-tip');
            if (text) {
                tooltip.textContent = text;
                tooltip.classList.add('visible');
            }
            solar.classList.add('solar-paused'); // freeze motion
        });

        item.addEventListener('mouseleave', () => {
            tooltip.classList.remove('visible');
            solar.classList.remove('solar-paused'); // resume motion
        });
    });


    /* ---------------------------------------------------------------------
       2) AUTOMATIC SOLAR SYSTEM SCALING (option C)
       - Fits the solar system inside the available width
       - Works on all screens
       - Prevents overflow or clipping
       - Requires NO CSS edits
    --------------------------------------------------------------------- */
    function autoScaleSolar() {
        const container = document.querySelector('.solar-landing');
        if (!container) return;

        const containerWidth = container.clientWidth;
        const solarWidth = solar.offsetWidth;

        // Scale factor to fit the solar system
        let scale = containerWidth / (solarWidth + 40);

        // Upper limit (prevent large screens from overscaling)
        scale = Math.min(scale, 1);

        // Lower limit (keeps usability on mobile)
        scale = Math.max(scale, 0.35);

        solar.style.transform = `scale(${scale})`;
        solar.style.transformOrigin = 'top center';
    }

    // Run scaling immediately
    autoScaleSolar();

    // Run scaling again on window resize
    window.addEventListener('resize', autoScaleSolar);

});