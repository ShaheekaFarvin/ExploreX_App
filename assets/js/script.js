document.addEventListener("DOMContentLoaded", function () {

    console.log("ExploreX loaded successfully.");

    // Gentle cursor-reactive foreground light on the home hero.
    const hero = document.querySelector(".home-hero");
    if (hero && !window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        hero.addEventListener("pointermove", function (event) {
            const rect = hero.getBoundingClientRect();
            const x = event.clientX - (rect.left + rect.width / 2);
            const y = event.clientY - (rect.top + rect.height / 2);
            hero.style.setProperty("--cursor-x", `${x * 0.12}px`);
            hero.style.setProperty("--cursor-y", `${y * 0.12}px`);
        }, { passive: true });

        hero.addEventListener("pointerleave", function () {
            hero.style.setProperty("--cursor-x", "0px");
            hero.style.setProperty("--cursor-y", "0px");
        }, { passive: true });
    }

});