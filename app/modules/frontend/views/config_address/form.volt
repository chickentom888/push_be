<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Config address management</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">
        <div class="col-md-12 col-xl-12 col-sm-12">
            {{ flash.output() }}
            <div class="card bg-white m-b-30">

                <div class="card-body">

                    <h5 class="header-title mb-4 mt-0">Config address management </h5>

                    <a href="/config_address" class="btn btn-info">Back</a>

                    <div class="general-label">
                        <form role="form" method="post" action="">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h5>Info</h5>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="platform">Platform</label>
                                        <select name="platform" class="form-control" id="platform">
                                            {% for key, item in listPlatform %}
                                                <option value="{{ key }}" {{ object['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="network">Network</label>
                                        <select name="network" class="form-control" id="network">
                                            {% for key, item in listNetwork %}
                                                <option value="{{ key }}" {{ object['network'] == key ? 'selected' : '' }}>{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="type">Type</label>
                                        <select name="type" class="form-control" id="type">
                                            {% for key, item in listConfigAddress %}
                                                <option value="{{ key }}" {{ object['type'] == key ? 'selected' : '' }}>{{ item }}</option>
                                            {% endfor %}
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="is_listen">Listen</label>
                                        <select name="is_listen" class="form-control" id="is_listen">
                                            <option value="1" {{ object['is_listen'] == 1 ? 'selected' : '' }}>True</option>
                                            <option value="0" {{ object['is_listen'] == 0 ? 'selected' : '' }}>False</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group factory_address" hidden>
                                        <label for="factory-address">Factory Address</label>
                                        <input class="form-control" type="text" name="factory_address" value="" readonly id="factory-address">
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group address">
                                        <label for="address">Address</label>
                                        <input class="form-control" type="text" name="address" value="{{ object['address'] }}" id="address" required>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="form-group description" hidden>
                                        <label for="description">Description</label>
                                        <input class="form-control" type="text" name="description" value="{{ object['description'] }}" id="description">
                                    </div>
                                </div>

                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>

<script>
    function getPresaleFactoryAddress(type) {
        let platform = $('#platform').val();
        let network = $('#network').val();
        if (type) {
            if (type === 'presale_generator') {
                type = 'presale_factory';
            } else if (type === 'mint_token_generator') {
                type = 'mint_token_factory';
            } else if (type === 'sale_generator') {
                type = 'sale_factory';
            }
            $.ajax({
                url: '/exchange_platform/get_factory_address',
                type: "POST",
                async: true,
                data: {
                    network: network,
                    platform: platform,
                    type: type
                },
                success: function (data) {
                    if (data.data) {
                        togglePresaleAddress(true);
                        $('#factory-address').val(data.data.factory_address);
                    } else {
                        $('#factory-address').val('');
                    }
                },
                done: function () {
                }
            });
        }
    }

    function togglePresaleAddress(toggle) {
        $('.factory_address').attr('hidden', true)
        $('.description').attr('hidden', true)
        if (toggle) {
            $('.factory_address').removeAttr('hidden');
            $('.description').removeAttr('hidden');
        }

    }

    $(document).ready(function () {
        var type = $('#type')
        if (type.val() === 'presale_generator' || type.val() === 'mint_token_generator' || type.val() === 'sale_generator') {
            getPresaleFactoryAddress(type.val());
            togglePresaleAddress(type.val());
        }

        type.on('change', function () {
            togglePresaleAddress();
            console.log(type.val())
            if (type.val() === 'presale_generator' || type.val() === 'mint_token_generator' || type.val() === 'sale_generator') {
                getPresaleFactoryAddress(type.val());
            }
        })

        $('#platform, #network').on('change', function () {
            if (type.val() === 'presale_generator' || type.val() === 'mint_token_generator' || type.val() === 'sale_generator') {
                getPresaleFactoryAddress(type.val());
            }
        });
    })

</script>