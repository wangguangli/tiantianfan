$(function () {
    /**
     * Coder:guokit.com(HCB 适配PC端)
     * 通用脚本
     */
    /*二级导航显示隐藏*/
    // $('').each(function () {
    //   $(this).hover(function () {
    //     $(this).parents('').siblings('.nav-hidden').show();
    //   },function () {
    //     $(this).parents('').siblings('.nav-hidden').hide();
    //   });
    // });

    /*返回顶部*/
    $('.gotop').click(function () {
      $('body,html').animate({scrollTop:0},500);
    });

    /*激活QQ*/
    $('.open-swt').click(function () {
      $this = $(this);
      var qq = $this.data('qq');
      window.open("http://wpa.qq.com/msgrd?v=3&uin="+qq+"&site=qq&menu=yes");
      return false;
    });
});


/*动画*/
$(document).ready(function() {$(window).scroll(scrollFn1);});
$(document).ready(function() {$(window).scroll(scrollFn2);});
$(document).ready(function() {$(window).scroll(scrollFn3);});
$(document).ready(function() {$(window).scroll(scrollFn4);});
$(document).ready(function() {$(window).scroll(scrollFn5);});
(function ( $ ) {
    $.fn.visible = function(partial) {
        var $t        = $(this),
            $w            = $(window),
            viewTop       = $w.scrollTop(),
            viewBottom    = viewTop + $w.height(),
            _top          = $t.offset().top,
            _bottom       = _top + $t.height(),
            compareTop    = partial === true ? _bottom : _top,
            compareBottom = partial === true ? _top : _bottom;
        return ((compareBottom <= viewBottom) && (compareTop >= viewTop));
    };
}( jQuery ));
function scrollFn1() {
    $(".product-section").each(function(i, el) {
        var el = $(el);
        if (el.visible(true)) {
            el.addClass("fadeInBottom");
        }
    });
}
function scrollFn2() {
    $(".adv-section .text01").each(function(i, el) {
        var el = $(el);
        if (el.visible(true)) {
            el.addClass("fadeInLeft");
        }
    });
}
function scrollFn3() {
    $(".adv-section .text02").each(function(i, el) {
        var el = $(el);
        if (el.visible(true)) {
            el.addClass("fadeInRight");
        }
    });
}
function scrollFn4() {
    $(".Information").each(function(i, el) {
        var el = $(el);
        if (el.visible(true)) {
            el.addClass("fadeInLeft");
        }
    });
}
function scrollFn5() {
    $("").each(function(i, el) {
        var el = $(el);
        if (el.visible(true)) {
            el.addClass("fadeInBottom");
        }
    });


}







