var search_opened = false;

function setCookie(cookie_name, cookie_value, cookie_age=7200) {
    document.cookie = `${cookie_name}=${cookie_value};max-age=${cookie_age};path=/`;
}
function empty(mixed_var) {
    return (mixed_var === "" || mixed_var === 0 || mixed_var === null || mixed_var === false || mixed_var === undefined || mixed_var.length === 0);
}
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}
function hexToRgb(hex) {
    // Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
    var shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
    hex = hex.replace(shorthandRegex, function(m, r, g, b) {
        return r + r + g + g + b + b;
    });
    var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}
(function($) {
    $.fn.hasScrollBar = function() {
        return this.get(0).scrollHeight > this.height();
    }
})(jQuery);
function getCurrentMoth() {
    const monthNames = ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
        "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"
    ];
    return monthNames[new Date().getMonth()];
}

document.getElementById("menu_button").addEventListener("click", function (){
    if (search_opened) {
        search_opened = false;
        switch_menu();
    }
});
document.getElementById("search_users").addEventListener("focus", function () {
    search_opened = true;
    switch_menu();
});
document.getElementById("search_users").addEventListener("blur", function () {
    if (search_opened) {
        search_opened = false;
        switch_menu();
    }
});
function switch_menu () {
    var menu = document.getElementById("menu_button");
    if (search_opened) {
        menu.classList.remove("wrap-menu");
        menu.classList.add("wrap-cancel");
        setTimeout(function (){
            menu.src = "src/svg/back.svg";
        }, 100);
    }else{
        menu.classList.remove("wrap-cancel");
        menu.classList.add("wrap-menu");
        setTimeout(function (){
            menu.src = "src/svg/menu.svg";
        }, 100);
    }
}