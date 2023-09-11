<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Exchange Platform </h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Exchange Platform </h5>

                    <a href="/exchange_platform/" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>
                                    <div class="form-group">
                                        <label for="exchange_name">Exchange Name (*)</label>
                                        <input class="form-control" type="text" name="exchange_name" value="{{ object['exchange_name'] }}" id="exchange_name" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="exchange_logo">Exchange Logo</label>
                                        <input class="form-control" type="url" name="exchange_logo" value="{{ object['exchange_logo'] }}" id="exchange_logo">
                                    </div>

                                    <div class="form-group">
                                        <label for="exchange_key">Exchange Key</label>
                                        <input class="form-control" type="text" name="exchange_key" value="{{ object['exchange_key'] }}" id="exchange_key" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="exchange_url">Exchange Url</label>
                                        <input class="form-control" type="url" name="exchange_url" value="{{ object['exchange_url'] }}" id="exchange_url">
                                    </div>

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
                                        <label for="dex-factory-address">Dex Factory Address</label>
                                        <input class="form-control" type="text" name="dex_factory_address" value="{{ object['dex_factory_address'] }}" id="dex-factory-address" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="dex-locker-address">Dex Locker Address</label>
                                        <input class="form-control" type="text" name="dex_locker_address" value="{{ object['dex_locker_address'] }}" id="dex-locker-address" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="dex-router-address">Dex Router Address</label>
                                        <input class="form-control" type="text" name="dex_router_address" value="{{ object['dex_router_address'] }}" id="dex-router-address" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="dex-wrap-token-address">Dex Wrap Token Address</label>
                                        <input class="form-control" type="text" name="dex_wrap_token_address" value="{{ object['dex_wrap_token_address'] }}" id="dex-wrap-token-address" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="presale-factory-address">Presale Factory Address</label>
                                        <input class="form-control" type="text" name="presale_factory_address" value="{{ object['presale_factory_address'] }}" id="presale-factory-address" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="presale-generator-address">Presale Generator Address</label>
                                        <input class="form-control" type="text" name="presale_generator_address" value="{{ object['presale_generator_address'] }}" id="presale-generator-address" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="presale-setting-address">Presale Setting Address</label>
                                        <input class="form-control" type="text" name="presale_setting_address" value="{{ object['presale_setting_address'] }}" id="presale-setting-address" required>
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