<div class="row">

    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">Lock Whitelist Address</h5>

                <div class="general-label">

                    {% if setting['whitelist_address']|length %}
                        {% for key, item in setting['whitelist_address'] %}
                            <div class="row whitelist-address-item">

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="whitelist-address-{{ key }}">Whitelist address {{ key + 1 }}</label>
                                        <a class="form-control whitelist-address" target="_blank" href="{{ helper.getLinkAddress(item, platform, network ) }}" id="whitelist-address-{{ key }}" readonly>{{ item }}</a>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <button class="btn btn-danger btn-delete-whitelist-address"><i class="fa fa-trash"></i></button>
                                    </div>
                                </div>

                            </div>
                        {% endfor %}
                    {% endif %}

                    <div class="row">
                        <div class="col-sm-6">

                            <div class="form-group">
                                <label for="whitelist-address">Add new whitelist address</label>
                                <input class="form-control" id="whitelist-address" type="text">
                            </div>

                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-add-whitelist-address btn-interact-sc">Update</button>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>