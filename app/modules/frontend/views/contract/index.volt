<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Contract management</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Contract management</h5>

                    {{ flash.output() }}

                    <div class="mb-2">
                        <a href="/contract/form" class="btn btn-success">Create</a>
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
                                <label for="address">Address</label>
                                <input placeholder="Address" id="address" type="text" class="form-control" name="address" value="{{ dataGet['address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="contract_key">Key</label>
                                <input placeholder="Key" id="contract_key" type="text" class="form-control" name="contract_key" value="{{ dataGet['contract_key'] }}">
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
                                <th class="border-top-0">Key</th>
                                <th class="border-top-0">Address</th>
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
                                        <div>{{ item['contract_key'] }}</div>
                                    </td>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['address'] }}</b></a></div>
                                        <div>Name: <b>{{ item['name'] }}</b></div>
                                        <div>Symbol: <b>{{ item['symbol'] }}</b></div>
                                        <div>Decimals: <b>{{ item['decimals'] }}</b></div>
                                    </td>
                                    <td>
                                        <a href="/contract/form/{{ item['_id'] }}" class="btn btn-info btn-sm">Edit</a>
                                        <a href="/contract/delete/{{ item['_id'] }}" class="btn btn-danger btn-sm need-confirm">Delete</a>
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
