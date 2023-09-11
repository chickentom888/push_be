<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Presale Setting Address</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">List Setting Address Type</h5>
                    <div class="row">
                        <div class="col-sm-12">
                            {% for key,item in listPresaleSettingAddressType %}
                                <a href="/presale_setting_address/index/{{ key }}" class="btn btn-{{ type == key ? 'info' : 'outline-dark' }}">{{ item }}</a>
                            {% endfor %}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Setting Address</h5>

                    {{ flash.output() }}

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
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="address">Address</label>
                                <input placeholder="Address" id="address" type="text" class="form-control" name="q" value="{{ dataGet['q'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
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
                                <th class="border-top-0">Type</th>
                                <th class="border-top-0">Avatar</th>
                                <th class="border-top-0">Token Info</th>
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
                                        <div>{{ item['type'] }}</div>
                                    </td>
                                    <td>
                                        <img src="{{ item['avatar'] }}" alt="">
                                    </td>
                                    <td>
                                        <div>Token Address: <a href="{{ helper.getLinkAddress(item['token_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['token_address'] }}</b></a></div>
                                        <div>Token Decimals: <b>{{ item['token_decimals'] }}</b></div>
                                        <div>Token Name: <b>{{ item['token_name'] }}</b></div>
                                        <div>Token Symbol: <b>{{ item['token_symbol'] }}</b></div>
                                        <div>Token Amount: <b>{{ item['token_amount'] }}</b></div>
                                    </td>
                                    <td>
                                        <a href="/presale_setting_address/form/{{ item['_id'] }}" class="btn btn-info btn-sm">Update</a>
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
