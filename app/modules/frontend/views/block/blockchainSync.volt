<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Blockchain Sync</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Blockchain Sync</h5>

                    {{ flash.output() }}

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                            <tr>
                                <th class="border-top-0">Platform Info</th>
                                <th class="border-top-0">Synced Block</th>
                                <th class="border-top-0">Last Block</th>
                                <th class="border-top-0">Miss Block</th>
                                <th class="border-top-0">Last Update</th>
                                <th class="border-top-0">Update</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listData %}
                                <tr>
                                    <td>
                                        <div>Name: <b>{{ item['name'] }}</b></div>
                                        <div>Key: <b>{{ item['key'] }}</b></div>
                                        <div>Ticker: <b>{{ item['ticker'] }}</b></div>
                                        <div>Platform: <b>{{ item['platform'] }}</b></div>
                                        <div>Network: <b>{{ item['network'] }}</b></div>
                                    </td>
                                    <td>
                                        <div>Synced: <b>{{ helper.numberFormat(item['last_block']) }}</b></div>
                                        <div>Scanned: <b>{{ helper.numberFormat(item['scan_block']) }}</b></div>
                                        <div>Scan Created: <b>{{ helper.numberFormat(item['scan_block_created']) }}</b></div>
                                    </td>
                                    <td>
                                        <div><b>{{ helper.numberFormat(item['current_block']) }}</b></div>
                                    </td>
                                    <td>
                                        <div>Synced: <b>{{ number_format(item['current_block'] - item['last_block']) }}</b></div>
                                        <div>Scanned: <b>{{ number_format(item['current_block'] - item['scan_block']) }}</b></div>
                                        <div>Scan Created: <b>{{ number_format(item['current_block'] - item['scan_block_created']) }}</b></div>
                                    </td>
                                    <td>
                                        <div>{{ date('d/m/Y H:i:s', item['updated_at']) }}</div>
                                    </td>

                                    <td>
                                        <a href="/block/updateBlockchainSync/{{ item['_id'] }}" class="btn btn-sm btn-info">Update</a>
                                    </td>
                                </tr>
                            {% endfor %}

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>
