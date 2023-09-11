<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">User Package Principal</h4>
            </div>
        </div>
    </div>
</div>

{% if userPackage %}
    {% include 'layouts/staking/user_package_info.volt' %}
{% endif %}

<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">User Package Principal</h5>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2 form-group">
                                <label for="user_address">User Address</label>
                                <input placeholder="User Address" id="user_address" type="text" class="form-control" name="user_address" value="{{ dataGet['user_address'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_connect_id">User ID</label>
                                <input placeholder="User ID" id="user_connect_id" type="text" class="form-control" name="user_connect_id" value="{{ dataGet['user_connect_id'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="user_package_id">User Package ID</label>
                                <input placeholder="User ID" id="user_package_id" type="text" class="form-control" name="user_package_id" value="{{ dataGet['user_package_id'] }}">
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>

                    <div class="row mt-2">
                        <div class="col-sm-12">
                            <div>Total: <b>{{ helper.numberFormat(summaryData['principal_amount'], 2) }}</b></div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">

                            <thead>
                            <tr>
                                <th class="border-top-0">User Info</th>
                                <th class="border-top-0">User Package</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Day</th>
                                <th class="border-top-0">Date</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                {% set userPackage = helper.getUserPackage(item['user_package_id']) %}
                                <tr>
                                    <td>
                                        <div>Address: <a href="{{ helper.getLinkAddress(item['user_address']) }}" target="_blank"><b>{{ helper.makeShortString(item['user_address']) }}</b></a></div>
                                        <div>ID: <b>{{ item['user_connect_id'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>ID: <b>{{ item['user_package_id'] }}</b></div>
                                        <div>Code: <b>{{ userPackage['code'] }}</b></div>
                                    </td>
                                    <td>{{ helper.numberFormat(item['principal_amount'], 2) }}</td>
                                    <td>{{ item['pricipal_day'] }}</td>
                                    <td>{{ date('d/m/Y H:i:s', item['created_at']) }}</td>
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