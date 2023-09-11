<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Airdrop Setting </h4>
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
                                <a href="/registry/airdrop_setting/{{ key }}" class="btn btn-{{ platform == key ? 'info' : 'outline-dark' }}">{{ item }}</a>
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

                    <h5 class="header-title mb-4 mt-0">Airdrop Setting Address</h5>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for=airdrop-setting-address>Airdrop setting address</label>
                                <a id="airdrop-setting-address" href="{{ helper.getLinkAddress(airdropSettingAddress, platform, network ) }}" target="_blank" class="form-control">{{ airdropSettingAddress }}</a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

    {% include 'partials/airdrop_setting_main_info.volt' %}
    <!-- end row -->

</div>

<input type="hidden" data-platform="{{ platform }}" data-network="{{ network }}" id="setting_platform_network">

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    async function updateSettingMain() {
        try {
            await checkConnect();

            let feeAmount = parseFloat($('#fee-amount').val() || 0);
            let feeAddress = $('#fee-address').val();

            feeAmount = (new BigNumber(feeAmount)).mul((new BigNumber(10)).pow(18)).toString();

            if (!web3.utils.isAddress(feeAddress)) {
                toastr["error"]("Invalid Fee Address");
            }

            let airdropSettingAddress = $('#airdrop-setting-address').text();
            let tokenSettingContract = new web3.eth.Contract(ABI_AIRDROP_SETTING, airdropSettingAddress);
            let result = await tokenSettingContract.methods.setSettingInfo(
                feeAmount,
                feeAddress
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

        $('.btn-update-airdrop-setting').on('click', updateSettingMain);
    });

</script>