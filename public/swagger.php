<?php

//error_reporting(0);
//ini_set("display_errors", 1);
//error_reporting(E_ALL);
//require(__DIR__ . "/../vendor/autoload.php");
//$openapi = \OpenApi\scan(__DIR__ . '/../app');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <title> Dimer API Playground </title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@3.33.0/swagger-ui.css">
    <style>
        ..swagger-ui .wrapper {
            padding: 0 40px;
        }
        .buttonLogin {
            position: fixed;
            width: 200px;
            height: 40px;
            left: 16px;
            top: 300px;
            border: 1px solid gray;
            font-size: 28px;
            padding: 0px;
            text-align: center;
            background: #fbf9f9;
            /* color: white; */
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            cursor: pointer;
            z-index: 10000;
            display: none;
        }
        .rotate {
            -moz-transform: translateX(-50%) translateY(-50%) rotate(90deg);
            -webkit-transform: translateX(-50%) translateY(-50%) rotate(90deg);
            transform:  translateX(-50%) translateY(-50%) rotate(90deg);
        }
        .form-container {
            position: fixed;
            width: 250px;
            height: 350px;
            border: 1px solid #ccc;
            top: 200px;
            left: 0;
            background: white;
            z-index: 10000;
            display: none;
        }
        .form-login {
            flex: 1;
            flex-direction: column;
            justify-content: center;
            display: flex;
            padding: 15px;
        }
        .mt-5 {
            margin-top: 15px !important;
        }
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 140px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 150%;
            left: 50%;
            margin-left: -75px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
<div class="swagger-ui">
    <div class="scheme-container">
        <div class="schemes wrapper block col-12">
            <div class="auth-wrapper">
                <button class="btn authorize" id="refresh">Làm mới</button>
            </div>
        </div>
        <div class="buttonLogin rotate">
            Đăng nhập
        </div>
        <div class="form-container">
            <div class="form-login">
                <input class="mt-5" type="text" placeholder="Username" id="username" value="admin">
                <input class="mt-5" type="text" placeholder="Password" id="password" value="123456">
                <button class="btn authorize mt-5" id="login">Đăng nhập</button>
                <input class="mt-5" type="text" placeholder="token" id="token">
                <button class="btn authorize mt-5" id="copy">Copy to author</button>
            </div>
        </div>
    </div>
</div>
<div id="ui"></div>
<script src="https://unpkg.com/swagger-ui-dist@3.33.0/swagger-ui-bundle.js"></script>
<script src="https://unpkg.com/swagger-ui-dist@3.33.0/swagger-ui-standalone-preset.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
window.onload = function() {
    document.getElementById('login').addEventListener('click', function() {
        login()
    })

    $(document).on("click", '.btn.authorize.unlocked', function() {
        openForm();
    })
    $(document).on("click", '.btn.authorize.locked', function() {
        openForm();
    })
    $(document).on("click", '.authorization__btn.unlocked', function() {
        openForm();
    })
    $(document).on("click", '.authorization__btn.locked', function() {
        openForm();
    })

    $(document).on("click", ".btn.modal-btn.auth.btn-done.button", function() {
        closeForm();
    })
    $(document).on("click", ".close-modal", function() {
        closeForm();
    })


    document.getElementsByClassName('buttonLogin')[0].addEventListener('click', function() {
        openForm();
    })

    function closeForm() {
       $('.form-container').hide(200)
    }

    function openForm() {
        $('.form-container').show(200)
    }

    $("#copy").click(function() {
        const token = $("#token").val();
        if (!token) {
            return;
        }
        var ev = new Event('input', { bubbles: true});
        ev.simulated = true;
        const element = $(".auth-container").find("input")[0];
        element.value = token;
        element.dispatchEvent(ev);
        $(".btn.modal-btn.auth.authorize.button").trigger('click');
        // $(".btn.modal-btn.auth.btn-done.button").trigger('click');
    })

    function login() {
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

        fetch('/api/authorize/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
                //
            },
            body: JSON.stringify({
                "username": username,
                "password": password
            })
        })
            .then(res => res.json())
            .then(res => {
                const token = res.optional.token;
                document.getElementById('token').value = res.optional.token
                // document.getElementsByClassName("auth-container")[0].getElementsByTagName("input")[0].value = token;
                var ev = new Event('input', { bubbles: true});
                ev.simulated = true;
                const element = $(".auth-container").find("input")[0];
                element.value = token;
                element.dispatchEvent(ev);
                $(".btn.modal-btn.auth.authorize.button").trigger('click');
                $(".btn.modal-btn.auth.btn-done.button").trigger('click');
            })
    }



    document.getElementById("refresh").addEventListener('click', function() {
        handle()
    })
    function handle() {
        document.getElementById("ui").getElementsByClassName("swagger-ui")[0].innerHTML = `
        <div class="loading-container"><div class="info"><div class="loading-container"><div class="loading"></div></div></div></div>
`;
        fetch('./render.php?refresh=true')
            .then(res => {
                SwaggerUIBundle({
                    dom_id: '#ui',
                    //spec: <?php //echo $openapi->toJson() ?>//,
                    //
                    url: "./render.php",
                    deepLinking: true,
                    presets: [
                        SwaggerUIBundle.presets.apis,
                        SwaggerUIStandalonePreset
                    ],
                    plugins: [
                        SwaggerUIBundle.plugins.DownloadUrl
                    ],
                    // layout: "StandaloneLayout"
                })
            })
    }
    SwaggerUIBundle({
            dom_id: '#ui',
            //spec: <?php //echo $openapi->toJson() ?>//,
            //
            url: "./render.php",
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset
            ],
            plugins: [
                SwaggerUIBundle.plugins.DownloadUrl
            ],
            // layout: "StandaloneLayout"
        })
    }
</script>
</body>
</html>
