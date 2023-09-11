<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Lock Setting </h4>
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
                                <a href="/registry/lock_setting/{{ key }}" class="btn btn-{{ platform == key ? 'info' : 'outline-dark' }}">{{ item }}</a>
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

                    <h5 class="header-title mb-4 mt-0">Lock Setting Address</h5>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for=lock-setting-address>Lock setting address</label>
                                <a id="lock-setting-address" href="{{ helper.getLinkAddress(lockSettingAddress, platform, network ) }}" target="_blank" class="form-control">{{ lockSettingAddress }}</a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    {% include 'partials/lock/lock_setting_fee_info.volt' %}

    {% include 'partials/lock/lock_setting_whitelist_address.volt' %}

    {% include 'partials/lock/lock_setting_discount_percent.volt' %}

    {% include 'partials/lock/lock_setting_whitelist_token.volt' %}
    <!-- end row -->

</div>

<input type="hidden" data-platform="{{ platform }}" data-network="{{ network }}" id="setting_platform_network">

{% include 'common/plugins/toastr.volt' %}

<script type="text/javascript">

    async function addWhitelistToken() {
        try {
            await checkConnect();

            let baseTokenContract = $('#base-token-address').val();
            let presaleSettingAddress = $('#presale-setting-address').text();
            let presaleSettingContract = new web3.eth.Contract(ABI_PRESALE_SETTING, presaleSettingAddress);
            let result = await presaleSettingContract.methods.updateBaseToken(baseTokenContract, true).send({
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

    async function updateReferer() {
        try {
            await checkConnect();

            let refererTokenAddress = $('#referer-token-address').val();
            let refererTokenDecimals = parseInt($('#referer-token-decimals').val());
            if (!web3.utils.isAddress(refererTokenAddress)) {
                refererTokenAddress = "0x0000000000000000000000000000000000000000";
            } else {
                refererTokenAddress = web3.utils.toChecksumAddress(refererTokenAddress);
            }
            let listLevelItem = $('.list-level-item .referer-level-item');
            let listLevelAmount = [], listLevelPercent = [];
            listLevelItem.each(function (i, v) {
                let tokenAmount = parseFloat($(v).find('.referer-level-token-amount').val() || 0);
                let levelPercent = parseFloat($(v).find('.referer-level-referer-percent').val() || 0);
                tokenAmount = (new BigNumber(tokenAmount)).mul((new BigNumber(10)).pow(refererTokenDecimals));
                tokenAmount = web3.utils.toBN(tokenAmount).toString();
                levelPercent = parseInt(levelPercent * 10);
                listLevelAmount.push(tokenAmount);
                listLevelPercent.push(levelPercent);
            });
            let levelNumber = listLevelAmount.length;

            let presaleSettingAddress = $('#presale-setting-address').text();
            let presaleSettingContract = new web3.eth.Contract(ABI_PRESALE_SETTING, presaleSettingAddress);
            let result = await presaleSettingContract.methods.setReferer(refererTokenAddress, levelNumber, listLevelAmount, listLevelPercent).send({
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

    async function updateRefererUser(address, action) {
        try {
            await checkConnect();
            if (!address.length) {
                return;
            }
            let presaleSettingAddress = $('#presale-setting-address').text();
            let presaleSettingContract = new web3.eth.Contract(ABI_PRESALE_SETTING, presaleSettingAddress);
            let result = await presaleSettingContract.methods.updateReferer(address, action).send({
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

    async function updateFeeSetting() {
        try {
            await checkConnect();

            let baseFee = parseFloat($('#base-fee').val() || 0);
            let tokenFeePercent = parseFloat($('#token-fee-percent').val() || 0);
            let addressFee = $('#address-fee').val().trim();

            baseFee = (new BigNumber(baseFee)).mul((new BigNumber(10)).pow(18));
            baseFee = web3.utils.toBN(baseFee).toString();
            tokenFeePercent = parseInt(tokenFeePercent * 10);

            let lockSettingAddress = $('#lock-setting-address').text();
            let presaleSettingContract = new web3.eth.Contract(ABI_LOCK_SETTING, lockSettingAddress);
            let result = await presaleSettingContract.methods.setFee(
                baseFee,
                tokenFeePercent,
                addressFee
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

            let lockSettingAddress = $('#lock-setting-address').text();
            let presaleSettingContract = new web3.eth.Contract(ABI_LOCK_SETTING, lockSettingAddress);
            let result = await presaleSettingContract.methods.setWhitelistFeeToken(
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

    async function updateWhitelistAddress(whitelistAddress, action) {
        try {
            await checkConnect();

            if (!web3.utils.isAddress(whitelistAddress)) {
                toastr["error"]("Invalid whitelist address");
                return;
            }

            let lockSettingAddress = $('#lock-setting-address').text();
            let presaleSettingContract = new web3.eth.Contract(ABI_LOCK_SETTING, lockSettingAddress);
            let result = await presaleSettingContract.methods.updateFeeWhitelist(
                whitelistAddress,
                action
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

            let lockSettingAddress = $('#lock-setting-address').text();
            let lockSettingContract = new web3.eth.Contract(ABI_LOCK_SETTING, lockSettingAddress);
            let result = await lockSettingContract.methods.setWhitelistFeeToken(
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

    async function updateDiscountPercent() {
        try {

            await checkConnect();
            let discountPercent = parseFloat($('#discount-percent').val() || 0);
            discountPercent = parseInt(discountPercent * 10);

            let lockSettingAddress = $('#lock-setting-address').text();
            let lockSettingContract = new web3.eth.Contract(ABI_LOCK_SETTING, lockSettingAddress);
            let result = await lockSettingContract.methods.setDiscountPercent(discountPercent).send({
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

        $('.btn-delete-base-token').on('click', function () {
            deleteBaseToken($(this));
        });

        // Whitelist token
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

            console.log(whitelistTokenAddress, whitelistTokenAmount);
            updateWhitelistToken(whitelistTokenAddress, whitelistTokenAmount);
        });

        // Fee
        $('.btn-update-fee').on('click', updateFeeSetting);

        // Discount
        $('.btn-update-discount-percent').on('click', updateDiscountPercent);

        // Whitelist address
        $('.btn-delete-whitelist-address').on('click', function () {
            let whitelistAddressItem = $(this).closest('.whitelist-address-item');
            let whitelistAddress = whitelistAddressItem.find('.whitelist-address').text();
            updateWhitelistAddress(whitelistAddress, 0);
        });

        $('.btn-add-whitelist-address').on('click', function () {
            let whitelistAddress = $('#whitelist-address').val();
            updateWhitelistAddress(whitelistAddress, 1);
        });
    });

</script>