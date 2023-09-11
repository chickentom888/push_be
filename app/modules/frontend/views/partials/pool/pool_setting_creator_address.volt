<div class="row">
    <div class="col-md-12 col-xl-12 col-sm-12">
        <div class="card bg-white m-b-30">
            <div class="card-body">

                <h5 class="header-title mb-4 mt-0">List Creator</h5>

                <div class="general-label">

                    {% if setting['creator_address']['address_number'] > 0 %}
                        {% for key, item in setting['creator_address']['list_address'] %}

                            <div class="row creator-address-info">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="creator-address-{{ key }}">Creator address {{ key + 1 }}</label>
                                        <a href="{{ helper.getLinkAddress(item, platform, network ) }}" target="_blank" class="form-control creator-address">{{ item }}</a>
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="button" class="btn btn-danger btn-delete-creator-address btn-interact-sc">Delete</button>
                                    </div>
                                    <hr>
                                </div>
                            </div>

                        {% endfor %}
                    {% endif %}


                    <div class="row">
                        <div class="col-sm-12">
                            <h5>Add new creator address</h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="creator-address">Creator address</label>
                                <input class="form-control" type="text" value="" id="creator-address">
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="form-group">
                                <button type="button" class="btn btn-success btn-add-creator-address btn-interact-sc">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>