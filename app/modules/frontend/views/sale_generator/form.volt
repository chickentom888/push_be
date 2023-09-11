<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Sale Generator</h4>
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

                    <h5 class="header-title mb-4 mt-0">Sale Generator</h5>

                    <a href="/sale_generator/" class="btn btn-info">Back</a>

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
                                        <label for="sale-factory-address">Sale Factory Address</label>
                                        <input class="form-control" type="text" name="factory_address" value="" id="sale-factory-address" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="sale-generator-address">Sale Generator Address</label>
                                        <input class="form-control" type="text" name="sale_generator_address" value="" id="sale-generator-address" required>
                                    </div>

                                </div>
                            </div>
                            <button type="button" class="btn btn-primary btn-add-sale-generator">Add</button>
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

    function getSaleFactoryAddress() {
        let platform = $('#platform').val();
        let network = $('#network').val();
        $.ajax({
            url: '/exchange_platform/get_factory_address',
            type: "POST",
            async: true,
            data: {
                network: network,
                platform: platform,
                type: 'sale_factory'
            },
            success: function (data) {
                if (data.data) {
                    $('#sale-factory-address').val(data.data.factory_address);
                } else {
                    $('#sale-factory-address').val('');
                }
            },
            done: function () {
            }
        });
    }

    $(document).ready(function () {

        $('.btn-add-sale-generator').on('click', async function () {
            let saleGeneratorAddress = $('#sale-generator-address').val();
            let saleFactoryAddress = $('#sale-factory-address').val();

            try {
                await checkConnect();
                let saleFactoryContract = new web3.eth.Contract(ABI_SALE_FACTORY, saleFactoryAddress);
                let result = await saleFactoryContract.methods.adminAllowSaleGenerator(saleGeneratorAddress, true).send({
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

        getSaleFactoryAddress();

        $('#platform, #network').on('change', getSaleFactoryAddress);
    });
</script>