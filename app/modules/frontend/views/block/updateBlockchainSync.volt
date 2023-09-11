<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Update Blockchain Sync</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Update Blockchain Sync</h5>

                    <a href="/block/blockchainSync/" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>

                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input class="form-control" type="text" value="{{ object['name'] }}" id="name" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="key">Key</label>
                                        <input class="form-control" type="url" value="{{ object['key'] }}" id="key" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="ticker">Ticker</label>
                                        <input class="form-control" type="text" value="{{ object['ticker'] }}" id="ticker" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="platform">Platform</label>
                                        <input class="form-control" type="text" value="{{ object['platform'] }}" id="platform" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="network">Network</label>
                                        <input class="form-control" type="text" value="{{ object['network'] }}" id="network" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label for="last-block">Synced Block</label>
                                        <input class="form-control" type="text" name="last_block" value="{{ object['last_block'] }}" id="last-block" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="scan-block">Scanned Block</label>
                                        <input class="form-control" type="text" name="scan_block" value="{{ object['scan_block'] }}" id="scan-block" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="scan-block-created">Scanned Block Created</label>
                                        <input class="form-control" type="text" name="scan_block_created" value="{{ object['scan_block_created'] }}" id="scan-block-created" required>
                                    </div>

                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>