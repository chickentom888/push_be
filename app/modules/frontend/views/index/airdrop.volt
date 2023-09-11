<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Airdrop</h4>
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

                    <h5 class="header-title mb-4 mt-0">Airdrop</h5>

                    <div class="general-label">

                        <div class="row">

                            <div class="col-sm-12">
                                <h5>Thông tin</h5>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="fee-amount">Airdrop Fee</label>
                                    <input class="form-control" type="text" value="{{ setting['fee_amount'] ? setting['fee_amount'] : 0.1 }}" id="fee-amount">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="token-type">Token Type</label>
                                    <select name="" class="form-control" id="token-type">
                                        <option value="main">Main</option>
                                        <option value="erc20">ERC20 Token</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row token-info">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="token-address">Token address</label>
                                    <input class="form-control" type="text" name="" value="" id="token-address">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="token-name">Token name</label>
                                    <input class="form-control" type="text" name="" value="" id="token-name" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="token-symbol">Token symbol</label>
                                    <input class="form-control" type="text" name="" value="" id="token-symbol" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="token-decimals">Token decimals</label>
                                    <input class="form-control" type="text" value="" id="token-decimals" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="amount-type">Amount Type</label>
                                    <select name="" class="form-control" id="amount-type">
                                        <option value="same">Same amount</option>
                                        <option value="custom">Custom amount</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-sm-6 token-amount-region">
                                <div class="form-group">
                                    <label for="token-amount">Token amount</label>
                                    <input class="form-control" type="text" value="" id="token-amount">
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="list-address">List address</label>
                                    <textarea class="form-control" type="text" id="list-address" rows="15"></textarea>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="total-token-amount">Total token amount</label>
                                    <input class="form-control" type="text" value="" id="total-token-amount" readonly>
                                </div>
                            </div>
                        </div>

                    </div>

                    <button type="button" class="btn btn-success" id="btn-approve">Approve</button>
                    <button type="button" class="btn btn-success" id="btn-airdrop">Airdrop</button>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    function onChangeTokenType() {
        let tokenType = $('#token-type').val();
        if (tokenType === 'main') {
            $('.token-info, #btn-approve').hide();
        } else {
            $('.token-info, #btn-approve').show();
        }
    }

    function onChangeAmountType() {
        let amountType = $('#amount-type').val();
        if (amountType === 'same') {
            $('.token-amount-region').show();
        } else {
            $('.token-amount-region').hide();
        }
    }

    function calculateAddress() {
        let amountType = $('#amount-type').val();
        let tokenType = $('#token-type').val();
        let listAddress = [];
        let listAmount = [];
        let amount = parseFloat($('#token-amount').val() || 0);
        let totalTokenAmount = 0;
        let tokenDecimals;
        if (tokenType === 'main') {
            tokenDecimals = 18;
        } else {
            tokenDecimals = parseInt($('#token-decimals').val() || 0);
        }

        let totalAmountDecimals = new BigNumber(0);

        $.each($('#list-address').val().split(/\n/), function (i, line) {
            if (amountType === 'custom') {
                let addressAmount = line.split(';');
                let address = addressAmount[0].trim();
                let amount = parseFloat(parseFloat(addressAmount[1] || 0).toFixed(6));
                let amountDecimals = (new BigNumber(amount)).mul((new BigNumber(10)).pow(tokenDecimals));
                totalAmountDecimals = totalAmountDecimals.plus(amountDecimals);
                amountDecimals = amountDecimals.toString();
                if (address.length && web3.utils.isAddress(address) && amountDecimals >= 0) {
                    listAddress.push(address);
                    listAmount.push(amountDecimals);
                    totalTokenAmount += amount;
                }
            } else {
                line = line.trim();
                if (line.length && web3.utils.isAddress(line)) {
                    listAddress.push(line);
                    let amountDecimals = (new BigNumber(amount)).mul((new BigNumber(10)).pow(tokenDecimals));
                    totalAmountDecimals = totalAmountDecimals.plus(amountDecimals);
                    amountDecimals = amountDecimals.toString();
                    listAmount.push(amountDecimals);
                    totalTokenAmount += amount;
                }
            }
        });
        if (!listAddress.length) {
            return false;
        }

        $('#total-token-amount').val(totalTokenAmount);
        return [listAddress, listAmount, totalAmountDecimals.toString()];
    }

    async function airdrop() {
        try {
            let addressAmount = calculateAddress();
            let listAddress = addressAmount[0];
            let listAmount = addressAmount[1];
            let totalAmountDecimals = new BigNumber(addressAmount[2]);
            if (listAddress <= 0) {
                toastr["error"]("Invalid list address");
            }

            let feeAmount = parseFloat($('#fee-amount').val() || 0);
            feeAmount = (new BigNumber(feeAmount)).mul((new BigNumber(10)).pow(18));
            totalAmountDecimals = totalAmountDecimals.plus(feeAmount).toString();
            let airdropContract = new web3.eth.Contract(ABI_AIRDROP_CONTRACT, AIRDROP_ADDRESS);
            let tokenType = $('#token-type').val();
            let result;
            if (tokenType === 'main') {
                result = await airdropContract.methods.airdropMain(listAddress, listAmount).send({
                    from: selectedAccount,
                    value: totalAmountDecimals
                }).on('transactionHash', function (hash) {
                    toastr["success"]("Please wait a minute to complete your transaction!");
                }).on('confirmation', function (confirmationNumber, receipt) {
                }).on('receipt', function (receipt) {
                }).on('error', function (error, receipt) {
                    toastr["error"]("Error");
                    console.log("error", error, receipt);
                });
            } else {
                let tokenAddress = $('#token-address').val();
                result = await airdropContract.methods.airdropToken(tokenAddress, listAddress, listAmount).send({
                    from: selectedAccount,
                    value: feeAmount
                }).on('transactionHash', function (hash) {
                    toastr["success"]("Please wait a minute to complete your transaction!");
                }).on('confirmation', function (confirmationNumber, receipt) {
                }).on('receipt', function (receipt) {
                }).on('error', function (error, receipt) {
                    toastr["error"]("Error");
                    console.log("error", error, receipt);
                });
            }

            $("#global_loading").hide();
            if (result.status) {
                toastr["success"]("Success");
            }
        } catch (ex) {
            console.log(ex);
        }
    }

    async function approveToken() {
        await checkConnect();
        let totalTokenAmount = parseFloat($('#total-token-amount').val() || 0);
        let saleTokenDecimals = parseInt($('#token-decimals').val());
        let tokenAddress = $('#token-address').val();
        if (totalTokenAmount < 0) {
            toastr["error"]("Invalid airdrop token amount");
            return;
        }
        totalTokenAmount = totalTokenAmount * 1000000;
        totalTokenAmount = (new BigNumber(totalTokenAmount)).mul((new BigNumber(10)).pow(saleTokenDecimals)).toString();
        totalTokenAmount = web3.utils.randomHex(32);
        console.log(totalTokenAmount);
        let tokenContract = new web3.eth.Contract(ABI_TOKEN, tokenAddress);
        let approve = await tokenContract.methods.approve(AIRDROP_ADDRESS, totalTokenAmount).send({
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

    $(document).ready(function () {

        let isSearching = 0;

        onChangeTokenType();

        onChangeAmountType();

        $('#token-type').on('change', onChangeTokenType);

        $('#amount-type').on('change', onChangeAmountType);

        $('#btn-approve').on('click', approveToken);

        $('#list-address, #token-amount').on('change', calculateAddress);

        $('#token-address').on('change', function () {

            let contractAddress = $(this).val();
            if (contractAddress.length < 42) {
                $('#token-name, #token-decimals, #token-symbol').val('');
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
                        $('#token-name').val(data.data.name);
                        $('#token-symbol').val(data.data.symbol);
                        $('#token-decimals').val(data.data.decimals);
                    } else {
                        $('#token-name, #token-decimals, #token-symbol').val('');
                    }
                    isSearching = 0;
                },
                done: function () {
                    isSearching = 0;
                }
            });
        });

        $('#btn-airdrop').on('click', airdrop);

    });

</script>