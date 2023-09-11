<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Airdrop</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Transaction Detail</h5>

                    <div class="general-label">

                        <div class="row">

                            <div class="col-sm-12">
                                <h5>Info</h5>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="block_hash">Block hash</label>
                                    <input class="form-control" type="text" value="{{ object['block_hash'] }}" id="block_hash" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="block_number">Block number</label>
                                    <input class="form-control" type="text" value="{{ object['block_number'] }}" id="block_number" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="from_address">From address</label>
                                    <a class="form-control" href="{{ helper.getLinkAddress(object['from'], object['platform'], object['network']) }}" target="_blank"><b>{{ object['from'] }}</b></a>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="to_address">To address</label>
                                    <a class="form-control" href="{{ helper.getLinkAddress(object['to'], object['platform'], object['network']) }}" target="_blank"><b>{{ object['to'] }}</b></a>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="hash">Hash</label>
                                    <a class="form-control" href="{{ helper.getLinkTx(object['hash'], object['platform'], object['network']) }}" target="_blank"><b>{{ object['hash'] }}</b></a>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="value">Value</label>
                                    <input class="form-control" type="text" value="{{ object['value'] }}" id="value" readonly>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="input">Input</label>
                                    <textarea class="form-control" name="" id="hash" cols="30" rows="10" readonly>{{ object['input'] }}</textarea>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="timestamp">Timestamp</label>
                                    <input class="form-control" type="text" value="{{ date('d/m/Y H:i:s', object['timestamp']) }}" id="timestamp" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="process_at">Process</label>
                                    <input class="form-control" type="text" value="{{ date('d/m/Y H:i:s', object['process_at']) }}" id="process_at" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="network">Network</label>
                                    <input class="form-control" type="text" value="{{ object['network'] }}" id="network" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="platform">Platform</label>
                                    <input class="form-control" type="text" value="{{ object['platform'] }}" id="platform" readonly>
                                </div>
                            </div>


                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="is_process">Process</label>
                                    <input class="form-control" type="text" value="{{ object['is_process'] == 1 ? 'Yes' : 'No' }}" id="is_process" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="contract_type">Contract</label>
                                    <input class="form-control" type="text" value="{{ object['contract_type'] }}" id="contract_type" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="function">Function</label>
                                    <input class="form-control" type="text" value="{{ object['function'] }}" id="function" readonly>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="data_input">Data Input</label>
                                    <textarea class="form-control" readonly name="" id="data_input" cols="30" rows="10">{{ print_r(object['data_input'], true) }}</textarea>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <label for="data_decode">Data Decode</label>
                                    <textarea class="form-control" readonly name="" id="data_decode" cols="30" rows="10">{{ print_r(object['data_decode'], true) }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                    <a class="btn btn-success" href="/index/transaction">Back</a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->