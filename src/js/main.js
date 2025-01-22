var search_opened = false;
var last_chat = null; var last_check = null; var opened_chat = false; var current_chat_timeout = null;
var editableDiv = document.querySelector('div[contenteditable="true"]');

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
function registerChatChoice() {
    $('.block-chat-info').click(function (){
        var lst = false;
        if (last_chat instanceof HTMLElement) {
            if (last_chat === this) {
                lst = true;
            }else{
                last_chat.style.background = "";
            }
        }
        var check_svg = this.childNodes[3].childNodes[1].childNodes[3].childNodes[1];
        var lct = false;
        if (last_check instanceof HTMLElement) {
            if (last_check === check_svg) {
                lct = true;
            }else{
                last_check.classList.remove("white_check");
            }
        }
        if (!lst) {
            if (this instanceof HTMLElement) {
                var chat_id = this.getAttribute("user_id");
                try {
                    chat_id = parseInt(chat_id);
                }catch(e){
                    return;
                }
                console.log(chat_id);
                var avatar = this.childNodes[1];
                var name = this.childNodes[3].childNodes[1].childNodes[1].innerText;
                document.getElementById("chat_spinner").style.display = "block";
                document.getElementById("chat_header").style.display = "none";
                document.getElementById("chat_input").style.display = "none";
                // TODO: Ask user meta data from server (socket)
                if (window.innerWidth >= 1000) {
                    document.getElementById("chat_dialog").style.display = "flex";
                }else{
                    document.getElementById("chat_dialog").style.display = "flex";
                    document.getElementById("chat_list").style.display = "none";
                }
                this.style.background = "rgba(152,97,172,0.7)";
                last_chat = this;
                if (current_chat_timeout !== null) {
                    clearTimeout(current_chat_timeout);
                }
                current_chat_timeout = setTimeout(function (){
                    document.getElementById("chat_header").style.display = "flex";
                    document.getElementById("chat_input").style.display = "flex";
                    document.getElementById("chat_spinner").style.display = "none";
                    document.getElementById("chat_avatar").src = avatar.src;
                    document.getElementById("chat_name").src = name;
                    opened_chat = true;
                }, 500);
            }
        }
        if (!lct) {
            if (check_svg instanceof HTMLElement) {
                check_svg.classList.add("white_check");
                last_check = check_svg;
            }
        }
    });
}
function switch_menu () {
    var menu = document.getElementById("menu_button");
    if (search_opened) {
        menu.classList.remove("wrap-menu");
        menu.classList.add("wrap-cancel");
        setTimeout(function (){
            menu.src = document.getElementById("back_svg").src;
        }, 100);
        document.getElementById("search_users").value = '';
        document.getElementById("clear_search").style.display = "";
    }else{
        menu.classList.remove("wrap-cancel");
        menu.classList.add("wrap-menu");
        setTimeout(function (){
            menu.src = document.getElementById("menu_svg").src;
        }, 100);
        document.getElementById("search_users").value = '';
        document.getElementById("clear_search").style.display = "none";
    }
}
function onResize(){
    var chat_d = document.getElementById("chat_dialog");
    var chat_l = document.getElementById("chat_list");
    if (window.innerWidth >= 1000) {
        chat_l.style.display = "block";
        if (opened_chat) {
            chat_d.style.display = "flex";
        }else {
            chat_d.style.display = "none";
        }
    }else{
        if (opened_chat) {
            chat_d.style.display = "flex";
            chat_l.style.display = "none";
        }else{
            chat_d.style.display = "none";
            chat_l.style.display = "block";
        }
    }
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
document.getElementById("search_users").addEventListener("blur", function (e) {
    if (search_opened) {
        var target = e.relatedTarget;
        if (target !== null) {
            if (target.id === "clear_search" || target.id === "icon_search") {
                return;
            }
        }
        search_opened = false;
        switch_menu();
    }
});
document.getElementById("clear_search").addEventListener("click", function (){
    document.getElementById("search_users").value = '';
    document.getElementById("search_users").focus();
    this.blur()
});
document.getElementById("chat_close").addEventListener("click", function (){
    document.getElementById("chat_dialog").style.display = "none";
    document.getElementById("chat_list").style.display = "block";
    opened_chat = false;
    if (last_chat instanceof HTMLElement) {
        last_chat.style.background = "";
        last_chat = null;
    }
    if (last_check instanceof HTMLElement) {
        last_check.classList.remove("white_check");
        last_check = null;
    }
});
editableDiv.addEventListener("paste", function(e) {
    e.preventDefault();
    var text = e.clipboardData.getData("text/plain");
    document.execCommand("insertHTML", false, text);
    editableDiv.scrollTo(0, editableDiv.scrollHeight)
});
jQuery(function($){
    $(".message-input").focusout(function(){
        var element = $(this);
        if (!element.text().replace(" ", "").length) {
            element.empty();
        }
    });
});
$(document).ready(function (){
    $(".message-input").empty();
});


registerChatChoice();
window.addEventListener("resize", onResize);