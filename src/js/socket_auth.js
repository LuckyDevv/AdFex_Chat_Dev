var socket_reconnection_attempt = 0; var socket_connected = false;
var socket = null;
var socket_check = false;
var start_ping = null;
function openSocket() {
    socket = new WebSocket('ws://localhost:8080');
    try {
        socket_reconnection_attempt++;
        socket.onopen = function() {
            socket_connected = true;
            if (socket_check) {
                $('#main_preloader').fadeOut().end().delay(400).fadeOut('slow');
            }
        }
        socket.onmessage = function(event) {
            handleSocketMessage(event.data);
        }
        socket.onerror = function (e) {}
        socket.onclose = function () {
            $('#main_preloader').fadeIn().end().delay(400).fadeIn('slow');
            socket_check = true;
            socket = null;
            socket_connected = false;
            setTimeout(function () {
                reg_form.style.display = "none";
                forgot_form.style.display = "none";
                auth_pass_form.style.display = "none";
                confirm_auth_form.style.display = "none";
                reg_confirm_form.style.display = "none";
                auth_login_form.style.display = "";
            }, 410);
            setTimeout(function (){
                openSocket();
            }, 3000);
        }
    }catch(e){
        setTimeout(function (){
            openSocket();
        }, 3000);
    }
}
openSocket();

function handleSocketMessage(data) {
    var data_parsed = JSON.parse(data);
    if (!empty(data_parsed.response)) {
        handleSocketResponse(data_parsed);
    }else if (!empty(data_parsed.error)) {
        handleSocketError(data_parsed);
    }else{
        console.log("Empty socket message");
    }
}

function handleSocketResponse(data) {
    switch (data.response.code) {
        case 1190:
            var ping = 0;
            if (!empty(start_ping)) {
                ping = Math.round(performance.now() - start_ping);
                start_ping = null;
            }
            console.log("Current ping to server: " + ping + " ms");
            break;
        case 2:
            resetForm("auth_email");
            auth_login_form.style.display = "none";
            auth_pass_form.classList.remove("block-ltr-animate");
            auth_pass_form.style.display = "";
            auth_pass_form.classList.add("block-rtl-animate");
            break;
        case 3:
            resetForm("auth_email");
            auth_login_form.style.display = "none";
            reg_input_email.value = data.response.message;
            reg_form.classList.remove("block-ltr-animate");
            reg_form.style.display = "";
            reg_form.classList.add("block-rtl-animate");
            break;
        case 4:
            resetForm("reg_form");
            reg_form.style.display = "none";
            reg_confirm_form.classList.remove("block-ltr-animate");
            reg_confirm_form.style.display = "";
            reg_confirm_form.classList.add("block-rtl-animate");
            break;
        case 5:
            resetForm("reg_confirm");
            setCookie("session_id", data.response.message, 604800);
            console.log("Response Session ID: " + data.response.message);
            location.reload();
            break;
        case 6:
            resetForm("auth_pass");
            auth_pass_form.style.display = "none";
            confirm_auth_form.classList.remove("block-ltr-animate");
            confirm_auth_form.style.display = "";
            confirm_auth_form.classList.add("block-rtl-animate");
            break;
        case 7:
            resetForm("auth_confirm");
            setCookie("session_id", data.response.message, 604800);
            console.log("Response Session ID: " + data.response.message);
            location.reload();
            break;
        case 8:
            resetForm("auth_pass");
            auth_pass_form.style.display = "none";
            forgot_form.classList.remove("block-ltr-animate");
            forgot_form.style.display = "";
            forgot_form.classList.add("block-rtl-animate");
            break;
        case 9:
            resetForm("forgot");
            setCookie("session_id", data.response.message, 604800);
            console.log("Response Session ID: " + data.response.message);
            location.reload();
            break;
    }
}

function handleSocketError(data) {
    switch (data.error.code) {
        case 1:
            resetForm("auth_email");
            sendNotify(notify_auth_email, data.error.message);
            break;
        case 3:
            resetForm("reg_form");
            sendNotify(notify_reg_email, data.error.message);
            break;
        case 4:
            resetForm("reg_form");
            sendNotify(notify_reg_name, data.error.message);
            break;
        case 5:
            resetForm("reg_form");
            sendNotify(notify_reg_surname, data.error.message);
            break;
        case 6:
            resetForm("reg_form");
            sendNotify(notify_reg_pass, data.error.message);
            break;
        case 7:
            resetForm("reg_form");
            sendNotify(notify_reg_pass_2, data.error.message);
            break;
        case 8:
            resetForm("reg_form");
            sendNotify(notify_reg_pass, data.error.message);
            sendNotify(notify_reg_pass_2, data.error.message);
            break;
        case 9:
            resetForm("reg_confirm");
            sendNotify(notify_reg_code, data.error.message);
            break;
        case 10:
            resetForm("auth_pass");
            sendNotify(notify_auth_password, data.error.message);
            break;
        case 11:
            resetForm("auth_confirm");
            sendNotify(notify_auth_code, data.error.message);
            break;
        case 12:
            resetForm("auth_pass");
            sendNotify(notify_auth_password, data.error.message);
            break;
        case 13:
            resetForm("forgot");
            sendNotify(notify_forgot_new_password, data.error.message);
            break;
        case 14:
            resetForm("forgot");
            sendNotify(notify_forgot_new_password, data.error.message);
            break;
        case 15:
            resetForm("forgot");
            sendNotify(notify_forgot_new_password_2, data.error.message);
            break;
        case 16:
            resetForm("forgot");
            sendNotify(notify_forgot_new_password, data.error.message);
            sendNotify(notify_forgot_new_password_2, data.error.message);
            break;
        case 17:
            resetForm("forgot");
            sendNotify(notify_code_forgot, data.error.message);
            break;
        default:
            console.log(data);
    }
}


// PING SERVER EVERY 50 SECONDS
setInterval(function () {
    start_ping = performance.now();
    socket.send(JSON.stringify({
        "type": "ping"
    }));
}, 50000);