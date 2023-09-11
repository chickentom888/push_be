<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Token management</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Token management</h5>

                    {{ flash.output() }}

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
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="id-status">Withdraw status</label>
                                <select name="status" id="id-status" class="form-control">
                                    <option value="">Status</option>
                                    {% for key,item in listStatus %}
                                        <option value="{{ item }}" {{ dataGet['status'] == item ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="search">Address</label>
                                <input placeholder="Token or address" id="search" type="text" class="form-control" name="q" value="{{ dataGet['q'] }}">
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
                                <th class="border-top-0">Image</th>
                                <th class="border-top-0">Platform</th>
                                <th class="border-top-0">Token Info</th>
                                <th class="border-top-0">Lock Info</th>
                                <th class="border-top-0">Time</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <img src="{{ item['image'] }}" alt="" width="20px"
                                             class="image-{{ item['address'] }}">
                                    </td>
                                    <td>
                                        <div>Platform: <b>{{ item['platform']|upper }}</b></div>
                                        <div>Network: <b>{{ item['network']|upper }}</b></div>
                                    </td>
                                    <td>
                                        <div>Address: <a
                                                    href="{{ helper.getLinkAddress(item['address'], item['platform'], item['network']) }}"
                                                    target="_blank"><b>{{ item['address'] }}</b></a>
                                        </div>
                                        <div>Name: <b>{{ item['name'] }}</b></div>
                                        <div>Symbol: <b>{{ item['symbol'] }}</b></div>
                                        <div>Decimals: <b>{{ item['decimals'] }}</b></div>
                                        <div>Supply: <b>{{ helper.numberFormat(item['total_supply_token']) }}</b></div>
                                        <div>Status: <b>{{ listStatus[item['status']] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Token lock amount: <b>{{ helper.numberFormat(item['token_lock_amount']) }}</b>
                                        </div>
                                        <div>Token lock percent: <b>{{ item['token_lock_percent'] }}</b></div>
                                        <div>Token lock value: <b>{{ helper.numberFormat(item['token_lock_value']) }}</b>
                                        </div>
                                        <div>Liquid lock percent: <b>{{ item['liquid_lock_percent'] }}</b></div>
                                        <div>Liquid lock value: <b>{{ helper.numberFormat(item['liquid_lock_value']) }}</b>
                                        </div>
                                        <div>Circulating supply amount:
                                            <b>{{ helper.numberFormat(item['circulating_supply_amount']) }}</b></div>
                                        <div>Circulating supply percent: <b>{{ item['circulating_supply_percent'] }}</b>
                                        </div>
                                        <div>Total lock value: <b>{{ helper.numberFormat(item['total_lock_value']) }}</b>
                                        </div>
                                    </td>
                                    <td>
                                        <div>Lock: <b>{{ date('d/m/Y H:i:s', item['lock_time']) }}</b></div>
                                        <div>Unlock: <b>{{ date('d/m/Y H:i:s', item['unlock_time']) }}</b></div>
                                    </td>
                                    <td>
                                        <button data-toggle="modal" data-address="{{ item['address'] }}"
                                                data-platform="{{ item['platform'] }}"
                                                data-network="{{ item['network'] }}"
                                                class="btn btn-update btn-sm">Edit
                                        </button>
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

    <div id="myModal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="" class="form update-image" method="post">
                    <div class="modal-header">
                        <h5 class="modal-title">Update image</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Modal body text goes here.</p>
                        <input placeholder="image url" type="url" class="form-control data-image" name="image" value="">
                        <input type="text" class="form-control data-platform" hidden name="platform" value="">
                        <input type="text" class="form-control data-network" hidden name="network" value="">
                        <input type="text" class="form-control data-address" hidden name="address" value="">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-save-image">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $('.btn-update').click(function () {
        var address = $(this).data('address'),
            platform = $(this).data('platform'),
            network = $(this).data('network');

        $('.data-platform').attr('value', platform);
        $('.data-network').attr('value', network);
        $('.data-address').attr('value', address);
        $('.data-image').val('');
        $('#myModal').modal('show');
    });

    $('.btn-save-image').click(function () {
        var platform = $('.data-platform').val(),
            network = $('.data-network').val(),
            image = $('.data-image').val(),
            address = $('.data-address').val(),
            url = '/api/token/update-image-by-address/' + address,
            url_validate = /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;

        if (!url_validate.test(image)) {
            alert('This link is not valid URL');
        } else {
            $.ajax({
                url: url,
                type: "POST",
                dataType: "text",
                data: {
                    platform: platform, network: network, image: image
                },
                success: function (res) {
                    res = JSON.parse(res);
                    if (res.status === 1) {
                        $('#myModal').modal('toggle');
                        $('.image-' + address).attr('src', image);
                    } else {
                        alert(res.message);
                    }
                },
            });
        }
    })
</script>