<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Create Token </h4>
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

                    <h5 class="header-title mb-4 mt-0">Create Token</h5>

                    <div class="general-label">

                        <div class="row">

                            <div class="col-sm-12">
                                <h5>Thông tin</h5>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="mint-generator-address">Mint Generator</label>
                                    <input class="form-control" type="text" value="" id="mint-generator-address" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="creation-fee">Creation Fee</label>
                                    <input class="form-control" type="text" value="{{ setting['creation_fee'] }}" id="creation-fee" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="total-supply-fee-percent">Total Supply Fee Percent</label>
                                    <input class="form-control" type="text" value="{{ setting['total_supply_fee_percent'] }}" id="total-supply-fee-percent" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">

                                <div class="form-group">
                                    <label for="token-name">Token name</label>
                                    <input class="form-control" type="text" name="" value="" id="token-name" required>
                                </div>

                            </div>

                            <div class="col-sm-6">

                                <div class="form-group">
                                    <label for="token-symbol">Token symbol</label>
                                    <input class="form-control" type="text" name="" value="" id="token-symbol" required>
                                </div>

                            </div>

                            <div class="col-sm-6">

                                <div class="form-group">
                                    <label for="total-supply">Total supply</label>
                                    <input class="form-control" type="text" value="" id="total-supply" required>
                                </div>

                            </div>
                            <div class="col-sm-6">

                                <div class="form-group">
                                    <label for="token-decimals">Token decimals</label>
                                    <input class="form-control" type="text" value="" id="token-decimals" required>
                                </div>

                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="total-supply-raw">Total supply raw</label>
                                    <input class="form-control" type="text" value="" id="total-supply-raw" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="total-supply-fee-amount-raw">Token fee amount raw</label>
                                    <input class="form-control" type="text" value="" id="total-supply-fee-amount-raw" readonly>
                                </div>
                            </div>

                        </div>

                        <button type="button" class="btn btn-success" id="btn-create-token">Create</button>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    function calculateTotalSupplyRaw() {
        let totalSupply = parseFloat($('#total-supply').val() || 0);
        let tokenDecimals = parseInt($('#token-decimals').val() || 0);

        totalSupply = (new BigNumber(totalSupply)).mul((new BigNumber(10)).pow(tokenDecimals));
        totalSupply = web3.utils.toBN(totalSupply).toString();
        let totalSupplyFeePercent = parseFloat($('#total-supply-fee-percent').val() || 0);
        let totalSupplyFeeAmount = (new BigNumber(totalSupply)).div(new BigNumber(100)).mul((new BigNumber(totalSupplyFeePercent)));
        $('#total-supply-fee-amount-raw').val(totalSupplyFeeAmount.toString());
        $('#total-supply-raw').val(totalSupply);
    }

    async function createToken() {
        try {

            let mintGeneratorAddress = $('mint-generator-address').val();
            if (!mintGeneratorAddress.length) {
                toastr["error"]("Mint Generator Not Found");
                return;
            }

            let totalSupply = parseFloat($('#total-supply').val() || 0);
            let tokenDecimals = parseInt($('#token-decimals').val());

            totalSupply = (new BigNumber(totalSupply)).mul((new BigNumber(10)).pow(tokenDecimals));
            totalSupply = web3.utils.toBN(totalSupply).toString();

            let tokenName = $('#token-name').val();
            let tokenSymbol = $('#token-symbol').val();

            let creationFee = parseFloat($('#creation-fee').val() || 0);
            creationFee = (new BigNumber(creationFee)).mul((new BigNumber(10)).pow(18));
            creationFee = web3.utils.toBN(creationFee);
            let mintTokenGeneratorContract = new web3.eth.Contract(ABI_MINT_TOKEN_GENERATOR, mintGeneratorAddress);
            let result = await mintTokenGeneratorContract.methods.createToken(tokenName, tokenSymbol, tokenDecimals, totalSupply).send({
                from: selectedAccount,
                value: creationFee
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
                alert("Success");
            }
        } catch (ex) {
            console.log(ex);
        }
    }

    function getConfigAddress(type = 'mint_token_generator') {
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
                    $('#mint-generator-address').val(data.data.address);
                } else {
                    $('#mint-generator-address').val('');
                }
            },
            done: function () {
            }
        });
    }

    $(document).ready(function () {

        setTimeout(function () {
            getConfigAddress();
        }, 3000);

        $('#btn-create-token').on('click', createToken);

        $('#token-decimals, #total-supply').on('change', calculateTotalSupplyRaw);

    });

</script>