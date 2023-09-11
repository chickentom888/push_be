<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Config address management</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Config address management</h5>

                    {{ flash.output() }}

                    <div class="mb-2">
                        <a href="/config_address/form" class="btn btn-success">Create</a>
                    </div>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">
                            <div class="col-sm-2 form-group">
                                <label for="platform">Platform</label>
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Platform</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="network">Network</label>
                                <select name="network" id="network" class="form-control">
                                    <option value="">Network</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="is_listen">Listen</label>
                                <select name="is_listen" id="is_listen" class="form-control">
                                    <option value="">Listen</option>
                                    <option value="1" {{ (dataGet['is_listen']|length AND dataGet['is_listen'] == 1) ? 'selected' : '' }}>
                                        Yes
                                    </option>
                                    <option value="0" {{ (dataGet['is_listen']|length AND dataGet['is_listen'] == 0) ? 'selected' : '' }}>
                                        No
                                    </option>
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="type">Type</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">Type</option>
                                    {% for key, item in listConfigAddress %}
                                        <option value="{{ key }}" {{ dataGet['type'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="search">Address</label>
                                <input placeholder="Type or address" id="search" type="text" class="form-control" name="q" value="{{ dataGet['q'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Platform</th>
                                <th class="border-top-0">Network</th>
                                <th class="border-top-0">Type</th>
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Listen</th>
                                <th class="border-top-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>{{ item['platform']|upper }}</div>
                                    </td>
                                    <td>
                                        <div>{{ item['network']|upper }}</div>
                                    </td>
                                    <td>
                                        <div>{{ item['type'] }}</div>
                                    </td>
                                    <td>
                                        <div>
                                            <a href="{{ helper.getLinkAddress(item['address'], item['platform'], item['network']) }}"
                                               target="_blank"><b>{{ item['description'] }} {{ item['address'] }}</b></a>
                                        </div>
                                    </td>
                                    <td>
                                        <b class="text-{{ item['is_listen'] == 1 ? 'success' : 'danger' }}">{{ item['is_listen'] == 1 ? 'True' : 'Fasle' }}</b>
                                    </td>
                                    <td>
                                        <a href="/config_address/form/{{ item['_id'] }}" class="btn btn-info btn-sm">Edit</a>
                                        <a href="/config_address/delete/{{ item['_id'] }}"
                                           class="btn btn-danger btn-sm need-confirm">Delete</a>
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
    <!-- end row -->

</div>
