$(function () {
    $(".unlike-icon").click(function () {
        $(this).css("z-index", "99");
        $(this).parent().find("like-icon").css("z-index", "100");
    });

    $(".hambuger-menu-reicever").click(function () {
        $(".wrapper").hide();
        $("#reicever-menu-wrapper").removeClass("hidden");
    });
    
    $("#reicever-profile-img").click(function () {
        $(".wrapper").show();
        $("#reicever-menu-wrapper").addClass("hidden");
    });

    $("#receiver-cart-button, .popup , .cart-link, .order_again").click(function () {
        $('.x-button').removeClass("hidden");
        $('.modal-select-time').removeClass("hidden");
        $('.wrapper').addClass("shadow");
        $(document).scrollTop(5);
    });

    $(".x-button").click(function () {
        $('.x-button').addClass("hidden");
        $('.modal-select-time').addClass("hidden");
        $('.wrapper').removeClass("shadow");
    });

});