<div class="container-fluid">
    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">
                    <h5 class="header-title mb-4 mt-0">Presale Whitelist User </h5>

                    {% if isSearch == true %}
                        <form action="" class="form settings-form" method="get">
                            <div class="row">
                                <div class="col-sm-2">
                                    <input placeholder="Address" id="user_address" type="text" class="form-control" name="q" value="{{ dataGet['q'] }}">
                                </div>

                                <div class="col-sm-2">
                                    <button class="btn btn-success" type="submit">Search</button>
                                </div>
                            </div>
                        </form>
                    {% endif %}

                    <div class="table-responsive">
                        <table class="table table-hover mb-0">

                            <thead>
                            <tr>
                                <th class="border-top-0">User Address</th>
                            </tr>
                            </thead>
                            <tbody>

                            {% for item in listPresaleWhitelist %}
                                <tr>
                                    <td>
                                        <a href="{{ helper.getLinkAddress(item['user_address'], presale['platform'], presale['network']) }}"
                                           target="_blank"><b>{{ item['user_address'] }}</b></a>
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

</div>