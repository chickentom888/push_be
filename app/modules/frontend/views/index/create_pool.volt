<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Pool </h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    {% include 'partials/wallet_connect.volt' %}

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Create Pool</h5>

                    <div class="general-label">
                        <form role="form" method="post" action="">

                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="pool-generator-address">Pool Generator</label>
                                        <input class="form-control" type="text" value="" id="pool-generator-address" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <h5>Thông tin</h5>

                                    <div class="form-group">
                                        <label for="pool_token">Pool Token</label>
                                        <input class="form-control" type="text" name="pool_token" value="" id="pool_token" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="contract_name">Contract Name</label>
                                        <input class="form-control" type="text" name="contract_name" value="" id="contract_name" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="contract_symbol">Contract Symbol</label>
                                        <input class="form-control" type="text" name="contract_symbol" value="" id="contract_symbol" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="contract_decimals">Contract Decimals</label>
                                        <input class="form-control" type="text" name="contract_decimals" value="" id="contract_decimals" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="user_balance">User Balance</label>
                                        <input class="form-control" type="text" name="user_balance" value="" id="user_balance" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token">Base Token</label>
                                        <select name="one_token_one_address" class="form-control" id="base_token">
                                            {% for baseToken in setting['base_token']['list_address'] %}
                                                <option value="{{ baseToken['token_address'] }}" data-symbol="{{ baseToken['token_symbol'] }}" data-decimals="{{ baseToken['token_decimals'] }}">{{ baseToken['token_symbol'] }} - {{ baseToken['token_address'] }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="pool_creator">Pool creator</label>
                                        <input class="form-control" type="text" name="pool_creator" value="" id="pool_creator" readonly>
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <h5>Cấu hình</h5>

                                    <div class="form-group">
                                        <label for="amount">How many <span class="pool_token_symbol"></span> are up for pool?</label>
                                        <input class="form-control" type="text" name="amount" value="" id="amount" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="soft_cap">Soft Cap (<span class="base_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="soft_cap" value="0" id="soft_cap" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="hard_cap">Hard Cap (<span class="base_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="hard_cap" value="" id="hard_cap">
                                    </div>

                                    <div class="form-group">
                                        <label for="pool_rate">Pool Rate (1 <span class="base_token_symbol"></span> = ? <span class="pool_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="pool_rate" value="" id="pool_rate" readonly>
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>Prediction</h5>

                                    <div class="form-group">
                                        <label for="base_amount_predict">Pool prediction (<span class="base_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="base_amount_predict" value="" id="base_amount_predict">
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token_owner_predict">Your <span class="base_token_symbol"></span></label>
                                        <input class="form-control" type="text" name="base_token_owner_predict" value="" id="base_token_owner_predict" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="pool_token_sold_predict"><span class="pool_token_symbol"></span> SOLD</label>
                                        <input class="form-control" type="text" name="pool_token_sold_predict" value="" id="pool_token_sold_predict" readonly>
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <h5>Buyer</h5>

                                    <div class="form-group">
                                        <label for="base_amount_per_user">Amount <span class="base_token_symbol"></span> / user</label>
                                        <input class="form-control" type="text" name="base_amount_per_user" value="" id="base_amount_per_user">
                                    </div>

                                    <div class="form-group">
                                        <label for="max_user">Max User</label>
                                        <input class="form-control" type="text" name="max_user" value="" id="max_user" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="start_time">Start Time</label>
                                        <input class="form-control" type="datetime-local" name="start_time" value="" id="start_time">
                                    </div>

                                    <div class="form-group">
                                        <label for="end_time">End Time</label>
                                        <input class="form-control" type="datetime-local" name="end_time" value="" id="end_time">
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>Round 0</h5>
                                    <div class="form-group">
                                        <label for="active_zero_round">Active Zero Round</label>
                                        <select name="active_zero_round" class="form-control" id="active_zero_round">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <h5>Round 1</h5>
                                    <div class="form-group">
                                        <label for="active_first_round">Active First Round</label>
                                        <select name="active_first_round" class="form-control" id="active_first_round">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Auction Round</h5>
                                    <div class="form-group">
                                        <label for="active_auction_round">Active Auction Round</label>
                                        <select name="active_zero_round" class="form-control" id="active_auction_round">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="auction_start_time">Auction Start Time</label>
                                        <input class="form-control" type="datetime-local" name="auction_start_time" value="" id="auction_start_time">
                                    </div>

                                    <div class="form-group">
                                        <label for="auction_end_time">Auction End Time</label>
                                        <input class="form-control" type="datetime-local" name="auction_end_time" value="" id="auction_end_time">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>Vesting</h5>
                                    <div class="form-group">
                                        <label for="active_vesting">Active Vesting</label>
                                        <select name="active_vesting" class="form-control" id="active_vesting">
                                            <option value="0">No</option>
                                            <option value="1">Yes</option>
                                        </select>
                                    </div>
                                </div>

                            </div>

                            <div class="vesting-region">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h5>Vesting Info</h5>
                                    </div>
                                </div>

                                <div class="list-vesting-item">
                                    <div class="row vesting-item">

                                        <div class="col-sm-12">
                                            <h6>Vesting: <span class="vesting-item-index">{{ key }}</span></h6>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="vesting-date-{{ key }}">Date</label>
                                                <input class="form-control vesting-date" type="datetime-local" value="" id="vesting-date-{{ key }}">
                                            </div>
                                        </div>

                                        <div class="col-sm-5">
                                            <div class="form-group">
                                                <label for="vesting-percent-{{ key }}">Percent</label>
                                                <input class="form-control vesting-percent" type="text" value="" id="vesting-percent-{{ key }}">
                                            </div>
                                        </div>

                                        <div class="col-sm-1">
                                            <div class="form-group">
                                                <button type="button" class="btn btn-danger btn-delete-vesting-item">Delete</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-info btn-add-vesting">Add vesting</button>
                                        </div>
                                        <hr>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-info" id="btn_approve">Approve</button>
                            <button type="button" class="btn btn-success" id="btn_create_pool">Create</button>

                        </form>
                    </div>

                </div>
            </div>
        </div>

    </div>
    <!-- end row -->

</div>

<script type="text/html" id="vesting-template">
    <div class="row vesting-item">

        <div class="col-sm-6">
            <div class="form-group">
                <label for="vesting-date">Date</label>
                <input class="form-control vesting-date" type="datetime-local" value="0" id="vesting-date">
            </div>
        </div>

        <div class="col-sm-5">
            <div class="form-group">
                <label for="vesting-percent">Percent</label>
                <input class="form-control vesting-percent" type="text" value="0" id="vesting-percent">
            </div>
        </div>

        <div class="col-sm-1">
            <div class="form-group">
                <button type="button" class="btn btn-danger btn-delete-vesting-item btn-interact-sc">Delete</button>
            </div>
        </div>

    </div>
</script>
{% include 'common/plugins/toastr.volt' %}
<script type="text/javascript">

    function changeBaseToken() {
        let baseTokenSymbol = $('#base_token option:selected').attr('data-symbol');
        $('.base_token_symbol').text(baseTokenSymbol);
    }

    function calculateAmount() {
        let hardCap = parseFloat($('#hard_cap').val() || 0);
        let poolTokenAmount = parseFloat($('#amount').val() || 0);
        if (hardCap <= 0) {
            $('#pool_rate').val(0);
            return;
        }
        let poolRate = poolTokenAmount / hardCap;
        $('#pool_rate').val(poolRate);

        let baseAmountPredict = parseFloat($('#base_amount_predict').val() || 0);
        let poolTokenSoldPredict = baseAmountPredict * poolRate;

        $('#base_token_owner_predict').val(baseAmountPredict);
        $('#pool_token_sold_predict').val(poolTokenSoldPredict);

        let baseAmountPerUser = parseFloat($('#base_amount_per_user').val() || 0);
        let maxUser = Math.ceil(hardCap / baseAmountPerUser);
        $('#max_user').val(maxUser);

        $('#pool_token_required').val(poolTokenAmount);
        $('#pool_token_for_pool').val(poolTokenAmount);

    }

    async function approveToken() {
        await checkConnect();

        let poolGeneratorAddress = $('#pool-generator-address').val();
        if (!poolGeneratorAddress.length) {
            toastr["error"]("Pool Generator Not Found");
            return;
        }

        let poolTokenRequired = parseFloat($('#amount').val());
        let poolTokenDecimals = parseInt($('#contract_decimals').val());
        let poolTokenAddress = $('#pool_token').val();
        if (poolTokenRequired <= 0) {
            alert('Invalid pool token amount');
            return;
        }
        poolTokenRequired = poolTokenRequired * 1000000;
        poolTokenRequired = (new BigNumber(poolTokenRequired)).mul((new BigNumber(10)).pow(poolTokenDecimals));
        poolTokenRequired = web3.utils.toBN(poolTokenRequired).toString();
        poolTokenRequired = web3.utils.randomHex(32);
        let tokenContract = new web3.eth.Contract(ABI_TOKEN, poolTokenAddress);
        let approve = await tokenContract.methods.approve(poolGeneratorAddress, poolTokenRequired).send({
            from: selectedAccount
        }).on('transactionHash', function (hash) {
            toastr["success"]("Please wait a minute to complete your transaction!");
        }).on('confirmation', function (confirmationNumber, receipt) {
        }).on('receipt', function (receipt) {
        }).on('error', function (error, receipt) {
            toastr["error"]("Error");
            console.log("error", error, receipt);
        });

        $("#global_loading").hide();
        if (approve.status) {
            toastr["success"]("Success");
        }

    }

    async function createPool() {
        try {
            await checkConnect();

            let poolGeneratorAddress = $('#pool-generator-address').val();
            if (!poolGeneratorAddress.length) {
                toastr["error"]("Pool Generator Not Found");
                return;
            }

            let poolTokenAmount = parseFloat($('#amount').val() || 0);
            let poolTokenDecimals = parseInt($('#contract_decimals').val());

            poolTokenAmount = (new BigNumber(poolTokenAmount)).mul((new BigNumber(10)).pow(poolTokenDecimals));
            poolTokenAmount = web3.utils.toBN(poolTokenAmount).toString();

            let tokenPrice = parseFloat($('#pool_rate').val());
            tokenPrice = (new BigNumber(tokenPrice)).mul((new BigNumber(10)).pow(poolTokenDecimals));
            tokenPrice = web3.utils.toBN(tokenPrice).toString();

            let baseTokenDecimals = parseInt($('#base_token option:selected').attr('data-decimals'));
            let limitPerBuyer = parseFloat($('#base_amount_per_user').val());

            limitPerBuyer = (new BigNumber(limitPerBuyer)).mul((new BigNumber(10)).pow(baseTokenDecimals));
            limitPerBuyer = web3.utils.toBN(limitPerBuyer).toString();

            let hardCap = parseFloat($('#hard_cap').val());
            hardCap = (new BigNumber(hardCap)).mul((new BigNumber(10)).pow(baseTokenDecimals));
            hardCap = web3.utils.toBN(hardCap).toString();

            let startTime = $('#start_time').val();
            startTime = parseInt((new Date(startTime)).getTime() / 1000);

            let endTime = $('#end_time').val();
            endTime = parseInt((new Date(endTime)).getTime() / 1000);

            let auctionStartTime = $('#auction_start_time').val();
            auctionStartTime = parseInt((new Date(auctionStartTime)).getTime() / 1000) || 0;

            let auctionEndTime = $('#auction_end_time').val();
            auctionEndTime = parseInt((new Date(auctionEndTime)).getTime() / 1000) || 0;

            let activeZeroRound = parseInt($('#active_zero_round').val());
            let activeFirstRound = parseInt($('#active_first_round').val());
            let activeAuctionRound = parseInt($('#active_auction_round').val());
            let activeVesting = parseInt($('#active_vesting').val());
            let activeInfo = [activeZeroRound, activeFirstRound, activeVesting, activeAuctionRound];

            let poolTokenAddress = $('#pool_token').val();
            let baseTokenAddress = $('#base_token').val();
            let params = [
                poolTokenAmount,
                tokenPrice,
                limitPerBuyer,
                hardCap,
                startTime,
                endTime,
                auctionStartTime,
                auctionEndTime
            ];

            let listVestingItem = $('.list-vesting-item .vesting-item');
            let listVestingPeriod = [];
            let listVestingPercent = [];
            listVestingItem.each(function (i, v) {
                vestingDate = $(v).find('.vesting-date').val();
                vestingDate = parseInt((new Date(vestingDate)).getTime() / 1000);
                vestingPercent = parseInt(parseFloat($(v).find('.vesting-percent').val()) * 10);
                if (vestingDate > 0 && vestingPercent > 0) {
                    listVestingPeriod.push(vestingDate);
                    listVestingPercent.push(vestingPercent);
                }
            });

            if (activeVesting === 1) {
                if (listVestingPeriod.length <= 0) {
                    alert('Invalid vesting data');
                    return;
                }
            }
            console.log(selectedAccount, poolTokenAddress, baseTokenAddress, activeInfo, params, listVestingPeriod, listVestingPercent);
            let poolGeneratorContract = new web3.eth.Contract(ABI_POOL_GENERATOR, poolGeneratorAddress);
            let approve = await poolGeneratorContract.methods.createPool(selectedAccount, poolTokenAddress, baseTokenAddress, activeInfo, params, listVestingPeriod, listVestingPercent).send({
                from: selectedAccount
            }).on('transactionHash', function (hash) {
                toastr["success"]("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                toastr["error"]("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (approve.status) {
                toastr["success"]("Success");
            }
        } catch (ex) {
            console.log(ex);
        }
    }

    async function checkConnect() {
        if (provider == null) {
            init();
            await onConnect();
            return false;
        }
    }

    function checkChangeActiveVesting() {
        let activeVesting = $('#active_vesting').val();
        if (activeVesting === '1') {
            $('.vesting-region').show();
        } else {
            $('.vesting-region').hide();
        }
    }

    function getConfigAddress(type = 'pool_generator') {
        let platform = $('#platform_connected').val();
        let network = $('#network_connected').val();

        $.ajax({
            url: '/index/getConfigAddress',
            type: "POST",
            async: true,
            data: {
                network: network,
                platform: platform,
                type: type
            },
            success: function (data) {
                if (data.data) {
                    $('#pool-generator-address').val(data.data.address);
                } else {
                    $('#pool-generator-address').val('');
                }
            },
            done: function () {
            }
        });
    }

    $(document).ready(function () {

        let isSearching = 0;

        changeBaseToken();

        setTimeout(function () {
            getConfigAddress();
        }, 3000);

        $('#base_token').on('change', changeBaseToken);

        $('#pool_token').on('change', function () {

            let contractAddress = $(this).val();
            if (contractAddress.length < 42) {
                $('#contract_name, #contract_decimals, #contract_symbol').val('');
                return;
            }

            if (isSearching === 1) {
                return;
            }

            isSearching = 1;

            $.ajax({
                url: '/api/token/getInfo',
                type: "POST",
                async: true,
                data: {
                    contract_address: contractAddress,
                    network: network,
                    platform: platform,
                    user_address: selectedAccount ?? ''
                },
                success: function (data) {
                    if (data.data) {
                        $('#contract_name').val(data.data.name);
                        $('#contract_symbol').val(data.data.symbol);
                        $('#contract_decimals').val(data.data.decimals);
                        $('#user_balance').val(data.data.user_balance);
                        $('.pool_token_symbol').text(data.data.symbol);
                    } else {
                        $('#contract_name, #contract_decimals, #contract_symbol').val('');
                    }
                    isSearching = 0;
                },
                done: function () {
                    isSearching = 0;
                }
            });
        });

        $('#amount, #hard_cap, #base_amount_predict, #base_amount_per_user').on('change', calculateAmount);

        $('#btn_approve').on('click', approveToken);

        $('#btn_create_pool').on('click', createPool);

        $('.btn-add-vesting').on('click', function () {
            let vestingTemplate = $('#vesting-template').html();
            $('.list-vesting-item').append(vestingTemplate);
        });

        $(document).on('click', '.btn-delete-vesting-item', function () {
            $(this).closest('.vesting-item').remove();
        });

        checkChangeActiveVesting();

        $('#active_vesting').on('change', checkChangeActiveVesting);

    });

</script>