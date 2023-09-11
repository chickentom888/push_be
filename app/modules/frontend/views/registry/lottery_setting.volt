<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Pool Setting </h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    {% include 'partials/wallet_connect.volt' %}

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">List Platform</h5>
                    <div class="row">
                        <div class="col-sm-12">
                            {% for key,item in listPlatform %}
                                <a href="/registry/pool_setting/{{ key }}" class="btn btn-{{ platform == key ? 'info' : 'outline-dark' }}">{{ item }}</a>
                            {% endfor %}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Lottery Address</h5>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for=lottery-setting-address>Lottery setting address</label>
                                <a id="lottery-setting-address" href="{{ helper.getLinkAddress(lotterySettingAddress, platform, network ) }}" target="_blank" class="form-control">{{ lotterySettingAddress }}</a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">Payment Token</h5>

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Payment Token</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="payment-token-address">Address</label>
                                <input class="form-control" type="text" value="{{ setting['payment_token']['token_address'] }}" id="payment-token-address" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="payment-token-name">Name</label>
                                <input class="form-control" type="text" value="{{ setting['payment_token']['token_name'] }}" id="payment-token-name" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="payment-token-symbol">Symbol</label>
                                <input class="form-control" type="text" value="{{ setting['payment_token']['token_symbol'] }}" id="payment-token-symbol" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="payment-token-decimals">Address</label>
                                <input class="form-control" type="text" value="{{ setting['payment_token']['token_decimals'] }}" id="payment-token-decimals" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="payment-token-price">Price</label>
                                <input class="form-control" type="text" value="{{ setting['payment_token']['token_price'] }}" id="payment-token-price" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">Random Generator</h5>

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Random Generator Address</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="random-generator-address">Address</label>
                                <input class="form-control" type="text" value="{{ setting['random_generator'] }}" id="random-generator-address">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-update-random-generator btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">Config Address</h5>

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Config address</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="operator-address">Operator address</label>
                                <input class="form-control" type="text" value="{{ setting['operator_address'] }}" id="operator-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="treasury-address">Treasury address</label>
                                <input class="form-control" type="text" value="{{ setting['treasury_address'] }}" id="treasury-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="injector-address">Injector address</label>
                                <input class="form-control" type="text" value="{{ setting['injector_address'] }}" id="injector-address">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-update-config-address btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">Max number ticket per buy</h5>

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Max number ticket per buy</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="max-number-ticket-per-buy">Max number</label>
                                <input class="form-control" type="text" value="{{ setting['max_number_tickets_per_buy_or_claim'] }}" id="max-number-ticket-per-buy">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-update-max-number-ticket-per-buy btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">Min And Max Ticket Price</h5>

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Min And Max Ticket Price</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="min-price-ticket">Min Price</label>
                                <input class="form-control" type="text" value="{{ setting['min_price_ticket'] }}" id="min-price-ticket">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="max-price-ticket">Max Price</label>
                                <input class="form-control" type="text" value="{{ setting['max_price_ticket'] }}" id="max-price-ticket">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-update-min-and-max-price-ticket btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">Inject Fund</h5>

                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Inject fund</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="lottery-contract-id">Lottery ID</label>
                                <input class="form-control" type="text" value="" id="lottery-contract-id">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="inject-amount">Amount</label>
                                <input class="form-control" type="text" value="" id="inject-amount">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-approve-inject-fund btn-interact-sc">Approve</button>
                                <button type="button" class="btn btn-success btn-inject-fund btn-interact-sc">Inject</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <form action="" method="post">
                        <h5 class="header-title mb-4 mt-0">Start Lottery Config</h5>

                        <div class="row">
                            <div class="col-sm-12">
                                <h5>Start Lottery Config</h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="discount-divisor">Discount divisor</label>
                                    <input class="form-control" type="text" value="{{ setting['discount_divisor'] }}" id="discount-divisor" name="discount_divisor" required>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="rewards-breakdown">Reward breakdown</label>
                                    <input class="form-control" type="text" value="{{ setting['rewards_breakdown'] }}" id="rewards-breakdown" name="rewards_breakdown" required>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="treasury-fee">Treasury fee (Multiply to 100)</label>
                                    <input class="form-control" type="text" value="{{ setting['treasury_fee'] }}" id="treasury-fee" name="treasury_fee" required>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="price-ticket">Price Ticket</label>
                                    <input class="form-control" type="text" value="{{ setting['price_ticket'] }}" id="price-ticket" name="price_ticket" required>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success btn-interact-sc">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- end row -->

