layui.use('carousel', function() {
    var carousel = layui.carousel;
    //建造实例
    carousel.render({
        elem: '#test1',
        width: '100%',
        height: '100%',
        arrow: 'none',
        anim: 'fade',
        indicator: 'none',
        interval: 5000
    });
    carousel.render({
        elem: '#test2',
        width: '100%',
        height: '937px',
        arrow: 'none',
        anim: 'fade',
        indicator: 'none',
        interval: 5000
    });
});