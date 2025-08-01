<?php
	$company_name	= isset($_SESSION['company_name']) ? $_SESSION['company_name'] : $title[1].' '. $title[2];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
	  	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	  	<title><?php echo $company_name; ?> | 403</title>
	  	
	  	<!-- Tell the browser to be responsive to screen width -->
	  	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!-- Bootstrap 4 -->
        <link rel="stylesheet" href="<?php echo assets('plugins/bootstrap/css/bootstrap.min.css')?>">

        <!-- custom style sheet -->
		<link rel="stylesheet" href="<?php echo assets('dist/css/bootstrap.css?v=12')?>">
          <style>
            @import url('https://fonts.googleapis.com/css?family=Comfortaa');
            * {
                box-sizing: border-box;
            }
            body, html {
                margin: 0;
                padding: 0;
                height: 100%;
                overflow: hidden;
            }
            body {
                background-color: #a74006;
                font-family: sans-serif;
            }
            .container {
                z-index: 1;
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                text-align: center;
                padding: 10px;
                min-width: 300px;
            }
            .container div {
                display: inline-block;
            }
            .container .lock {
                opacity: 1;
            }
            .container h1 {
                font-family: 'Comfortaa', cursive;
                font-size: 100px;
                text-align: center;
                color: #eee;
                font-weight: 100;
                margin: 0;
            }
            .container p {
                color: #fff;
            }
            .lock {
                transition: 0.5s ease;
                position: relative;
                overflow: hidden;
                opacity: 0;
            }
            .lock.generated {
                transform: scale(0.5);
                position: absolute;
                animation: 2s move linear;
                animation-fill-mode: forwards;
            }
            .lock ::after {
                content: '';
                background: #a74006;
                opacity: 0.3;
                display: block;
                position: absolute;
                height: 100%;
                width: 50%;
                top: 0;
                left: 0;
            }
            .lock .bottom {
                background: #d68910;
                height: 40px;
                width: 60px;
                display: block;
                position: relative;
                margin: 0 auto;
            }
            .lock .top {
                height: 60px;
                width: 50px;
                border-radius: 50%;
                border: 10px solid #fff;
                display: block;
                position: relative;
                top: 30px;
                margin: 0 auto;
            }
            .lock .top::after {
                padding: 10px;
                border-radius: 50%;
            }
            @keyframes move {
                to {
                    top: 100%;
                }
            }
            @media (max-width: 420px) {
                .container {
                    transform: translate(-50%, -50%) scale(0.8);
                }
                .lock.generated {
                    transform: scale(0.3);
                }
            }
        </style>
	</head>
	<body>
        <div class="container">
            <h1>
                4
                <div class="lock">
                    <div class="top"></div>
                    <div class="bottom"></div>
                </div>
                3
            </h1>
            <p>Access denied</p>
            <a 
                class="btn btn-secondary"
                href="javascript:history.go(-1)"
            >GO BACK</a>
        </div>
        <script>
            const interval = 500;
            function generateLocks() {
            const lock = document.createElement('div'),
                    position = generatePosition();
            lock.innerHTML = '<div class="top"></div><div class="bottom"></div>';
            lock.style.top = position[0];
            lock.style.left = position[1];
            lock.classList = 'lock'// generated';
            document.body.appendChild(lock);
            setTimeout(()=>{
                lock.style.opacity = '1';
                lock.classList.add('generated');
            },100);
            setTimeout(()=>{
                lock.parentElement.removeChild(lock);
            }, 2000);
            }
            function generatePosition() {
            const x = Math.round((Math.random() * 100) - 10) + '%';
            const y = Math.round(Math.random() * 100) + '%';
            return [x,y];
            }
            setInterval(generateLocks,interval);
            generateLocks();
        </script>
    </body>        
</html>