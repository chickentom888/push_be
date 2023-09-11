<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Exchange Platform</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Exchange Platform</h5>

                    {{ flash.output() }}

                    <div class="mb-2">
                        <a href="/exchange_platform/form" class="btn btn-success">Create</a>
                    </div>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2 form-group">
                                <label for="platform">Platform</label>
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Select</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="network">Network</label>
                                <select name="network" id="network" class="form-control">
                                    <option value="">Select</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="search">Name</label>
                                <input placeholder="Exchange name" id="search" type="text" class="form-control" name="q" value="{{ dataGet['q'] }}">
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-2">
                        Total: {{ helper.numberFormat(count) }}
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Platform</th>
                                <th class="border-top-0">Exchange</th>
                                <th class="border-top-0">Presale Address</th>
                                <th class="border-top-0">Dex Address</th>
                                <th class="border-top-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Platform: <b>{{ item['platform']|upper }}</b></div>
                                        <div>Network: <b>{{ item['network']|upper }}</b></div>
                                    </td>
                                    <td>
                                        <div>Name: <b>{{ item['exchange_name'] }}</b></div>
                                        <div>Key: <b>{{ item['exchange_key'] }}</b></div>
                                        <div>Url: <a href="{{ item['exchange_url'] }}" target="_blank"><b>{{ item['exchange_url'] }}</b></a></div>
                                    </td>
                                    <td>
                                        <div>Presale Factory Address: <a href="{{ helper.getLinkAddress(item['presale_factory_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['presale_factory_address'] }}</b></a></div>
                                        <div>Presale Generator Address: <a href="{{ helper.getLinkAddress(item['presale_generator_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['presale_generator_address'] }}</b></a></div>
                                        <div>Presale Setting Address: <a href="{{ helper.getLinkAddress(item['presale_setting_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['presale_setting_address'] }}</b></a></div>
                                    </td>

                                    <td>
                                        <div>Dex Factory Address: <a href="{{ helper.getLinkAddress(item['dex_factory_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['dex_factory_address'] }}</b></a></div>
                                        <div>Dex Router Address: <a href="{{ helper.getLinkAddress(item['dex_router_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['dex_router_address'] }}</b></a></div>
                                        <div>Dex Locker Address: <a href="{{ helper.getLinkAddress(item['dex_locker_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['dex_locker_address'] }}</b></a></div>
                                        <div>Dex Wrap Token Address: <a href="{{ helper.getLinkAddress(item['dex_wrap_token_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['dex_wrap_token_address'] }}</b></a></div>
                                    </td>
                                    <td>
                                        <a href="/exchange_platform/form/{{ item['_id'] }}" class="btn btn-info btn-sm">Edit</a>
                                        <a href="/exchange_platform/delete/{{ item['_id'] }}" class="btn btn-danger btn-sm need-confirm">Delete</a>
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
