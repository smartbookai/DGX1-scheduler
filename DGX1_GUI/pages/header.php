<?php
if (session_status() == PHP_SESSION_NONE)
    session_start();
?>

<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>UAEU DGX-1 Portal</title>

    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <!-- Jquery JS -->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>


    <!-- Materialize CSS (JS is in footer) -->
    <link rel="stylesheet" href="/css/materialize.css">

    <!-- dhtmlxgantt CSS/JS -->
    <script type="text/javascript" src="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.js"></script>
    <link href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css" rel="stylesheet">


    <!-- NumJS -->
    <script src="/js/thirdparty/numjs.js"></script>


    <link rel="stylesheet" href="/css/footable.standalone.min.css">
    <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

    <script type="text/javascript" src="/js/thirdparty/footable.js"></script>

    <link rel="stylesheet" href="/css/style.css">
    <!-- skedTape CSS/JS -->
    <link rel="stylesheet" href="/css/jquery.skedTape.css">
    <script type="text/javascript" src="/js/thirdparty/jquery.skedTape.js"></script>
    <link rel="stylesheet" href="/generateSkedTapeCSS">
</head>

<body>
    <header>
        <nav class="nav-wrapper cyan darken-4">
            <div class="container">
                <a href="/" class="brand-logo">DGX-1 Portal</a>
                <a href="#" class="sidenav-trigger" data-target="mobile-links">
                    <i class="material-icons">menu</i>
                </a>
                <ul class="right hide-on-med-and-down">
<?php   if(isset($_SESSION['isAdmin'])) { ?>
                    <li class="logged-in-admin">
                        <a class='dropdown-trigger' href='#' data-target='dropdown1'>Admin</a>
                        <ul id='dropdown1' class='dropdown-content'>
                          <li><a href="/admin">Tasks</a></li>
                          <li><a href="/users">Users</a></li>
                        </ul>
                    </li>
<?php   }
        if (isset($_SESSION['user_ID'])) { ?>
                    <li class="logged-in"><a href="/account">Account</a></li>
                    <li class="logged-in"><a href="/request">Request</a></li>
                    <li class="logged-in"><a href="/?logout=1">Logout</a></li>
<?php   } else { ?>
                    <li class="logged-out"><a href="/login">Login/Sign Up</a></li>
<?php   } ?>
                    <li class="logged-out"><a href="/faq">FAQ</a></li>
                </ul>
            </div>
        </nav>
    <header>

    <ul class="sidenav" id="mobile-links">
<?php   if(isset($_SESSION['isAdmin'])) { ?>
                    <li class="logged-in-admin">
                        <a class='dropdown-trigger' href='#' data-target='dropdown2'>Admin</a>
                        <ul id='dropdown2' class='dropdown-content'>
                          <li><a href="/admin">Tasks</a></li>
                          <li><a href="/users">Users</a></li>
                        </ul>
                    </li>
<?php   }
        if (isset($_SESSION['user_ID'])) { ?>
        <li class="logged-in"><a href="/account">Account</a></li>
        <li class="logged-in"><a href="/request">Request</a></li>
        <li class="logged-in"><a href="/?logout=1">Logout</a></li>
<?php   } else { ?>
        <li class="logged-out"><a href="/login">Login/Sign Up</a></li>
<?php   } ?>
        <li class="logged-out"><a href="/faq">FAQ</a></li>
    </ul>
