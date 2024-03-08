var splide = new Splide('.splide', {
    perPage: 4,
    perMove: 1,
    rewind : true,
    pagination: false,
    breakpoints: {
        640: {
            perPage: 2,
        },
        520: {
            perPage: 1,
        },
    }
});

splide.mount();