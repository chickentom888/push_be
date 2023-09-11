<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Sale </h4>
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

                    <h5 class="header-title mb-4 mt-0">Create Sale</h5>

                    <div class="general-label">
                        <form role="form" method="post" action="">

                            <div class="row">

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="sale-generator-address">Sale Generator</label>
                                        <input class="form-control" type="text" value="" id="sale-generator-address" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <h5>Thông tin</h5>

                                    <div class="form-group">
                                        <label for="sale_token">Sale Token</label>
                                        <input class="form-control" type="text" name="sale_token" value="" id="sale_token" required>
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
                                        <label for="sale_creator">Sale creator</label>
                                        <input class="form-control" type="text" name="sale_creator" value="" id="sale_creator" readonly>
                                    </div>

                                </div>

                                <div class="col-sm-6">
                                    <h5>Cấu hình</h5>

                                    <div class="form-group">
                                        <label for="amount">How many <span class="sale_token_symbol"></span> are up for sale?</label>
                                        <input class="form-control" type="text" name="amount" value="" id="amount" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="soft_cap">Soft Cap (<span class="base_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="soft_cap" value="" id="soft_cap">
                                    </div>

                                    <div class="form-group">
                                        <label for="hard_cap">Hard Cap (<span class="base_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="hard_cap" value="" id="hard_cap">
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_rate">Sale Rate (1 <span class="base_token_symbol"></span> = ? <span class="sale_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="sale_rate" value="" id="sale_rate" readonly>
                                    </div>

                                </div>

                            </div>

                            <div class="row">
                                <div class="col-sm-6">
                                    <h5>Prediction</h5>

                                    <div class="form-group">
                                        <label for="base_amount_predict">Sale prediction (<span class="base_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="base_amount_predict" value="" id="base_amount_predict">
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token_fee">Base Token Fee ({{ setting['setting']['base_fee_percent'] }}%) (<span class="base_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="base_token_fee" value="" id="base_token_fee" readonly data-base-fee-percent="{{ setting['setting']['base_fee_percent'] }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_fee">Sale Token Fee ({{ setting['setting']['token_fee_percent'] }}%) (<span class="sale_token_symbol"></span>)</label>
                                        <input class="form-control" type="text" name="sale_token_fee" value="" id="sale_token_fee" readonly data-token-fee-percent="{{ setting['setting']['token_fee_percent'] }}">
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token_owner_predict">Your <span class="base_token_symbol"></span></label>
                                        <input class="form-control" type="text" name="base_token_owner_predict" value="" id="base_token_owner_predict" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_sold_predict"><span class="sale_token_symbol"></span> SOLD</label>
                                        <input class="form-control" type="text" name="sale_token_sold_predict" value="" id="sale_token_sold_predict" readonly>
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
                                    <h5>Fee</h5>

                                    <div class="form-group">
                                        <label for="creation_fee">Creation Fee</label>
                                        <input class="form-control" type="text" name="creation_fee" value="{{ setting['setting']['creation_fee'] }}" id="creation_fee" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="base_token_raised_fee"><span class="base_token_symbol"></span> raised fee</label>
                                        <input class="form-control" type="text" name="base_token_raised_fee" value="{{ setting['setting']['base_fee_percent'] }}%" id="base_token_raised_fee" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_raised_fee"><span class="sale_token_symbol"></span> raised fee</label>
                                        <input class="form-control" type="text" name="sale_token_raised_fee" value="{{ setting['setting']['token_fee_percent'] }}%" id="sale_token_raised_fee" readonly>
                                    </div>

                                </div>

                                <div class="col-sm-6">

                                    <h5>Fee</h5>

                                    <div class="form-group">
                                        <label for="sale_token_required"><span class="sale_token_symbol"></span> Required</label>
                                        <input class="form-control" type="text" name="sale_token_required" value="" id="sale_token_required" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_for_sale"><span class="sale_token_symbol"></span> for sale</label>
                                        <input class="form-control" type="text" name="sale_token_for_sale" value="" id="sale_token_for_sale" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale_token_for_fee"><span class="sale_token_symbol"></span> for fee</label>
                                        <input class="form-control" type="text" name="sale_token_for_fee" value="" id="sale_token_for_fee" readonly>
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
                            <button type="button" class="btn btn-success" id="btn_create_sale">Create</button>

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

<script type="text/javascript">

    function changeBaseToken() {
        let baseTokenSymbol = $('#base_token option:selected').attr('data-symbol');
        $('.base_token_symbol').text(baseTokenSymbol);
    }

    function calculateAmount() {
        let hardCap = parseFloat($('#hard_cap').val() || 0);
        let saleTokenAmount = parseFloat($('#amount').val() || 0);
        if (hardCap <= 0) {
            $('#sale_rate').val(0);
            return;
        }
        let saleRate = saleTokenAmount / hardCap;
        $('#sale_rate').val(saleRate);

        let baseTokenFeeInput = $('#base_token_fee');
        let saleTokenFeeInput = $('#sale_token_fee');

        let baseFeePercent = parseFloat(baseTokenFeeInput.attr('data-base-fee-percent') || 0);
        let tokenFeePercent = parseFloat(saleTokenFeeInput.attr('data-token-fee-percent') || 0);

        let baseAmountPredict = parseFloat($('#base_amount_predict').val() || 0);
        let baseTokenFeePredict = baseAmountPredict / 100 * baseFeePercent;
        let saleTokenSoldPredict = baseAmountPredict * saleRate;
        let saleTokenFeePredict = saleTokenSoldPredict / 100 * tokenFeePercent;
        let baseAmountPredictAfterFee = baseAmountPredict - baseTokenFeePredict;

        baseTokenFeeInput.val(baseTokenFeePredict);
        saleTokenFeeInput.val(saleTokenFeePredict);
        let baseTokenOwnerPredict = baseAmountPredictAfterFee;
        $('#base_token_owner_predict').val(baseTokenOwnerPredict);
        $('#sale_token_sold_predict').val(saleTokenSoldPredict);

        let baseAmountPerUser = parseFloat($('#base_amount_per_user').val() || 0);
        let maxUser = Math.ceil(hardCap / baseAmountPerUser);
        $('#max_user').val(maxUser);

        let baseFeeAmount = hardCap / 100 * baseFeePercent;

        let maxSaleTokenAmountForFee = saleTokenAmount / 100 * tokenFeePercent;
        let maxSaleTokenAmountRequired = saleTokenAmount + maxSaleTokenAmountForFee;

        $('#sale_token_required').val(maxSaleTokenAmountRequired);
        $('#sale_token_for_sale').val(saleTokenAmount);
        $('#sale_token_for_fee').val(maxSaleTokenAmountForFee);

    }

    async function approveToken() {
        await checkConnect();

        let saleGeneratorAddress = $('#sale-generator-address').val();
        if (!saleGeneratorAddress.length) {
            toastr["error"]("Sale Generator Not Found");
            return;
        }

        let saleTokenRequired = parseFloat($('#sale_token_required').val());
        let saleTokenDecimals = parseInt($('#contract_decimals').val());
        let saleTokenAddress = $('#sale_token').val();
        if (saleTokenRequired <= 0) {
            alert('Invalid sale token amount');
            return;
        }
        saleTokenRequired = saleTokenRequired * 1000000;
        saleTokenRequired = (new BigNumber(saleTokenRequired)).mul((new BigNumber(10)).pow(saleTokenDecimals));
        saleTokenRequired = web3.utils.toBN(saleTokenRequired).toString();
        let tokenContract = new web3.eth.Contract(ABI_TOKEN, saleTokenAddress);
        let approve = await tokenContract.methods.approve(saleGeneratorAddress, saleTokenRequired).send({
            from: selectedAccount
        }).on('transactionHash', function (hash) {
            alert("Please wait a minute to complete your transaction!");
        }).on('confirmation', function (confirmationNumber, receipt) {
        }).on('receipt', function (receipt) {
        }).on('error', function (error, receipt) {
            alert("Error");
            console.log("error", error, receipt);
        });

        $("#global_loading").hide();
        if (approve.status) {
            alert("Success");
        }

    }

    async function createSale() {
        try {
            await checkConnect();

            let saleGeneratorAddress = $('#sale-generator-address').val();
            if (!saleGeneratorAddress.length) {
                toastr["error"]("Sale Generator Not Found");
                return;
            }

            let saleTokenAmount = parseFloat($('#amount').val() || 0);
            let saleTokenDecimals = parseInt($('#contract_decimals').val());

            saleTokenAmount = (new BigNumber(saleTokenAmount)).mul((new BigNumber(10)).pow(saleTokenDecimals));
            saleTokenAmount = web3.utils.toBN(saleTokenAmount).toString();

            let tokenPrice = parseFloat($('#sale_rate').val());
            tokenPrice = (new BigNumber(tokenPrice)).mul((new BigNumber(10)).pow(saleTokenDecimals));
            tokenPrice = web3.utils.toBN(tokenPrice).toString();

            let baseTokenDecimals = parseInt($('#base_token option:selected').attr('data-decimals'));
            let limitPerBuyer = parseFloat($('#base_amount_per_user').val());

            limitPerBuyer = (new BigNumber(limitPerBuyer)).mul((new BigNumber(10)).pow(baseTokenDecimals));
            limitPerBuyer = web3.utils.toBN(limitPerBuyer).toString();

            let hardCap = parseFloat($('#hard_cap').val());
            hardCap = (new BigNumber(hardCap)).mul((new BigNumber(10)).pow(baseTokenDecimals));
            hardCap = web3.utils.toBN(hardCap).toString();

            let softCap = parseFloat($('#soft_cap').val());
            softCap = (new BigNumber(softCap)).mul((new BigNumber(10)).pow(baseTokenDecimals));
            softCap = web3.utils.toBN(softCap).toString();

            let startTime = $('#start_time').val();
            startTime = parseInt((new Date(startTime)).getTime() / 1000);

            let endTime = $('#end_time').val();
            endTime = parseInt((new Date(endTime)).getTime() / 1000);

            let activeZeroRound = parseInt($('#active_zero_round').val());
            let activeFirstRound = parseInt($('#active_first_round').val());
            let activeVesting = parseInt($('#active_vesting').val());
            let activeInfo = [activeZeroRound, activeFirstRound, activeVesting];

            let saleTokenAddress = $('#sale_token').val();
            let baseTokenAddress = $('#base_token').val();
            let params = [
                saleTokenAmount,
                tokenPrice,
                limitPerBuyer,
                hardCap,
                softCap,
                startTime,
                endTime
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

            let creationFee = parseFloat($('#creation_fee').val());
            creationFee = (new BigNumber(creationFee)).mul((new BigNumber(10)).pow(18));
            creationFee = web3.utils.toBN(creationFee);
            console.log(selectedAccount, saleTokenAddress, baseTokenAddress, activeInfo, params, listVestingPeriod, listVestingPercent);
            let saleGeneratorContract = new web3.eth.Contract(ABI_SALE_GENERATOR, saleGeneratorAddress);
            let approve = await saleGeneratorContract.methods.createSale(selectedAccount, saleTokenAddress, baseTokenAddress, activeInfo, params, listVestingPeriod, listVestingPercent).send({
                from: selectedAccount,
                value: creationFee
            }).on('transactionHash', function (hash) {
                alert("Please wait a minute to complete your transaction!");
            }).on('confirmation', function (confirmationNumber, receipt) {
            }).on('receipt', function (receipt) {
            }).on('error', function (error, receipt) {
                alert("Error");
                console.log("error", error, receipt);
            });

            $("#global_loading").hide();
            if (approve.status) {
                alert("Success");
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

    function getConfigAddress(type = 'sale_generator') {
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
                    $('#sale-generator-address').val(data.data.address);
                } else {
                    $('#sale-generator-address').val('');
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

        $('#sale_token').on('change', function () {

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
                        $('.sale_token_symbol').text(data.data.symbol);
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

        $('#btn_create_sale').on('click', createSale);

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