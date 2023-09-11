<div class="container-fluid">

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">User Package Info</h5>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">User</th>
                                <th class="border-top-0">Interest</th>
                                <th class="border-top-0">Fund Interest</th>
                                <th class="border-top-0">Principal</th>
                                <th class="border-top-0">Amount</th>
                                <th class="border-top-0">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div>Address: <a href="{{ helper.getLinkAddress(userPackage['user_address'], userPackage['platform'], userPackage['network']) }}" target="_blank"><b>{{ helper.makeShortString(userPackage['user_address']) }}</b></a></div>
                                    <div>Code: <b>{{ userPackage['code'] }}</b></div>
                                    <div>Expire: <b>{{ date('d/m/Y H:i:s', userPackage['expired_at']) }}</b></div>
                                </td>
                                <td>
                                    <div>Last pay: <b>{{ userPackage['last_interest_at'] > 0 ? date('d/m/Y H:i:s', userPackage['last_interest_at']) : '' }}</b></div>
                                    <div>Next pay: <b>{{ userPackage['next_interest_at'] > 0 ? date('d/m/Y H:i:s', userPackage['next_interest_at']) : '' }}</b></div>
                                    <div>Paid day: <b>{{ userPackage['interest_paid_day'] }}</b></div>
                                    <div>Paid amount: <b>{{ userPackage['interest_amount_paid'] }}</b></div>
                                </td>

                                <td>
                                    <div>Last pay: <b>{{ userPackage['last_fund_interest_at'] > 0 ? date('d/m/Y H:i:s', userPackage['last_fund_interest_at']) : '' }}</b></div>
                                    <div>Next pay: <b>{{ userPackage['next_fund_interest_at'] > 0 ? date('d/m/Y H:i:s', userPackage['next_fund_interest_at']) : '' }}</b></div>
                                    <div>Paid times: <b>{{ userPackage['fund_interest_paid_times'] }}</b></div>
                                    <div>Total amount: <b>{{ userPackage['total_fund_interest_amount'] }}</b></div>
                                    <div>Paid amount: <b>{{ userPackage['fund_interest_amount_paid'] }}</b></div>
                                    <div>Pending amount: <b>{{ userPackage['fund_interest_amount_pending'] }}</b></div>
                                </td>

                                <td>
                                    <div>Last pay: <b>{{ userPackage['last_principal_at'] > 0 ? date('d/m/Y H:i:s', userPackage['last_principal_at']) : '' }}</b></div>
                                    <div>Next pay: <b>{{ userPackage['next_principal_at'] > 0 ? date('d/m/Y H:i:s', userPackage['next_principal_at']) : '' }}</b></div>
                                    <div>Paid day: <b>{{ userPackage['principal_paid_day'] }}</b></div>
                                    <div>Paid amount: <b>{{ userPackage['principal_amount_paid'] }}</b></div>
                                </td>

                                <td>
                                    <div><b>{{ userPackage['token_amount'] }}</b></div>
                                </td>
                                <td>
                                    <b class="text-{{ userPackage['status'] == 0 ? 'danger' : 'success' }}">{{ userPackage['status'] == 0 ? 'Inactive' : 'Active' }}</b>
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