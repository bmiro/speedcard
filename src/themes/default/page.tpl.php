<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title><?php print page_get_title(); ?></title>

    <!-- Bootstrap -->
    <link href="/static/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/css/speedcard.css" rel="stylesheet">
    <link href="/static/css/speedcard-navbar.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    
    <div class="navbar navbar-default navbar-inverse" role="navigation">   
      <div class="container">
        <div class="navbar-header pull-left">
          <div class="navbar-brand">
            <p>&#x1F0AB; Speed Card</p>
            <!-- <img alt="&#x1F0AB; Speed Card" src='' height=25px> -->
          </div>
        </div>
        <!--
        <div class="navbar-header pull-right">
          <ul class="nav navbar-nav">
            <li>
              <a href="#">
                anonymous
                <i class="fa fa-sign-out"></i>
              </a>
            </li>
          </ul>
        </div>
        -->
      </div>
    </div>


    <?php print $main; ?>

    <footer class="footer text-center">
      <div class="container">
        <p>
          Cartomagic practice cybertools.
        </p>
      </div>
    </footer>

    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <script type="text/javascript" src="/static/js/bootstrap.min.js"></script>
  </body>