</div>
<input type="hidden" id="payment-token-decimals" value="{{ setting['payment_token']['token_decimals'] }}">
<input type="hidden" id="payment-token-address" value="{{ setting['payment_token']['token_address'] }}">
<input type="hidden" data-platform="{{ platform }}" data-network="{{ network }}" id="setting_platform_network">

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    async function updateRandomGenerator() {
        try {
            await checkConnect();
            let lotterySettingAddress = $('#lottery-setting-address').text();
            let randomGeneratorAddress = $('#random-generator-address').val();

            if (!web3.utils.isAddress(randomGeneratorAddress)) {
                toastr["error"]("Invalid random generator address");
                return;
            }

            let lotterySettingContract = new web3.eth.Contract(ABI_LOTTERY, lotterySettingAddress);
            let result = await lotterySettingContract.methods.changeRandomGenerator(randomGeneratorAddress).send({
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
            if (result.status) {
                toastr["success"]("Success");
            }
        } catch (ex) {
            toastr["error"](ex.message);
        }
    }

    async function updateConfigAddress() {
        try {
            await checkConnect();
            let lotterySettingAddress = $('#lottery-setting-address').text();
            let operatorAddress = $('#operator-address').val();
            if (!web3.utils.isAddress(operatorAddress)) {
                toastr["error"]("Invalid operator address");
                return;
            }
            let treasuryAddress = $('#treasury-address').val();
            if (!web3.utils.isAddress(treasuryAddress)) {
                toastr["error"]("Invalid treasury address");
                return;
            }
            let injectorAddress = $('#injector-address').val();
            if (!web3.utils.isAddress(injectorAddress)) {
                toastr["error"]("Invalid injector address");
                return;
            }

            let lotterySettingContract = new web3.eth.Contract(ABI_LOTTERY, lotterySettingAddress);
            let result = await lotterySettingContract.methods.setAddress(operatorAddress, treasuryAddress, injectorAddress).send({
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
            if (result.status) {
                toastr["success"]("Success");
            }
        } catch (ex) {
            toastr["error"](ex.message);
        }
    }

    async function updateMaxNumberTicketPerBuy() {
        try {
            await checkConnect();
            let lotterySettingAddress = $('#lottery-setting-address').text();
            let maxNumberTicketPerBuy = parseInt($('#max-number-ticket-per-buy').val());

            if (maxNumberTicketPerBuy <= 0) {
                toastr["error"]("Invalid max number ticket per buy");
                return;
            }

            let lotterySettingContract = new web3.eth.Contract(ABI_LOTTERY, lotterySettingAddress);
            let result = await lotterySettingContract.methods.setMaxNumberTicketsPerBuy(maxNumberTicketPerBuy).send({
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
            if (result.status) {
                toastr["success"]("Success");
            }
        } catch (ex) {
            toastr["error"](ex.message);
        }
    }

    async function updateMinAndMaxPriceTicket() {
        try {
            await checkConnect();
            let lotterySettingAddress = $('#lottery-setting-address').text();
            let minPriceTicket = $('#min-price-ticket').val();
            let maxPriceTicket = $('#max-price-ticket').val();
            let paymentTokenDecimals = $('#payment-token-decimals').val();

            minPriceTicket = (new BigNumber(minPriceTicket)).mul((new BigNumber(10)).pow(paymentTokenDecimals));
            minPriceTicket = web3.utils.toBN(minPriceTicket).toString();

            maxPriceTicket = (new BigNumber(maxPriceTicket)).mul((new BigNumber(10)).pow(paymentTokenDecimals));
            maxPriceTicket = web3.utils.toBN(maxPriceTicket).toString();

            let lotterySettingContract = new web3.eth.Contract(ABI_LOTTERY, lotterySettingAddress);
            let result = await lotterySettingContract.methods.setMinAndMaxTicketPrice(minPriceTicket, maxPriceTicket).send({
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
            if (result.status) {
                toastr["success"]("Success");
            }
        } catch (ex) {
            toastr["error"](ex.message);
        }
    }

    async function injectFund() {
        try {
            await checkConnect();
            let lotterySettingAddress = $('#lottery-setting-address').text();

            let lotteryContractId = parseInt($('#lottery-contract-id').val());
            let injectAmount = parseFloat($('#inject-amount').val()) || 0;
            let paymentTokenDecimals = $('#payment-token-decimals').val();
            if (injectAmount <= 0) {
                toastr["error"]("Invalid inject amount");
                return;
            }

            injectAmount = (new BigNumber(injectAmount)).mul((new BigNumber(10)).pow(paymentTokenDecimals));
            injectAmount = web3.utils.toBN(injectAmount).toString();

            let lotterySettingContract = new web3.eth.Contract(ABI_LOTTERY, lotterySettingAddress);
            let result = await lotterySettingContract.methods.injectFunds(lotteryContractId, injectAmount).send({
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
            if (result.status) {
                toastr["success"]("Success");
            }
        } catch (ex) {
            toastr["error"](ex.message);
        }
    }

    async function approveInjectFund() {
        try {
            await checkConnect();
            let lotterySettingAddress = $('#lottery-setting-address').text();
            let tokenAddress = $('#payment-token-address').val();
            let amountToken = web3.utils.randomHex(32);
            let tokenContract = new web3.eth.Contract(ABI_TOKEN, tokenAddress);
            let result = await tokenContract.methods.approve(lotterySettingAddress, amountToken).send({
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
            if (result.status) {
                toastr["success"]("Success");
            }
        } catch (ex) {
            toastr["error"](ex.message);
        }
    }

    $(document).ready(function () {

        $('.btn-update-random-generator').on('click', updateRandomGenerator);

        $('.btn-update-config-address').on('click', updateConfigAddress);

        $('.btn-update-max-number-ticket-per-buy').on('click', updateMaxNumberTicketPerBuy);

        $('.btn-update-min-and-max-price-ticket').on('click', updateMinAndMaxPriceTicket);

        $('.btn-inject-fund').on('click', injectFund);

        $('.btn-approve-inject-fund').on('click', approveInjectFund);

    });

</script>