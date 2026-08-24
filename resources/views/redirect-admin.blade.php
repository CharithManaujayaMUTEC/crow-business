<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Redirecting...</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: #020406;
            color: #ffffff;
        }

        .container {
            text-align: center;
            padding: 40px 20px;
        }

        .logo {
            max-width: 220px;
            width: 100%;
            margin-bottom: 40px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        p {
            color: #a0a0a0;
            font-size: 16px;
        }

        .countdown {
            font-size: 64px;
            font-weight: bold;
            color: #f5a623;
            margin: 25px 0;
        }

        .loader {
            width: 260px;
            height: 6px;
            background: #1f2933;
            border-radius: 10px;
            overflow: hidden;
            margin: 30px auto;
        }

        .progress {
            height: 100%;
            width: 100%;
            background: #f5a623;
            transform-origin: left;
            animation: countdown 5s linear forwards;
        }

        @keyframes countdown {
            from {
                transform: scaleX(1);
            }

            to {
                transform: scaleX(0);
            }
        }

        .admin-link {
            display: inline-block;
            margin-top: 15px;
            color: #f5a623;
            text-decoration: none;
        }

        .admin-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

<div class="container">

    {{-- Replace with your actual logo path if needed --}}
    {{-- <img src="{{ asset('images/logo.png') }}" class="logo" alt="Crow.lk"> --}}

    <h1>Redirecting you...</h1>

    <p>Please wait while we take you to the administration panel.</p>

    <div class="countdown" id="countdown">5</div>

    <div class="loader">
        <div class="progress"></div>
    </div>

    <p>
        If you are not redirected automatically,
        <br>
        <a href="{{ url('/admin') }}" class="admin-link">
            click here to continue
        </a>
    </p>

</div>

<script>
    let seconds = 5;

    const countdown = document.getElementById('countdown');

    const timer = setInterval(() => {
        seconds--;

        countdown.textContent = seconds;

        if (seconds <= 0) {
            clearInterval(timer);

            window.location.href = "{{ url('/admin/customers') }}";
        }
    }, 1000);
</script>

</body>
</html>