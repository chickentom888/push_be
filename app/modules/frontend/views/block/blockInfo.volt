<div class="container-fluid">

    <div class="row">
        <div class="col-sm-12">
            <div class="page-title-box">
                <h4 class="page-title">Block Info</h4>
            </div>
        </div>
    </div>
    <!-- end page title end breadcrumb -->

    <div class="row">

        <div class="col-md-12 col-xl-12 col-sm-12">
            <div class="card bg-white m-b-30">
                <div class="card-body new-user">

                    <h5 class="header-title mb-4 mt-0">Block Info</h5>

                    {{ flash.output() }}

                    <form action="" class="form settings-form" method="get">
                        <div class="row">

                            <div class="col-sm-2">
                                <select name="platform" id="platform" class="form-control">
                                    <option value="">Platform</option>
                                    {% for key,item in listPlatform %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <select name="network" id="network" class="form-control">
                                    <option value="">Network</option>
                                    {% for key,item in listNetwork %}
                                        <option value="{{ key }}" {{ dataGet['platform'] == key ? 'selected' : '' }}>{{ item }}</option>
                                    {% endfor %}
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <input placeholder="Block" id="block" type="text" class="form-control" name="block" value="{{ dataGet['block'] }}">
                            </div>

                            <div class="col-sm-2">
                                <select name="sort" id="sort" class="form-control">
                                    <option value="">Sort</option>
                                    <option value="created_at" {{ dataGet['sort'] == 'created_at' ? 'selected' : '' }}>Date</option>
                                    <option value="block" {{ dataGet['sort'] == 'block' ? 'selected' : '' }}>Block</option>
                                </select>
                            </div>

                            <div class="col-sm-2">
                                <button class="btn btn-success" type="submit">Search</button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-2">
                        Total: {{ helper.numberFormat(count) }}
                    </div>

                    <form action="/block/deleteBlockInfo" method="post">
                        <div class="mt-2 mb-2">
                            <button class="btn btn-danger" type="submit">Delete</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="checkedAll">
                                    </th>
                                    <th class="border-top-0">Platform</th>
                                    <th class="border-top-0">Block</th>
                                    <th class="border-top-0">Tx</th>
                                    <th class="border-top-0">Date</th>
                                    <th class="border-top-0">Action</th>
                                </tr>
                                </thead>
                                <tbody>

                                {% for item in listData %}
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="checkSingle" name="id[]" value="{{ item['_id'] }}">
                                        </td>
                                        <td>
                                            <div>Platform: <b>{{ item['platform']|upper }}</b></div>
                                            <div>Network: <b>{{ item['network']|upper }}</b></div>
                                        </td>
                                        <td>
                                            <b>{{ item['block'] }}</b>
                                        </td>
                                        <td>
                                            <b>{{ item['transactions']|length }}</b>
                                        </td>
                                        <td>
                                            <div>Created: <b>{{ date("d-m-Y H:i:s", item['created_at']) }}</b></div>
                                        </td>
                                        <td>
                                            <a href="/block/deleteBlockInfo/{{ item['_id'] }}" class="btn btn-sm btn-danger">Delete</a>
                                        </td>
                                    </tr>
                                {% endfor %}

                                </tbody>
                            </table>
                        </div>
                    </form>

                    <div class="mt-2 mb-2">
                        {% include 'layouts/paging.volt' %}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#checkedAll").change(function () {
            if (this.checked) {
                $(".checkSingle").each(function () {
                    this.checked = true;
                });
            } else {
                $(".checkSingle").each(function () {
                    this.checked = false;
                });
            }
        });

        $(".checkSingle").click(function () {
            if ($(this).is(":checked")) {
                let isAllChecked = 0;

                $(".checkSingle").each(function () {
                    if (!this.checked)
                        isAllChecked = 1;
                });

                if (isAllChecked == 0) {
                    $("#checkedAll").prop("checked", true);
                }
            } else {
                $("#checkedAll").prop("checked", false);
            }
        });
    })
</script>