<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Swap</h4>
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

                    <h5 class="header-title mb-4 mt-0">Swap</h5>

                    <div class="general-label">

                        <div class="row">

                            <div class="col-sm-12">
                                <h5>Thông tin</h5>
                            </div>
                        </div>

                        <div class="row sell-token-info">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="sell-token-address">Sell token address</label>
                                    <input class="form-control" type="text" name="" value="" id="sell-token-address">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="sell-token-name">Sell token name</label>
                                    <input class="form-control" type="text" name="" value="" id="sell-token-name" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="sell-token-symbol">Sell Token symbol</label>
                                    <input class="form-control" type="text" name="" value="" id="sell-token-symbol" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="sell-token-decimals">Sell Token decimals</label>
                                    <input class="form-control" type="text" value="" id="sell-token-decimals" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="sell-token-balance">Sell Token Balance</label>
                                    <input class="form-control" type="text" value="" id="sell-token-balance" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row buy-token-info">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="buy-token-address">Buy token address</label>
                                    <input class="form-control" type="text" name="" value="" id="buy-token-address">
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="buy-token-name">Buy token name</label>
                                    <input class="form-control" type="text" name="" value="" id="buy-token-name" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="buy-token-symbol">Buy Token symbol</label>
                                    <input class="form-control" type="text" name="" value="" id="buy-token-symbol" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="buy-token-decimals">Buy Token decimals</label>
                                    <input class="form-control" type="text" value="" id="buy-token-decimals" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="buy-token-balance">Buy Token Balance</label>
                                    <input class="form-control" type="text" value="" id="buy-token-balance" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-sm-6 token-amount-region">
                                <div class="form-group">
                                    <label for="sell-token-amount">Sell Token amount</label>
                                    <input class="form-control" type="text" value="" id="sell-token-amount">
                                </div>
                            </div>

                            <div class="col-sm-6 token-amount-region">
                                <div class="form-group">
                                    <label for="buy-token-amount">Buy Token amount</label>
                                    <input class="form-control" type="text" value="" id="buy-token-amount">
                                </div>
                            </div>
                        </div>

                    </div>

                    <button type="button" class="btn btn-success" id="btn-get-quote">Get Quote</button>
                    <button type="button" class="btn btn-success" id="btn-approve">Approve</button>
                    <button type="button" class="btn btn-success" id="btn-swap">Swap</button>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    let swapInfo = {
        spenderAddress: '',
        toAddress: '',
        input: '',
        output: '',
        adjustedOutput: '',
        data: '',
    };

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

    async function swap() {
        try {
            let gasPrice = await web3.eth.getGasPrice();
            let nonce = await web3.eth.getTransactionCount(selectedAccount, "pending");
            gasPrice = web3.utils.toHex(gasPrice);
            nonce = web3.utils.toHex(nonce);
            let gas = web3.utils.toHex(500000);
            let chainId = await web3.eth.getChainId();
            chainId = web3.utils.toHex(chainId);

            let sellTokenAddress = $('#sell-token-address').val().trim().toLowerCase();
            let amount = 0;
            if (sellTokenAddress === 'bnb') {
                let decimals = 18;
                amount = parseFloat($('#sell-token-amount').val()) || 0;
                if (amount <= 0) {
                    alert('Invalid sell amount');
                    return;
                }
                amount = parseFloat(amount.toFixed(decimals));
                amount = (new BigNumber(amount)).times((new BigNumber(10)).pow(decimals)).toFixed();
            }

            amount = web3.utils.toHex(amount);

            let transactionParameters = {
                nonce: nonce,
                gasPrice: gasPrice,
                gas: gas,
                to: swapInfo.toAddress,
                from: selectedAccount,
                value: amount,
                data: swapInfo.data,
                chainId: chainId
            };

            const txHash = await window.ethereum.request({
                method: 'eth_sendTransaction',
                params: [transactionParameters],
            });
            console.log(txHash);
        } catch (ex) {
            console.log(ex);
        }
    }

    async function approveToken() {
        await checkConnect();
        let tokenAddress = web3.utils.toChecksumAddress($('#sell-token-address').val().trim());

        let totalTokenAmount = web3.utils.randomHex(32);
        let tokenContract = new web3.eth.Contract(ABI_TOKEN, tokenAddress);
        let approve = await tokenContract.methods.approve(swapInfo.spenderAddress, totalTokenAmount).send({
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

    function getQuote() {
        let inputType = '';
        let sellTokenAddress = $('#sell-token-address').val();
        let sellTokenDecimals = parseInt($('#sell-token-decimals').val());
        let sellTokenAmount = parseFloat($('#sell-token-amount').val()) || 0;
        if (sellTokenAddress < 42) {
            sellTokenAddress = 'bnb';
            sellTokenDecimals = 18;
        }

        let buyTokenAddress = $('#buy-token-address').val();
        let buyTokenDecimals = parseInt($('#buy-token-decimals').val());
        let buyTokenAmount = parseFloat($('#buy-token-amount').val()) || 0;
        if (buyTokenAddress < 42) {
            buyTokenAddress = 'bnb';
            buyTokenDecimals = 18;
        }


        if (sellTokenAmount > 0) {
            sellTokenAmount = parseFloat(sellTokenAmount.toFixed(buyTokenDecimals));
            sellTokenAmount = (new BigNumber(sellTokenAmount)).times((new BigNumber(10)).pow(sellTokenDecimals)).toFixed();
            buyTokenAmount = 0;
            inputType = 'sell';
        }


        if (buyTokenAmount > 0) {
            buyTokenAmount = parseFloat(buyTokenAmount.toFixed(buyTokenDecimals));
            buyTokenAmount = (new BigNumber(buyTokenAmount)).times((new BigNumber(10)).pow(buyTokenDecimals)).toFixed();
            sellTokenAmount = 0;
            inputType = 'buy';
        }

        $.ajax({
            url: '/index/getQuote',
            type: "GET",
            async: true,
            data: {
                sell_token: sellTokenAddress,
                sell_amount: sellTokenAmount,
                buy_token: buyTokenAddress,
                buy_amount: buyTokenAmount,
            },
            success: function (data) {

                if (data.data) {
                    swapInfo.spenderAddress = web3.utils.toChecksumAddress(data.data.spender_address);
                    swapInfo.toAddress = web3.utils.toChecksumAddress(data.data.to_address);
                    swapInfo.input = data.data.input;
                    swapInfo.output = data.data.output;
                    swapInfo.adjustedOutput = data.data.adjusted_output;
                    swapInfo.data = data.data.data;
                    let outputAmount;
                    if (inputType === 'sell') {
                        outputAmount = (new BigNumber(swapInfo.output)).div((new BigNumber(10)).pow(buyTokenDecimals)).toString();
                        $('#buy-token-amount').val(outputAmount)
                    } else {
                        outputAmount = (new BigNumber(swapInfo.output)).div((new BigNumber(10)).pow(sellTokenDecimals)).toString();
                        $('#sell-token-amount').val(outputAmount)
                    }

                } else {
                    swapInfo.spenderAddress = '';
                    swapInfo.toAddress = '';
                    swapInfo.input = '';
                    swapInfo.output = '';
                    swapInfo.adjustedOutput = '';
                    swapInfo.data = '';
                    alert('Rate not found');
                }
                console.log(swapInfo);

            },
            done: function () {

            }
        });
    }

    $(document).ready(function () {

        let isSearching = 0;

        $('#btn-approve').on('click', approveToken);

        $('#sell-token-address').on('change', async function () {

            let contractAddress = $(this).val();
            if (contractAddress.length < 42) {
                if (contractAddress.toLowerCase().trim() === 'bnb') {
                    let userBalance = await web3.eth.getBalance(selectedAccount);
                    let decimals = 18;
                    userBalance = (new BigNumber(userBalance)).div((new BigNumber(10)).pow(decimals)).toFixed()
                    $('#sell-token-name').val('BNB');
                    $('#sell-token-symbol').val('BNB');
                    $('#sell-token-decimals').val(decimals);
                    $('#sell-token-balance').val(userBalance);
                }
                return;
            }

            /*if (isSearching === 1) {
                return;
            }*/

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
                        $('#sell-token-name').val(data.data.name);
                        $('#sell-token-symbol').val(data.data.symbol);
                        $('#sell-token-decimals').val(data.data.decimals);
                        $('#sell-token-balance').val(data.data.user_balance);
                    } else {
                        $('#sell-token-name, #sell-token-decimals, #sell-token-symbol, #sell-token-balance').val('');
                    }
                    isSearching = 0;
                },
                done: function () {
                    isSearching = 0;
                }
            });
        });

        $('#buy-token-address').on('change', async function () {

            let contractAddress = $(this).val();
            if (contractAddress.length < 42) {
                if (contractAddress.toLowerCase().trim() === 'bnb') {
                    let userBalance = await web3.eth.getBalance(selectedAccount);
                    let decimals = 18;
                    userBalance = (new BigNumber(userBalance)).div((new BigNumber(10)).pow(decimals)).toFixed()
                    $('#buy-token-name').val('BNB');
                    $('#buy-token-symbol').val('BNB');
                    $('#buy-token-decimals').val(decimals);
                    $('#buy-token-balance').val(userBalance);
                }
                return;
            }

            /*if (isSearching === 1) {
                return;
            }*/

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
                        $('#buy-token-name').val(data.data.name);
                        $('#buy-token-symbol').val(data.data.symbol);
                        $('#buy-token-decimals').val(data.data.decimals);
                        $('#buy-token-balance').val(data.data.user_balance);
                    } else {
                        $('#buy-token-name, #buy-token-decimals, #buy-token-symbol, #buy-token-balance').val('');
                    }
                    isSearching = 0;
                },
                done: function () {
                    isSearching = 0;
                }
            });
        });

        $('#btn-swap').on('click', swap);

        $('#btn-get-quote').on('click', getQuote);

        $('#sell-token-amount').on('change', function () {
            $('#buy-token-amount').val('');
            getQuote();
        });

        $('#buy-token-amount').on('change', function () {
            $('#sell-token-amount').val('');
            getQuote();
        });

    });

</script>