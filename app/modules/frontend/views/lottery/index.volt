<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Lottery</h4>
            </div>
        </div>
    </div>

    {% include 'partials/wallet_connect.volt' %}

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">List Lottery</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="platform">Platform</label>
                                    <select name="platform" id="platform" class="form-control">
                                        <option value="">Platform</option>
                                        {% for key,item in listPlatform %}
                                            <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="network">Network</label>
                                    <select name="network" id="network" class="form-control">
                                        <option value="">Network</option>
                                        {% for key,item in listNetwork %}
                                            <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="lottery-status">Status</label>
                                    <select name="status" id="lottery-status" class="form-control">
                                        <option value="">Status</option>
                                        {% for key,item in listLotteryStatus %}
                                            <option value="{{ item }}" {{ dataGet['status'] == item ? 'selected' : '' }}>{{ item }}</option>
                                        {% endfor %}
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">&nbsp;</label>
                                    <button class="btn btn-success" type="submit">Search</button>
                                    <input class="btn btn-danger" type="submit" name="export" value="Export"/>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Platform</th>
                                <th class="border-top-0">Time</th>
                                <th class="border-top-0">Info</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Reward Breakdown</th>
                                <th class="border-top-0">Token Bracket</th>
                                <th class="border-top-0">Status</th>
                                <th class="border-top-0">Result</th>
                                <th class="border-top-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr data-lottery-contract-id="{{ item['lottery_contract_id'] }}" data-contract-address="{{ item['contract_address'] }}">

                                    <td>
                                        <div>Platform: <b>{{ item['platform']|upper }}</b></div>
                                        <div>Network: <b>{{ item['network']|upper }}</b></div>
                                    </td>
                                    <td>
                                        <div>Created: <b>{{ date('d/m/Y H:i:s', item['created_at']) }}</b></div>
                                        <div>Start: <b>{{ date('d/m/Y H:i:s', item['start_time']) }}</b></div>
                                        <div>End: <b>{{ date('d/m/Y H:i:s', item['end_time']) }}</b></div>
                                    </td>
                                    <td>
                                        <div>ID: <b>{{ item['lottery_contract_id'] }}</b></div>
                                        <div>Price: <b>{{ item['price'] }}</b></div>
                                        <div>Treasury Percent: <b>{{ item['treasury_fee'] }}</b></div>
                                        <div>Treasury Amount: <b>{{ item['amount_withdraw_to_treasury'] }}</b></div>
                                        <div>Auto Injection: <b class="text-{{ item['auto_injection'] ? 'success' : 'danger' }}">{{ item['auto_injection'] ? 'Yes' : 'No' }}</b></div>
                                    </td>
                                    <td>
                                        <div>Collected: <b>{{ item['amount_collected'] }}</b></div>
                                        <div>Pending Injected: <b>{{ item['pending_injected_amount'] }}</b></div>
                                        <div>Buyer: <b>{{ item['number_buyer'] }}</b></div>
                                        <div>Injected: <b>{{ item['amount_injected'] }}</b></div>
                                        <div>Number ticket: <b>{{ item['number_ticket'] }}</b></div>
                                        <div>Number ticket win: <b>{{ item['number_ticket_win'] }}</b></div>
                                    </td>
                                    <td>
                                        {% for bdKey, bdItem in item['rewards_breakdown'] %}
                                            <div>{{ bdKey }}: <b>{{ bdItem }}% - {{ item['token_breakdown'][bdKey] }} <i class="fa fa-dollar"></i></b></div>
                                        {% endfor %}
                                    </td>
                                    <td>
                                        {% for tpbKey, tpbItem in item['token_per_ticket_in_bracket'] %}
                                            <div>{{ tpbKey }}: <b>{{ tpbItem }} <i class="fa fa-dollar"></i> - {{ item['count_winners_per_bracket'][tpbKey] }} <i class="fa fa-user-o"></i></b></div>
                                        {% endfor %}
                                    </td>
                                    <td>
                                        <b>{{ listLotteryStatus[item['status']] ? listLotteryStatus[item['status']] : null }}</b>
                                    </td>
                                    <td>
                                        <div>Contract Raw: <b>{{ item['contract_raw_final_number'] }}</b></div>
                                        <div>Contract Real: <b>{{ item['contract_real_final_number'] }}</b></div>
                                        <div>User Raw: <b>{{ item['user_raw_final_number'] }}</b></div>
                                        <div>User Real: <b>{{ item['user_real_final_number'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>
                                            <a class="btn btn-info btn-sm" href="/lottery/ticket/{{ item['_id'] }}">Ticket</a>
                                        </div>
                                        <div class="mt-2">
                                            <a class="btn btn-success btn-sm" href="/lottery/buyLog/{{ item['_id'] }}">Buy Log</a>
                                            <a class="btn btn-success btn-sm" href="/lottery/userLog/{{ item['_id'] }}">User Log</a>
                                        </div>

                                        <div class="mt-2">
                                            {% if item['status'] == 1 %}
                                                <button type="button" class="btn btn-info btn-sm btn-close-lottery">Close lottery</button>
                                            {% endif %}

                                            {% if item['status'] == 2 %}
                                                <button type="button" class="btn btn-info btn-sm btn-calculate-reward">Calculate Reward</button>
                                            {% endif %}
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
{% include 'common/plugins/toastr.volt' %}
<script type="text/javascript">

    async function checkConnect() {
        if (provider == null) {
            init();
            await onConnect();
            return false;
        }
    }

    async function closeLottery(contractAddress, lotteryContractId) {
        try {
            await checkConnect();

            if (!contractAddress.length) {
                toastr["error"]("Contract Address Not Found");
                return;
            }
            let lotteryContract = new web3.eth.Contract(ABI_LOTTERY, contractAddress);
            let approve = await lotteryContract.methods.closeLottery(lotteryContractId).send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                toastr["info"]("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                toastr["error"]("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (approve.status) {
                toastr["success"]("Transaction Success");
            }
        } catch (ex) {
            console.log(ex);
        }
    }

    async function calculateReward(contractAddress, lotteryContractId) {
        try {
            await checkConnect();

            if (!contractAddress.length) {
                toastr["error"]("Contract Address Not Found");
                return;
            }
            let lotteryContract = new web3.eth.Contract(ABI_LOTTERY, contractAddress);
            let approve = await lotteryContract.methods.calculateReward(lotteryContractId, false).send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                toastr["info"]("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                toastr["error"]("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (approve.status) {
                toastr["success"]("Transaction Success");
            }
        } catch (ex) {
            console.log(ex);
        }
    }

    $(document).ready(function () {
        $('.btn-close-lottery').on('click', function () {
            let tr = $(this).closest('tr');
            let lotteryContractId = tr.attr('data-lottery-contract-id');
            let contractAddress = tr.attr('data-contract-address');
            closeLottery(contractAddress, lotteryContractId);
        });

        $('.btn-calculate-reward').on('click', function () {
            let tr = $(this).closest('tr');
            let lotteryContractId = tr.attr('data-lottery-contract-id');
            let contractAddress = tr.attr('data-contract-address');
            calculateReward(contractAddress, lotteryContractId);
        });
    });
</script>