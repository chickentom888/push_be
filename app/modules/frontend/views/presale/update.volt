<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">
                <div class="card-body">
                    <h5 class="header-title mb-4 mt-0">Update Presale Name</h5>
                    {% if presale %}
                        <div class="general-label">
                            <form role="form" method="post" action="{{ presale['_id'] }}">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="sale_token_name">Presale name</label>
                                            <input class="form-control" type="text" name="sale_token_name"
                                                   value="{{ presale['sale_token_name'] }}"
                                                   id="sale_token_name">
                                        </div>

                                        <div class="form-group">
                                            <label for="sale_token_address">Sale Token Address</label>
                                            <a class="form-control" readonly
                                               href="{{ helper.getLinkAddress(presale['sale_token_address'], presale['platform'], presale['network']) }}"
                                               target="_blank"><b>{{ presale['sale_token_address'] }}</b></a>
                                        </div>

                                        <div class="form-group">
                                            <label for="sale_token_symbol">Sale token symbol</label>
                                            <input class="form-control" type="text" name="sale_token_symbol"
                                                   value="{{ presale['sale_token_symbol'] }}"
                                                   id="sale_token_symbol" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="sale_token_decimals">Sale token decimals</label>
                                            <input class="form-control" type="text" name="sale_token_decimals"
                                                   value="{{ presale['sale_token_decimals'] }}" id="sale_token_decimals"
                                                   readonly>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label for="base_token_address">Base Token Address</label>
                                            <a class="form-control" readonly
                                               href="{{ helper.getLinkAddress(presale['base_token_address'], presale['platform'], presale['network']) }}"
                                               target="_blank"><b>{{ presale['base_token_address'] }}</b></a>
                                        </div>

                                        <div class="form-group">
                                            <label for="base_token_name">Base token name</label>
                                            <input class="form-control" type="text" name="base_token_name"
                                                   value="{{ presale['base_token_name'] }}"
                                                   id="base_token_name" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="base_token_symbol">Base token symbol</label>
                                            <input class="form-control" type="text" name="base_token_symbol"
                                                   value="{{ presale['base_token_symbol'] }}"
                                                   id="base_token_symbol" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="base_token_decimals">Base token decimals</label>
                                            <input class="form-control" type="text" name="base_token_decimals"
                                                   value="{{ presale['base_token_decimals'] }}" id="base_token_decimals"
                                                   readonly>
                                        </div>

                                    </div>
                                    <div class="col-sm-6">
                                        <div class="mt-2">
                                            <button class="btn btn-success btn-sm" data-url="/presale/set_show/{{ item['_id'] }}?is_show=1">Update
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                    {% else %}
                        <div class="text-center">
                            <p class="m-t-10">No Record Available</p>
                        </div>
                    {% endif %}
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>