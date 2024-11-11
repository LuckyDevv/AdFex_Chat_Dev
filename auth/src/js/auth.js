var auth_login_form = document.getElementById("email_form");
var auth_login_label = document.getElementById("auth_label_email");
var auth_login_input = document.getElementById("auth_input_email");
var auth_login_button = document.getElementById("auth_button_email_next");
var auth_login_span = document.getElementById("auth_email_span");

var auth_pass_form = document.getElementById("password_form");
var auth_pass_show = document.getElementById("auth_show_pass");
var auth_forgot_pass = document.getElementById("auth_forgot_pass");
var auth_pass_label = document.getElementById("auth_label_password");
var auth_pass_input = document.getElementById("auth_input_password");
var auth_pass_button_next = document.getElementById("auth_button_password_next");
var auth_pass_button_prev = document.getElementById("auth_button_password_prev");
var auth_pass_span = document.getElementById("auth_password_span");

var confirm_auth_form = document.getElementById("confirm_form_auth");
var confirm_auth_input = document.getElementById("auth_input_code");
var confirm_auth_label = document.getElementById("auth_label_code");
var confirm_auth_button_next = document.getElementById("auth_button_code_next");
var confirm_auth_button_prev = document.getElementById("auth_button_code_prev");
var confirm_auth_span = document.getElementById("auth_code_span");

var reg_form = document.getElementById("reg_form");
var reg_button_next = document.getElementById("reg_button_next");
var reg_button_prev = document.getElementById("reg_button_prev");
var reg_span = document.getElementById("reg_span");
var reg_show_pass = document.getElementById("reg_show_pass");
var reg_input_email = document.getElementById("reg_input_email");
var reg_input_name = document.getElementById("reg_input_name");
var reg_input_surname = document.getElementById("reg_input_surname");
var reg_input_pass1 = document.getElementById("reg_input_password");
var reg_input_pass2 = document.getElementById("reg_input_password_2");

var reg_confirm_form = document.getElementById("confirm_form_reg");
var reg_confirm_button_prev = document.getElementById("reg_button_code_prev");
var reg_confirm_button_next = document.getElementById("reg_button_code_next");
var reg_confirm_span = document.getElementById("reg_code_span");
var reg_confirm_input = document.getElementById("reg_input_code");
var reg_confirm_label = document.getElementById("reg_label_code");

var notify_auth_email = document.getElementById("notify_auth_email");
var notify_auth_password = document.getElementById("notify_auth_password");
var notify_auth_code = document.getElementById("notify_code_auth");
var notify_reg_email = document.getElementById("notify_reg_email");
var notify_reg_name = document.getElementById("notify_reg_name");
var notify_reg_surname = document.getElementById("notify_reg_surname");
var notify_reg_pass = document.getElementById("notify_reg_pass");
var notify_reg_pass_2 = document.getElementById("notify_reg_pass_2");
var notify_reg_code = document.getElementById("notify_code_reg");

var forgot_form = document.getElementById("forgot_form");
var forgot_input_new_password = document.getElementById("forgot_input_new_password");
var forgot_label_new_password = document.getElementById("forgot_label_new_password");
var forgot_input_new_password_2 = document.getElementById("forgot_input_new_password_2");
var forgot_label_new_password_2 = document.getElementById("forgot_label_new_password_2");
var forgot_input_code = document.getElementById("forgot_input_code");
var forgot_label_code = document.getElementById("forgot_label_code");
var forgot_form_next = document.getElementById("forgot_form_next");
var forgot_form_span = document.getElementById("forgot_form_span");
var forgot_form_prev = document.getElementById("forgot_form_prev");

var stop_tips = false;
var browser = null;
var os = null;

// AUTHORIZATION EVENTS
auth_login_button.addEventListener("click", function (){
    var email = auth_login_input.value;
    if (email === "") {
        sendNotify(notify_auth_email, "Вы не ввели почту!");
        return;
    }
    sendNotify(notify_auth_email, "");
    auth_login_span.innerText = '';
    auth_login_span.classList.add('button--loading');
    auth_login_button.setAttribute("disabled", "");
    auth_login_input.setAttribute("disabled", "");
    if (socket !== null) {
        socket.send(JSON.stringify({
            "type": "email_check",
            "email": email,
            "uuid": new DeviceUUID().get()
        }));
    }else{
        sendNotify(notify_auth_email, "Не удалось подключиться к серверу!");
    }

});
auth_pass_show.addEventListener("click", function (){
    if (!stop_tips) {
        if (auth_pass_input.type === "password") {
            this.innerText = "Скрыть пароль";
            auth_pass_input.type = "text";
        }else{
            this.innerText = "Показать пароль";
            auth_pass_input.type = "password";
        }
    }
});
auth_pass_button_prev.addEventListener("click", function (){
    auth_pass_input.value = "";
    auth_pass_input.type = "password";
    auth_pass_show.innerText = "Показать пароль";
    auth_pass_form.style.display = "none";
    reg_form.classList.remove("block-rtl-animate");
    auth_login_form.style.display = "";
    auth_login_form.classList.add("block-ltr-animate");
});
auth_pass_button_next.addEventListener("click", function (){
    var password = auth_pass_input.value;
    if (password === "") {
        sendNotify(notify_auth_password, "Вы не ввели пароль!");
        return;
    }
    sendNotify(notify_auth_password, "");
    auth_pass_span.innerText = '';
    auth_pass_span.classList.add('button--loading');
    auth_pass_button_next.setAttribute("disabled", "");
    auth_pass_input.setAttribute("disabled", "");
    auth_pass_button_prev.setAttribute("disabled", "");
    stop_tips = true;
    socket.send(JSON.stringify({
        "type": "auth_password",
        "uuid": new DeviceUUID().get(),
        "password": password
    }));
});
confirm_auth_button_prev.addEventListener("click", function () {
    confirm_auth_input.value = "";
    confirm_auth_form.style.display = "none";
    reg_form.classList.remove("block-rtl-animate");
    auth_pass_form.style.display = "";
    auth_pass_form.classList.add("block-ltr-animate");
});
confirm_auth_button_next.addEventListener("click", function () {

});

