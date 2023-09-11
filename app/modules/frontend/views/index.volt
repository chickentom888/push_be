<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>PUSH SWAP</title>
    <meta content="PUSH SWAP" name="description"/>
    <meta content="Tony Stark" name="author"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>

    <link rel="shortcut icon" href="{{ config.site.icon }}">
    <link href="/assets/plugins/jvectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet">

    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/icons.css" rel="stylesheet" type="text/css">
    <link href="/assets/css/style.css?v=24" rel="stylesheet" type="text/css">
    <!-- jQuery  -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/const.js?v={{ config.site.file_version }}"></script>
    <script src="/custom/js/custom.js"></script>

    <script src="/assets/js/bignumber.min.js"></script>
    <script src="/assets/plugins/web3/web3.min.js"></script>
    <script src="/assets/plugins/web3/index.js"></script>
    <script src="/assets/plugins/web3/evm-chains.min.js"></script>
    <script src="/assets/plugins/web3/web3-provider.min.js"></script>
    <script src="/assets/plugins/web3/app.js?v=4"></script>

</head>


<body class="fixed-left">

<!-- Begin page -->
<div id="wrapper">

    <!-- ========== Left Sidebar Start ========== -->
    <div class="left side-menu">
        <button type="button" class="button-menu-mobile button-menu-mobile-topbar open-left waves-effect"><i class="ion-close"></i></button><!-- LOGO -->
        <div class="topbar-left">
            <div class="text-center">
                <div class="row">
                    <div class="col-sm-12">
                        <a href="/" class="logo">
                            <img src="{{ config.site.logo }}" alt="{{ config.site.name }}" width="90%">
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="slimScrollDiv" style="position: relative; overflow: auto; width: auto; height: 773px;">
            <div class="sidebar-inner slimscrollleft" style="overflow: auto; width: auto; height: 773px;">
                <div id="sidebar-menu">
                    <ul>

                        {% if listMenu %}
                            {% for item in listMenu %}
                                {% if in_array(userInfo['role'], item['role']) %}
                                    {% if item['type'] == 'split' %}
                                        <li class="menu-title">{{ item['name'] }}</li>
                                    {% else %}
                                        <li class="{{ item['child']|length > 0 ? 'has_sub' : '' }}">
                                            <a href="{{ item['link'] }}" class="waves-effect {{ activeMenu[0] == item['active_menu'] ? 'active' : '' }}">
                                                <i class="{{ item['icon'] }}"></i>
                                                <span>{{ item['name'] }}</span>

                                                {% if item['child']|length %}
                                                    <span class="float-right"><i class="mdi mdi-chevron-right"></i></span>
                                                {% endif %}
                                            </a>

                                            {% if item['child']|length %}
                                                <ul class="list-unstyled">
                                                    {% for cItem in item['child'] %}
                                                        {% if in_array(userInfo['role'], cItem['role']) %}
                                                            <li {{ activeMenu[1] == cItem['active_menu'] ? 'active' : '' }}><a href="{{ cItem['link'] }}">{{ cItem['name'] }}</a></li>
                                                        {% endif %}
                                                    {% endfor %}
                                                </ul>
                                            {% endif %}
                                        </li>
                                    {% endif %}
                                {% endif %}
                            {% endfor %}

                        {% endif %}
                    </ul>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="slimScrollBar" style="background: rgb(158, 165, 171); width: 10px; position: absolute; top: 0px; opacity: 0.4; display: none; border-radius: 7px; z-index: 99; right: 1px; height: 773px;"></div>
            <div class="slimScrollRail" style="width: 10px; height: 100%; position: absolute; top: 0px; display: none; border-radius: 7px; background: rgb(51, 51, 51); opacity: 0.2; z-index: 90; right: 1px;"></div>
        </div><!-- end sidebar inner -->
    </div>
    <!-- Left Sidebar End -->

    <!-- Start right Content here -->

    <div class="content-page">
        <!-- Start content -->
        <div class="content">

            <!-- Top Bar Start -->
            <div class="topbar">

                <nav class="navbar-custom">

                    <ul class="list-inline float-right mb-0">

                        <li class="list-inline-item dropdown notification-list">
                            <a class="nav-link dropdown-toggle arrow-none waves-effect nav-user" data-toggle="dropdown" href="#" role="button"
                               aria-haspopup="false" aria-expanded="false">
                                <img src="/assets/images/users/avatar-1.jpg" alt="user" class="rounded-circle">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                                <!-- item-->
                                <div class="dropdown-item noti-title">
                                    <h5>Welcome {{ userInfo['username'] }}</h5>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/user/change_password"><i class="mdi mdi-lock-open-outline m-r-5 text-muted"></i>Password</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="/authorize/logout"><i class="mdi mdi-logout m-r-5 text-muted"></i> Logout</a>
                            </div>
                        </li>

                    </ul>

                    <ul class="list-inline menu-left mb-0">
                        <li class="float-left">
                            <button class="button-menu-mobile open-left waves-light waves-effect">
                                <i class="mdi mdi-menu"></i>
                            </button>
                        </li>
                    </ul>

                    <div class="clearfix"></div>

                </nav>

            </div>
            <!-- Top Bar End -->

            <div class="page-content-wrapper ">

                {{ content() }}

            </div> <!-- Page content Wrapper -->

        </div> <!-- content -->

        <footer class="footer">
            © 2023 {{ config.site.name }}
        </footer>

    </div>
    <!-- End Right content here -->

</div>
<!-- END wrapper -->


<script src="/assets/js/popper.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/modernizr.min.js"></script>
<script src="/assets/js/detect.js"></script>
<script src="/assets/js/fastclick.js"></script>
<script src="/assets/js/jquery.slimscroll.js"></script>
<script src="/assets/js/jquery.blockUI.js"></script>
<script src="/assets/js/waves.js"></script>
<script src="/assets/js/jquery.nicescroll.js"></script>
<script src="/assets/js/jquery.scrollTo.min.js"></script>

<!-- App js -->
<script src="/assets/js/app.js"></script>
<script>

    $(document).ready(function () {
        $("#boxscroll").niceScroll({cursorborder: "", cursorcolor: "#cecece", boxzoom: true});
        $("#boxscroll2").niceScroll({cursorborder: "", cursorcolor: "#cecece", boxzoom: true});
    });

</script>


</body>
</html>
