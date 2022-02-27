<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Title -->
    <title>Login | Hobbies Admin Panel</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- Favicon -->
    <link rel="shortcut icon" href="img/favicon.ico">

    <!-- Template -->
    <link rel="stylesheet" href="graindashboard/css/graindashboard.css">
</head>

<body class="">

    <main class="main">

        <div class="content">

            <div class="container-fluid pb-5">

                <div class="row justify-content-md-center">
                    <div class="card-wrapper col-12 col-md-4 mt-5">
                        <div class="brand text-center mb-3">
                            <a href="/"><img src="img/logo.png"></a>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Login</h4>
                                <form id="formLogin" action="/public/home/login" method="post">
                                    <div class="form-group">
                                        <label for="email">E-Mail Address</label>
                                        <input id="email" type="email" class="form-control" name="email" required="true"
                                            autofocus="">
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Password
                                        </label>
                                        <input id="password" type="password" class="form-control" name="password"
                                            required="true">
                                        <div class="text-right">
                                            <!--  <a href="password-reset.html" class="small">
                                                Forgot Your Password?
                                            </a> -->
                                        </div>
                                    </div>

                                    <div class="form-group">

                                    </div>

                                    <div class="form-group no-margin">
                                        <a href="#" onclick="return loginPanel();" class="btn btn-primary btn-block">
                                            Sign In
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <footer class="footer mt-3">
                            <div class="container-fluid">
                                <div class="footer-content text-center small">
                                    <span class="text-muted">&copy; <?php echo date('Y');?> Play boys Dashboard. All
                                        Rights Reserved.</span>
                                </div>
                            </div>
                        </footer>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Modal -->
    <div class="modal fade" id="loadMe" tabindex="-1" role="dialog" aria-labelledby="loadMeLabel">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="loader"></div>
                    <div clas="loader-txt">
                        <p>Please wait...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->

    <script src="graindashboard/js/graindashboard.js"></script>
    <script src="graindashboard/js/graindashboard.vendor.js"></script>

    <script>
    loginPanel = function() {
        var em = $('#email').val();
        var ps = $('#password').val();

        console.log('email : ' + em + ' password ' + ps);

        $('#loadMe').modal('show');
        $('#formLogin').submit();

        setTimeout(function() {
            $('#loadMe').modal('hide');
            //window.location.href = 'home/login?';
        }, 2000);

        return false;
    }
    </script>
</body>

</html>