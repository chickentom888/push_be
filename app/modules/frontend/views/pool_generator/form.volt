<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Pool Generator</h4>
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

                    <h5 class="header-title mb-4 mt-0">Pool Generator</h5>

                    <a href="/pool_generator/" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>

                                    <div class="form-group">
                                        <label for="platform">Platform</label>
                                        <select name="platform" class="form-control" id="platform">
                                            {% for key, item in listPlatform %}
                                                <option value="{{ key }}">{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="network">Network</label>
                                        <select name="network" class="form-control" id="network">
                                            {% for key, item in listNetwork %}
                                                <option value="{{ key }}">{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="pool-factory-address">Pool Factory Address</label>
                                        <input class="form-control" type="text" name="factory_address" value="" id="pool-factory-address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="pool-generator-address">Pool Generator Address</label>
                                        <input class="form-control" type="text" name="pool_generator_address" value="" id="pool-generator-address" required>
                                    </div>

                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-add-pool-generator">Add</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    function getPoolFactoryAddress() {
        let platform = $('#platform').val();
        let network = $('#network').val();
        $.ajax({
            url: '/exchange_platform/get_factory_address',
            type: "POST",
            async: true,
            data: {
                network: network,
                platform: platform,
                type: 'pool_factory'
            },
            success: function (data) {
                if (data.data) {
                    $('#pool-factory-address').val(data.data.factory_address);
                } else {
                    $('#pool-factory-address').val('');
                }
            },
            done: function () {
            }
        });
    }

    $(document).ready(function () {

        $('.btn-add-pool-generator').on('click', async function () {
            let poolGeneratorAddress = $('#pool-generator-address').val();
            let poolFactoryAddress = $('#pool-factory-address').val();

            try {
                await checkConnect();
                let poolFactoryContract = new web3.eth.Contract(ABI_POOL_FACTORY, poolFactoryAddress);
                let result = await poolFactoryContract.methods.adminAllowPoolGenerator(poolGeneratorAddress, true).send({
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

        });

        getPoolFactoryAddress();

        $('#platform, #network').on('change', getPoolFactoryAddress);
    });
</script>