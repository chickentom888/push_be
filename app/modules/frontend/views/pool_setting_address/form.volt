<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Pool Setting Address</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">
                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Pool Setting Address</h5>

                    <a href="/pool_setting_address/index" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>

                                    <div class="form-group">
                                        <label for="token_address">Token address</label>
                                        <a class="form-control" href="{{ helper.getLinkAddress(object['token_address'], object['platform'], object['network']) }}" target="_blank"><b>{{ object['token_address'] }}</b></a>
                                    </div>

                                    <div class="form-group">
                                        <label for="token_name">Token name</label>
                                        <input class="form-control" type="text" value="{{ object['token_name'] }}" id="token_name" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="token_symbol">Token symbol</label>
                                        <input class="form-control" type="text" value="{{ object['token_symbol'] }}" id="token_symbol" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="token_decimals">Token decimals</label>
                                        <input class="form-control" type="text" value="{{ object['token_decimals'] }}" id="token_decimals" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="token_amount">Token amount</label>
                                        <input class="form-control" type="text" value="{{ object['token_amount'] }}" id="token_amount" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="platform">Platform</label>
                                        <select class="form-control" id="platform" readonly="">
                                            {% for key, item in listPlatform %}
                                                <option value="{{ key }}" {{ object['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="network">Network</label>
                                        <select class="form-control" id="network" readonly>
                                            {% for key, item in listNetwork %}
                                                <option value="{{ key }}" {{ object['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="avatar">Avatar</label>
                                        <input class="form-control" type="text" name="avatar" value="{{ object['avatar'] }}" id="avatar">
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

