<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ page_title }}</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">{{ page_title }}</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Platform</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <select name="network" id="network" class="form-control">
                                    <option value="">Network</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                                <input class="btn btn-danger" type="submit" name="export" value="Export" />
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
                                <th class="border-top-0">Network</th>
                                <th class="border-top-0">Old value</th>
                                <th class="border-top-0">Value</th>
                                <th class="border-top-0">Created time</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Platform: <b>{{ item['platform']|upper }}</b></div>
                                    </td>
                                    <td>
                                        <div>Network: <b>{{ item['network']|upper }}</b></div>
                                    </td>
                                    <td>
                                        <div>Base fee: <b>{{ item['old_value']['base_fee'] }}</b></div>
                                        <div>Token fee percent: <b>{{ item['old_value']['token_fee_percent'] }}</b></div>
                                        <div>Fee address: <a href="{{ helper.getLinkAddress(item['old_value']['address_fee'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['old_value']['address_fee'] }}</b></a></div>
                                    </td>
                                    <td>
                                        <div>Base fee: <b>{{ item['value']['base_fee'] }}</b></div>
                                        <div>Token fee percent: <b>{{ item['value']['token_fee_percent'] }}</b></div>
                                        <div>Fee address: <a href="{{ helper.getLinkAddress(item['value']['address_fee'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['value']['address_fee'] }}</b></a></div>
                                    </td>
                                    <td>
                                        {{ date('d/m/Y H:i:s', item['created_at']) }}
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