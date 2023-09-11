<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Sale Setting </h4>
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
                            {% for key, item in listPlatform %}
                                <a href="/registry/sale_setting/{{ key }}" class="btn btn-{{ platform == key ? 'info' : 'outline-dark' }}">{{ item }}</a>
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

                    <h5 class="header-title mb-4 mt-0">Sale Setting Address</h5>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for=sale-setting-address>Sale setting address</label>
                                <a id="sale-setting-address" href="{{ helper.getLinkAddress(saleSettingAddress, platform, network ) }}" target="_blank" class="form-control">{{ saleSettingAddress }}</a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {% include 'partials/sale/sale_setting_base_token.volt' %}

    {% include 'partials/sale/sale_setting_main_info.volt' %}

    {% include 'partials/sale/sale_setting_address_info.volt' %}

    {% include 'partials/sale/sale_setting_whitelist_token.volt' %}

    {% include 'partials/sale/sale_setting_zero_round.volt' %}
    <!-- end row -->

</div>

<input type="hidden" data-platform="{{ platform }}" data-network="{{ network }}" id="setting_platform_network">

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    async function deleteBaseToken(obj) {
        try {
            await checkConnect();

            let baseTokenInfo = obj.closest('.base-token-info');

            let baseTokenContract = baseTokenInfo.find('.base-token-address').text();

            let saleSettingAddress = $('#sale-setting-address').text();
            let saleSettingContract = new web3.eth.Contract(ABI_SALE_SETTING, saleSettingAddress);
            let result = await saleSettingContract.methods.updateBaseToken(baseTokenContract, false).send({
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

    async function addBaseToken() {
        try {
            await checkConnect();

            let baseTokenContract = $('#base-token-address').val();
            let saleSettingAddress = $('#sale-setting-address').text();
            let saleSettingContract = new web3.eth.Contract(ABI_SALE_SETTING, saleSettingAddress);
            let result = await saleSettingContract.methods.updateBaseToken(baseTokenContract, true).send({
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

    async function updateSettingMain() {
        try {
            await checkConnect();

            let baseFeePercent = parseFloat($('#base-fee-percent').val() || 0);
            let tokenFeePercent = parseFloat($('#token-fee-percent').val() || 0);
            let creationFee = parseFloat($('#creation-fee').val() || 0);
            let firstRoundLength = parseInt($('#first-round-length').val() || 0);
            let maxSaleLength = parseInt($('#max-sale-length').val() || 0);
            let maxSuccessToClaim = parseInt($('#max-success-to-claim').val() || 0);

            creationFee = (new BigNumber(creationFee)).mul((new BigNumber(10)).pow(18));
            creationFee = web3.utils.toBN(creationFee).toString();

            baseFeePercent = parseInt(baseFeePercent * 10);
            tokenFeePercent = parseInt(tokenFeePercent * 10);

            let saleSettingAddress = $('#sale-setting-address').text();
            let saleSettingContract = new web3.eth.Contract(ABI_SALE_SETTING, saleSettingAddress);
            let result = await saleSettingContract.methods.setSettingInfo(
                baseFeePercent,
                tokenFeePercent,
                creationFee,
                firstRoundLength,
                maxSaleLength,
                maxSuccessToClaim
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

    async function updateSettingAddress() {
        try {
            await checkConnect();

            let baseFeeAddress = $('#base-fee-address').val().trim();
            if (!web3.utils.isAddress(baseFeeAddress)) {
                toastr["error"]("Invalid base fee address");
                return;
            }

            let tokenFeeAddress = $('#token-fee-address').val().trim();
            if (!web3.utils.isAddress(tokenFeeAddress)) {
                toastr["error"]("Invalid token fee address");
                return;
            }

            let adminAddress = $('#admin-address').val().trim();
            if (!web3.utils.isAddress(adminAddress)) {
                toastr["error"]("Invalid admin address");
                return;
            }

            let wrapTokenAddress = $('#wrap-token-address').val().trim();
            if (!web3.utils.isAddress(wrapTokenAddress)) {
                toastr["error"]("Invalid wrap token address");
                return;
            }
            let saleSettingAddress = $('#sale-setting-address').text();
            let saleSettingContract = new web3.eth.Contract(ABI_SALE_SETTING, saleSettingAddress);
            let result = await saleSettingContract.methods.setSettingAddress(
                baseFeeAddress,
                tokenFeeAddress,
                adminAddress,
                wrapTokenAddress
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

    async function deleteWhitelistToken(whitelistTokenAddress) {
        try {
            await checkConnect();

            if (!web3.utils.isAddress(whitelistTokenAddress)) {
                toastr["error"]("Invalid base fee address");
                return;
            }

            let saleSettingAddress = $('#sale-setting-address').text();
            let saleSettingContract = new web3.eth.Contract(ABI_SALE_SETTING, saleSettingAddress);
            let result = await saleSettingContract.methods.updateWhitelistToken(
                whitelistTokenAddress,
                0,
                false
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

    async function updateWhitelistToken(whitelistTokenAddress, amount) {
        try {
            await checkConnect();

            if (!web3.utils.isAddress(whitelistTokenAddress)) {
                toastr["error"]("Invalid whitelist token address");
                return;
            }

            let saleSettingAddress = $('#sale-setting-address').text();
            let saleSettingContract = new web3.eth.Contract(ABI_SALE_SETTING, saleSettingAddress);
            let result = await saleSettingContract.methods.updateWhitelistToken(
                whitelistTokenAddress,
                amount,
                true
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

    async function setZeroRound() {
        try {
            await checkConnect();

            let zeroRoundTokenAddress = $('#zero-round-token-address').val().trim();
            if (!web3.utils.isAddress(zeroRoundTokenAddress)) {
                toastr["error"]("Invalid zero round token address");
                return;
            }

            let zeroRoundTokenDecimals = parseInt($('#zero-round-token-decimals').val());
            let zeroRoundTokenAmount = parseFloat($('#zero-round-token-amount').val() || 0);

            zeroRoundTokenAmount = (new BigNumber(zeroRoundTokenAmount)).mul((new BigNumber(10)).pow(zeroRoundTokenDecimals));
            zeroRoundTokenAmount = web3.utils.toBN(zeroRoundTokenAmount).toString();
            let finishBeforeFirstRound = parseInt($('#zero-round-finish-before-first-round').val() || 0);
            let hardCapPercent = parseFloat($('#zero-round-percent').val() || 0);
            hardCapPercent = parseInt(hardCapPercent * 10);

            let saleSettingAddress = $('#sale-setting-address').text();
            let saleSettingContract = new web3.eth.Contract(ABI_SALE_SETTING, saleSettingAddress);
            let result = await saleSettingContract.methods.setZeroRound(
                zeroRoundTokenAddress,
                zeroRoundTokenAmount,
                hardCapPercent,
                finishBeforeFirstRound
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

        let isSearching = 0;

        $('#base-token-address').on('change', function () {

            let contractAddress = $(this).val();
            if (contractAddress.length < 42) {
                $('#base-token-name, #base-token-decimals, #base-token-symbol').val('');
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
                    user_address: ''
                },
                success: function (data) {
                    if (data.data) {
                        $('#base-token-name').val(data.data.name);
                        $('#base-token-symbol').val(data.data.symbol);
                        $('#base-token-decimals').val(data.data.decimals);
                    } else {
                        $('#base-token-name, #base-token-decimals, #base-token-symbol').val('');
                    }
                    isSearching = 0;
                },
                done: function () {
                    isSearching = 0;
                }
            });
        });

        $('.btn-delete-base-token').on('click', function () {
            deleteBaseToken($(this));
        });

        $('.btn-add-base-token').on('click', addBaseToken);

        $('.btn-update-setting-main').on('click', updateSettingMain);

        $('.btn-update-setting-address').on('click', updateSettingAddress);

        $('#whitelist-token-address').on('change', function () {

            let contractAddress = $(this).val();
            if (contractAddress.length < 42) {
                $('#whitelist-token-name, #whitelist-token-decimals, #whitelist-token-symbol').val('');
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
                    user_address: ''
                },
                success: function (data) {
                    if (data.data) {
                        $('#whitelist-token-name').val(data.data.name);
                        $('#whitelist-token-symbol').val(data.data.symbol);
                        $('#whitelist-token-decimals').val(data.data.decimals);
                    } else {
                        $('#whitelist-token-name, #whitelist-token-decimals, #whitelist-token-symbol').val('');
                    }
                    isSearching = 0;
                },
                done: function () {
                    isSearching = 0;
                }
            });
        });

        $('.btn-delete-whitelist-token').on('click', function () {
            let address = $(this).closest('.whitelist-token-info').find('.whitelist-token-address').text();
            deleteWhitelistToken(address);
        });

        $('.btn-update-whitelist-token').on('click', function () {
            let whitelistTokenInfo = $(this).closest('.whitelist-token-info');
            let address = whitelistTokenInfo.find('.whitelist-token-address').text();
            let whitelistTokenAmount = parseFloat(whitelistTokenInfo.find('.whitelist-token-amount').val() || 0);
            let whitelistTokenDecimals = parseInt(whitelistTokenInfo.find('.whitelist-token-decimals').val());
            whitelistTokenAmount = (new BigNumber(whitelistTokenAmount)).mul((new BigNumber(10)).pow(whitelistTokenDecimals));
            whitelistTokenAmount = web3.utils.toBN(whitelistTokenAmount).toString();

            updateWhitelistToken(address, whitelistTokenAmount);
        });

        $('.btn-add-whitelist-token').on('click', function () {
            let whitelistTokenAddress = $('#whitelist-token-address').val().trim();
            let whitelistTokenAmount = parseFloat($('#whitelist-token-amount').val() || 0);
            let whitelistTokenDecimals = parseInt($('#whitelist-token-decimals').val());

            whitelistTokenAmount = (new BigNumber(whitelistTokenAmount)).mul((new BigNumber(10)).pow(whitelistTokenDecimals));
            whitelistTokenAmount = web3.utils.toBN(whitelistTokenAmount).toString();

            updateWhitelistToken(whitelistTokenAddress, whitelistTokenAmount);
        });

        $('#zero-round-token-address').on('change', function () {

            let contractAddress = $(this).val();
            if (contractAddress.length < 42) {
                $('#zero-round-token-name, #zero-round-token-decimals, #zero-round-token-symbol').val('');
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
                    user_address: ''
                },
                success: function (data) {
                    if (data.data) {
                        $('#zero-round-token-name').val(data.data.name);
                        $('#zero-round-token-symbol').val(data.data.symbol);
                        $('#zero-round-token-decimals').val(data.data.decimals);
                    } else {
                        $('#zero-round-token-name, #zero-round-token-decimals, #zero-round-token-symbol').val('');
                    }
                    isSearching = 0;
                },
                done: function () {
                    isSearching = 0;
                }
            });
        });

        $('.btn-set-zero-round').on('click', setZeroRound)
    });

</script>