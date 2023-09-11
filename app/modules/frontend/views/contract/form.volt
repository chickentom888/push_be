<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Contract management</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Contract management </h5>

                    <a href="/contract" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>
                                    <div class="form-group">
                                        <label for="platform">Platform</label>
                                        <select name="platform" class="form-control" id="platform">
                                            {% for key, item in listPlatform %}
                                                <option value="{{ key }}" {{ object['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="network">Network</label>
                                        <select name="network" class="form-control" id="network">
                                            {% for key, item in listNetwork %}
                                                <option value="{{ key }}" {{ object['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <input class="form-control" type="text" name="address" value="{{ object['address'] }}" id="address" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="contract_key">Key</label>
                                        <input class="form-control" type="text" name="contract_key" value="{{ object['contract_key'] }}" id="contract_key" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input class="form-control" type="text" name="name" value="{{ object['name'] }}" id="name" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="decimals">Decimals</label>
                                        <input class="form-control" type="text" name="decimals" value="{{ object['decimals'] }}" id="decimals" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="symbol">Symbol</label>
                                        <input class="form-control" type="text" name="symbol" value="{{ object['symbol'] }}" id="symbol" required>
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