<div class="row">
    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Whitelist Token</h5>

                <div class="general-label">

                    {% if setting['whitelist_token']|length %}
                        {% for key, item in setting['whitelist_token'] %}

                            <div class="row whitelist-token-info">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="whitelist-token-address-{{ key }}">Token address</label>
                                        <a href="{{ helper.getLinkAddress(item['address'], platform, network ) }}" target="_blank" class="form-control whitelist-token-address">{{ item['address'] }}</a>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="whitelist-token-name-{{ key }}">Token name</label>
                                        <input class="form-control" type="text" value="{{ item['name'] }}" id="whitelist-token-name-{{ key }}" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="whitelist-token-symbol-{{ key }}">Token symbol</label>
                                        <input class="form-control" type="text" value="{{ item['symbol'] }}" id="whitelist-token-symbol-{{ key }}" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="whitelist-token-decimals-{{ key }}">Token decimals</label>
                                        <input class="form-control whitelist-token-decimals" type="text" value="{{ item['decimals'] }}" id="whitelist-token-decimals-{{ key }}" readonly>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="whitelist-token-amount-{{ key }}">Token amount</label>
                                        <input class="form-control whitelist-token-amount" type="text" value="{{ item['amount'] }}" id="whitelist-token-amount-{{ key }}">
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-danger btn-delete-whitelist-token btn-interact-sc">Delete</button>
                                        <button type="button" class="btn btn-success btn-update-whitelist-token btn-interact-sc">Update</button>
                                    </div>
                                    <hr>
                                </div>
                            </div>

                        {% endfor %}
                    {% endif %}


                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Add new whitelist token</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="whitelist-token-address">Token address</label>
                                <input class="form-control" type="text" value="" id="whitelist-token-address">
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="whitelist-token-name">Token name</label>
                                <input class="form-control" type="text" value="" id="whitelist-token-name" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="whitelist-token-symbol">Token symbol</label>
                                <input class="form-control" type="text" value="" id="whitelist-token-symbol" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="whitelist-token-decimals">Token decimals</label>
                                <input class="form-control" type="text" value="" id="whitelist-token-decimals" readonly>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="whitelist-token-amount">Token amount</label>
                                <input class="form-control" type="text" value="" id="whitelist-token-amount">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-add-whitelist-token btn-interact-sc">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>