<div class="container-fluid">

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Lottery Info</h5>

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
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div>Platform: <b>{{ lottery['platform']|upper }}</b></div>
                                    <div>Network: <b>{{ lottery['network']|upper }}</b></div>
                                </td>
                                <td>
                                    <div>Created: <b>{{ date('d/m/Y H:i:s', lottery['created_at']) }}</b></div>
                                    <div>Start: <b>{{ date('d/m/Y H:i:s', lottery['start_time']) }}</b></div>
                                    <div>End: <b>{{ date('d/m/Y H:i:s', lottery['end_time']) }}</b></div>
                                </td>
                                <td>
                                    <div>ID: <b>{{ lottery['lottery_contract_id'] }}</b></div>
                                    <div>Price: <b>{{ lottery['price'] }}</b></div>
                                    <div>Treasury Percent: <b>{{ lottery['treasury_fee'] }}</b></div>
                                    <div>Treasury Amount: <b>{{ lottery['amount_withdraw_to_treasury'] }}</b></div>
                                    <div>Auto Injection: <b>{{ lottery['auto_injection'] }}</b></div>
                                </td>
                                <td>
                                    <div>Collected: <b>{{ lottery['amount_collected'] }}</b></div>
                                    <div>Pending Injected: <b>{{ lottery['pending_injected_amount'] }}</b></div>
                                    <div>Buyer: <b>{{ lottery['number_buyer'] }}</b></div>
                                    <div>Injected: <b>{{ item['amount_injected'] }}</b></div>
                                </td>
                                <td>
                                    {% for bdKey, bdItem in lottery['rewards_breakdown'] %}
                                        <div>{{ bdKey }}: <b>{{ bdItem }}% - {{ lottery['token_breakdown'][bdKey] }} <i class="fa fa-dollar"></i></b></div>
                                    {% endfor %}
                                </td>
                                <td>
                                    {% for tpbKey, tpbItem in lottery['token_per_ticket_in_bracket'] %}
                                        <div>{{ tpbKey }}: <b>{{ tpbItem }} <i class="fa fa-dollar"></i> - {{ lottery['count_winners_per_bracket'][tpbKey] }} <i class="fa fa-user-o"></i></b></div>
                                    {% endfor %}
                                </td>
                                <td>
                                    <b>{{ listLotteryStatus[lottery['status']] ? listLotteryStatus[lottery['status']] : null }}</b>
                                </td>
                                <td>
                                    <div>Contract Raw: <b>{{ lottery['contract_raw_final_number'] }}</b></div>
                                    <div>Contract Real: <b>{{ lottery['contract_real_final_number'] }}</b></div>
                                    <div>User Raw: <b>{{ lottery['user_raw_final_number'] }}</b></div>
                                    <div>User Real: <b>{{ lottery['user_real_final_number'] }}</b></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>