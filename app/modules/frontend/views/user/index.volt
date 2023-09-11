<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">User</h4>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">List User</h5>

                    {{ flash.output() }}

                    <div class="mb-2">
                        <a href="/user/form" class="btn btn-success">Create</a>
                    </div>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2 form-group">
                                <label for="user-status">Status</label>
                                <select name="status" id="user-status" class="form-control">
                                    <option value="">Select</option>
                                    <option value="0" {{ (dataGet['status']|length AND dataGet['status'] == 0) ? 'selected' : '' }}>Inactive</option>
                                    <option value="1" {{ (dataGet['status']|length AND dataGet['status'] == 1) ? 'selected' : '' }}>Active</option>
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="role">Role</label>
                                <select name="role" id="role" class="form-control">
                                    <option value="">Select</option>
                                    <option value="1" {{ (dataGet['status']|length AND dataGet['status'] == 1) ? 'selected' : '' }}>Admin</option>
                                    <option value="2" {{ (dataGet['status']|length AND dataGet['status'] == 2) ? 'selected' : '' }}>Reporter</option>
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="username">Username</label>
                                <input placeholder="Username" id="username" type="text" class="form-control" name="username" value="{{ dataGet['username'] }}">
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">&nbsp;</label>
                                    <button class="btn btn-success" type="submit">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Username</th>
                                <th class="border-top-0">Role</th>
                                <th class="border-top-0">Status</th>
                                <th class="border-top-0">Last login</th>
                                <th class="border-top-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>

                                    <td>{{ item['username'] }}</td>
                                    <td>{{ item['role'] == 1 ? 'Admin' : 'Reporter' }}</td>
                                    <td>
                                        <b class="text-{{ item['status'] == 0 ? 'danger' : 'success' }}">{{ item['status'] == 0 ? 'Inactive' : 'Active' }}</b>
                                    </td>
                                    <td>{{ item['last_login'] > 0 ? date('d/m/Y H:i:s', item['last_login']) : '' }}</td>
                                    <td>
                                        <div>
                                            <a class="btn btn-success btn-sm" href="/user/form/{{ item['_id'] }}">Update</a>
                                        </div>
                                    </td>
                                </tr>
                            {% endfor %}

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2 mb-2">
                        {% include 'layouts/paging.volt' %}
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>