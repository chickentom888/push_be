<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Main Token </h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Main token</h5>

                    <form action="" class="form settings-form" method="get">
                        <div class="row">
                            <div class="col-sm-2 form-group">
                                <label for="platform">Platform</label>
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Platform</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <label for="network">Network</label>
                                <select name="network" id="network" class="form-control">
                                    <option value="">Network</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2 form-group">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>
                    <form action="/index/upsertMainToken" method="post">
                        <div class="general-label">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Thông tin</h5>
                                </div>

                                <input type="hidden" name="token_platform" id="token_platform"
                                       value="{{ dataGet['platform'] }}">
                                <input type="hidden" name="token_network" id="token_network"
                                       value="{{ dataGet['network'] }}">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="token_address">Token address</label>
                                        <input class="form-control" type="text" name="token_address"
                                               value="{{ token is defined ? token['token_address'] : '' }}"
                                               id="token_address">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="token_name">Token name</label>
                                        <input class="form-control" type="text"
                                               value="{{ token is defined ? token['token_name'] : '' }}" id="token_name"
                                               name="token_name" required readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="token_symbol">Token symbol</label>
                                        <input class="form-control" type="text"
                                               value="{{ token is defined ? token['token_symbol'] : '' }}"
                                               id="token_symbol" name="token_symbol" required readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="token_decimals">Token decimals</label>
                                        <input class="form-control" type="text"
                                               value="{{ token is defined ? token['token_decimals'] : '' }}"
                                               id="token_decimals" name="token_decimals" required readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="token_total_supply">Token total supply</label>
                                        <input class="form-control" type="text"
                                               value="{{ token is defined ? token['total_supply'] : '' }}"
                                               id="token_total_supply" name="token_total_supply" required readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="token_price">Token price</label>
                                        <input class="form-control" type="text"
                                               value="{{ token is defined ? token['token_price'] : '' }}"
                                               id="token_price" name="token_price" required>
                                    </div>
                                </div>
                                '
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="token_icon">Token icon</label>
                                        <input class="form-control" type="text"
                                               value="{{ token is defined ? token['token_icon'] : '' }}" id="token_icon"
                                               name="token_icon" required>
                                        <img id="token-img" style="height: 100px; width: 100px; object-fit: contain" src=" {{ token is defined ? token['token_icon'] : '' }}" alt="token icon">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success" id="btn-create-token">Create or update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">
    function debounce(callback, delay) {
        let timeout;
        return function () {
            clearTimeout(timeout);
            timeout = setTimeout(callback, delay);
        }
    }

    $(document).ready(function () {
        console.log(platform)
        const tokenAddressInput = document.getElementById('token_address');

        tokenAddressInput.addEventListener(
            "keyup",
            debounce(syncContractInfo, 1000)
        )

        $('#platform').on('change', function () {
            const platform = $(this).val();
            $('#token_platform').val(platform);
        });

        $('#network').on('change', function () {
            const platform = $(this).val();
            $('#token_network').val(platform);
        });

        $('#token_icon').on('change', function () {
            const src = $('#token_icon').val();
            $("#token-img").attr("src", src);
        });

        function syncContractInfo() {
            let platform = $('#platform').val();
            let network = $('#network').val();
            if (!platform.length || !network.length) return;
            platform = platform.toLowerCase();
            network = network.toLowerCase();
            let contractAddress = tokenAddressInput.value;
            if (contractAddress.length < 42) {
                $('#token_name, #token_symbol, #token_decimals, #token_total_supply').val('');
                return;
            }

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
                error: function (request, status, error) {
                    console.log(`request::::${request}`);
                    console.log(`status::::${status}`);
                    console.log(`error::::${error}`);
                },
                success: function (data) {
                    console.log(data);
                    if (data.data) {
                        $('#token_name').val(data.data.name);
                        $('#token_symbol').val(data.data.symbol);
                        $('#token_decimals').val(data.data.decimals);
                        $('#token_total_supply').val(data.data.total_supply);
                    } else {
                        $('#token_name, #token_symbol, #token_decimals, #token_total_supply').val('');
                    }
                },
                done: function () {
                    console.log('done');
                }
            });
        }
    });

</script>