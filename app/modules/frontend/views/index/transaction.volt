<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Transaction</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Transaction</h5>

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
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>
                            <div class="col-sm-2 form-group">
                                <label for="address">Address</label>
                                <input placeholder="Address" id="address" type="text" class="form-control" name="address" value="{{ dataGet['address'] }}">
                            </div>
                            <div class="col-sm-2 form-group">
                                <label for="hash">Hash</label>
                                <input placeholder="Tx" id="hash" type="text" class="form-control" name="hash" value="{{ dataGet['hash'] }}">
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="contract_type">Contract Type</label>
                                <select name="contract_type" id="contract_type" class="form-control">
                                    <option value="">Contract type</option>
                                    {% for key,item in listContractType %}
                                        <option value="{{ key }}" {{ dataGet['type'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="function">Function</label>
                                <input placeholder="Function" id="function" type="text" class="form-control" name="function" value="{{ dataGet['function'] }}">
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
                                <th class="border-top-0">Platform Info</th>
                                <th class="border-top-0">Block</th>
                                <th class="border-top-0">Address</th>
                                <th class="border-top-0">Function</th>
                                <th class="border-top-0">Time</th>
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
                                        <div><b>{{ item['block_number'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>
                                            <label for="base-token-address-{{ key }}">From: </label>
                                            <a href="{{ helper.getLinkAddress(item['from'], item['platform'], item['network']) }}"
                                               target="_blank"><b>{{ item['from'] }}</b></a>
                                        </div>
                                        <div>
                                            <label for="base-token-address-{{ key }}">To: </label>
                                            <a href="{{ helper.getLinkAddress(item['to'], item['platform'], item['network']) }}"
                                               target="_blank"><b>{{ item['to'] }}</b></a>
                                        </div>
                                        <div>
                                            <label for="base-token-address-{{ key }}">Hash: </label>
                                            <a href="{{ helper.getLinkTx(item['hash'], item['platform'], item['network']) }}"
                                               target="_blank"><b>{{ item['hash'] }}</b></a>
                                        </div>
                                    </td>
                                    <td>
                                        <div>Contract type: <b>{{ listContractType[item['contract_type']] }}</b></div>
                                        <div>Function: <b>{{ item['function'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Timestamp: <b>{{ date('d/m/Y H:i:s', item['timestamp']) }}</b></div>
                                        <div>Created at: <b>{{ date('d/m/Y H:i:s', item['created_at']) }}</b></div>
                                        <div>Process at: <b>{{ item['process_at'] > 0 ? date('d/m/Y H:i:s', item['process_at']) : '' }}</b></div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary btn-reset-scan" data-id="{{ item['_id'] }}" {{ item['is_process'] == 0 ? 'hidden' : '' }}> Reset scan</button>
                                        <a href="/index/transaction_detail/{{ item['_id'] }}" class="btn btn-sm btn-info"> Detail</a>
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
<script>
    $('.btn-reset-scan').on('click', function () {
        let _id = $(this).data('id'), $this = $(this);
        $.ajax({
            url: '/index/reset_scan_tx',
            type: "POST",
            async: true,
            data: {
                id: _id
            },
            success: function (data) {
                if (data.status === 1) {
                    $this.prop('hidden', true)
                    alert(data.message);
                } else {
                    alert('Something went wrong!');
                }
            },
            done: function () {
            }
        });
    })
</script>
