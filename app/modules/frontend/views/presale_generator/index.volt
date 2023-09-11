<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Presale Generator</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    {% include 'partials/wallet_connect.volt' %}

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Presale Generator</h5>

                    {{ flash.output() }}

                    <div class="mb-2">
                        <a href="/presale_generator/form" class="btn btn-success">Create</a>
                    </div>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2 form-group">
                                <label for="platform">Platform</label>
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Select</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="network">Network</label>
                                <select name="network" id="network" class="form-control">
                                    <option value="">Select</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="address">Address</label>
                                <input placeholder="Address" id="address" type="text" class="form-control" name="q" value="{{ dataGet['q'] }}">
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-2">
                        Total: {{ helper.numberFormat(count) }}
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Platform</th>
                                <th class="border-top-0">Presale Generator Address</th>
                                <th class="border-top-0">Presale Factory Address</th>
                                <th class="border-top-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Platform: <b>{{ item['platform']|upper }}</b></div>
                                        <div>Network: <b>{{ item['network']|upper }}</b></div>
                                    </td>
                                    <td>
                                        <div><a class="presale-generator-address" href="{{ helper.getLinkAddress(item['presale_generator_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['presale_generator_address'] }}</b></a></div>
                                    </td>

                                    <td>
                                        <div><a class="presale-factory-address" href="{{ helper.getLinkAddress(item['presale_factory_address'], item['platform'], item['network']) }}" target="_blank"><b>{{ item['presale_factory_address'] }}</b></a></div>
                                    </td>
                                    <td>
                                        {% if item['platform'] == connectedPlatform AND item['network'] == connectedNetwork %}
                                            <a href="javascript:" class="btn btn-danger btn-sm btn-delete-presale-generator">Delete</a>
                                        {% endif %}
                                    </td>
                                </tr>
                            {% endfor %}

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-2 mb-2">
                        {% include 'layouts/paging.volt' %}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>
{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">
    $(document).ready(function () {
        $('.btn-delete-presale-generator').on('click', async function () {
            let tr = $(this).closest('tr');
            let presaleGeneratorAddress = tr.find('.presale-generator-address').text();
            let presaleFactoryAddress = tr.find('.presale-factory-address').text();

            try {
                await checkConnect();
                let presaleSettingContract = new web3.eth.Contract(ABI_PRESALE_FACTORY, presaleFactoryAddress);
                let result = await presaleSettingContract.methods.adminAllowPresaleGenerator(presaleGeneratorAddress, false).send({
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
    });
</script>
