$(function () {
    $(".unlike-icon").click(function () {
        $(this).css("z-index", "99");
        $(this).parent().find("like-icon").css("z-index", "100");
    });

    $(".hambuger-menu-reicever").click(function () {
        $(".wrapper").hide();
        $("#reicever-menu-wrapper").removeClass("hidden");
        $("#main-menu").removeClass("hidden");
        $("#setting-menu").addClass("hidden");
    });
    
    $(".li-profile").click(function () {
        $(".wrapper").show();
        $("#reicever-menu-wrapper").addClass("hidden");
    });

    $("#receiver-cart-button, .popup , .cart-link, .order_again").click(function () {
        $('.x-button').removeClass("hidden");
        $('.modal-select-time').removeClass("hidden");
        $('.wrapper').addClass("shadow");
        $(document).scrollTop(5);
    });

    $(".x-button, .close").click(function () {
        $('.x-button').addClass("hidden");
        $('.modal-select-time').addClass("hidden");
        $('.wrapper').removeClass("shadow");
    });

    $("#setting").click(function () {
        $("#main-menu").addClass("hidden");
        $("#setting-menu").removeClass("hidden");
    });

    var createForm = function (action, data) {
        var $form = $('<form action="' + action + '" method="post"></form>');
        for (input in data) {
            if (data.hasOwnProperty(input)) {
                $form.append('<input name="' + input + '" value="' + data[input] + '">');
            }
        }
        return $form;
    };

    $('a[token-for-anchor]').click(function (e) {
        e.preventDefault();
        var $this = $(this);
        var data = $this.data();
        if (data.confirm != false) {
            if (!confirm(data.message ? data.message : '削除してもよろしいですか?')) {
                return false;
            }
        }

        var $form = createForm($this.attr('href'), {
            _token: $this.attr('token-for-anchor'),
            _method: data.method
        }).hide();

        $('body').append($form); // Firefox requires form to be on the page to allow submission
        $form.submit();
    });
});