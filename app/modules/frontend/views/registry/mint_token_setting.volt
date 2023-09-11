<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Token Setting </h4>
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
                                <a href="/registry/mint_token_setting/{{ key }}" class="btn btn-{{ platform == key ? 'info' : 'outline-dark' }}">{{ item }}</a>
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

                    <h5 class="header-title mb-4 mt-0">Token Setting Address</h5>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for=token-setting-address>Token setting address</label>
                                <a id="token-setting-address" href="{{ helper.getLinkAddress(mintTokenSettingAddress, platform, network ) }}" target="_blank" class="form-control">{{ mintTokenSettingAddress }}</a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

    {% include 'partials/mint_token_setting_main_info.volt' %}
    <!-- end row -->

</div>

<input type="hidden" data-platform="{{ platform }}" data-network="{{ network }}" id="setting_platform_network">

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    async function updateSettingMain() {
        try {
            await checkConnect();

            let creationFee = parseFloat($('#creation-fee').val() || 0);
            let totalSupplyFeePercent = parseFloat($('#total-supply-fee-percent').val() || 0);
            let tokenFeeAddress = $('#token-fee-address').val();

            creationFee = (new BigNumber(creationFee)).mul((new BigNumber(10)).pow(18));
            creationFee = web3.utils.toBN(creationFee).toString();
            totalSupplyFeePercent = parseInt(totalSupplyFeePercent * 10);

            if (!web3.utils.isAddress(tokenFeeAddress)) {
                toastr["error"]("Invalid Fee Address");
            }

            let mintTokenSettingAddress = $('#token-setting-address').text();
            let mintTokenSettingContract = new web3.eth.Contract(ABI_MINT_TOKEN_SETTING, mintTokenSettingAddress);
            let result = await mintTokenSettingContract.methods.setSettingInfo(
                creationFee,
                totalSupplyFeePercent,
                tokenFeeAddress
            ).send({
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
            console.log(ex);
        }
    }

    $(document).ready(function () {

        $('.btn-update-setting-main').on('click', updateSettingMain);
    });

</script>