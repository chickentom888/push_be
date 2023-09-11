<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">User Info</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Update Password</h5>

                    <a href="/" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>
                                    <div class="form-group">
                                        <label for="password">Current Password</label>
                                        <input class="form-control" type="password" name="password" value="" id="password">
                                    </div>

                                    <div class="form-group">
                                        <label for="new_password">New Password</label>
                                        <input class="form-control" type="password" name="new_password" value="" id="new_password">
                                    </div>

                                    <div class="form-group">
                                        <label for="confirm_password">Confirm Password</label>
                                        <input class="form-control" type="password" name="confirm_password" value="" id="confirm_password">
                                    </div>

                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>