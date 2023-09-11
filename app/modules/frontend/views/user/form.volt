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

                    <h5 class="header-title mb-4 mt-0">User Info</h5>

                    <a href="/user" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>


                                    <div class="form-group">
                                        <label for="username">Username</label>
                                        <input class="form-control" type="text" name="username" value="{{ object['username'] }}" id="username">
                                    </div>

                                    <div class="form-group">
                                        <label for="user-status">Status</label>
                                        <select name="status" class="form-control" id="user-status">
                                            <option value="0" {{ (object['status']|length AND object['status'] == 0) ? 'selected' : '' }}>Inactive</option>
                                            <option value="1" {{ (object['status']|length AND object['status'] == 1) ? 'selected' : '' }}>Active</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="role">Role</label>
                                        <select name="role" class="form-control" id="role">
                                            <option value="1" {{ (object['role']|length AND object['role'] == 1) ? 'selected' : '' }}>Admin</option>
                                            <option value="2" {{ (object['role']|length AND object['role'] == 2) ? 'selected' : '' }}>Reporter</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input class="form-control" type="text" name="password" value="" id="password" placeholder="Blank if no update">
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