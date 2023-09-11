<div class="row">

    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">

            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Base Token</h5>
                <div class="row">
                    <div class="col-sm-12">
                        <h5>List base token</h5>
                    </div>
                </div>

                {% if setting['base_token']['address_number'] > 0 %}
                    {% for key, item in setting['base_token']['list_address'] %}
                        <div class="row base-token-info">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="base-token-address-{{ key }}">Token address</label>
                                    <a href="{{ helper.getLinkAddress(item['token_address'], platform, network ) }}" target="_blank" class="form-control base-token-address">{{ item['token_address'] }}</a>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="base-token-name-{{ key }}">Token name</label>
                                    <input class="form-control" type="text" value="{{ item['token_name'] }}" id="base-token-name-{{ key }}" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="base-token-symbol-{{ key }}">Token symbol</label>
                                    <input class="form-control" type="text" value="{{ item['token_symbol'] }}" id="base-token-symbol-{{ key }}" readonly>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label for="base-token-decimals-{{ key }}">Token decimals</label>
                                    <input class="form-control" type="text" value="{{ item['token_decimals'] }}" id="base-token-decimals-{{ key }}" readonly>
                                </div>
                            </div>

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <button type="button" class="btn btn-danger btn-delete-base-token btn-interact-sc">Delete</button>
                                </div>
                                <hr>
                            </div>
                        </div>

                    {% endfor %}
                {% endif %}

                <div class="row">
                    <div class="col-sm-12">
                        <h5>Add new base token</h5>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="base-token-address">Token address</label>
                            <input class="form-control" type="text" value="" id="base-token-address">
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="base-token-name">Token name</label>
                            <input class="form-control" type="text" value="" id="base-token-name" readonly>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="base-token-symbol">Token symbol</label>
                            <input class="form-control" type="text" value="" id="base-token-symbol" readonly>
                        </div>
                    </div>

                    <div class="col-sm-6">
                        <div class="form-group">
                            <label for="base-token-decimals">Token decimals</label>
                            <input class="form-control" type="text" value="" id="base-token-decimals" readonly>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group">
                            <button type="button" class="btn btn-success btn-add-base-token btn-interact-sc">Add</button>
                        </div>
                        <hr>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>