// REGISTRATION EVENTS
reg_button_prev.addEventListener("click", function () {
    reg_input_email.value = "";
    reg_input_name.value = "";
    reg_input_surname.value = "";
    reg_input_pass1.value = "";
    reg_input_pass2.value = "";
    reg_input_pass1.type = "password";
    reg_input_pass2.type = "password";
    reg_show_pass.innerText = "Показать пароль";
    reg_form.style.display = "none";
    reg_form.classList.remove("block-rtl-animate");
    auth_login_form.style.display = "";
    auth_login_form.classList.add("block-ltr-animate");
});
reg_button_next.addEventListener("click", function () {
    var email = reg_input_email.value;
    if (email === "") {
        sendNotify(notify_reg_email, "Вы не ввели почту!");
        return;
    }else sendNotify(notify_reg_email, "");
    var name = reg_input_name.value;
    if (name === "") {
        sendNotify(notify_reg_name, "Вы не ввели имя!");
        return;
    }else sendNotify(notify_reg_name, "");
    var surname = reg_input_surname.value;
    if (surname === "") {
        sendNotify(notify_reg_surname, "Вы не ввели фамилию!");
        return;
    }else sendNotify(notify_reg_surname, "");
    var pass1 = reg_input_pass1.value;
    if (pass1 === "") {
        sendNotify(notify_reg_pass, "Вы не ввели пароль!");
        return;
    }else sendNotify(notify_reg_pass, "");
    var pass2 = reg_input_pass2.value;
    if (pass2 === "") {
        sendNotify(notify_reg_pass_2, "Вы не ввели подтверждение пароля!");
        return;
    }else sendNotify(notify_reg_pass_2, "");
    if (pass1 !== pass2) {
        sendNotify(notify_reg_pass, "Подтверждение пароля не совпадает с паролем!");
        sendNotify(notify_reg_pass_2, "Подтверждение пароля не совпадает с паролем!");
        return;
    }else{
        sendNotify(notify_reg_pass, "");
        sendNotify(notify_reg_pass_2, "");
    }

    reg_span.innerText = '';
    reg_span.classList.add('button--loading');
    reg_input_email.setAttribute("disabled", "");
    reg_input_name.setAttribute("disabled", "");
    reg_input_surname.setAttribute("disabled", "");
    reg_input_pass1.setAttribute("disabled", "");
    reg_input_pass2.setAttribute("disabled", "");
    reg_button_next.setAttribute("disabled", "");

    socket.send(JSON.stringify({
        "type": "registration_step1",
        "uuid": new DeviceUUID().get(),
        "email": email,
        "name": name,
        "surname": surname,
        "password": pass1,
        "password_confirmation": pass2
    }));
});
reg_show_pass.addEventListener("click", function (){
    if (!stop_tips) {
        if (reg_input_pass1.type === "password" && reg_input_pass2.type === "password") {
            reg_input_pass1.type = "text";
            reg_input_pass2.type = "text";
            this.innerText = "Скрыть пароль";
        }else{
            reg_input_pass1.type = "password";
            reg_input_pass2.type = "password";
            this.innerText = "Показать пароль";
        }
    }
});
reg_confirm_button_next.addEventListener("click", function () {
    console.log('fsdfsdf');
    var code = reg_confirm_input.value;
    if (code === "") {
        sendNotify(notify_auth_code, "Вы не ввели код!");
        return;
    }
    reg_confirm_span.innerText = "";
    reg_confirm_span.classList.add("button--loading");
    reg_confirm_input.setAttribute("disabled", "");
    reg_confirm_button_next.setAttribute("disabled", "");
    reg_confirm_button_prev.setAttribute("disabled", "");
    socket.send(JSON.stringify({
        "type": "registration_step2",
        "uuid": new DeviceUUID().get(),
        "code": code
    }));
});
reg_confirm_button_prev.addEventListener("click", function () {
    reg_confirm_input.value = "";
    confirm_auth_form.style.display = "none";
    reg_confirm_form.style.display = "none";
    reg_form.classList.remove("block-rtl-animate");
    reg_form.style.display = "";
    reg_form.classList.add("block-ltr-animate");
});

