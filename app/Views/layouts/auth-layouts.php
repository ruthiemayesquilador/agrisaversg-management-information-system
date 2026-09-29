<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Agri Savers G' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            zoom: 0.8;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo-container img {
            max-width: 400px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .logo-image {
            max-width: 300px;
            height: auto;
        }

        .logo-text {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .logo-text h1 {
            font-size: 48px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .logo-text .agri {
            color: #333;
        }

        .logo-text .savers-g {
            color: #FF7F3E;
        }

        .logo-shield {
            width: 80px;
            height: 80px;
            background: #333;
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .logo-shield::before {
            content: '⚙';
            font-size: 40px;
            color: #FF7F3E;
        }

        .welcome-text {
            color: #666;
            font-size: 18px;
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 18px 20px;
            border: none;
            border-radius: 50px;
            background: #FF7F3E;
            color: #000;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-control::placeholder {
            color: #000;
            opacity: 1;
        }

        .form-control:focus {
            background: #FF8C4A;
            box-shadow: 0 0 0 3px rgba(255, 127, 62, 0.2);
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            width: 24px;
            height: 24px;
            background: transparent;
            border: none;
            padding: 0;
        }

        .toggle-password svg {
            width: 24px;
            height: 24px;
            fill: #000;
        }

        .btn {
            width: 100%;
            padding: 18px 20px;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-login {
            background: #FF7F3E;
            color: #fff;
            margin-bottom: 15px;
        }

        .btn-login:hover {
            background: #FF8C4A;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 127, 62, 0.3);
        }

        .btn-signup {
            background: #FF7F3E;
            color: #fff;
            margin-top: 10px;
        }

        .btn-signup:hover {
            background: #FF8C4A;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 127, 62, 0.3);
        }

        .btn-link {
            background: transparent;
            color: #FF7F3E;
            border: 2px solid #FF7F3E;
            margin-top: 15px;
            font-weight: 600;
        }

        .btn-link:hover {
            background: #FF7F3E;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 127, 62, 0.3);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .form-label {
            display: block;
            text-align: left;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 40px 30px;
                margin: 20px;
            }

            .logo-text h1 {
                font-size: 36px;
            }

            .logo-shield {
                width: 60px;
                height: 60px;
            }

            .logo-shield::before {
                font-size: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="<?= base_url('images/agri-savers-logo.png') ?>" alt="Agri Savers G Logo" class="logo-image">
        </div>
        
        <?= $this->renderSection('content') ?>
    </div>

    <script>
        function togglePassword(fieldId, iconId) {
            const passwordInput = document.getElementById(fieldId);
            const eyeIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
            }
        }
    </script>
</body>
</html>
