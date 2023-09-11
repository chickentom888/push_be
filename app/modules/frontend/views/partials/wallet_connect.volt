<div class="row">

    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">

            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Wallet Connect</h5>

                <div class="general-label">

                    <div class="row">

                        <div class="col-sm-12">
                            <h5>Thông tin</h5>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <label for="wallet_connected">Address</label>
                                <input class="form-control" type="text" value="" id="wallet_connected" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="platform_connected">Platform</label>
                                <input class="form-control" type="text" value="" id="platform_connected" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="network_connected">Network</label>
                                <input class="form-control" type="text" value="" id="network_connected" readonly>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-success btn-connect-wallet">Connect</button>
                    <button type="button" class="btn btn-danger btn-disconnect-wallet">Disconnect</button>

                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('.btn-connect-wallet').on('click', async function (event) {
            event.preventDefault();
            init();
            await onConnect();
        });

        $('.btn-disconnect-wallet').on('click', async function (event) {
            event.preventDefault();
            await onDisconnect();
        });
    });
</script>