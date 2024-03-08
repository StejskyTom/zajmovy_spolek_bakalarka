$(window).on('scroll', e => {
    if($(window).scrollTop() < 150) {
        console.log('sdf');
        $('a:not(.lang)').removeClass('active')
        $('a#home').addClass('active');
    } else {
        $('h2').each(function() {
            if($(this).offset().top - 150 < $(window).scrollTop()) {
                let id = '#' + $(this).closest('article').data('section');
                console.log(id);
                $('a:not(.lang)').removeClass('active')
                $('a' + id).addClass('active')
            }
        })
    }
})