auth_forgot_pass.addEventListener("click", function () {
    if (!stop_tips) {
        auth_pass_form.style.display = "none";
        forgot_form.classList.remove("block-ltr-animate");
        forgot_form.style.display = "";
        forgot_form.classList.add("block-rtl-animate");
    }
});
document.getElementById("forgot_show_pass").addEventListener("click", function (){
    if (!stop_tips) {
        if (forgot_input_new_password.type === "password" && forgot_input_new_password_2.type === "password") {
            forgot_input_new_password_2.type = "text";
            forgot_input_new_password.type = "text";
            this.innerText = "Скрыть пароль";
        }else{
            forgot_input_new_password_2.type = "password";
            forgot_input_new_password.type = "password";
            this.innerText = "Показать пароль";
        }
    }
});
forgot_form_next.addEventListener("click", function (){
    // TODO: Send to server
});
forgot_form_prev.addEventListener("click", function (){
    forgot_form.style.display = "none";
    auth_pass_form.classList.remove("block-rtl-animate");
    auth_pass_form.style.display = "";
    auth_pass_form.classList.add("block-ltr-animate");
    forgot_input_new_password.value = "";
    forgot_input_new_password_2.value = "";
    forgot_input_code.value = "";
});



window.onload = function (){
    var du = new DeviceUUID().parse();
    os = du.os;
    browser = du.source.toLowerCase().includes("yabrowser") ? "Яндекс Браузер" : du.browser;
    setCookie("_usi_g%$_id$$", new DeviceUUID().get(), 604800);
    var device_width = window.innerWidth;
    var content_auth = document.getElementById("content_auth");
    var no_support_element = document.getElementById("no_support");
    if (device_width < 300) {
        content_auth.style.display = 'none';
        content_auth.setAttribute('hidden', '');
        no_support_element.style.display = 'flex';
        no_support_element.removeAttribute('hidden');
    }else{
        no_support_element.style.display = 'none';
        no_support_element.setAttribute('hidden', '');
        content_auth.style.display = '';
        content_auth.removeAttribute('hidden');
    }
}
window.addEventListener('load', function () {
    if (!empty(getCookie("session_id"))){
        location.href = "../";
    }else{
        var inter = setInterval(function (){
            if (socket_connected) {
                $('#main_preloader').fadeOut().end().delay(400).fadeOut('slow');
                clearInterval(inter);
            }
        }, 1500);
    }
});
function resetForm(type) {
    switch (type) {
        case "auth_email":
            auth_login_span.innerText = 'Продолжить';
            auth_login_span.classList.remove('button--loading');
            auth_login_button.removeAttribute("disabled");
            auth_login_input.removeAttribute("disabled");
            break;
        case "auth_pass":
            auth_pass_span.innerText = 'Продолжить';
            auth_pass_span.classList.remove('button--loading');
            auth_pass_button_next.removeAttribute("disabled");
            auth_pass_input.removeAttribute("disabled");
            auth_pass_button_prev.removeAttribute("disabled");
            stop_tips = true;
            break;
        case "auth_confirm":
            break;
        case "reg_form":
            reg_input_email.removeAttribute("disabled");
            reg_input_name.removeAttribute("disabled");
            reg_input_surname.removeAttribute("disabled");
            reg_input_pass1.removeAttribute("disabled");
            reg_input_pass2.removeAttribute("disabled");
            reg_button_next.removeAttribute("disabled");
            reg_span.innerText = 'Продолжить';
            reg_span.classList.remove('button--loading');
            break;
        case "reg_confirm":
            reg_confirm_span.innerText = "Продолжить";
            reg_confirm_span.classList.remove("button--loading");
            reg_confirm_input.removeAttribute("disabled");
            reg_confirm_button_next.removeAttribute("disabled");
            reg_confirm_button_prev.removeAttribute("disabled");
            break;
        case "forgot":
            break;
    }
    stop_tips = false;
}
function sendNotify(element, text) {
    if (element instanceof HTMLElement) {
        if (text === "") {
            element.style.display = "none";
        }else{
            element.style.display = "";
            element.innerText = text;
        }
        return true;
    }
    return false;
}
function empty(mixed_var) {
    return (mixed_var === "" || mixed_var === 0 || mixed_var === null || mixed_var === false || mixed_var === undefined || mixed_var.length === 0);
}
function setCookie(cookie_name, cookie_value) {
    document.cookie = `${cookie_name}=${cookie_value};max-age=604800;path=/`;
}
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}
